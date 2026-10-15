<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Thêm/sửa loại phòng (LoaiPhongController + LoaiPhongModel::validate của bản gốc).
 */
class LoaiPhongRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'tenLoaiPhong' => ['required', 'string', 'max:20'],
            'giaPhong' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'moTa' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng nhập tên loại phòng và đơn giá hợp lệ.';
        $quaDai = 'Tên loại phòng tối đa 20 ký tự, mô tả tối đa 255 ký tự và giá phải lớn hơn 0.';

        return [
            'string' => 'Dữ liệu gửi lên không hợp lệ.',
            'tenLoaiPhong.required' => $thieu,
            'giaPhong.required' => $thieu,
            'giaPhong.integer' => $thieu,
            'giaPhong.min' => $thieu,
            'tenLoaiPhong.max' => $quaDai,
            'giaPhong.max' => $quaDai,
            'moTa.max' => $quaDai,
        ];
    }
}
