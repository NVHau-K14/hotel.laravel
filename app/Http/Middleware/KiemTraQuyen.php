<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware "quyen": chặn nhân viên không có mã quyền, ví dụ ->middleware('quyen:PHONG').
 * Dùng sau middleware auth:admin (Auth::requirePermission của bản gốc).
 */
class KiemTraQuyen
{
    public function handle(Request $request, Closure $next, string $maQuyen): Response
    {
        $nhanVien = $request->user('admin');

        if (! $nhanVien || ! $nhanVien->coQuyen($maQuyen)) {
            abort(403, "Bạn không có quyền thực hiện chức năng này ($maQuyen).");
        }

        return $next($request);
    }
}
