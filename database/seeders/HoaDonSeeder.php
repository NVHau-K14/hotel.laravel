<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HoaDonSeeder extends Seeder
{
    public function run(): void
    {
        // Giữ y nguyên dữ liệu mẫu: đơn #2 và #3 mỗi đơn có 2 hóa đơn, tất cả CHUA_THANH_TOAN (mặc định).
        DB::table('HOADON')->insert([
            ['MaHoaDon' => 1, 'NgayThuTien' => '2026-05-07', 'SoTienThu' => 150000, 'MaCTDatPhong' => 2, 'MaNhanVien' => 2],
            ['MaHoaDon' => 2, 'NgayThuTien' => '2026-05-08', 'SoTienThu' => 200000, 'MaCTDatPhong' => 2, 'MaNhanVien' => 2],
            ['MaHoaDon' => 3, 'NgayThuTien' => '2026-05-10', 'SoTienThu' => 3000000, 'MaCTDatPhong' => 3, 'MaNhanVien' => 2],
            ['MaHoaDon' => 4, 'NgayThuTien' => '2026-05-10', 'SoTienThu' => 350000, 'MaCTDatPhong' => 3, 'MaNhanVien' => 2],
            ['MaHoaDon' => 5, 'NgayThuTien' => '2026-05-13', 'SoTienThu' => 350000, 'MaCTDatPhong' => 5, 'MaNhanVien' => 2],
        ]);
    }
}
