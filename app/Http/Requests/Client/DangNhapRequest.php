<?php

namespace App\Http\Requests\Client;

use App\Rules\SoDienThoaiVN;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Khách đăng nhập bằng email hoặc số điện thoại.
 */
class DangNhapRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /** Tài khoản là SĐT thì chuẩn hóa (bỏ khoảng trắng, +84 thành 0); là email thì chuyển chữ thường */
    protected function prepareForValidation(): void
    {
        $taiKhoan = trim((string) $this->input('account'));
        $this->merge([
            'account' => str_contains($taiKhoan, '@') ? mb_strtolower($taiKhoan) : SoDienThoaiVN::chuanHoa($taiKhoan),
        ]);
    }

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
