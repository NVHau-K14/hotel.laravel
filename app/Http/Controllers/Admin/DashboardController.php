<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * Bảng điều khiển. Giai đoạn 3: chỉ có khung trang; số liệu làm ở Giai đoạn 4E.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard.index');
    }
}
