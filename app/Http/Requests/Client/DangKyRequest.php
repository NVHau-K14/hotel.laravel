<?php

namespace App\Http\Requests\Client;

use App\Rules\MatKhauManh;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Đăng ký tài khoản khách hàng.
 * SĐT/email trùng tài khoản khác được kiểm tra ở KhachHang::dangKy() (khách vãng lai cũ được nhận lại bản ghi).
 */
class DangKyRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    protected function prepareForValidation(): void
    {
        $this->merge(ThongTinKhach::chuanHoa($this));
    }

    public function rules(): array
    {
        return [
            ...ThongTinKhach::rules(),
            'password' => ['required', 'string', new MatKhauManh([$this->input('dienThoai'), $this->input('email')]), 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            ...ThongTinKhach::messages(),
            'password.required' => 'Vui lòng điền họ tên, số điện thoại và mật khẩu.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
        ];
    }
}
