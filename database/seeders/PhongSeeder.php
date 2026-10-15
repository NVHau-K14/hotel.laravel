<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhongSeeder extends Seeder
{
    public function run(): void
    {
        // Tầng 1: Bình Dân, tầng 2: Thương Gia, tầng 3: Vip. TrangThai để mặc định 'Trống'.
        $loaiTheoTang = [1 => 1, 2 => 2, 3 => 3];

        $rows = [];
        foreach ($loaiTheoTang as $tang => $maLoaiPhong) {
            for ($so = 1; $so <= 5; $so++) {
                $maPhong = $tang * 100 + $so;
                $rows[] = [
                    'MaPhong' => $maPhong,
                    'MaLoaiPhong' => $maLoaiPhong,
                    'HinhAnhDaiDien' => "/Images/ChiTietPhong/Phong{$maPhong}/01.jpg",
                ];
            }
        }

        DB::table('PHONG')->insert($rows);
    }
}
