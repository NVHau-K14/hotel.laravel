<?php

namespace App\Services;

use App\Models\CtDichVu;
use App\Models\DatPhong;
use App\Models\HoaDon;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Nghiệp vụ hóa đơn (chuyển từ models/HoaDonModel.php).
 */
class HoaDonService
{
    /** Giới hạn của cột INT trong MySQL */
    public const GIOI_HAN_INT = 2147483647;

    public function __construct(private DatPhongService $datPhongService) {}

    /**
     * Tính tiền của đơn: số ngày, tiền phòng, dịch vụ, tổng cộng.
     * Hóa đơn đã thanh toán thì trả về bản chụp ChiTietJSON (không tính lại).
     * Kết quả có khóa: booking, days, roomRate, roomTotal, services, serviceTotal, grandTotal.
     */
    public function calculateBookingTotal(int $maCTDatPhong): array
    {
        $datPhong = DatPhong::with(['khachHang', 'phong.loaiPhong', 'nhanVien'])->find($maCTDatPhong);
        if (! $datPhong) {
            return [];
        }

        $hoaDon = HoaDon::layTheoDon($maCTDatPhong);
        if ($hoaDon && $hoaDon->daThanhToan() && ! empty($hoaDon->getRawOriginal('ChiTietJSON'))) {
            $banChup = $hoaDon->ChiTietJSON;
            if (is_array($banChup)) {
                return $banChup + ['booking' => $datPhong];
            }
        }

        $this->datPhongService->validateStay(
            $datPhong->NgayNhan?->toDateString() ?? '',
            $datPhong->NgayTra?->toDateString() ?? '',
            true
        );

        $soNgay = max(1, (int) $datPhong->NgayNhan->diffInDays($datPhong->NgayTra));
        $giaPhong = (int) ($datPhong->GiaPhong ?? 0);
        $tienPhong = $soNgay * $giaPhong;

        // Dạng mảng để lưu vào ChiTietJSON giống hệt bản gốc
        $dichVus = CtDichVu::cuaDon($maCTDatPhong)->map->toArray()->all();
        $tienDichVu = array_sum(array_map(fn ($dv) => (int) ($dv['TongTienDichVu'] ?? 0), $dichVus));

        return [
            'booking' => $datPhong,
            'days' => $soNgay,
            'roomRate' => $giaPhong,
            'roomTotal' => $tienPhong,
            'services' => $dichVus,
            'serviceTotal' => $tienDichVu,
            'grandTotal' => $tienPhong + $tienDichVu,
        ];
    }

    /**
     * Trả phòng: chỉ đơn đang ở mới được lập hóa đơn trả phòng (DatPhongController::checkout).
     * Trả về mã hóa đơn.
     */
    public function checkout(int $maCTDatPhong, ?int $maNhanVien = null): int
    {
        $datPhong = DatPhong::find($maCTDatPhong);
        if (! $datPhong || $datPhong->TrangThai !== DatPhong::DANG_SU_DUNG) {
            throw new DomainException('Chỉ trả phòng cho đơn đang sử dụng.');
        }

        return $this->createOrUpdateInvoice($maCTDatPhong, $maNhanVien);
    }

    /**
     * Lập hóa đơn mới hoặc cập nhật hóa đơn chưa thanh toán của đơn. Trả về mã hóa đơn.
     */
    public function createOrUpdateInvoice(int $maCTDatPhong, ?int $maNhanVien = null): int
    {
        return DB::transaction(function () use ($maCTDatPhong, $maNhanVien) {
            $datPhong = $this->datPhongService->lockBooking($maCTDatPhong);

            $hoaDon = HoaDon::layTheoDon($maCTDatPhong);
            if ($hoaDon && $hoaDon->daThanhToan()) {
                return $hoaDon->MaHoaDon;
            }

            if (! in_array($datPhong->TrangThai, [DatPhong::DANG_SU_DUNG, DatPhong::DA_XAC_NHAN], true)) {
                throw new DomainException('Không thể lập hóa đơn cho đơn chưa xác nhận, đã hủy hoặc đã kết thúc.');
            }

            $chiTiet = $this->calculateBookingTotal($maCTDatPhong);
            $tongTien = $chiTiet['grandTotal'] ?? 0;
            if ($tongTien > self::GIOI_HAN_INT) {
                throw new DomainException('Tổng hóa đơn vượt giới hạn cho phép.');
            }
            unset($chiTiet['booking']);

            if ($hoaDon) {
                $hoaDon->SoTienThu = $tongTien;
                $hoaDon->ChiTietJSON = $chiTiet;
                if ($maNhanVien !== null) {
                    $hoaDon->MaNhanVien = $maNhanVien;
                }
                $hoaDon->save();

                return $hoaDon->MaHoaDon;
            }

            return HoaDon::create([
                'NgayThuTien' => today()->toDateString(),
                'SoTienThu' => $tongTien,
                'MaCTDatPhong' => $maCTDatPhong,
                'MaNhanVien' => $maNhanVien,
                'TrangThai' => HoaDon::CHUA_THANH_TOAN,
                'ChiTietJSON' => $chiTiet,
            ])->MaHoaDon;
        });
    }

    /**
     * Xác nhận thanh toán: lưu bản chụp cuối, đánh dấu payment_status và hoàn tất đơn.
     */
    public function payInvoice(int $maHoaDon): bool
    {
        return DB::transaction(function () use ($maHoaDon) {
            $hoaDon = HoaDon::find($maHoaDon);
            if (! $hoaDon) {
                throw new DomainException('Hóa đơn không tồn tại.');
            }

            $datPhong = $this->datPhongService->lockBooking((int) $hoaDon->MaCTDatPhong);
            $hoaDon->refresh();
            if ($hoaDon->daThanhToan()) {
                return true;
            }

            $daTraBangHoaDonKhac = HoaDon::where('MaCTDatPhong', $hoaDon->MaCTDatPhong)
                ->where('TrangThai', HoaDon::DA_THANH_TOAN)
                ->where('MaHoaDon', '<>', $maHoaDon)
                ->exists();
            if ($daTraBangHoaDonKhac) {
                throw new DomainException('Đơn này đã được thanh toán bằng hóa đơn khác.');
            }
            if ($datPhong->TrangThai !== DatPhong::DANG_SU_DUNG) {
                throw new DomainException('Chỉ thanh toán trả phòng cho đơn đang sử dụng.');
            }

            $chiTiet = $this->calculateBookingTotal((int) $hoaDon->MaCTDatPhong);
            unset($chiTiet['booking']);
            if ($chiTiet['grandTotal'] > self::GIOI_HAN_INT) {
                throw new DomainException('Tổng hóa đơn vượt giới hạn cho phép.');
            }

            $hoaDon->update([
                'TrangThai' => HoaDon::DA_THANH_TOAN,
                'NgayThuTien' => today()->toDateString(),
                'SoTienThu' => $chiTiet['grandTotal'],
                'ChiTietJSON' => $chiTiet,
            ]);
            $datPhong->update(['payment_status' => DatPhong::THANH_TOAN_XONG]);

            // Đơn chuyển sang DA_HOAN_THANH, phòng được giải phóng
            $this->datPhongService->updateStatus((int) $hoaDon->MaCTDatPhong, DatPhong::DA_HOAN_THANH);

            return true;
        });
    }
}
