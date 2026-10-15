<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DanhGiaSeeder extends Seeder
{
    public function run(): void
    {
        // Bảng CTDANHGIA
        DB::table('CTDANHGIA')->insert([
            ['ID' => 1, 'NoiDung' => 'Phòng sạch sẽ, nhân viên hỗ trợ rất nhiệt tình', 'NgayDanhGia' => '2026-05-07', 'MaPhong' => 101, 'MaKhachHang' => 1, 'MaCTDatPhong' => 1, 'SoSao' => 5],
            ['ID' => 2, 'NoiDung' => 'Giao diện đặt phòng dễ dùng, phòng view đẹp', 'NgayDanhGia' => '2026-05-08', 'MaPhong' => 102, 'MaKhachHang' => 2, 'MaCTDatPhong' => 2, 'SoSao' => 5],
        ]);
    }
}
