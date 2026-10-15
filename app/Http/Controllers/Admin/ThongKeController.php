<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThongKeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Báo cáo thống kê (quyền THONG_KE): tổng quan + doanh thu 12 tháng của năm chọn (?year, mặc định năm nay).
 */
class ThongKeController extends Controller
{
    public function index(Request $request, ThongKeService $thongKe): View
    {
        // Giống (int)($_GET['year'] ?? date('Y')) của bản gốc
        $year = $request->integer('year', now()->year);

        return view('admin.thong-ke.index', [
            'summary' => $thongKe->getSummary(),
            'monthlyRevenue' => $thongKe->getMonthlyRevenue($year),
            'year' => $year,
        ]);
    }
}
