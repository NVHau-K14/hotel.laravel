<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Bảng NHANVIEN — tài khoản đăng nhập trang quản trị (guard "admin").
 */
class NhanVien extends Authenticatable
{
    /** Mã quyền => route trang danh sách, theo thứ tự menu */
    public const TRANG_THEO_QUYEN = [
        'DASHBOARD' => 'admin.dashboard',
        'DAT_PHONG' => 'admin.dat-phong.index',
        'PHONG' => 'admin.phong.index',
        'LOAI_PHONG' => 'admin.loai-phong.index',
        'DICH_VU' => 'admin.dich-vu.index',
        'CT_DICH_VU' => 'admin.ct-dich-vu.index',
        'HOA_DON' => 'admin.hoa-don.index',
        'KHACH_HANG' => 'admin.khach-hang.index',
        'NHAN_VIEN' => 'admin.nhan-vien.index',
        'PHAN_CONG' => 'admin.phan-cong.index',
        'CHUC_VU' => 'admin.chuc-vu.index',
        'DANH_GIA' => 'admin.danh-gia.index',
        'THONG_KE' => 'admin.thong-ke.index',
    ];

    protected $table = 'NHANVIEN';

    protected $primaryKey = 'MaNhanVien';

    public $timestamps = false;

    /** Cột mật khẩu của bảng là "Password" */
    protected $authPasswordName = 'Password';

    /** Không dùng "remember me" nên không có cột remember_token */
    protected $rememberTokenName = '';

    protected $fillable = [
        'Username',
        'Password',
        'TenNhanVien',
        'NgaySinh',
        'CMND',
        'NgayVaoLam',
        'MaChucVu',
    ];

    protected $hidden = ['Password'];

    /** Lưu tạm danh sách quyền trong một request */
    protected ?array $dsQuyenDaTai = null;

    protected function casts(): array
    {
        return [
            'NgaySinh' => 'date',
            'NgayVaoLam' => 'date',
        ];
    }

    public function chucVu(): BelongsTo
    {
        return $this->belongsTo(ChucVu::class, 'MaChucVu', 'MaChucVu');
    }

    public function phanCongs(): HasMany
    {
        return $this->hasMany(PhanCong::class, 'MaNhanVien', 'MaNhanVien');
    }

    public function datPhongs(): HasMany
    {
        return $this->hasMany(DatPhong::class, 'MaNhanVien', 'MaNhanVien');
    }

    public function hoaDons(): HasMany
    {
        return $this->hasMany(HoaDon::class, 'MaNhanVien', 'MaNhanVien');
    }

    /**
     * Nhân viên có quyền $maQuyen không. Chức vụ "Quản lý" có toàn quyền.
     */
    public function coQuyen(string $maQuyen): bool
    {
        if ($this->laQuanLy()) {
            return true;
        }

        $this->dsQuyenDaTai ??= $this->chucVu?->dsQuyen() ?? [];

        return in_array($maQuyen, $this->dsQuyenDaTai, true);
    }

    public function laQuanLy(): bool
    {
        return $this->chucVu?->TenChucVu === ChucVu::QUAN_LY;
    }

    /**
     * Trang đầu tiên nhân viên được phép mở (sau đăng nhập / đổi mật khẩu / trang 403),
     * theo thứ tự menu. Không có quyền nào → trang đổi mật khẩu.
     */
    public function trangDauTien(): string
    {
        foreach (self::TRANG_THEO_QUYEN as $maQuyen => $tenRoute) {
            if ($this->coQuyen($maQuyen)) {
                return route($tenRoute);
            }
        }

        return route('admin.doi-mat-khau');
    }
}
