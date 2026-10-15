<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DichVuRequest;
use App\Models\DichVu;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Quản lý dịch vụ (quyền DICH_VU).
 */
class DichVuController extends Controller
{
    public function index(): View
    {
        $items = DichVu::orderBy('MaDichVu')->get();

        return view('admin.dich-vu.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.dich-vu.form', ['item' => null]);
    }

    public function store(DichVuRequest $request): RedirectResponse
    {
        $ten = $request->input('tenDichVu');
        $hinhAnh = $this->luuAnhTaiLen($request, 'fileAnh', 'dichvu') ?? $request->input('hinhAnh');

        DichVu::create([
            'TenDichVu' => $ten,
            'GiaDichVu' => $request->integer('giaDichVu'),
            'HinhAnh' => $hinhAnh ?: null,
        ]);

        return redirect()->route('admin.dich-vu.index')->thongBao('success', "Đã thêm dịch vụ \"$ten\" thành công!");
    }

    public function edit(DichVu $dichVu): View
    {
        return view('admin.dich-vu.form', ['item' => $dichVu]);
    }

    public function update(DichVuRequest $request, DichVu $dichVu): RedirectResponse
    {
        $ten = $request->input('tenDichVu');

        $dichVu->update([
            'TenDichVu' => $ten,
            'GiaDichVu' => $request->integer('giaDichVu'),
            'HinhAnh' => $this->luuAnhTaiLen($request, 'fileAnh', 'dichvu') ?? $request->input('hinhAnh'),
        ]);

        return redirect()->route('admin.dich-vu.index')->thongBao('success', "Cập nhật dịch vụ \"$ten\" thành công!");
    }

    public function destroy(DichVu $dichVu): RedirectResponse
    {
        // Dịch vụ đã dùng trong đơn → lỗi 1451, đổi thành thông báo tiếng Việt ở bootstrap/app.php
        $dichVu->delete();

        return redirect()->route('admin.dich-vu.index')->thongBao('success', 'Đã xóa dịch vụ thành công.');
    }
}
