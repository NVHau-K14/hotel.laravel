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
        if ($this->chucVu?->TenChucVu === ChucVu::QUAN_LY) {
            return true;
        }

        $this->dsQuyenDaTai ??= $this->chucVu?->dsQuyen() ?? [];

        return in_array($maQuyen, $this->dsQuyenDaTai, true);
    }
}
