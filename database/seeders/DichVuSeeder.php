<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DichVuSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('DICHVU')->insert([
            ['MaDichVu' => 1, 'TenDichVu' => 'Ăn sáng', 'GiaDichVu' => 30000, 'HinhAnh' => '/Images/DichVu/BuaSang/service.jpg'],
            ['MaDichVu' => 2, 'TenDichVu' => 'Ăn trưa', 'GiaDichVu' => 35000, 'HinhAnh' => '/Images/DichVu/BuaTrua/service.jpg'],
            ['MaDichVu' => 3, 'TenDichVu' => 'Ăn tối', 'GiaDichVu' => 35000, 'HinhAnh' => '/Images/DichVu/BuaToi/service.jpg'],
            ['MaDichVu' => 4, 'TenDichVu' => 'Massage', 'GiaDichVu' => 600000, 'HinhAnh' => '/Images/DichVu/Massage/service.jpg'],
            ['MaDichVu' => 5, 'TenDichVu' => 'Du thuyền', 'GiaDichVu' => 1000000, 'HinhAnh' => '/Images/DichVu/DuThuyen/service.jpg'],
        ]);
    }
}
