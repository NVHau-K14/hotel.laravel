<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDatPhongRequest;
use App\Http\Requests\Admin\ThemDichVuVaoDonRequest;
use App\Models\DatPhong;
use App\Models\DichVu;
use App\Models\HoaDon;
use App\Models\KhachHang;
use App\Models\Phong;
use App\Services\CtDichVuService;
use App\Services\DatPhongService;
use App\Services\HoaDonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Quản lý đặt phòng (quyền DAT_PHONG): danh sách, tạo đơn, chi tiết, duyệt, nhận phòng,
 * trả phòng (lập hóa đơn), hủy, thêm/xóa dịch vụ trên đơn.
 */
class DatPhongController extends Controller
{
    public function __construct(
        private DatPhongService $datPhongService,
        private HoaDonService $hoaDonService,
        private CtDichVuService $ctDichVuService,
    ) {}

    public function index(Request $request): View
    {
        $trangThai = $request->input('trangThai');
        $keyword = $request->input('keyword');

        return view('admin.dat-phong.index', [
            'items' => DatPhong::with(['khachHang', 'phong.loaiPhong'])->loc($trangThai, $keyword)->get(),
            'counts' => DatPhong::demTheoTrangThai(),
            'trangThai' => $trangThai,
            'keyword' => $keyword,
        ]);
    }

    public function create(): View
    {
        return view('admin.dat-phong.create', [
            'customers' => KhachHang::orderByDesc('MaKhachHang')->get(),
            'rooms' => Phong::with('loaiPhong')->orderBy('MaPhong')->get(),
        ]);
    }

    public function store(StoreDatPhongRequest $request): RedirectResponse
    {
        $maDon = $this->datPhongService->createBooking(
            $request->integer('maKhachHang'),
            $request->integer('maPhong'),
            $request->input('ngayNhan'),
            $request->input('ngayTra'),
            $request->user('admin')->MaNhanVien,
            $request->input('trangThai') ?: DatPhong::DA_XAC_NHAN,
        );

        return redirect()->route('admin.dat-phong.show', $maDon)->thongBao('success', "Tạo đơn đặt phòng #$maDon thành công!");
    }

    public function show(DatPhong $datPhong): View
    {
        $datPhong->load(['khachHang', 'phong.loaiPhong', 'nhanVien']);

        return view('admin.dat-phong.show', [
            'item' => $datPhong,
            'calc' => $this->hoaDonService->calculateBookingTotal($datPhong->MaCTDatPhong),
            'allServices' => DichVu::orderBy('MaDichVu')->get(),
            'invoice' => HoaDon::layTheoDon($datPhong->MaCTDatPhong),
        ]);
    }

    public function approve(Request $request, DatPhong $datPhong): RedirectResponse
    {
        $id = $datPhong->MaCTDatPhong;
        $this->datPhongService->updateStatus($id, DatPhong::DA_XAC_NHAN, $request->user('admin')->MaNhanVien);

        return redirect()->route('admin.dat-phong.show', $id)->thongBao('success', "Đã duyệt đơn đặt phòng #$id thành công.");
    }

    public function checkin(Request $request, DatPhong $datPhong): RedirectResponse
    {
        $id = $datPhong->MaCTDatPhong;
        $this->datPhongService->updateStatus($id, DatPhong::DANG_SU_DUNG, $request->user('admin')->MaNhanVien);

        return redirect()->route('admin.dat-phong.show', $id)->thongBao('success', "Khách đã nhận phòng (Check-in) cho đơn #$id.");
    }

    /**
     * Trả phòng: lập (hoặc cập nhật) hóa đơn rồi chuyển sang trang hóa đơn để xác nhận thanh toán.
     */
    public function checkout(Request $request, DatPhong $datPhong): RedirectResponse
    {
        $id = $datPhong->MaCTDatPhong;
        $maHoaDon = $this->hoaDonService->checkout($id, $request->user('admin')->MaNhanVien);

        return redirect()->route('admin.hoa-don.show', $maHoaDon)
            ->thongBao('success', "Đã tạo hóa đơn cho đơn đặt phòng #$id. Vui lòng kiểm tra và xác nhận thanh toán.");
    }

    public function cancel(DatPhong $datPhong): RedirectResponse
    {
        $id = $datPhong->MaCTDatPhong;
        // Hủy không ghi lại nhân viên (giống bản gốc)
        $this->datPhongService->updateStatus($id, DatPhong::DA_HUY);

        return redirect()->route('admin.dat-phong.index')->thongBao('success', "Đã hủy đơn đặt phòng #$id.");
    }

    public function addService(ThemDichVuVaoDonRequest $request, DatPhong $datPhong): RedirectResponse
    {
        $this->ctDichVuService->addToBooking(
            $datPhong->MaCTDatPhong,
            $request->integer('serviceId'),
            $request->integer('quantity'),
        );

        return redirect()->route('admin.dat-phong.show', $datPhong)->thongBao('success', 'Đã thêm dịch vụ vào đơn đặt phòng.');
    }

    public function removeService(DatPhong $datPhong, int $maDichVu): RedirectResponse
    {
        $this->ctDichVuService->delete($maDichVu, $datPhong->MaCTDatPhong);

        return redirect()->route('admin.dat-phong.show', $datPhong)->thongBao('success', 'Đã xóa dịch vụ khỏi đơn.');
    }
}
