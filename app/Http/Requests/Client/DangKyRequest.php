<?php

namespace App\Http\Requests\Client;

use App\Models\KhachHang;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Đăng ký tài khoản khách hàng (AccountController::register + validate_customer của bản gốc).
 * Email trùng được kiểm tra ở KhachHang::taoKhachHang().
 */
class DangKyRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'hoTen' => ['required', 'string', 'max:255'],
            'dienThoai' => ['required', 'string', 'unique:KHACHHANG,DienThoai', 'regex:'.KhachHang::REGEX_DIEN_THOAI],
            'password' => ['required', 'string'],
            'email' => ['nullable', 'string', 'email:filter', 'max:254'],
            'diaChi' => ['nullable', 'string', 'max:255'],
            'cmnd' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng điền họ tên, số điện thoại và mật khẩu.';
        $hoTenSdt = 'Vui lòng nhập họ tên và số điện thoại hợp lệ.';
        $lienHe = 'Email, địa chỉ hoặc số giấy tờ không hợp lệ hoặc quá dài.';

        return [
            'string' => 'Dữ liệu gửi lên không hợp lệ.',
            'hoTen.required' => $thieu,
            'dienThoai.required' => $thieu,
            'password.required' => $thieu,
            'dienThoai.unique' => 'Số điện thoại này đã được đăng ký tài khoản.',
            'hoTen.max' => $hoTenSdt,
            'dienThoai.regex' => $hoTenSdt,
            'email.email' => $lienHe,
            'email.max' => $lienHe,
            'diaChi.max' => $lienHe,
            'cmnd.max' => $lienHe,
        ];
    }
}
