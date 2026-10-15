<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Khách đang đăng nhập mà bị admin khóa thì tự đăng xuất ở request kế tiếp
 * (Auth::isClientLoggedIn của bản gốc). Chỉ đăng xuất phía khách, không đụng tới admin.
 */
class DangXuatKhachBiKhoa
{
    public function handle(Request $request, Closure $next): Response
    {
        $khachHang = Auth::guard('web')->user();

        if ($khachHang && $khachHang->biKhoa()) {
            Auth::guard('web')->logout();
            $request->session()->forget(['guest_booking_ids', 'guest_customer_id']);
        }

        return $next($request);
    }
}
