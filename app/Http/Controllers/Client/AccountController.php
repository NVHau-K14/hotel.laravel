<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Concerns\GioiHanDangNhap;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CapNhatHoSoRequest;
use App\Http\Requests\Client\DangKyRequest;
use App\Http\Requests\Client\DangNhapRequest;
use App\Http\Requests\Client\DoiMatKhauRequest;
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
 * Tài khoản khách hàng (guard "web"): đăng nhập, đăng ký, đăng xuất, hồ sơ, đổi mật khẩu,
 * lịch sử và chi tiết đơn của mình.
 */
class AccountController extends Controller
{
    use GioiHanDangNhap;

    public function showLogin(): View
    {
        return view('client.account.login');
    }

    public function login(DangNhapRequest $request): RedirectResponse
    {
        $taiKhoan = $request->input('account');
        $matKhau = $request->input('password');

        $khoa = $this->khoaGioiHan($request, 'khach', $taiKhoan);
        if ($thongBao = $this->dangBiKhoaTam($khoa)) {
            return back()->withErrors(['account' => $thongBao])->onlyInput('account');
        }

        // Đăng nhập bằng email HOẶC số điện thoại
        $khachHang = KhachHang::where('Email', $taiKhoan)->orWhere('DienThoai', $taiKhoan)->first();
        $saiThongTin = back()
            ->withErrors(['account' => 'Thông tin đăng nhập hoặc mật khẩu không chính xác.'])
            ->onlyInput('account');

        // Khách vãng lai (MatKhau NULL) không đăng nhập được: cần đăng ký để đặt mật khẩu
        if (! $khachHang || ! Hash::check($matKhau, (string) $khachHang->MatKhau)) {
            $this->ghiNhanSai($khoa);

            return $saiThongTin;
        }
        $this->xoaGioiHan($khoa);

        if ($khachHang->biKhoa()) {
            return $saiThongTin->thongBao('danger', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ khách sạn.');
        }

        if (Hash::needsRehash($khachHang->MatKhau)) {
            $khachHang->update(['MatKhau' => Hash::make($matKhau)]);
        }

        Auth::guard('web')->login($khachHang);
        $request->session()->regenerate();

        // Quay lại trang đang xem dở (ví dụ trang đặt phòng) nếu có
        return redirect()->intended(route('trang-chu'))->thongBao('success', 'Đăng nhập thành công! Chào mừng quý khách.');
    }

    public function showRegister(): View
    {
        return view('client.account.register');
    }

    public function register(DangKyRequest $request): RedirectResponse
    {
        try {
            $khachHang = KhachHang::dangKy(
                $request->input('hoTen'),
                $request->input('email'),
                $request->input('dienThoai'),
                $request->input('diaChi'),
                $request->input('cmnd'),
                $request->input('password'),
            );
        } catch (DomainException $e) {
            return back()->withErrors(['email' => $e->getMessage()])->withInput($request->except('password', 'password_confirmation'));
        }

        Auth::guard('web')->login($khachHang);
        $request->session()->regenerate();

        $loiChao = $khachHang->datPhongs()->exists()
            ? 'Đăng ký thành công! Các đơn đặt phòng trước đây của bạn đã được gắn vào tài khoản.'
            : 'Đăng ký tài khoản thành công!';

        return redirect()->intended(route('trang-chu'))->thongBao('success', $loiChao);
    }

    public function profile(Request $request): View
    {
        return view('client.account.profile', ['khachHang' => $request->user('web')]);
    }

    public function edit(Request $request): View
    {
        return view('client.account.edit', ['khachHang' => $request->user('web')]);
    }

    public function update(CapNhatHoSoRequest $request): RedirectResponse
    {
        $request->user('web')->capNhatThongTin(
            $request->input('hoTen'),
            $request->input('email'),
            $request->input('dienThoai'),
            $request->input('diaChi'),
            $request->input('cmnd'),
        );

        return redirect()->route('tai-khoan.ho-so')->thongBao('success', 'Đã cập nhật thông tin cá nhân.');
    }

    public function changePassword(DoiMatKhauRequest $request): RedirectResponse
    {
        $request->user('web')->update(['MatKhau' => Hash::make($request->input('matKhauMoi'))]);
        $request->session()->regenerate();

        return redirect()->route('tai-khoan.ho-so')->thongBao('success', 'Đổi mật khẩu thành công!');
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
        $request->session()->regenerateToken();

        return redirect()->route('trang-chu')->thongBao('success', 'Đã đăng xuất tài khoản.');
    }
}
