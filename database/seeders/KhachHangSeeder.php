<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KhachHangSeeder extends Seeder
{
    public function run(): void
    {
        $matKhau = Hash::make('1');

        // Không ghi EmailConnexion: MySQL tự sinh từ Email
        DB::table('KHACHHANG')->insert([
            ['MaKhachHang' => 1, 'HoTen' => 'Lê Văn Tèo', 'Email' => 'teo@gmail.com', 'DiaChi' => 'Hà Nội', 'DienThoai' => '0912345678', 'CMND' => '258963147', 'MatKhau' => $matKhau, 'TrangThai' => 'HOAT_DONG'],
            ['MaKhachHang' => 2, 'HoTen' => 'Lê Văn Tí', 'Email' => 'ti@gmail.com', 'DiaChi' => 'Hồ Chí Minh', 'DienThoai' => '0987654321', 'CMND' => '258963148', 'MatKhau' => $matKhau, 'TrangThai' => 'HOAT_DONG'],
            ['MaKhachHang' => 3, 'HoTen' => 'Nguyễn Văn A', 'Email' => 'vana@gmail.com', 'DiaChi' => 'Đà Nẵng', 'DienThoai' => '0905123456', 'CMND' => '258963149', 'MatKhau' => $matKhau, 'TrangThai' => 'HOAT_DONG'],
            ['MaKhachHang' => 4, 'HoTen' => 'Trần Thị B', 'Email' => 'thib@gmail.com', 'DiaChi' => 'Cần Thơ', 'DienThoai' => '0934123456', 'CMND' => '258963150', 'MatKhau' => $matKhau, 'TrangThai' => 'HOAT_DONG'],
            ['MaKhachHang' => 5, 'HoTen' => 'Phạm Minh C', 'Email' => 'minhc@gmail.com', 'DiaChi' => 'Hải Phòng', 'DienThoai' => '0977123456', 'CMND' => '258925647', 'MatKhau' => $matKhau, 'TrangThai' => 'HOAT_DONG'],
        ]);
    }
}
