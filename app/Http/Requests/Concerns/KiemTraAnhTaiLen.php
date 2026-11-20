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
            // Đường dẫn nhập tay chỉ được trỏ tới ảnh của website (không cho URL bên ngoài)
            'hinhAnh' => ['nullable', 'string', 'max:255', 'regex:#^/(Images|uploads)/[A-Za-z0-9_\-./]+\.(jpg|jpeg|png|webp|gif)$#i', 'not_regex:#\.\.#'],
            'fileAnh' => ['nullable', 'file', 'max:5120', 'image', 'mimes:jpg,jpeg,png,webp,gif'],
        ];
    }

    protected function messagesAnh(): array
    {
        $khongTaiDuoc = 'Không tải được ảnh. Vui lòng chọn ảnh tối đa 5 MB.';
        $saiLoai = 'Chỉ chấp nhận ảnh JPG, PNG, WebP hoặc GIF hợp lệ.';

        $saiDuongDan = 'Đường dẫn ảnh phải bắt đầu bằng /Images/ hoặc /uploads/ và là file ảnh của website.';

        return [
            'hinhAnh.max' => $saiDuongDan,
            'hinhAnh.regex' => $saiDuongDan,
            'hinhAnh.not_regex' => $saiDuongDan,
            'fileAnh.uploaded' => $khongTaiDuoc,
            'fileAnh.file' => $khongTaiDuoc,
            'fileAnh.max' => $khongTaiDuoc,
            'fileAnh.image' => $saiLoai,
            'fileAnh.mimes' => $saiLoai,
        ];
    }
}
