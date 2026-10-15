<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChucVuSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('CHUCVU')->insert([
            ['MaChucVu' => 1, 'TenChucVu' => 'Quản lý'],
            ['MaChucVu' => 2, 'TenChucVu' => 'Lễ tân'],
            ['MaChucVu' => 3, 'TenChucVu' => 'Bảo vệ'],
            ['MaChucVu' => 4, 'TenChucVu' => 'Tạp vụ'],
        ]);
    }
}
