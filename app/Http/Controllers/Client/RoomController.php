<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use App\Models\Phong;
use App\Services\DatPhongService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('client.room-detail', ['room' => $phong->load('loaiPhong')]);
    }
}
