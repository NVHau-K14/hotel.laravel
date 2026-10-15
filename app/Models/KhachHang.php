<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

/**
 * Bảng KHACHHANG — tài khoản khách hàng (guard "web").
 * MatKhau = NULL là khách vãng lai. Cột EmailConnexion do MySQL tự sinh: không bao giờ ghi vào.
 */
class KhachHang extends Authenticatable
{
    public const HOAT_DONG = 'HOAT_DONG';

    public const KHOA = 'KHOA';

    protected $table = 'KHACHHANG';

    protected $primaryKey = 'MaKhachHang';

    public $timestamps = false;

    /** Cột mật khẩu của bảng là "MatKhau" */
    protected $authPasswordName = 'MatKhau';

    /** Không dùng "remember me" nên không có cột remember_token */
    protected $rememberTokenName = '';

    protected $fillable = [
        'HoTen',
        'Email',
        'DiaChi',
        'DienThoai',
        'CMND',
        'MatKhau',
        'TrangThai',
    ];

    protected $hidden = ['MatKhau'];

    public function datPhongs(): HasMany
    {
        return $this->hasMany(DatPhong::class, 'MaKhachHang', 'MaKhachHang');
    }

    public function danhGias(): HasMany
    {
        return $this->hasMany(DanhGia::class, 'MaKhachHang', 'MaKhachHang');
    }

    public function biKhoa(): bool
    {
        return $this->TrangThai === self::KHOA;
    }

    /**
     * Tìm theo họ tên, email, số điện thoại, CMND (KhachHangModel::getAll).
     */
    public function scopeTimKiem(Builder $query, ?string $keyword): void
    {
        if ($keyword === null || trim($keyword) === '') {
            return;
        }

        $tuKhoa = '%'.trim($keyword).'%';
        $query->where(function (Builder $q) use ($tuKhoa) {
            $q->where('HoTen', 'like', $tuKhoa)
                ->orWhere('Email', 'like', $tuKhoa)
                ->orWhere('DienThoai', 'like', $tuKhoa)
                ->orWhere('CMND', 'like', $tuKhoa);
        });
    }

    /**
     * Tạo khách hàng mới (đăng ký hoặc khách vãng lai). Mật khẩu rỗng → khách vãng lai (NULL).
     */
    public static function taoKhachHang(string $hoTen, ?string $email, string $dienThoai, ?string $diaChi = null, ?string $cmnd = null, ?string $matKhau = null): self
    {
        // Bản gốc luôn lưu chuỗi (kể cả chuỗi rỗng) cho các cột này
        $email = (string) $email;

        $daTonTai = self::query()
            ->where('DienThoai', $dienThoai)
            ->orWhere(fn (Builder $q) => $q->where('Email', '<>', '')->where('Email', $email))
            ->exists();
        if ($daTonTai) {
            throw new DomainException('Số điện thoại hoặc email đã được đăng ký. Vui lòng đăng nhập tài khoản này.');
        }

        return self::create([
            'HoTen' => $hoTen,
            'Email' => $email,
            'DiaChi' => (string) $diaChi,
            'DienThoai' => $dienThoai,
            'CMND' => (string) $cmnd,
            'MatKhau' => ($matKhau === null || $matKhau === '') ? null : Hash::make($matKhau),
            'TrangThai' => self::HOAT_DONG,
        ]);
    }

    /**
     * Cập nhật thông tin liên hệ (khi khách đặt phòng lại).
     */
    public function capNhatThongTin(string $hoTen, ?string $email, string $dienThoai, ?string $diaChi, ?string $cmnd): void
    {
        $email = (string) $email;

        $trungTaiKhoanKhac = self::query()
            ->where('MaKhachHang', '<>', $this->MaKhachHang)
            ->where(function (Builder $q) use ($dienThoai, $email) {
                $q->where('DienThoai', $dienThoai)
                    ->orWhere(fn (Builder $q2) => $q2->where('Email', '<>', '')->where('Email', $email));
            })
            ->exists();
        if ($trungTaiKhoanKhac) {
            throw new DomainException('Số điện thoại hoặc email đã thuộc tài khoản khác.');
        }

        $this->update([
            'HoTen' => $hoTen,
            'Email' => $email,
            'DienThoai' => $dienThoai,
            'DiaChi' => (string) $diaChi,
            'CMND' => (string) $cmnd,
        ]);
    }

    /**
     * Khóa ↔ mở khóa tài khoản.
     */
    public function doiTrangThai(): void
    {
        $this->update(['TrangThai' => $this->TrangThai === self::HOAT_DONG ? self::KHOA : self::HOAT_DONG]);
    }
}
