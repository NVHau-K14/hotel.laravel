<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Nạp dữ liệu mẫu (y hệt QuanLyKhachSan.sql) theo thứ tự khóa ngoại.
     * BANGPHANCONG, PAYMENTS, CTDICHVU không có dữ liệu mẫu.
     */
    public function run(): void
    {
        $this->call([
            ChucVuSeeder::class,
            ChucVuQuyenSeeder::class,
            NhanVienSeeder::class,
            KhachHangSeeder::class,
            LoaiPhongSeeder::class,
            PhongSeeder::class,
            DichVuSeeder::class,
            DatPhongSeeder::class,
            DanhGiaSeeder::class,
            HoaDonSeeder::class,
        ]);
    }
}
