<?php

use App\Services\DatPhongService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Mỗi ngày: hủy đơn đã quá ngày trả mà khách chưa nhận phòng, tính lại trạng thái phòng theo ngày.
// (Dashboard, danh sách đơn và danh sách phòng cũng tự gọi việc này khi mở trang.)
Artisan::command('dat-phong:don-dep', function (DatPhongService $datPhongService) {
    $soDon = $datPhongService->donDepQuaHan();
    $this->info("Đã hủy $soDon đơn quá hạn và tính lại trạng thái phòng.");
})->purpose('Hủy đơn đặt phòng quá hạn, tính lại trạng thái phòng');

Schedule::command('dat-phong:don-dep')->dailyAt('00:05');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
