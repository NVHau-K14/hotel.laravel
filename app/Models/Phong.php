<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Bảng PHONG. MaPhong do người dùng nhập (101, 102, ...) nên không tự tăng.
 */
class Phong extends Model
{
    public const TRONG = 'Trống';

    public const DA_DAT = 'Đã đặt';

    public const DANG_THUE = 'Đang thuê';

    public const BAO_TRI = 'Bảo trì';

    /** Giá trị cũ, không còn chọn được trên form nhưng thống kê vẫn đếm (lỗi L1) */
    public const DANG_SU_DUNG = 'Đang sử dụng';

    /** 4 trạng thái của phòng */
    public const TRANG_THAI = [self::TRONG, self::DA_DAT, self::DANG_THUE, self::BAO_TRI];

    /**
     * Trạng thái admin được chọn khi thêm/sửa phòng. "Đã đặt" / "Đang thuê" do hệ thống
     * tự tính từ đơn đặt phòng (DatPhongService::syncRoomStatus).
     */
    public const TRANG_THAI_CHON_TAY = [self::TRONG, self::BAO_TRI];

    /**
     * Khoảng giá cho ô lọc phòng: mã => [nhãn, giá từ (>=), giá đến (<)]. null = không giới hạn.
     */
    public const KHOANG_GIA = [
        'duoi-500k' => ['Dưới 500.000 đ', null, 500000],
        '500k-2tr' => ['500.000 đ – 2.000.000 đ', 500000, 2000000],
        '2tr-5tr' => ['2.000.000 đ – 5.000.000 đ', 2000000, 5000000],
        'tren-5tr' => ['Từ 5.000.000 đ trở lên', 5000000, null],
    ];

    protected $table = 'PHONG';

    protected $primaryKey = 'MaPhong';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['MaPhong', 'MaLoaiPhong', 'HinhAnhDaiDien', 'TrangThai'];

    public function loaiPhong(): BelongsTo
    {
        return $this->belongsTo(LoaiPhong::class, 'MaLoaiPhong', 'MaLoaiPhong');
    }

    public function datPhongs(): HasMany
    {
        return $this->hasMany(DatPhong::class, 'MaPhong', 'MaPhong');
    }

    public function danhGias(): HasMany
    {
        return $this->hasMany(DanhGia::class, 'MaPhong', 'MaPhong');
    }

    /**
     * Lọc theo mã phòng / tên loại, loại phòng, khoảng giá (mã trong KHOANG_GIA), sắp theo mã phòng.
     */
    public function scopeLoc(Builder $query, ?string $keyword = null, ?int $maLoaiPhong = null, ?string $khoangGia = null): void
    {
        if ($keyword !== null && trim($keyword) !== '') {
            $tuKhoa = '%'.trim($keyword).'%';
            $query->where(function (Builder $q) use ($tuKhoa) {
                $q->where('MaPhong', 'like', $tuKhoa)
                    ->orWhereHas('loaiPhong', fn (Builder $lp) => $lp->where('TenLoaiPhong', 'like', $tuKhoa));
            });
        }

        if ($maLoaiPhong !== null && $maLoaiPhong > 0) {
            $query->where('MaLoaiPhong', $maLoaiPhong);
        }

        if ($khoangGia !== null && isset(self::KHOANG_GIA[$khoangGia])) {
            [, $giaTu, $giaDen] = self::KHOANG_GIA[$khoangGia];
            if ($giaTu !== null) {
                $query->whereHas('loaiPhong', fn (Builder $lp) => $lp->where('GiaPhong', '>=', $giaTu));
            }
            if ($giaDen !== null) {
                $query->whereHas('loaiPhong', fn (Builder $lp) => $lp->where('GiaPhong', '<', $giaDen));
            }
        }

        $query->orderBy('MaPhong');
    }

    /**
     * Phòng thuộc loại phòng chưa bị xóa (phía khách chỉ thấy các phòng này).
     */
    public function scopeDangKinhDoanh(Builder $query): void
    {
        $query->whereHas('loaiPhong', fn (Builder $lp) => $lp->conHoatDong());
    }

