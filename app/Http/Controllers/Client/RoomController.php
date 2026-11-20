<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use App\Models\Phong;
use App\Services\DatPhongService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

/**
 * Danh sách phòng (lọc loại, giá, ngày trống) và chi tiết phòng.
 */
class RoomController extends Controller
{
    public function index(Request $request): View
    {
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

        $query = Phong::with('loaiPhong')->dangKinhDoanh()->loc($keyword, $maLoaiPhong, $khoangGia);
        if ($ngayNhan && $ngayTra) {
            $query->conTrong($ngayNhan, $ngayTra);
        }

        return view('client.rooms', [
            'items' => $query->get(),
            'loaiPhongs' => LoaiPhong::conHoatDong()->orderBy('MaLoaiPhong')->get(),
            'maLoaiPhong' => $maLoaiPhong,
            'khoangGia' => $khoangGia,
            'ngayNhan' => $ngayNhan,
            'ngayTra' => $ngayTra,
            'dateError' => $dateError,
        ]);
    }

    public function show(Phong $phong): View|RedirectResponse
    {
        // Phòng thuộc loại đã ngừng kinh doanh: không hiển thị cho khách
        if ($phong->loaiPhong?->DaXoa) {
            return redirect()->route('phong.index')->thongBao('danger', 'Không tìm thấy thông tin phòng.');
        }

        return view('client.room-detail', [
            'room' => $phong->load('loaiPhong'),
            'gallery' => $this->anhCuaPhong($phong),
        ]);
    }

    /**
     * Ảnh đại diện trước, sau đó các ảnh cùng thư mục Images/ChiTietPhong/PhongXXX (tối đa 8 ảnh).
     * Ảnh tải lên (uploads/...) nằm chung thư mục giữa các phòng nên chỉ lấy đúng ảnh đại diện.
     *
     * @return list<string>
     */
    private function anhCuaPhong(Phong $phong): array
    {
        $anhDaiDien = $phong->HinhAnhDaiDien;
        if (empty($anhDaiDien)) {
            return [];
        }

        $thuMuc = dirname(ltrim($anhDaiDien, '/'));
        if (! str_starts_with($thuMuc, 'Images/ChiTietPhong/') || str_contains($thuMuc, '..') || ! File::isDirectory(public_path($thuMuc))) {
            return [$anhDaiDien];
        }

        $anhKhac = collect(File::files(public_path($thuMuc)))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'webp'], true))
            ->map(fn ($file) => '/'.$thuMuc.'/'.$file->getFilename())
            ->sort()
            ->reject(fn (string $duongDan) => $duongDan === '/'.ltrim($anhDaiDien, '/'));

        return collect([$anhDaiDien])->merge($anhKhac)->take(8)->values()->all();
    }
}
