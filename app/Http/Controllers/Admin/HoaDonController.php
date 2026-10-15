<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HoaDon;
use App\Services\HoaDonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Quản lý hóa đơn (quyền HOA_DON): danh sách, chi tiết (in), xác nhận thanh toán.
 */
class HoaDonController extends Controller
{
    public function __construct(private HoaDonService $hoaDonService) {}

    public function index(): View
    {
        $items = HoaDon::with(['datPhong.khachHang', 'datPhong.phong.loaiPhong', 'nhanVien'])
            ->orderByDesc('MaHoaDon')
            ->get();

        return view('admin.hoa-don.index', compact('items'));
    }

    public function show(HoaDon $hoaDon): View
    {
        $hoaDon->load(['datPhong.khachHang', 'datPhong.phong.loaiPhong', 'nhanVien']);

        // Hóa đơn đã thanh toán: chi tiết lấy từ bản chụp ChiTietJSON
        $calc = $hoaDon->MaCTDatPhong ? $this->hoaDonService->calculateBookingTotal($hoaDon->MaCTDatPhong) : [];

        return view('admin.hoa-don.show', ['item' => $hoaDon, 'calc' => $calc]);
    }

    public function pay(HoaDon $hoaDon): RedirectResponse
    {
        $id = $hoaDon->MaHoaDon;
        $this->hoaDonService->payInvoice($id);

        return redirect()->route('admin.hoa-don.show', $id)
            ->thongBao('success', "Xác nhận thanh toán hóa đơn #$id thành công! Phòng đã được giải phóng về trạng thái Trống.");
    }
}
