<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Chuẩn mật khẩu chung cho khách hàng và nhân viên:
 * tối thiểu 8 ký tự, có chữ hoa, chữ thường, chữ số; không chứa tên đăng nhập / SĐT / phần trước @ của email.
 */
class MatKhauManh implements ValidationRule
{
    public const THONG_BAO = 'Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường và chữ số.';

    /**
     * @param  array<int, string|null>  $thongTinCaNhan  Các giá trị không được xuất hiện trong mật khẩu
     */
    public function __construct(private array $thongTinCaNhan = []) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $matKhau = (string) $value;
        if (mb_strlen($matKhau) < 8 || mb_strlen($matKhau) > 72
            || ! preg_match('/\p{Lu}/u', $matKhau) || ! preg_match('/\p{Ll}/u', $matKhau) || ! preg_match('/\d/', $matKhau)) {
            $fail(self::THONG_BAO);

            return;
        }

        foreach ($this->thongTinCaNhan as $giaTri) {
            $giaTri = mb_strtolower(trim((string) $giaTri));
            if (str_contains($giaTri, '@')) {
                $giaTri = strstr($giaTri, '@', true);
            }
            if (mb_strlen($giaTri) >= 3 && str_contains(mb_strtolower($matKhau), $giaTri)) {
                $fail('Mật khẩu không được chứa tên đăng nhập, số điện thoại hoặc email.');

                return;
            }
        }
    }
}
