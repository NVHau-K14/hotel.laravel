<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoaiPhongSeeder extends Seeder
{
    public function run(): void
    {
        // DaXoa để mặc định (false) như file SQL
        DB::table('LOAIPHONG')->insert([
            ['MaLoaiPhong' => 1, 'TenLoaiPhong' => 'Bình Dân', 'GiaPhong' => 300000, 'MoTa' => 'Một giường đôi. Máy lạnh'],
            ['MaLoaiPhong' => 2, 'TenLoaiPhong' => 'Thương Gia', 'GiaPhong' => 20000000, 'MoTa' => 'Một giường đôi. Máy lạnh. View ngắm biển. Tivi. Có phòng làm việc'],
            ['MaLoaiPhong' => 3, 'TenLoaiPhong' => 'Vip', 'GiaPhong' => 4000000, 'MoTa' => 'Một giường đôi. Máy lạnh. View ngắm biển. Tivi'],
        ]);
    }
}
