<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Khách sửa thông tin liên hệ ở trang hồ sơ. Trùng SĐT/email tài khoản khác: KhachHang::capNhatThongTin().
 */
class CapNhatHoSoRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    protected function prepareForValidation(): void
    {
        $this->merge(ThongTinKhach::chuanHoa($this));
    }

    public function rules(): array
    {
        return ThongTinKhach::rules();
    }

    public function messages(): array
    {
        return [
            ...ThongTinKhach::messages(),
            'hoTen.required' => 'Vui lòng nhập họ tên và số điện thoại.',
            'dienThoai.required' => 'Vui lòng nhập họ tên và số điện thoại.',
        ];
    }
}
