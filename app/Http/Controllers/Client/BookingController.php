<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\DatPhongRequest;
use App\Models\DatPhong;
use App\Models\Phong;
use App\Services\DatPhongService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Đặt phòng phía khách (khách đã đăng nhập hoặc khách vãng lai) và trang đặt thành công.
 */
class BookingController extends Controller
{
    public function __construct(private DatPhongService $datPhongService) {}

    public function create(Request $request, Phong $phong): View
    {
        return view('client.booking', [
            'room' => $phong->load('loaiPhong'),
            'clientUser' => $request->user('web'),
            // Mặc định nhận hôm nay, trả ngày mai
            'ngayNhan' => $request->input('ngayNhan', today()->toDateString()),
            'ngayTra' => $request->input('ngayTra', today()->addDay()->toDateString()),
        ]);
    }

    public function store(DatPhongRequest $request, Phong $phong): RedirectResponse
    {
        $khachDangNhap = $request->user('web');

        try {
            $maDon = $this->datPhongService->datPhongTrucTuyen(
                $khachDangNhap,
                $request->session()->get('guest_customer_id'),
                $request->thongTinKhach(),
                $phong->MaPhong,
                $request->input('ngayNhan'),
                $request->input('ngayTra'),
            );
        } catch (DomainException $e) {
            // Bản gốc hiện lỗi nghiệp vụ ngay trong form đặt phòng
            return back()->withErrors(['datPhong' => $e->getMessage()])->withInput();
        }

        // Khách vãng lai: ghi nhớ đơn trong phiên để xem trang thành công và đặt tiếp
        if (! $khachDangNhap) {
            $request->session()->put("guest_booking_ids.$maDon", true);
            $request->session()->put('guest_customer_id', DatPhong::whereKey($maDon)->value('MaKhachHang'));
        }

        return redirect()->route('dat-phong.thanh-cong', $maDon);
    }

    public function success(Request $request, DatPhong $datPhong): View|RedirectResponse
    {
        $khach = $request->user('web');
        $duocXem = $request->session()->get("guest_booking_ids.{$datPhong->MaCTDatPhong}")
            || ($khach && (int) $datPhong->MaKhachHang === (int) $khach->MaKhachHang);
        if (! $duocXem) {
            return redirect()->route('trang-chu');
        }

        return view('client.booking-success', ['booking' => $datPhong->load(['khachHang', 'phong.loaiPhong'])]);
    }
}