    /**
     * Phòng nhận đặt mới được không: không bảo trì và loại phòng chưa bị xóa.
     */
    public function nhanDatPhong(): bool
    {
        return $this->TrangThai !== self::BAO_TRI && $this->loaiPhong && ! $this->loaiPhong->DaXoa;
    }

    /**
     * Chỉ lấy phòng còn trống trong khoảng ngày: không bảo trì, loại phòng chưa xóa,
     * không có đơn (chưa hủy/chưa hoàn thành) chồng lên khoảng ngày này.
     * Ngày phải được kiểm tra hợp lệ trước khi gọi.
     */
    public function scopeConTrong(Builder $query, string $ngayNhan, string $ngayTra): void
    {
        $query->where(fn (Builder $q) => $q->whereNull('TrangThai')->orWhere('TrangThai', '<>', self::BAO_TRI))
            ->whereDoesntHave('loaiPhong', fn (Builder $lp) => $lp->where('DaXoa', true))
            ->whereNotIn('MaPhong', function (QueryBuilder $sub) use ($ngayNhan, $ngayTra) {
                $sub->select('MaPhong')
                    ->from('CTDATPHONG')
                    ->whereNotNull('MaPhong')
                    ->whereIn('TrangThai', DatPhong::TRANG_THAI_GIU_PHONG)
                    ->whereRaw(DatPhong::SQL_NGAY_TRA_THUC_TE.' > ?', [today()->addDay()->toDateString(), $ngayNhan])
                    ->where('NgayNhan', '<', $ngayTra);
            });
    }

    /**
     * Đếm phòng theo trạng thái (PhongModel::countByStatus).
     *
     * @return array{phongTrong: int, phongDangThue: int, phongBaoTri: int, tongPhong: int}
     */
    public static function demTheoTrangThai(): array
    {
        $row = DB::table('PHONG')->selectRaw(
            'SUM(CASE WHEN TrangThai = ? OR TrangThai IS NULL THEN 1 ELSE 0 END) AS phongTrong,
             SUM(CASE WHEN TrangThai IN (?, ?, ?) THEN 1 ELSE 0 END) AS phongDangThue,
             SUM(CASE WHEN TrangThai = ? THEN 1 ELSE 0 END) AS phongBaoTri,
             COUNT(*) AS tongPhong',
            [self::TRONG, self::DANG_THUE, self::DANG_SU_DUNG, self::DA_DAT, self::BAO_TRI]
        )->first();

        return [
            'phongTrong' => (int) $row->phongTrong,
            'phongDangThue' => (int) $row->phongDangThue,
            'phongBaoTri' => (int) $row->phongBaoTri,
            'tongPhong' => (int) $row->tongPhong,
        ];
    }

    /**
     * Phòng có đơn đang hoạt động (chờ duyệt / đã xác nhận còn hạn, hoặc đang ở).
     */
    public function coDonDangHoatDong(): bool
    {
        return $this->datPhongs()
            ->whereIn('TrangThai', DatPhong::TRANG_THAI_GIU_PHONG)
            ->where(fn (Builder $q) => $q->where('NgayTra', '>', today()->toDateString())
                ->orWhere('TrangThai', DatPhong::DANG_SU_DUNG))
            ->exists();
    }

    /**
     * Không cho đổi loại phòng, hoặc bật/tắt Bảo trì, khi phòng đang có lịch đặt.
     */
    public function kiemTraDoiLoaiHoacTrangThai(int $maLoaiPhongMoi, string $trangThaiMoi): void
    {
        $doiBaoTri = ($trangThaiMoi === self::BAO_TRI) !== ($this->TrangThai === self::BAO_TRI);
        $coThayDoi = $doiBaoTri || $maLoaiPhongMoi !== (int) $this->MaLoaiPhong;

        if ($coThayDoi && $this->coDonDangHoatDong()) {
            throw new DomainException('Phòng đang có lịch đặt; hãy xử lý đơn trước khi đổi loại hoặc trạng thái phòng.');
        }
    }
}
