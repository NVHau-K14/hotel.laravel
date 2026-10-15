<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatPhong;
use App\Models\Phong;
use App\Services\ThongKeService;
use Illuminate\View\View;

/**
 * Bảng điều khiển (quyền DASHBOARD): thẻ số liệu phòng, tổng doanh thu, đơn chờ duyệt.
 */
class DashboardController extends Controller
{
    public function index(ThongKeService $thongKe): View
    {
        return view('admin.dashboard.index', [
            'summary' => $thongKe->getSummary(),
            'donChoDuyet' => DatPhong::with(['khachHang', 'phong.loaiPhong'])
                ->loc(DatPhong::CHO_ADMIN_DUYET)
                ->get(),
            'phongStats' => Phong::demTheoTrangThai(),
        ]);
    }
}
