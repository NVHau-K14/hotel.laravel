<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PhanCongRequest;
use App\Models\NhanVien;
use App\Models\PhanCong;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Phân công công việc (quyền PHAN_CONG).
 */
class PhanCongController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $ngay = $request->input('ngay');

        return view('admin.phan-cong.index', [
            'tieuDe' => 'Quản lý phân công công việc',
            'items' => PhanCong::with('nhanVien.chucVu')->loc($keyword, $ngay)->get(),
            'keyword' => $keyword,
            'ngay' => $ngay,
        ]);
    }

    /**
     * Phân công của ngày hôm nay (dùng chung view danh sách).
     */
    public function homNay(): View
    {
        $homNay = today()->toDateString();

        return view('admin.phan-cong.index', [
            'tieuDe' => 'Phân công công việc hôm nay',
            'items' => PhanCong::with('nhanVien.chucVu')->loc(null, $homNay)->get(),
            'keyword' => null,
            'ngay' => $homNay,
        ]);
    }

    public function create(): View
    {
        return view('admin.phan-cong.form', [
            'item' => null,
            'nhanViens' => NhanVien::with('chucVu')->orderBy('MaNhanVien')->get(),
        ]);
    }

    public function store(PhanCongRequest $request): RedirectResponse
    {
        PhanCong::create($this->duLieu($request));

        return redirect()->route('admin.phan-cong.index')->thongBao('success', 'Thêm phân công công việc thành công!');
    }

    public function edit(PhanCong $phanCong): View
    {
        return view('admin.phan-cong.form', [
            'item' => $phanCong,
            'nhanViens' => NhanVien::with('chucVu')->orderBy('MaNhanVien')->get(),
        ]);
    }

    public function update(PhanCongRequest $request, PhanCong $phanCong): RedirectResponse
    {
        $phanCong->update($this->duLieu($request));

        return redirect()->route('admin.phan-cong.index')->thongBao('success', 'Cập nhật phân công thành công!');
    }

    public function destroy(PhanCong $phanCong): RedirectResponse
    {
        $phanCong->delete();

        return redirect()->route('admin.phan-cong.index')->thongBao('success', 'Đã xóa phân công công việc.');
    }

    private function duLieu(PhanCongRequest $request): array
    {
        return [
            'NgayPhanCong' => $request->input('ngayPhanCong'),
            'LoaiCongViec' => $request->input('loaiCongViec'),
            // Không chọn nhân viên → NULL (chưa chỉ định)
            'MaNhanVien' => $request->integer('maNhanVien') ?: null,
        ];
    }
}
