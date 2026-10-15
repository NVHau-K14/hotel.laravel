<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DangNhapRequest;
use App\Http\Requests\Admin\DoiMatKhauRequest;
use App\Models\NhanVien;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Đăng nhập / đăng xuất / đổi mật khẩu trang quản trị (guard "admin").
 */
class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.auth.login');
    }

    public function login(DangNhapRequest $request): RedirectResponse
    {
        $matKhau = $request->input('password');
        $nhanVien = NhanVien::where('Username', $request->input('username'))->first();

        if (! $nhanVien || ! Hash::check($matKhau, (string) $nhanVien->Password)) {
            return back()
                ->withErrors(['username' => 'Tài khoản hoặc mật khẩu không chính xác.'])
                ->onlyInput('username');
        }

        if (Hash::needsRehash($nhanVien->Password)) {
            $nhanVien->update(['Password' => Hash::make($matKhau)]);
        }

        Auth::guard('admin')->login($nhanVien);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')
            ->thongBao('success', 'Đăng nhập quản trị thành công! Chào mừng bạn trở lại.');
    }

    public function showChangePassword(): View
    {
        return view('admin.auth.change-password');
    }

    public function changePassword(DoiMatKhauRequest $request): RedirectResponse
    {
        $request->user('admin')->update([
            'Password' => Hash::make($request->input('matKhauMoi')),
        ]);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->thongBao('success', 'Đổi mật khẩu thành công!');
    }

    public function logout(Request $request): RedirectResponse
    {
        // Chỉ đăng xuất guard admin. Không gọi session()->invalidate() vì sẽ đăng xuất luôn khách hàng.
        Auth::guard('admin')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('admin.dang-nhap')->thongBao('success', 'Đã đăng xuất thành công.');
    }
}
