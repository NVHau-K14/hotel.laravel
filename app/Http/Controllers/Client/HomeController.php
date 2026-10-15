<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DichVu;
use App\Models\Phong;
use Illuminate\View\View;

/**
 * Trang chủ và trang liên hệ.
 */
class HomeController extends Controller
{
    public function index(): View
    {
        return view('client.home', [
            // 6 phòng đầu theo mã phòng + tất cả dịch vụ (như bản gốc)
            'phongs' => Phong::with('loaiPhong')->orderBy('MaPhong')->limit(6)->get(),
            'dichVus' => DichVu::orderBy('MaDichVu')->get(),
        ]);
    }

    public function contact(): View
    {
        return view('client.contact');
    }
}
