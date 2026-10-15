<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChucVuQuyenSeeder extends Seeder
{
    public function run(): void
    {
        // Mã chức vụ => danh sách mã quyền (22 dòng như file SQL)
        $quyenTheoChucVu = [
            1 => [
                'DASHBOARD', 'NHAN_VIEN', 'CHUC_VU', 'KHACH_HANG', 'LOAI_PHONG',
                'PHONG', 'DAT_PHONG', 'DICH_VU', 'CT_DICH_VU', 'HOA_DON',
                'PHAN_CONG', 'DANH_GIA', 'THONG_KE',
            ],
            2 => ['DASHBOARD', 'KHACH_HANG', 'DAT_PHONG', 'DICH_VU', 'CT_DICH_VU', 'HOA_DON'],
            3 => ['DASHBOARD'],
            4 => ['DASHBOARD', 'PHAN_CONG'],
        ];

        $rows = [];
        foreach ($quyenTheoChucVu as $maChucVu => $dsQuyen) {
            foreach ($dsQuyen as $maQuyen) {
                $rows[] = ['MaChucVu' => $maChucVu, 'MaQuyen' => $maQuyen];
            }
        }

        DB::table('CHUCVU_QUYEN')->insert($rows);
    }
}
