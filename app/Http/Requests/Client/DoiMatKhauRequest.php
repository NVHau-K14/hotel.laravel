<?php

namespace App\Http\Requests\Client;

use App\Rules\MatKhauManh;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Khách đổi mật khẩu ở trang hồ sơ.
 */
class DoiMatKhauRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        $khach = $this->user('web');

        return [
            'matKhauCu' => ['required', 'string', 'current_password:web'],
            'matKhauMoi' => ['required', 'string', new MatKhauManh([$khach?->DienThoai, $khach?->Email]), 'different:matKhauCu'],
            'xacNhanMatKhau' => ['required', 'string', 'same:matKhauMoi'],
        ];
    }

    public function messages(): array
    {
        return [
            'matKhauCu.required' => 'Mật khẩu hiện tại không chính xác.',
            'matKhauCu.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'matKhauMoi.required' => 'Vui lòng nhập mật khẩu mới.',
            'matKhauMoi.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
            'xacNhanMatKhau.required' => 'Mật khẩu nhập lại không khớp.',
            'xacNhanMatKhau.same' => 'Mật khẩu nhập lại không khớp.',
        ];
    }
}
