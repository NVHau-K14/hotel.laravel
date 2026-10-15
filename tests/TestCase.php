<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

/**
 * Feature test chạy trên MySQL (DB riêng hotel_laravel_test, cấu hình trong phpunit.xml).
 * Đầu lượt chạy: migrate:fresh --seed một lần; mỗi test chạy trong transaction rồi hoàn tác.
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /** Nạp DatabaseSeeder để test dùng đúng dữ liệu mẫu */
    protected $seed = true;

    /**
     * Chặn chạy nhầm vào DB thật: RefreshDatabase sẽ xóa sạch DB trước khi test.
     */
    protected function beforeRefreshingDatabase(): void
    {
        $tenDb = config('database.connections.'.config('database.default').'.database');

        if ($tenDb !== 'hotel_laravel_test') {
            throw new RuntimeException("Feature test chỉ chạy trên DB hotel_laravel_test (đang là \"$tenDb\"). Kiểm tra phpunit.xml và chạy php artisan config:clear.");
        }
    }
}
