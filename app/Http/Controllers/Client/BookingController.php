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
 * Đặt phòng phía khách. Bắt buộc đăng nhập (route có middleware auth:web): khách chưa có tài khoản
 * phải đăng ký; thông tin liên hệ trên đơn lấy từ tài khoản.
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
        try {
            $maDon = $this->datPhongService->datPhongTrucTuyen(
                $request->user('web'),
                $phong->MaPhong,
                $request->input('ngayNhan'),
                $request->input('ngayTra'),
            );
        } catch (DomainException $e) {
            return back()->withErrors(['datPhong' => $e->getMessage()])->withInput();
        }

        return redirect()->route('dat-phong.thanh-cong', $maDon);
    }

    public function success(Request $request, DatPhong $datPhong): View|RedirectResponse
    {
        if ((int) $datPhong->MaKhachHang !== (int) $request->user('web')->MaKhachHang) {
            return redirect()->route('trang-chu');
        }

        return view('client.booking-success', ['booking' => $datPhong->load(['khachHang', 'phong.loaiPhong'])]);
    }
}
