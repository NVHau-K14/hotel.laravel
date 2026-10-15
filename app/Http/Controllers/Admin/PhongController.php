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
    public function index(Request $request): View
    {
        $keyword = $request->input('keyword');
        $maLoaiPhong = $request->filled('maLoaiPhong') ? $request->integer('maLoaiPhong') : null;
        $giaTu = $request->filled('giaTu') ? $request->integer('giaTu') : null;
        $giaDen = $request->filled('giaDen') ? $request->integer('giaDen') : null;
        $ngayNhan = $request->input('ngayNhan');
        $ngayTra = $request->input('ngayTra');

        // Lọc theo ngày cần đủ cả hai ngày hợp lệ, ngày trả sau ngày nhận; sai thì bỏ lọc ngày và báo lỗi
        $dateError = '';
        if (($ngayNhan || $ngayTra)
            && (! DatPhongService::ngayHopLe($ngayNhan) || ! DatPhongService::ngayHopLe($ngayTra) || $ngayNhan >= $ngayTra)) {
            $dateError = 'Vui lòng chọn đủ ngày nhận/trả hợp lệ; ngày trả phải sau ngày nhận.';
            $ngayNhan = null;
            $ngayTra = null;
        }

        $query = Phong::with('loaiPhong')->loc($keyword, $maLoaiPhong, $giaTu, $giaDen);
        if ($ngayNhan && $ngayTra) {
            $query->conTrong($ngayNhan, $ngayTra);
        }

        return view('admin.phong.index', [
            'items' => $query->get(),
            'counts' => Phong::demTheoTrangThai(),
            'loaiPhongs' => LoaiPhong::conHoatDong()->orderBy('MaLoaiPhong')->get(),
            'keyword' => $keyword,
            'maLoaiPhong' => $maLoaiPhong,
            'giaTu' => $giaTu,
            'giaDen' => $giaDen,
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

        $phong->update([
            'MaLoaiPhong' => $maLoaiPhong,
            'TrangThai' => $trangThai,
            'HinhAnhDaiDien' => $this->luuAnhTaiLen($request, 'fileAnh', 'phong') ?? $request->input('hinhAnh'),
        ]);

        return redirect()->route('admin.phong.index')->thongBao('success', "Cập nhật thông tin phòng {$phong->MaPhong} thành công!");
    }

    public function destroy(Phong $phong): RedirectResponse
    {
        // Phòng đã có đơn/đánh giá → MySQL báo lỗi 1451, bootstrap/app.php đổi thành thông báo tiếng Việt
        $phong->delete();

        return redirect()->route('admin.phong.index')->thongBao('success', "Đã xóa phòng {$phong->MaPhong} thành công.");
    }
}
