<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePermissionsRequest;
use App\Models\ChucVu;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Chức vụ & phân quyền (quyền CHUC_VU): tick quyền cho từng chức vụ.
 */
class ChucVuController extends Controller
{
    public function index(): View
    {
        $chucVus = ChucVu::orderBy('MaChucVu')->get();
        foreach ($chucVus as $chucVu) {
            $chucVu->setAttribute('permissions', $chucVu->dsQuyen());
        }

        return view('admin.chuc-vu.index', [
            'chucVus' => $chucVus,
            'allPermissions' => ChucVu::QUYEN,
        ]);
    }

    public function updatePermissions(UpdatePermissionsRequest $request, ChucVu $chucVu): RedirectResponse
    {
        $nhanVien = $request->user('admin');
        if (! $nhanVien->laQuanLy()
            && ($chucVu->TenChucVu === ChucVu::QUAN_LY || (int) $chucVu->MaChucVu === (int) $nhanVien->MaChucVu)) {
            throw new DomainException('Chỉ Quản lý mới được sửa quyền của chức vụ Quản lý hoặc chức vụ của chính mình.');
        }

        $dsQuyen = array_values(array_unique($request->input('permissions', [])));
        $chucVu->capNhatQuyen($dsQuyen);

        return redirect()->route('admin.chuc-vu.index')
            ->thongBao('success', "Cập nhật quyền thành công cho chức vụ #{$chucVu->MaChucVu}.");
    }
}
