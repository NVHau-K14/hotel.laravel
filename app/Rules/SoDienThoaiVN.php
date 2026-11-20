<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Số di động Việt Nam 10 số: 03x, 05x, 07x, 08x, 09x. Dùng sau khi đã chuẩn hóa bằng chuanHoa().
 */
class SoDienThoaiVN implements ValidationRule
{
    public const REGEX = '/^0(3|5|7|8|9)\d{8}$/';

    /**
     * Bỏ khoảng trắng, dấu chấm, gạch, ngoặc; đổi +84 / 84 ở đầu thành 0.
     */
    public static function chuanHoa(?string $soDienThoai): ?string
    {
        if ($soDienThoai === null) {
            return null;
        }
        $so = preg_replace('/[\s.\-()]/', '', trim($soDienThoai));
        if (str_starts_with($so, '+84')) {
            $so = '0'.substr($so, 3);
        } elseif (preg_match('/^84\d{9}$/', $so)) {
            $so = '0'.substr($so, 2);
        }

        return $so;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match(self::REGEX, $value)) {
            $fail('Số điện thoại phải là số di động Việt Nam hợp lệ (10 số, ví dụ 0912345678).');
        }
    }
}
