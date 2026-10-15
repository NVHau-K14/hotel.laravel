<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoaiPhongRequest;
use App\Models\LoaiPhong;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Quản lý loại phòng (quyền LOAI_PHONG).
 */
class LoaiPhongController extends Controller
{
    public function index(): View
    {
        // Chỉ hiện loại phòng chưa bị xóa mềm
        $items = LoaiPhong::conHoatDong()->orderBy('MaLoaiPhong')->get();

        return view('admin.loai-phong.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.loai-phong.form', ['item' => null]);
    }

    public function store(LoaiPhongRequest $request): RedirectResponse
    {
        $ten = $request->input('tenLoaiPhong');

        LoaiPhong::create([
            'TenLoaiPhong' => $ten,
            'GiaPhong' => $request->integer('giaPhong'),
            'MoTa' => (string) $request->input('moTa'),
            'DaXoa' => false,
        ]);

        return redirect()->route('admin.loai-phong.index')->thongBao('success', "Đã thêm loại phòng \"$ten\" thành công!");
    }

    public function edit(LoaiPhong $loaiPhong): View
    {
        return view('admin.loai-phong.form', ['item' => $loaiPhong]);
    }

    public function update(LoaiPhongRequest $request, LoaiPhong $loaiPhong): RedirectResponse
    {
        $ten = $request->input('tenLoaiPhong');

        $loaiPhong->update([
            'TenLoaiPhong' => $ten,
            'GiaPhong' => $request->integer('giaPhong'),
            'MoTa' => (string) $request->input('moTa'),
        ]);

        return redirect()->route('admin.loai-phong.index')->thongBao('success', "Cập nhật loại phòng \"$ten\" thành công!");
    }

    public function destroy(LoaiPhong $loaiPhong): RedirectResponse
    {
        // Còn phòng dùng loại này → chỉ đánh dấu DaXoa; không còn → xóa hẳn
        $loaiPhong->xoaHoacAn();

        return redirect()->route('admin.loai-phong.index')->thongBao('success', 'Đã xóa loại phòng thành công.');
    }
}
