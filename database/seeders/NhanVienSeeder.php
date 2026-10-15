<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class NhanVienSeeder extends Seeder
{
    public function run(): void
    {
        // File SQL gốc lưu mật khẩu '1' dạng chữ thường; ở đây phải mã hóa bằng Hash::make
        $matKhau = Hash::make('1');

        DB::table('NHANVIEN')->insert([
            ['MaNhanVien' => 1, 'Username' => 'viet', 'Password' => $matKhau, 'TenNhanVien' => 'Nguyễn Quốc Việt', 'NgaySinh' => '2001-05-20', 'CMND' => '225786369', 'NgayVaoLam' => '2026-01-01', 'MaChucVu' => 1],
            ['MaNhanVien' => 2, 'Username' => 'binh', 'Password' => $matKhau, 'TenNhanVien' => 'Ngô Quốc Bình', 'NgaySinh' => '2001-08-15', 'CMND' => '225764728', 'NgayVaoLam' => '2026-01-02', 'MaChucVu' => 2],
            ['MaNhanVien' => 3, 'Username' => 'vung', 'Password' => $matKhau, 'TenNhanVien' => 'Hoàng Trọng Vững', 'NgaySinh' => '2001-03-10', 'CMND' => '225712598', 'NgayVaoLam' => '2026-01-05', 'MaChucVu' => 3],
            ['MaNhanVien' => 4, 'Username' => 'van', 'Password' => $matKhau, 'TenNhanVien' => 'Bùi Thị Vấn', 'NgaySinh' => '2001-11-25', 'CMND' => '225896128', 'NgayVaoLam' => '2026-01-06', 'MaChucVu' => 4],
        ]);
    }
}
