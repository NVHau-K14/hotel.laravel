<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Khách đăng nhập bằng email hoặc số điện thoại.
 */
class DangNhapRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'account' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng nhập số điện thoại/email và mật khẩu.';

        return [
            'required' => $thieu,
            'string' => $thieu,
        ];
    }
}
