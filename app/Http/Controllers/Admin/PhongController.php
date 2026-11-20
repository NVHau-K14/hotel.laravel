<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePhongRequest;
use App\Http\Requests\Admin\UpdatePhongRequest;
use App\Models\LoaiPhong;
use App\Models\Phong;
use App\Services\DatPhongService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Quản lý phòng (quyền PHONG).
 */
class PhongController extends Controller
{
    public function __construct(private DatPhongService $datPhongService) {}

    public function index(Request $request): View
    {
        // Trạng thái "Đã đặt" phụ thuộc ngày hôm nay → tính lại trước khi hiển thị
        $this->datPhongService->donDepQuaHan();

        $keyword = $request->input('keyword');
        $maLoaiPhong = $request->filled('maLoaiPhong') ? $request->integer('maLoaiPhong') : null;
        // Khoảng giá chọn từ danh sách cố định (Phong::KHOANG_GIA); giá trị lạ thì bỏ qua
        $khoangGia = array_key_exists((string) $request->input('khoangGia'), Phong::KHOANG_GIA) ? $request->input('khoangGia') : null;
        $ngayNhan = $request->input('ngayNhan');
        $ngayTra = $request->input('ngayTra');

        // Lọc theo ngày cần đủ cả hai ngày hợp lệ, không ở quá khứ, ngày trả sau ngày nhận; sai thì bỏ lọc ngày và báo lỗi
        $dateError = '';
        if ($ngayNhan || $ngayTra) {
            if (! DatPhongService::ngayHopLe($ngayNhan) || ! DatPhongService::ngayHopLe($ngayTra) || $ngayNhan >= $ngayTra) {
                $dateError = 'Vui lòng chọn đủ ngày nhận/trả hợp lệ; ngày trả phải sau ngày nhận.';
            } elseif ($ngayNhan < today()->toDateString()) {
                $dateError = 'Ngày nhận phòng không được nằm trong quá khứ.';
            }
            if ($dateError) {
                $ngayNhan = null;
                $ngayTra = null;
            }
        }

        $query = Phong::with('loaiPhong')->loc($keyword, $maLoaiPhong, $khoangGia);
        if ($ngayNhan && $ngayTra) {
            $query->conTrong($ngayNhan, $ngayTra);
        }

        return view('admin.phong.index', [
            'items' => $query->get(),
            'counts' => Phong::demTheoTrangThai(),
            'loaiPhongs' => LoaiPhong::conHoatDong()->orderBy('MaLoaiPhong')->get(),
            'keyword' => $keyword,
            'maLoaiPhong' => $maLoaiPhong,
            'khoangGia' => $khoangGia,
            'ngayNhan' => $ngayNhan,
            'ngayTra' => $ngayTra,
            'dateError' => $dateError,
        ]);
    }

    public function create(): View
    {
        return view('admin.phong.form', [
            'item' => null,
            'loaiPhongs' => LoaiPhong::conHoatDong()->orderBy('MaLoaiPhong')->get(),
        ]);
    }

    public function store(StorePhongRequest $request): RedirectResponse
    {
        $maPhong = $request->integer('maPhong');
        // Ảnh tải lên được ưu tiên, nếu không có thì lấy đường dẫn nhập tay
        $hinhAnh = $this->luuAnhTaiLen($request, 'fileAnh', 'phong') ?? $request->input('hinhAnh');

        Phong::create([
            'MaPhong' => $maPhong,
            'MaLoaiPhong' => $request->integer('maLoaiPhong'),
            // Chỉ "Trống" hoặc "Bảo trì" (StorePhongRequest)
            'TrangThai' => $request->input('trangThai', Phong::TRONG),
            'HinhAnhDaiDien' => $hinhAnh ?: null,
        ]);

        return redirect()->route('admin.phong.index')->thongBao('success', "Thêm phòng $maPhong thành công!");
    }

    public function edit(Phong $phong): View
    {
        return view('admin.phong.form', [
            'item' => $phong,
            'loaiPhongs' => LoaiPhong::conHoatDong()->orderBy('MaLoaiPhong')->get(),
        ]);
    }

    public function update(UpdatePhongRequest $request, Phong $phong): RedirectResponse
    {
        $maLoaiPhong = $request->integer('maLoaiPhong');
        $trangThai = $request->input('trangThai', Phong::TRONG);

        // Phòng đang có lịch đặt thì không được đổi loại hoặc trạng thái (kiểm tra trước khi lưu ảnh)
        $phong->kiemTraDoiLoaiHoacTrangThai($maLoaiPhong, $trangThai);

        // Phòng đang "Đã đặt"/"Đang thuê" mà admin để "Trống" thì giữ nguyên, hệ thống tự tính lại bên dưới
        $phong->update([
            'MaLoaiPhong' => $maLoaiPhong,
            'TrangThai' => $trangThai,
            'HinhAnhDaiDien' => $this->luuAnhTaiLen($request, 'fileAnh', 'phong') ?? $request->input('hinhAnh'),
        ]);
        $this->datPhongService->syncRoomStatus($phong->MaPhong);

        return redirect()->route('admin.phong.index')->thongBao('success', "Cập nhật thông tin phòng {$phong->MaPhong} thành công!");
    }

    public function destroy(Phong $phong): RedirectResponse
    {
        // Phòng đã có đơn/đánh giá → MySQL báo lỗi 1451, bootstrap/app.php đổi thành thông báo tiếng Việt
        $phong->delete();

        return redirect()->route('admin.phong.index')->thongBao('success', "Đã xóa phòng {$phong->MaPhong} thành công.");
    }
}
