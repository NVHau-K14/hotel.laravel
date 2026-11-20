<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Chống dò mật khẩu: sai quá SO_LAN_SAI lần (theo tài khoản + IP) thì khóa tạm KHOA_GIAY giây.
 */
trait GioiHanDangNhap
{
    protected const SO_LAN_SAI = 5;

    protected const KHOA_GIAY = 60;

    protected function khoaGioiHan(Request $request, string $phia, ?string $taiKhoan): string
    {
        return $phia.'|'.Str::lower(trim((string) $taiKhoan)).'|'.$request->ip();
    }

    /** Trả về câu thông báo nếu đang bị khóa tạm, ngược lại null */
    protected function dangBiKhoaTam(string $khoa): ?string
    {
        if (! RateLimiter::tooManyAttempts($khoa, self::SO_LAN_SAI)) {
            return null;
        }

        return 'Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau '.RateLimiter::availableIn($khoa).' giây.';
    }

    protected function ghiNhanSai(string $khoa): void
    {
        RateLimiter::hit($khoa, self::KHOA_GIAY);
    }

    protected function xoaGioiHan(string $khoa): void
    {
        RateLimiter::clear($khoa);
    }
}
