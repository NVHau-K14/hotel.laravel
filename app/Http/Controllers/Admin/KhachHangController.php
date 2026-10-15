<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhachHang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Quản lý khách hàng (quyền KHACH_HANG): danh sách + tìm kiếm, khóa/mở khóa, lịch sử đặt phòng.
 */
class KhachHangController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $items = KhachHang::timKiem($keyword)->orderByDesc('MaKhachHang')->get();

        return view('admin.khach-hang.index', compact('items', 'keyword'));
    }

    public function toggle(KhachHang $khachHang): RedirectResponse
    {
        $khachHang->doiTrangThai();

        return redirect()->route('admin.khach-hang.index')
            ->thongBao('success', "Đã thay đổi trạng thái tài khoản khách hàng #{$khachHang->MaKhachHang}.");
    }

    public function history(KhachHang $khachHang): View
    {
        $bookings = $khachHang->datPhongs()
            ->with('phong.loaiPhong')
            ->orderByDesc('MaCTDatPhong')
            ->get();

        return view('admin.khach-hang.history', [
            'customer' => $khachHang,
            'bookings' => $bookings,
        ]);
    }
}
