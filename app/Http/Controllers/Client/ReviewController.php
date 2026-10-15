<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\GuiDanhGiaRequest;
use App\Models\DanhGia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Trang đánh giá: xem tất cả đánh giá, khách đăng nhập gửi đánh giá phòng đã ở.
 */
class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $khach = $request->user('web');

        return view('client.reviews', [
            'items' => DanhGia::with('khachHang')->orderByDesc('NgayDanhGia')->orderByDesc('ID')->get(),
            'phongs' => $khach ? DanhGia::phongCoTheDanhGia($khach->MaKhachHang) : collect(),
        ]);
    }

    public function store(GuiDanhGiaRequest $request): RedirectResponse
    {
        $khach = $request->user('web');
        $maPhong = $request->integer('maPhong');

        $datPhong = DanhGia::donCoTheDanhGia($khach->MaKhachHang, $maPhong);
        if (! $datPhong) {
            return redirect()->route('danh-gia.index')
                ->thongBao('danger', 'Bạn chỉ được đánh giá phòng đã đặt và đã hoàn thành ngày trả phòng.');
        }

        DanhGia::taoDanhGia(
            $request->input('noiDung'),
            today()->toDateString(),
            $maPhong,
            $request->integer('soSao', 5), // ép về 1–5 trong taoDanhGia
            $khach->MaKhachHang,
            $datPhong->MaCTDatPhong,
        );

        return redirect()->route('danh-gia.index')
            ->thongBao('success', 'Cảm ơn bạn đã gửi đánh giá! Ý kiến của bạn giúp chúng tôi nâng cao chất lượng dịch vụ.');
    }
}
