<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Bảng CTDATPHONG — đơn đặt phòng.
 * Logic đặt phòng / chuyển trạng thái nằm ở App\Services\DatPhongService.
 */
class DatPhong extends Model
{
    public const CHO_ADMIN_DUYET = 'CHO_ADMIN_DUYET';

    public const DA_XAC_NHAN = 'DA_XAC_NHAN';

    public const DANG_SU_DUNG = 'DANG_SU_DUNG';

    public const DA_HOAN_THANH = 'DA_HOAN_THANH';

    public const DA_HUY = 'DA_HUY';

    /** Các trạng thái đang giữ phòng (dùng khi kiểm tra trùng lịch) */
    public const TRANG_THAI_GIU_PHONG = [self::CHO_ADMIN_DUYET, self::DA_XAC_NHAN, self::DANG_SU_DUNG];

    /** Luồng chuyển trạng thái hợp lệ: trạng thái hiện tại => các trạng thái được chuyển sang */
    public const LUONG_TRANG_THAI = [
        self::CHO_ADMIN_DUYET => [self::DA_XAC_NHAN, self::DA_HUY],
        self::DA_XAC_NHAN => [self::DANG_SU_DUNG, self::DA_HUY],
        self::DANG_SU_DUNG => [self::DA_HOAN_THANH],
    ];

    /** Giá trị cột payment_status */
    public const THANH_TOAN_CHUA_TAO = 'CHUA_TAO';

    public const THANH_TOAN_XONG = 'DA_THANH_TOAN';

    protected $table = 'CTDATPHONG';

    protected $primaryKey = 'MaCTDatPhong';

    public $timestamps = false;

    protected $fillable = [
        'NgayThucHien',
        'MaKhachHang',
        'NgayNhan',
        'NgayTra',
        'MaPhong',
        'MaNhanVien',
        'TrangThai',
        'GiaPhongDat',
        'payment_status',
        'payment_requested_at',
        'payment_expired_at',
    ];

    protected function casts(): array
    {
        return [
            'NgayThucHien' => 'date',
            'NgayNhan' => 'date',
            'NgayTra' => 'date',
            'GiaPhongDat' => 'integer',
            'payment_requested_at' => 'datetime',
            'payment_expired_at' => 'datetime',
        ];
    }

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'MaKhachHang', 'MaKhachHang');
    }

    public function phong(): BelongsTo
    {
        return $this->belongsTo(Phong::class, 'MaPhong', 'MaPhong');
    }

    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(NhanVien::class, 'MaNhanVien', 'MaNhanVien');
    }

    public function hoaDons(): HasMany
    {
        return $this->hasMany(HoaDon::class, 'MaCTDatPhong', 'MaCTDatPhong');
    }

    public function danhGias(): HasMany
    {
        return $this->hasMany(DanhGia::class, 'MaCTDatPhong', 'MaCTDatPhong');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id', 'MaCTDatPhong');
    }

    /** Các dòng CTDICHVU của đơn */
    public function ctDichVus(): HasMany
    {
        return $this->hasMany(CtDichVu::class, 'MaCTDatPhong', 'MaCTDatPhong');
    }

    /** Dịch vụ đã dùng, qua bảng trung gian CTDICHVU */
    public function dichVus(): BelongsToMany
    {
        return $this->belongsToMany(DichVu::class, 'CTDICHVU', 'MaCTDatPhong', 'MaDichVu')
            ->withPivot('MaCTDichVu', 'SoLuong', 'TongTienDichVu');
    }

    /**
     * $datPhong->GiaPhong: giá lúc đặt, nếu chưa lưu thì lấy giá hiện tại của loại phòng
     * (COALESCE(GiaPhongDat, lp.GiaPhong) của bản gốc). Nên eager load "phong.loaiPhong".
     */
    protected function giaPhong(): Attribute
    {
        return Attribute::get(fn () => $this->GiaPhongDat ?? $this->phong?->loaiPhong?->GiaPhong);
    }

    /**
     * Lọc theo trạng thái và từ khóa (mã đơn, tên/SĐT khách, mã phòng), mới nhất trước
     * (DatPhongModel::getAll).
     */
    public function scopeLoc(Builder $query, ?string $trangThai = null, ?string $keyword = null): void
    {
        if ($trangThai !== null && $trangThai !== '' && $trangThai !== 'ALL') {
            $query->where('TrangThai', $trangThai);
        }

        if ($keyword !== null && trim($keyword) !== '') {
            $tuKhoa = '%'.trim($keyword).'%';
            $query->where(function (Builder $q) use ($tuKhoa) {
                $q->where('MaCTDatPhong', 'like', $tuKhoa)
                    ->orWhere('MaPhong', 'like', $tuKhoa)
                    ->orWhereHas('khachHang', fn (Builder $kh) => $kh->where('HoTen', 'like', $tuKhoa)
                        ->orWhere('DienThoai', 'like', $tuKhoa));
            });
        }

        $query->orderByDesc('MaCTDatPhong');
    }

    /**
     * Đếm đơn theo trạng thái (DatPhongModel::countByStatus).
     *
     * @return array{choDuyet: int, daXacNhan: int, dangSuDung: int, daHoanThanh: int, daHuy: int, tongDon: int}
     */
    public static function demTheoTrangThai(): array
    {
        $row = DB::table('CTDATPHONG')->selectRaw(
            'SUM(CASE WHEN TrangThai = ? THEN 1 ELSE 0 END) AS choDuyet,
             SUM(CASE WHEN TrangThai = ? THEN 1 ELSE 0 END) AS daXacNhan,
             SUM(CASE WHEN TrangThai = ? THEN 1 ELSE 0 END) AS dangSuDung,
             SUM(CASE WHEN TrangThai = ? THEN 1 ELSE 0 END) AS daHoanThanh,
             SUM(CASE WHEN TrangThai = ? THEN 1 ELSE 0 END) AS daHuy,
             COUNT(*) AS tongDon',
            [self::CHO_ADMIN_DUYET, self::DA_XAC_NHAN, self::DANG_SU_DUNG, self::DA_HOAN_THANH, self::DA_HUY]
        )->first();

        return array_map('intval', (array) $row);
    }
}
