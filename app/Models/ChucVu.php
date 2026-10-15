<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Bảng CHUCVU. Quyền của chức vụ nằm ở bảng CHUCVU_QUYEN (khóa chính ghép),
 * đọc/ghi bằng query builder nên không có model riêng.
 */
class ChucVu extends Model
{
    /** Chức vụ có toàn quyền (giống Auth::hasPermission của bản gốc) */
    public const QUAN_LY = 'Quản lý';

    /** 13 mã quyền và tên hiển thị (ChucVuController::ALL_PERMISSIONS của bản gốc) */
    public const QUYEN = [
        'DASHBOARD' => 'Xem Bảng điều khiển (Dashboard)',
        'PHONG' => 'Quản lý Phòng',
        'LOAI_PHONG' => 'Quản lý Loại phòng',
        'DAT_PHONG' => 'Quản lý Đặt phòng',
        'DICH_VU' => 'Quản lý Dịch vụ',
        'CT_DICH_VU' => 'Thêm/bớt Dịch vụ vào Đơn phòng',
        'HOA_DON' => 'Quản lý & Thanh toán Hóa đơn',
        'KHACH_HANG' => 'Quản lý Khách hàng',
        'NHAN_VIEN' => 'Quản lý Nhân viên',
        'PHAN_CONG' => 'Quản lý Phân công công việc',
        'CHUC_VU' => 'Quản lý Chức vụ & Phân quyền',
        'DANH_GIA' => 'Quản lý Đánh giá khách hàng',
        'THONG_KE' => 'Xem Báo cáo Thống kê',
    ];

    protected $table = 'CHUCVU';

    protected $primaryKey = 'MaChucVu';

    public $timestamps = false;

    protected $fillable = ['TenChucVu'];

    public function nhanViens(): HasMany
    {
        return $this->hasMany(NhanVien::class, 'MaChucVu', 'MaChucVu');
    }

    /**
     * Danh sách mã quyền của chức vụ này.
     *
     * @return array<int, string>
     */
    public function dsQuyen(): array
    {
        return DB::table('CHUCVU_QUYEN')
            ->where('MaChucVu', $this->MaChucVu)
            ->pluck('MaQuyen')
            ->all();
    }

    /**
     * Ghi đè toàn bộ quyền của chức vụ (xóa hết rồi thêm lại) trong một transaction.
     *
     * @param  array<int, string>  $dsQuyen
     */
    public function capNhatQuyen(array $dsQuyen): void
    {
        DB::transaction(function () use ($dsQuyen) {
            DB::table('CHUCVU_QUYEN')->where('MaChucVu', $this->MaChucVu)->delete();

            $rows = array_map(fn ($maQuyen) => ['MaChucVu' => $this->MaChucVu, 'MaQuyen' => $maQuyen], $dsQuyen);
            if ($rows !== []) {
                DB::table('CHUCVU_QUYEN')->insert($rows);
            }
        });
    }
}
