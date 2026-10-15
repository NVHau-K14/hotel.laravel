<?php

namespace App\Services;

use App\Models\CtDichVu;
use App\Models\DichVu;
use App\Models\HoaDon;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Thêm/sửa/xóa dịch vụ trong đơn đặt phòng (chuyển từ models/CtDichVuModel.php).
 * Sau mỗi thay đổi, nếu đơn đã có hóa đơn (chưa thanh toán) thì tính lại hóa đơn.
 */
class CtDichVuService
{
    public const SO_LUONG_TOI_DA = 10000;

    public function __construct(
        private DatPhongService $datPhongService,
        private HoaDonService $hoaDonService,
    ) {}

    /**
     * Đặt số lượng của một dịch vụ trong đơn (thêm mới nếu chưa có).
     * Dòng đã có thì giữ đơn giá cũ; dòng mới lấy giá hiện tại của dịch vụ.
     */
    public function save(int $maDichVu, int $maCTDatPhong, int $soLuong, ?int $maCTDichVu = null): bool
    {
        return DB::transaction(function () use ($maDichVu, $maCTDatPhong, $soLuong, $maCTDichVu) {
            $this->datPhongService->assertServiceEditable($maCTDatPhong);

            if ($soLuong < 1 || $soLuong > self::SO_LUONG_TOI_DA) {
                throw new DomainException('Số lượng dịch vụ phải từ 1 đến 10.000.');
            }

            $dichVu = DichVu::find($maDichVu);
            if (! $dichVu) {
                throw new DomainException('Dịch vụ không tồn tại.');
            }

            $daCo = CtDichVu::timTheoKhoa($maDichVu, $maCTDatPhong);
            $donGia = $daCo ? (float) $daCo->GiaDichVu : (float) $dichVu->GiaDichVu;
            $tongTien = (int) round($donGia * $soLuong);
            if ($tongTien > HoaDonService::GIOI_HAN_INT) {
                throw new DomainException('Tổng tiền dịch vụ vượt giới hạn cho phép.');
            }

            if ($daCo) {
                CtDichVu::capNhatTheoKhoa($maDichVu, $maCTDatPhong, [
                    'SoLuong' => $soLuong,
                    'TongTienDichVu' => $tongTien,
                ]);
            } else {
                CtDichVu::create([
                    // Khóa thật là (MaDichVu, MaCTDatPhong); MaCTDichVu chỉ là số hiển thị
                    'MaCTDichVu' => $maCTDichVu ?: (int) CtDichVu::max('MaCTDichVu') + 1,
                    'MaDichVu' => $maDichVu,
                    'SoLuong' => $soLuong,
                    'MaCTDatPhong' => $maCTDatPhong,
                    'TongTienDichVu' => $tongTien,
                ]);
            }

            $this->refreshInvoice($maCTDatPhong);

            return true;
        });
    }

    /**
     * Cộng thêm số lượng dịch vụ vào đơn.
     */
    public function addToBooking(int $maCTDatPhong, int $maDichVu, int $soLuong = 1): bool
    {
        return DB::transaction(function () use ($maCTDatPhong, $maDichVu, $soLuong) {
            $this->datPhongService->assertServiceEditable($maCTDatPhong);

            if ($soLuong < 1) {
                throw new DomainException('Số lượng dịch vụ phải lớn hơn 0.');
            }

            $daCo = CtDichVu::timTheoKhoa($maDichVu, $maCTDatPhong);

            return $this->save($maDichVu, $maCTDatPhong, (int) ($daCo?->SoLuong ?? 0) + $soLuong);
        });
    }

    /**
     * Xóa một dịch vụ khỏi đơn.
     */
    public function delete(int $maDichVu, int $maCTDatPhong): bool
    {
        return DB::transaction(function () use ($maDichVu, $maCTDatPhong) {
            $this->datPhongService->assertServiceEditable($maCTDatPhong);

            CtDichVu::xoaTheoKhoa($maDichVu, $maCTDatPhong);
            $this->refreshInvoice($maCTDatPhong);

            return true;
        });
    }

    private function refreshInvoice(int $maCTDatPhong): void
    {
        if (HoaDon::layTheoDon($maCTDatPhong)) {
            $this->hoaDonService->createOrUpdateInvoice($maCTDatPhong);
        }
    }
}
