<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\LoaiPhong;
use App\Models\Phong;
use App\Services\DatPhongService;
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

        return view('client.rooms', [
            'items' => $query->get(),
            'loaiPhongs' => LoaiPhong::conHoatDong()->orderBy('MaLoaiPhong')->get(),
            'maLoaiPhong' => $maLoaiPhong,
            'giaTu' => $giaTu,
            'giaDen' => $giaDen,
            'ngayNhan' => $ngayNhan,
            'ngayTra' => $ngayTra,
            'dateError' => $dateError,
        ]);
    }

    public function show(Phong $phong): View
    {
        return view('client.room-detail', ['room' => $phong->load('loaiPhong')]);
    }
}
