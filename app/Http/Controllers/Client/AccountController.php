<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\DangKyRequest;
use App\Http\Requests\Client\DangNhapRequest;
use App\Models\KhachHang;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Tài khoản khách hàng (guard "web"). Giai đoạn 3: đăng nhập, đăng ký, đăng xuất.
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

    public function logout(Request $request): RedirectResponse
    {
        // Chỉ đăng xuất guard web (không invalidate session để admin vẫn đăng nhập)
        Auth::guard('web')->logout();
        $request->session()->forget(['guest_booking_ids', 'guest_customer_id']);
        $request->session()->regenerateToken();

        return redirect()->route('trang-chu')->thongBao('success', 'Đã đăng xuất tài khoản.');
    }
}
