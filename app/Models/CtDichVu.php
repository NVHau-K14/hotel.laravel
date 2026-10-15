<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bảng CTDICHVU — dịch vụ dùng trong đơn đặt phòng.
 *
 * Khóa chính thật là (MaDichVu, MaCTDatPhong). Eloquent không hỗ trợ khóa ghép nên
 * $primaryKey = null: KHÔNG gọi save()/delete() trên một bản ghi đã có, hãy cập nhật/xóa
 * bằng where('MaDichVu', ...)->where('MaCTDatPhong', ...). MaCTDichVu chỉ là số hiển thị.
 * Logic thêm/sửa/xóa nằm ở App\Services\CtDichVuService.
 */
class CtDichVu extends Model
{
    protected $table = 'CTDICHVU';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['MaCTDichVu', 'MaDichVu', 'SoLuong', 'MaCTDatPhong', 'TongTienDichVu'];

    public function dichVu(): BelongsTo
    {
        return $this->belongsTo(DichVu::class, 'MaDichVu', 'MaDichVu');
    }

    public function datPhong(): BelongsTo
    {
        return $this->belongsTo(DatPhong::class, 'MaCTDatPhong', 'MaCTDatPhong');
    }

    /**
     * Kèm tên, ảnh dịch vụ và đơn giá. Đơn giá = TongTienDichVu / SoLuong (giá lúc thêm),
     * nếu không tính được thì lấy giá hiện tại của dịch vụ.
     */
    public function scopeKemDonGia(Builder $query): void
    {
        $query->join('DICHVU', 'DICHVU.MaDichVu', '=', 'CTDICHVU.MaDichVu')
            ->select('CTDICHVU.*', 'DICHVU.TenDichVu')
            ->selectRaw('COALESCE(CTDICHVU.TongTienDichVu / NULLIF(CTDICHVU.SoLuong, 0), DICHVU.GiaDichVu) AS GiaDichVu')
            ->addSelect('DICHVU.HinhAnh');
    }

    /**
     * Một dòng theo khóa ghép, kèm đơn giá (CtDichVuModel::getById).
     */
    public static function timTheoKhoa(int $maDichVu, int $maCTDatPhong): ?self
    {
        return self::query()->kemDonGia()
            ->where('CTDICHVU.MaDichVu', $maDichVu)
            ->where('CTDICHVU.MaCTDatPhong', $maCTDatPhong)
            ->first();
    }

    /**
     * Dịch vụ của một đơn, sắp theo tên dịch vụ (DichVuModel::getServicesByBooking).
     */
    public static function cuaDon(int $maCTDatPhong): Collection
    {
        return self::query()->kemDonGia()
            ->where('CTDICHVU.MaCTDatPhong', $maCTDatPhong)
            ->orderBy('DICHVU.TenDichVu')
            ->get();
    }

    /**
     * Toàn bộ chi tiết dịch vụ kèm phòng và ngày lưu trú (CtDichVuModel::getAll).
     */
    public static function danhSach(): Collection
    {
        return self::query()
            ->leftJoin('DICHVU', 'DICHVU.MaDichVu', '=', 'CTDICHVU.MaDichVu')
            ->leftJoin('CTDATPHONG', 'CTDATPHONG.MaCTDatPhong', '=', 'CTDICHVU.MaCTDatPhong')
            ->select('CTDICHVU.*', 'DICHVU.TenDichVu')
            ->selectRaw('COALESCE(CTDICHVU.TongTienDichVu / NULLIF(CTDICHVU.SoLuong, 0), DICHVU.GiaDichVu) AS GiaDichVu')
            ->addSelect('CTDATPHONG.MaPhong', 'CTDATPHONG.NgayNhan', 'CTDATPHONG.NgayTra')
            ->orderByDesc('CTDICHVU.MaCTDatPhong')
            ->orderBy('CTDICHVU.MaDichVu')
            ->get();
    }

    /**
     * Cập nhật một dòng theo khóa ghép.
     */
    public static function capNhatTheoKhoa(int $maDichVu, int $maCTDatPhong, array $giaTri): int
    {
        return self::query()
            ->where('MaDichVu', $maDichVu)
            ->where('MaCTDatPhong', $maCTDatPhong)
            ->update($giaTri);
    }

    /**
     * Xóa một dòng theo khóa ghép.
     */
    public static function xoaTheoKhoa(int $maDichVu, int $maCTDatPhong): int
    {
        return self::query()
            ->where('MaDichVu', $maDichVu)
            ->where('MaCTDatPhong', $maCTDatPhong)
            ->delete();
    }
}
