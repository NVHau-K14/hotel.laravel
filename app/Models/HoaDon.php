<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bảng HOADON. ChiTietJSON là bản chụp chi tiết (tiền phòng, dịch vụ, tổng) lúc lập/thanh toán.
 * Logic lập hóa đơn / thanh toán nằm ở App\Services\HoaDonService.
 */
class HoaDon extends Model
{
    public const CHUA_THANH_TOAN = 'CHUA_THANH_TOAN';

    public const DA_THANH_TOAN = 'DA_THANH_TOAN';

    /** Hóa đơn chưa thanh toán của đơn đã bị hủy */
    public const DA_HUY = 'DA_HUY';

    protected $table = 'HOADON';

    protected $primaryKey = 'MaHoaDon';

    public $timestamps = false;

    protected $fillable = ['NgayThuTien', 'SoTienThu', 'ChiTietJSON', 'MaCTDatPhong', 'MaNhanVien', 'TrangThai'];

    protected function casts(): array
    {
        return [
            'NgayThuTien' => 'date',
            'SoTienThu' => 'integer',
            // Lưu JSON giữ nguyên chữ tiếng Việt (JSON_UNESCAPED_UNICODE như bản gốc)
            'ChiTietJSON' => 'json:unicode',
        ];
    }

    public function datPhong(): BelongsTo
    {
        return $this->belongsTo(DatPhong::class, 'MaCTDatPhong', 'MaCTDatPhong');
    }

    public function nhanVien(): BelongsTo
    {
        return $this->belongsTo(NhanVien::class, 'MaNhanVien', 'MaNhanVien');
    }

    public function daThanhToan(): bool
    {
        return $this->TrangThai === self::DA_THANH_TOAN;
    }

    public function daHuy(): bool
    {
        return $this->TrangThai === self::DA_HUY;
    }

    /**
     * Chỉ thanh toán được hóa đơn chưa thanh toán của đơn đang ở (trả phòng).
     */
    public function thanhToanDuoc(): bool
    {
        return $this->TrangThai === self::CHUA_THANH_TOAN
            && $this->datPhong?->TrangThai === DatPhong::DANG_SU_DUNG;
    }

    /**
     * Hóa đơn chính của một đơn: ưu tiên hóa đơn đã thanh toán, sau đó hóa đơn mới nhất
     * (HoaDonModel::getByBookingId).
     */
    public static function layTheoDon(int $maCTDatPhong): ?self
    {
        return self::query()
            ->where('MaCTDatPhong', $maCTDatPhong)
            ->where('TrangThai', '<>', self::DA_HUY)
            ->orderByRaw('(TrangThai = ?) DESC', [self::DA_THANH_TOAN])
            ->orderByDesc('MaHoaDon')
            ->first();
    }
}
