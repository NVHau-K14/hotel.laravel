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
            ['ID' => 1, 'NoiDung' => 'Phòng sạch sẽ, nhân viên hỗ trợ rất nhiệt tình', 'NgayDanhGia' => today()->subDays(7)->toDateString(), 'MaPhong' => 101, 'MaKhachHang' => 1, 'MaCTDatPhong' => 1, 'SoSao' => 5],
            ['ID' => 2, 'NoiDung' => 'Giao diện đặt phòng dễ dùng, phòng view đẹp', 'NgayDanhGia' => today()->subDays(3)->toDateString(), 'MaPhong' => 102, 'MaKhachHang' => 2, 'MaCTDatPhong' => 2, 'SoSao' => 5],
        ]);
    }
}
