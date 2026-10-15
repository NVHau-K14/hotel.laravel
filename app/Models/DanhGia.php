<?php

namespace App\Models;

use App\Services\DatPhongService;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Bảng CTDANHGIA. Khóa chính tên "ID". Đánh giá do admin thêm không gắn khách/đơn.
 */
class DanhGia extends Model
{
    protected $table = 'CTDANHGIA';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = ['NoiDung', 'NgayDanhGia', 'MaPhong', 'MaKhachHang', 'MaCTDatPhong', 'SoSao'];

    protected function casts(): array
    {
        return [
            'NgayDanhGia' => 'date',
            'SoSao' => 'integer',
        ];
    }

    public function phong(): BelongsTo
    {
        return $this->belongsTo(Phong::class, 'MaPhong', 'MaPhong');
    }

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'MaKhachHang', 'MaKhachHang');
    }

    public function datPhong(): BelongsTo
    {
        return $this->belongsTo(DatPhong::class, 'MaCTDatPhong', 'MaCTDatPhong');
    }

    /**
     * Thêm đánh giá (DanhGiaModel::create). Số sao ép về 1–5, ngày trống thì lấy hôm nay.
     * Nếu gắn với đơn: đơn phải hoàn tất, đúng khách, đúng phòng và chưa được đánh giá.
     */
    public static function taoDanhGia(string $noiDung, ?string $ngayDanhGia, int $maPhong, int $soSao = 5, ?int $maKhachHang = null, ?int $maCTDatPhong = null): self
    {
        return DB::transaction(function () use ($noiDung, $ngayDanhGia, $maPhong, $soSao, $maKhachHang, $maCTDatPhong) {
            if ($maCTDatPhong !== null) {
                // Khóa đơn để hai lần gửi cùng lúc không tạo hai đánh giá
                $datPhong = app(DatPhongService::class)->lockBooking($maCTDatPhong);

                if ($datPhong->TrangThai !== DatPhong::DA_HOAN_THANH
                    || (int) $datPhong->MaKhachHang !== $maKhachHang
                    || (int) $datPhong->MaPhong !== $maPhong) {
                    throw new DomainException('Chỉ đánh giá lần lưu trú đã hoàn tất của chính bạn.');
                }

                if (self::where('MaCTDatPhong', $maCTDatPhong)->exists()) {
                    throw new DomainException('Bạn đã đánh giá lần lưu trú này.');
                }
            }

            return self::create([
                'NoiDung' => $noiDung,
                'NgayDanhGia' => $ngayDanhGia ?: today()->toDateString(),
                'MaPhong' => $maPhong,
                'MaKhachHang' => $maKhachHang,
                'MaCTDatPhong' => $maCTDatPhong,
                'SoSao' => max(1, min(5, $soSao)),
            ]);
        });
    }

    /**
     * Sửa đánh giá (DanhGiaModel::update). Đánh giá gắn với đơn không được đổi sang phòng khác.
     */
    public function capNhat(string $noiDung, ?string $ngayDanhGia, int $maPhong, int $soSao = 5): void
    {
        if ($this->MaCTDatPhong && (int) $this->MaPhong !== $maPhong) {
            throw new DomainException('Không thể chuyển đánh giá của khách sang phòng khác.');
        }

        $this->update([
            'NoiDung' => $noiDung,
            'NgayDanhGia' => $ngayDanhGia ?: today()->toDateString(),
            'MaPhong' => $maPhong,
            'SoSao' => max(1, min(5, $soSao)),
        ]);
    }

    /**
     * Phòng khách được phép đánh giá: có đơn hoàn tất, đã qua ngày trả, đơn chưa có đánh giá
     * (DanhGiaModel::getReviewableRoomsByCustomer).
     */
    public static function phongCoTheDanhGia(int $maKhachHang): Collection
    {
        return Phong::query()
            ->with('loaiPhong')
            ->whereHas('datPhongs', fn (Builder $q) => self::locDonCoTheDanhGia($q, $maKhachHang))
            ->orderBy('MaPhong')
            ->get();
    }

    /**
     * Đơn gần nhất của khách ở phòng này còn được đánh giá (DanhGiaModel::getReviewableBooking).
     */
    public static function donCoTheDanhGia(int $maKhachHang, int $maPhong): ?DatPhong
    {
        $query = DatPhong::query()->where('MaPhong', $maPhong);
        self::locDonCoTheDanhGia($query, $maKhachHang);

        return $query->orderByDesc('MaCTDatPhong')->first();
    }

    private static function locDonCoTheDanhGia(Builder $query, int $maKhachHang): void
    {
        $query->where('MaKhachHang', $maKhachHang)
            ->where('NgayTra', '<=', today()->toDateString())
            ->where('TrangThai', DatPhong::DA_HOAN_THANH)
            ->whereDoesntHave('danhGias');
    }
}
