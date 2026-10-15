<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Đăng nhập trang quản trị.
 */
class DangNhapRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';

        return [
            'required' => $thieu,
            'string' => $thieu,
        ];
    }
}
