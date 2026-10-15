<?php

namespace App\Http\Requests\Concerns;

/**
 * Luật kiểm tra ô tải ảnh "fileAnh" (upload_image của bản gốc).
 */
trait KiemTraAnhTaiLen
{
    protected function rulesAnh(): array
    {
        return [
            'fileAnh' => ['nullable', 'file', 'max:5120', 'image', 'mimes:jpg,jpeg,png,webp,gif'],
        ];
    }

    protected function messagesAnh(): array
    {
        $khongTaiDuoc = 'Không tải được ảnh. Vui lòng chọn ảnh tối đa 5 MB.';
        $saiLoai = 'Chỉ chấp nhận ảnh JPG, PNG, WebP hoặc GIF hợp lệ.';

        return [
            'fileAnh.uploaded' => $khongTaiDuoc,
            'fileAnh.file' => $khongTaiDuoc,
            'fileAnh.max' => $khongTaiDuoc,
            'fileAnh.image' => $saiLoai,
            'fileAnh.mimes' => $saiLoai,
        ];
    }
}
