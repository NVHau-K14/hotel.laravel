<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePermissionsRequest;
use App\Models\ChucVu;
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
        $dsQuyen = array_values(array_unique($request->input('permissions', [])));
        $chucVu->capNhatQuyen($dsQuyen);

        return redirect()->route('admin.chuc-vu.index')
            ->thongBao('success', "Cập nhật quyền thành công cho chức vụ #{$chucVu->MaChucVu}.");
    }
}
