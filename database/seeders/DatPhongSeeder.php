<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatPhongSeeder extends Seeder
{
    /**
     * Bảng CTDATPHONG. Ngày tính theo hôm nay để dữ liệu mẫu luôn dùng được cho demo,
     * mỗi đơn ở một trạng thái của luồng nghiệp vụ:
     *  #1, #2 đã hoàn tất (đã thanh toán, có đánh giá) · #3 khách đang ở · #4 đến nhận phòng hôm nay
     *  · #5 chờ admin duyệt.
     */
    public function run(): void
    {
        $ngay = fn (int $soNgay) => today()->addDays($soNgay)->toDateString();

        $don = fn (int $ma, int $khach, int $phong, int $nhan, int $tra, string $trangThai, int $gia, int $thucHien, ?string $thanhToan = null) => [
            'MaCTDatPhong' => $ma,
            'NgayThucHien' => $ngay($thucHien),
            'MaKhachHang' => $khach,
            'NgayNhan' => $ngay($nhan),
            'NgayTra' => $ngay($tra),
            'MaPhong' => $phong,
            'MaNhanVien' => $trangThai === 'CHO_ADMIN_DUYET' ? null : 2,
            'TrangThai' => $trangThai,
            'GiaPhongDat' => $gia,
            'payment_status' => $thanhToan ?? 'CHUA_TAO',
        ];

        DB::table('CTDATPHONG')->insert([
            $don(1, 1, 101, -10, -8, 'DA_HOAN_THANH', 300000, -15, 'DA_THANH_TOAN'),
            $don(2, 2, 102, -6, -4, 'DA_HOAN_THANH', 300000, -12, 'DA_THANH_TOAN'),
            $don(3, 3, 201, -1, 1, 'DANG_SU_DUNG', 5000000, -5),
            $don(4, 4, 103, 0, 2, 'DA_XAC_NHAN', 300000, -3),
            $don(5, 5, 202, 3, 5, 'CHO_ADMIN_DUYET', 5000000, -1),
        ]);
    }
}
