<?php

use App\Services\AnhThuNhoService;
use Illuminate\Support\Carbon;

/*
| Hàm tiện ích dùng trong view (chuyển từ hotel_php/config/helpers.php).
| Những gì Laravel đã có (e(), asset(), csrf...) thì không viết lại.
*/

if (! function_exists('format_currency')) {
    /**
     * Định dạng tiền VNĐ: 1260000 → "1.260.000 đ".
     */
    function format_currency($amount): string
    {
        if ($amount === null || $amount === '') {
            return '0 đ';
        }

        return number_format((float) $amount, 0, ',', '.').' đ';
    }
}

if (! function_exists('format_date')) {
    /**
     * Định dạng ngày d/m/Y. Nhận chuỗi ngày hoặc đối tượng Carbon (cột đã cast "date").
     */
    function format_date($date): string
    {
        if (! $date) {
            return '';
        }

        if ($date instanceof DateTimeInterface) {
            return Carbon::instance($date)->format('d/m/Y');
        }

        $timestamp = strtotime((string) $date);

        return $timestamp ? date('d/m/Y', $timestamp) : '';
    }
}

if (! function_exists('anh_url')) {
    /**
     * URL ảnh để hiển thị: ảnh nhỏ WebP gần nhất mà không nhỏ hơn $beRong, nếu chưa có thì ảnh gốc.
     */
    function anh_url(?string $anh, int $beRong = 640): string
    {
        if (empty($anh)) {
            return '';
        }

        $coSan = app(AnhThuNhoService::class)->anhCoSan($anh);
        foreach ($coSan as $rong => $duongDan) {
            if ($rong >= $beRong) {
                return asset($duongDan);
            }
        }

        return asset($anh);
    }
}

if (! function_exists('anh_srcset')) {
    /**
     * Giá trị srcset từ các ảnh nhỏ đã tạo ("…/640.webp 640w, …/1600.webp 1600w"); rỗng nếu chưa có.
     */
    function anh_srcset(?string $anh): string
    {
        return collect(app(AnhThuNhoService::class)->anhCoSan($anh))
            ->map(fn (string $duongDan, int $rong) => asset($duongDan).' '.$rong.'w')
            ->implode(', ');
    }
}
