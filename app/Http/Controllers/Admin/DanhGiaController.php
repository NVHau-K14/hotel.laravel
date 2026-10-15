<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DanhGiaRequest;
use App\Models\DanhGia;
use App\Models\Phong;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Quản lý đánh giá (quyền DANH_GIA). Đánh giá do admin thêm không gắn khách/đơn.
 */
class DanhGiaController extends Controller
{
    public function index(): View
    {
        $items = DanhGia::with(['khachHang', 'phong.loaiPhong'])
            ->orderByDesc('NgayDanhGia')
            ->orderByDesc('ID')
            ->get();

        return view('admin.danh-gia.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.danh-gia.form', [
            'item' => null,
            'phongs' => Phong::with('loaiPhong')->orderBy('MaPhong')->get(),
        ]);
    }

    public function store(DanhGiaRequest $request): RedirectResponse
    {
        DanhGia::taoDanhGia(
            $request->input('noiDung'),
            $request->input('ngayDanhGia'),
            $request->integer('maPhong'),
            $request->integer('soSao', 5),
        );

        return redirect()->route('admin.danh-gia.index')->thongBao('success', 'Thêm đánh giá mới thành công!');
    }

    public function edit(DanhGia $danhGia): View
    {
        return view('admin.danh-gia.form', [
            'item' => $danhGia,
            'phongs' => Phong::with('loaiPhong')->orderBy('MaPhong')->get(),
        ]);
    }

    public function update(DanhGiaRequest $request, DanhGia $danhGia): RedirectResponse
    {
        // Đánh giá gắn với đơn của khách không được chuyển sang phòng khác (DanhGia::capNhat)
        $danhGia->capNhat(
            $request->input('noiDung'),
            $request->input('ngayDanhGia'),
            $request->integer('maPhong'),
            $request->integer('soSao', 5),
        );

        return redirect()->route('admin.danh-gia.index')->thongBao('success', 'Cập nhật đánh giá thành công!');
    }

    public function destroy(DanhGia $danhGia): RedirectResponse
    {
        $danhGia->delete();

        return redirect()->route('admin.danh-gia.index')->thongBao('success', 'Đã xóa đánh giá thành công.');
    }
}
