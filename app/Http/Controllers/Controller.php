<?php

namespace App\Http\Controllers;

use App\Services\AnhThuNhoService;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Lưu ảnh tải lên vào public/uploads/{thư mục} (disk "uploads") với tên ngẫu nhiên.
     * Tạo kèm ảnh nhỏ WebP (AnhThuNhoService).
     * Trả về đường dẫn ghi vào DB, vd. "/uploads/phong/abc...xyz.jpg"; không có file thì trả về null.
     * File đã được kiểm tra bằng Form Request (ảnh JPG/PNG/WebP/GIF, tối đa 5 MB).
     */
    protected function luuAnhTaiLen(Request $request, string $truong, string $thuMuc): ?string
    {
        if (! $request->hasFile($truong)) {
            return null;
        }

        $duongDan = '/uploads/'.$request->file($truong)->store($thuMuc, 'uploads');

        // Tạo luôn ảnh nhỏ WebP để trang khách không phải tải ảnh gốc
        app(AnhThuNhoService::class)->tao($duongDan);

        return $duongDan;
    }
}
