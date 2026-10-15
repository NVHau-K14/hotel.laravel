<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\DangKyRequest;
use App\Http\Requests\Client\DangNhapRequest;
use App\Models\CtDichVu;
use App\Models\DatPhong;
use App\Models\KhachHang;
use App\Services\HoaDonService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Tài khoản khách hàng (guard "web"): đăng nhập, đăng ký, đăng xuất, hồ sơ, lịch sử và chi tiết đơn của mình.
 */
class AccountController extends Controller
{
    public function showLogin(): View
    {
        return view('client.account.login');
    }

    public function login(DangNhapRequest $request): RedirectResponse
    {
        $taiKhoan = $request->input('account');
        $matKhau = $request->input('password');

        // Đăng nhập bằng email HOẶC số điện thoại
        $khachHang = KhachHang::where('Email', $taiKhoan)->orWhere('DienThoai', $taiKhoan)->first();
        $saiThongTin = back()
            ->withErrors(['account' => 'Thông tin đăng nhập hoặc mật khẩu không chính xác.'])
            ->onlyInput('account');

        // Khách vãng lai (MatKhau NULL) không đăng nhập được
        if (! $khachHang || ! Hash::check($matKhau, (string) $khachHang->MatKhau)) {
            return $saiThongTin;
        }

        if ($khachHang->biKhoa()) {
            return $saiThongTin->thongBao('danger', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ khách sạn.');
        }

        if (Hash::needsRehash($khachHang->MatKhau)) {
            $khachHang->update(['MatKhau' => Hash::make($matKhau)]);
        }

        Auth::guard('web')->login($khachHang);
        $request->session()->regenerate();

        return redirect()->route('trang-chu')->thongBao('success', 'Đăng nhập thành công! Chào mừng quý khách.');
    }

    public function showRegister(): View
    {
        return view('client.account.register');
    }

    public function register(DangKyRequest $request): RedirectResponse
    {
        try {
            $khachHang = KhachHang::taoKhachHang(
                $request->input('hoTen'),
                $request->input('email'),
                $request->input('dienThoai'),
                $request->input('diaChi'),
                $request->input('cmnd'),
                $request->input('password'),
            );
        } catch (DomainException $e) {
            // Bản gốc hiện lỗi này ngay trong form đăng ký
            return back()->withErrors(['email' => $e->getMessage()])->withInput($request->except('password'));
        }

        Auth::guard('web')->login($khachHang);
        $request->session()->regenerate();

        return redirect()->route('trang-chu')->thongBao('success', 'Đăng ký tài khoản thành công!');
    }

    public function profile(Request $request): View
    {
        return view('client.account.profile', ['khachHang' => $request->user('web')]);
    }

    public function history(Request $request): View
    {
        return view('client.account.history', [
            'bookings' => $request->user('web')->datPhongs()
                ->with('phong.loaiPhong')
                ->orderByDesc('MaCTDatPhong')
                ->get(),
        ]);
    }

    /**
     * Chi tiết một đơn — chỉ xem được đơn của chính mình.
     */
    public function detail(Request $request, DatPhong $datPhong, HoaDonService $hoaDonService): View|RedirectResponse
    {
        if ((int) $datPhong->MaKhachHang !== (int) $request->user('web')->MaKhachHang) {
            return redirect()->route('tai-khoan.lich-su')
                ->thongBao('danger', 'Không tìm thấy đơn đặt phòng hoặc bạn không có quyền truy cập.');
        }

        return view('client.account.booking-detail', [
            'datPhong' => $datPhong->load('phong.loaiPhong'),
            'dichVuDaDung' => CtDichVu::cuaDon($datPhong->MaCTDatPhong),
            // Đơn đã thanh toán: số tiền lấy từ bản chụp hóa đơn
            'calc' => $hoaDonService->calculateBookingTotal($datPhong->MaCTDatPhong),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        // Chỉ đăng xuất guard web (không invalidate session để admin vẫn đăng nhập)
        Auth::guard('web')->logout();
        $request->session()->forget(['guest_booking_ids', 'guest_customer_id']);
        $request->session()->regenerateToken();

        return redirect()->route('trang-chu')->thongBao('success', 'Đã đăng xuất tài khoản.');
    }
}
