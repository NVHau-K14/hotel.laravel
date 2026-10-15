<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Sửa nhân viên: không đổi tên đăng nhập; mật khẩu để trống thì giữ nguyên.
 * Quy tắc "không tự đổi chức vụ của mình" nằm ở NhanVienController@update.
 */
class UpdateNhanVienRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            ...StoreNhanVienRequest::rulesThongTin(),
            'password' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng nhập họ tên và chọn chức vụ.';

        return [
            'tenNhanVien.required' => $thieu,
            'maChucVu.required' => $thieu,
            'maChucVu.integer' => $thieu,
            'maChucVu.min' => $thieu,
            ...StoreNhanVienRequest::messagesThongTin(),
        ];
    }
}
