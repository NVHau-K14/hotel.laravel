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

    /** 4 trạng thái hợp lệ khi thêm/sửa phòng */
    public const TRANG_THAI = [self::TRONG, self::DA_DAT, self::DANG_THUE, self::BAO_TRI];

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
     * Lọc theo mã phòng / tên loại, loại phòng, khoảng giá (PhongModel::getAll), sắp theo mã phòng.
     */
    public function scopeLoc(Builder $query, ?string $keyword = null, ?int $maLoaiPhong = null, ?int $giaTu = null, ?int $giaDen = null): void
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

        if ($giaTu !== null && $giaTu > 0) {
            $query->whereHas('loaiPhong', fn (Builder $lp) => $lp->where('GiaPhong', '>=', $giaTu));
        }

        if ($giaDen !== null && $giaDen > 0) {
            $query->whereHas('loaiPhong', fn (Builder $lp) => $lp->where('GiaPhong', '<=', $giaDen));
        }

        $query->orderBy('MaPhong');
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
                    ->whereNotIn('TrangThai', [DatPhong::DA_HUY, DatPhong::DA_HOAN_THANH])
                    ->where('NgayTra', '>', $ngayNhan)
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
     * Không cho đổi loại hoặc trạng thái khi phòng đang có lịch đặt.
     */
    public function kiemTraDoiLoaiHoacTrangThai(int $maLoaiPhongMoi, string $trangThaiMoi): void
    {
        $coThayDoi = $trangThaiMoi !== $this->TrangThai || $maLoaiPhongMoi !== (int) $this->MaLoaiPhong;

        if ($coThayDoi && $this->coDonDangHoatDong()) {
            throw new DomainException('Phòng đang có lịch đặt; hãy xử lý đơn trước khi đổi loại hoặc trạng thái phòng.');
        }
    }
}
