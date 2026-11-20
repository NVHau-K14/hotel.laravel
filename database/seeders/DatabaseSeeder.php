<?php

namespace Database\Seeders;

use App\Services\DatPhongService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Nạp dữ liệu mẫu (dựa trên QuanLyKhachSan.sql) theo thứ tự khóa ngoại.
     * Đơn đặt phòng / hóa đơn / đánh giá tính ngày theo hôm nay để luôn demo được.
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

        // Trạng thái phòng (Trống / Đã đặt / Đang thuê) tính từ các đơn mẫu
        app(DatPhongService::class)->donDepQuaHan();
    }
}
