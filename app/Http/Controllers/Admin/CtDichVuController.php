<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CtDichVuRequest;
use App\Http\Requests\Admin\ThemDichVuVaoDonRequest;
use App\Models\CtDichVu;
use App\Models\DatPhong;
use App\Models\DichVu;
use App\Services\CtDichVuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Chi tiết sử dụng dịch vụ (quyền CT_DICH_VU).
 */
class CtDichVuController extends Controller
{
    public function __construct(private CtDichVuService $ctDichVuService) {}

    public function index(): View
    {
        return view('admin.ct-dich-vu.index', ['items' => CtDichVu::danhSach()]);
    }

    public function create(Request $request): View
    {
        return view('admin.ct-dich-vu.create', [
            'dichVus' => DichVu::orderBy('MaDichVu')->get(),
            'datPhongs' => DatPhong::with('khachHang')->loc()->get(),
            // ?maCTDatPhong=... để chọn sẵn đơn
            'maCTDatPhongChon' => $request->input('maCTDatPhong'),
        ]);
    }

    public function store(CtDichVuRequest $request): RedirectResponse
    {
        // Đặt số lượng (không cộng dồn), hóa đơn chưa thanh toán tự tính lại trong service
        $this->ctDichVuService->save(
            $request->integer('maDichVu'),
            $request->integer('maCTDatPhong'),
            $request->integer('soLuong'),
            $request->integer('maCTDichVu') ?: null,
        );

        return redirect()->route('admin.ct-dich-vu.index')->thongBao('success', 'Thêm chi tiết dịch vụ thành công!');
    }

    /**
     * Trang riêng "Thêm dịch vụ vào đơn #..." (bản gốc không link tới, vẫn giữ — lỗi L5).
     */
    public function addToBooking(DatPhong $datPhong): View
    {
        return view('admin.ct-dich-vu.them-vao-don', [
            'datPhong' => $datPhong->load('khachHang'),
            'dichVus' => DichVu::orderBy('MaDichVu')->get(),
        ]);
    }

    public function saveToBooking(ThemDichVuVaoDonRequest $request, DatPhong $datPhong): RedirectResponse
    {
        // Cộng dồn số lượng vào đơn
        $this->ctDichVuService->addToBooking(
            $datPhong->MaCTDatPhong,
            $request->integer('maDichVu'),
            $request->integer('soLuong'),
        );

        return redirect()->route('admin.dat-phong.show', $datPhong)->thongBao('success', 'Đã thêm dịch vụ vào đơn thành công.');
    }

    /**
     * Xóa theo khóa ghép. Ô ẩn back=detail → quay về chi tiết đơn.
     */
    public function destroy(Request $request, int $maCTDatPhong, int $maDichVu): RedirectResponse
    {
        $this->ctDichVuService->delete($maDichVu, $maCTDatPhong);

        $chuyenToi = $request->input('back') === 'detail'
            ? redirect()->route('admin.dat-phong.show', $maCTDatPhong)
            : redirect()->route('admin.ct-dich-vu.index');

        return $chuyenToi->thongBao('success', 'Đã xóa chi tiết dịch vụ.');
    }
}
