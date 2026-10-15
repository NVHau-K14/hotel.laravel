<?php

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
