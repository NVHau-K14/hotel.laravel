<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Admin thêm/sửa đánh giá (DanhGiaController + DanhGiaModel::create/update của bản gốc).
 */
class DanhGiaRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'noiDung' => ['required', 'string', 'max:255'],
            'maPhong' => ['required', 'integer', 'min:1', 'exists:PHONG,MaPhong'],
            'ngayDanhGia' => ['nullable', 'date_format:Y-m-d'],
            'soSao' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng nhập nội dung đánh giá và chọn phòng.';
        $sai = 'Đánh giá cần nội dung tối đa 255 ký tự và ngày hợp lệ.';

        return [
            'string' => 'Dữ liệu gửi lên không hợp lệ.',
            'noiDung.required' => $thieu,
            'maPhong.required' => $thieu,
            'maPhong.integer' => $thieu,
            'maPhong.min' => $thieu,
            'noiDung.max' => $sai,
            'ngayDanhGia.date_format' => $sai,
            'maPhong.exists' => 'Dữ liệu không hợp lệ, quá dài hoặc bản ghi liên quan không còn tồn tại. Vui lòng kiểm tra lại biểu mẫu.',
            'soSao.integer' => 'Dữ liệu gửi lên không hợp lệ.',
        ];
    }
}
