<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatPhongSeeder extends Seeder
{
    public function run(): void
    {
        // Bảng CTDATPHONG. GiaPhongDat để NULL như file SQL (khi hiển thị lấy giá của loại phòng);
        // payment_status để mặc định 'CHUA_TAO'.
        DB::table('CTDATPHONG')->insert([
            ['MaCTDatPhong' => 1, 'NgayThucHien' => '2026-05-01', 'MaKhachHang' => 1, 'NgayNhan' => '2026-05-05', 'NgayTra' => '2026-05-07', 'MaPhong' => 101, 'MaNhanVien' => 2, 'TrangThai' => 'DA_XAC_NHAN'],
            ['MaCTDatPhong' => 2, 'NgayThucHien' => '2026-05-01', 'MaKhachHang' => 2, 'NgayNhan' => '2026-05-06', 'NgayTra' => '2026-05-08', 'MaPhong' => 102, 'MaNhanVien' => 2, 'TrangThai' => 'DA_XAC_NHAN'],
            ['MaCTDatPhong' => 3, 'NgayThucHien' => '2026-05-02', 'MaKhachHang' => 3, 'NgayNhan' => '2026-05-08', 'NgayTra' => '2026-05-10', 'MaPhong' => 201, 'MaNhanVien' => 2, 'TrangThai' => 'DA_XAC_NHAN'],
            ['MaCTDatPhong' => 4, 'NgayThucHien' => '2026-05-03', 'MaKhachHang' => 4, 'NgayNhan' => '2026-05-08', 'NgayTra' => '2026-05-10', 'MaPhong' => 103, 'MaNhanVien' => 2, 'TrangThai' => 'DA_XAC_NHAN'],
            ['MaCTDatPhong' => 5, 'NgayThucHien' => '2026-05-04', 'MaKhachHang' => 5, 'NgayNhan' => '2026-05-11', 'NgayTra' => '2026-05-13', 'MaPhong' => 201, 'MaNhanVien' => 2, 'TrangThai' => 'DA_XAC_NHAN'],
        ]);
    }
}
