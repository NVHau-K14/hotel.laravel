<?php

use App\Models\KhachHang;
use App\Models\NhanVien;

return [

    /*
    |--------------------------------------------------------------------------
    | Mặc định
    |--------------------------------------------------------------------------
    |
    | Guard mặc định "web" là khách hàng. Trang quản trị dùng guard "admin".
    | Không dùng chức năng quên mật khẩu nên không cấu hình password broker.
    |
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards
    |--------------------------------------------------------------------------
    |
    | Hai guard dùng chung một session nhưng lưu ở hai khóa khác nhau, nên có thể
    | đăng nhập cùng lúc admin và khách hàng trên một trình duyệt.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'khach_hang',
        ],

        'admin' => [
            'driver' => 'session',
            'provider' => 'nhan_vien',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'khach_hang' => [
            'driver' => 'eloquent',
            'model' => KhachHang::class,
        ],

        'nhan_vien' => [
            'driver' => 'eloquent',
            'model' => NhanVien::class,
        ],
    ],

    'passwords' => [],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
