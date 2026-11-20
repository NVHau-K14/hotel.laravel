<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HoaDonSeeder extends Seeder
{
    /**
     * Mỗi đơn đã hoàn tất có đúng một hóa đơn đã thanh toán (2 đêm × 300.000 đ), kèm bản chụp ChiTietJSON.
     * Đơn đang ở / chưa nhận phòng chưa có hóa đơn (lập khi trả phòng).
     */
    public function run(): void
    {
        $hoaDon = fn (int $ma, int $maDon, int $ngayThu) => [
            'MaHoaDon' => $ma,
            'NgayThuTien' => today()->addDays($ngayThu)->toDateString(),
            'SoTienThu' => 600000,
            'MaCTDatPhong' => $maDon,
            'MaNhanVien' => 2,
            'TrangThai' => 'DA_THANH_TOAN',
            'ChiTietJSON' => json_encode([
                'days' => 2,
                'lateNights' => 0,
                'roomRate' => 300000,
                'roomTotal' => 600000,
                'services' => [],
                'serviceTotal' => 0,
                'grandTotal' => 600000,
            ], JSON_UNESCAPED_UNICODE),
        ];

        DB::table('HOADON')->insert([
            $hoaDon(1, 1, -8),
            $hoaDon(2, 2, -4),
        ]);
    }
}
