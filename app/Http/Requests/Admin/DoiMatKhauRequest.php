<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Đổi mật khẩu của nhân viên đang đăng nhập.
 * Thứ tự kiểm tra giống bản gốc: mật khẩu cũ → mật khẩu mới → xác nhận → khác mật khẩu cũ.
 */
class DoiMatKhauRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            // current_password:admin so với mật khẩu của nhân viên đang đăng nhập (guard admin)
            'matKhauCu' => ['required', 'string', 'current_password:admin'],
            'matKhauMoi' => ['required', 'string'],
            'xacNhanMatKhau' => ['bail', 'required', 'string', 'same:matKhauMoi', 'different:matKhauCu'],
        ];
    }

    public function messages(): array
    {
        return [
            'matKhauCu.required' => 'Mật khẩu cũ không chính xác.',
            'matKhauCu.string' => 'Mật khẩu cũ không chính xác.',
            'matKhauCu.current_password' => 'Mật khẩu cũ không chính xác.',
            'matKhauMoi.required' => 'Vui lòng nhập mật khẩu mới.',
            'matKhauMoi.string' => 'Vui lòng nhập mật khẩu mới.',
            'xacNhanMatKhau.required' => 'Xác nhận mật khẩu mới không khớp.',
            'xacNhanMatKhau.string' => 'Xác nhận mật khẩu mới không khớp.',
            'xacNhanMatKhau.same' => 'Xác nhận mật khẩu mới không khớp.',
            'xacNhanMatKhau.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ];
    }
}
