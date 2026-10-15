<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreNhanVienRequest;
use App\Http\Requests\Admin\UpdateNhanVienRequest;
use App\Models\ChucVu;
use App\Models\NhanVien;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Quản lý nhân viên (quyền NHAN_VIEN).
 */
class NhanVienController extends Controller
{
    public function index(): View
    {
        $items = NhanVien::with('chucVu')->orderBy('MaNhanVien')->get();

        return view('admin.nhan-vien.index', compact('items'));
    }

    public function create(): View
    {
        return view('admin.nhan-vien.form', [
            'item' => null,
            'chucVus' => ChucVu::orderBy('MaChucVu')->get(),
        ]);
    }

    public function store(StoreNhanVienRequest $request): RedirectResponse
    {
        $ten = $request->input('tenNhanVien');

        NhanVien::create([
            'Username' => $request->input('username'),
            'Password' => Hash::make($request->input('password')),
            'TenNhanVien' => $ten,
            'NgaySinh' => $request->input('ngaySinh'),
            'CMND' => $request->input('cmnd'),
            // Bỏ trống ngày vào làm → hôm nay
            'NgayVaoLam' => $request->input('ngayVaoLam') ?: today()->toDateString(),
            'MaChucVu' => $request->integer('maChucVu'),
        ]);

        return redirect()->route('admin.nhan-vien.index')->thongBao('success', "Thêm nhân viên \"$ten\" thành công!");
    }

    public function edit(NhanVien $nhanVien): View
    {
        return view('admin.nhan-vien.form', [
            'item' => $nhanVien,
            'chucVus' => ChucVu::orderBy('MaChucVu')->get(),
        ]);
    }

    public function update(UpdateNhanVienRequest $request, NhanVien $nhanVien): RedirectResponse
    {
        $ten = $request->input('tenNhanVien');
        $maChucVu = $request->integer('maChucVu');

        if ($nhanVien->is($request->user('admin')) && $maChucVu !== (int) $nhanVien->MaChucVu) {
            throw new DomainException('Không được tự thay đổi chức vụ của tài khoản đang đăng nhập.');
        }

        $duLieu = [
            'TenNhanVien' => $ten,
            'NgaySinh' => $request->input('ngaySinh'),
            'CMND' => $request->input('cmnd'),
            'NgayVaoLam' => $request->input('ngayVaoLam'),
            'MaChucVu' => $maChucVu,
        ];
        // Mật khẩu để trống thì giữ nguyên
        $matKhau = (string) $request->input('password');
        if (trim($matKhau) !== '') {
            $duLieu['Password'] = Hash::make($matKhau);
        }
        $nhanVien->update($duLieu);

        return redirect()->route('admin.nhan-vien.index')->thongBao('success', "Cập nhật nhân viên \"$ten\" thành công!");
    }

    public function destroy(Request $request, NhanVien $nhanVien): RedirectResponse
    {
        if ($nhanVien->is($request->user('admin'))) {
            throw new DomainException('Không được xóa tài khoản đang đăng nhập.');
        }

        // Nhân viên đã có đơn/hóa đơn/phân công → lỗi 1451, đổi thành câu tiếng Việt ở bootstrap/app.php
        $nhanVien->delete();

        return redirect()->route('admin.nhan-vien.index')->thongBao('success', 'Đã xóa nhân viên.');
    }
}
