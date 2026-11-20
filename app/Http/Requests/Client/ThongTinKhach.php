<?php

namespace App\Http\Requests\Client;

use App\Rules\SoDienThoaiVN;
use Illuminate\Http\Request;

/**
 * Chuẩn hóa và luật kiểm tra thông tin liên hệ của khách — dùng chung cho đăng ký và sửa hồ sơ.
 */
class ThongTinKhach
{
    /**
     * Họ tên bỏ khoảng trắng thừa, SĐT chuẩn hóa về 0xxxxxxxxx, email chữ thường.
     */
    public static function chuanHoa(Request $request): array
    {
        return [
            'hoTen' => preg_replace('/\s+/u', ' ', trim((string) $request->input('hoTen'))),
            'dienThoai' => SoDienThoaiVN::chuanHoa($request->input('dienThoai')),
            'email' => $request->filled('email') ? mb_strtolower(trim((string) $request->input('email'))) : null,
            'cmnd' => $request->filled('cmnd') ? preg_replace('/\s+/', '', (string) $request->input('cmnd')) : null,
        ];
    }

    public static function rules(): array
    {
        return [
            'hoTen' => ['required', 'string', 'max:100'],
            'dienThoai' => ['required', 'string', new SoDienThoaiVN],
            'email' => ['nullable', 'string', 'email:filter', 'max:254'],
            'diaChi' => ['nullable', 'string', 'max:255'],
            'cmnd' => ['nullable', 'string', 'regex:/^(\d{9}|\d{12})$/'],
        ];
    }

    public static function messages(): array
    {
        $thieu = 'Vui lòng điền họ tên, số điện thoại và mật khẩu.';

        return [
            'string' => 'Dữ liệu gửi lên không hợp lệ.',
            'hoTen.required' => $thieu,
            'dienThoai.required' => $thieu,
            'hoTen.max' => 'Họ tên tối đa 100 ký tự.',
            'email.email' => 'Email không hợp lệ.',
            'email.max' => 'Email không hợp lệ.',
            'diaChi.max' => 'Địa chỉ tối đa 255 ký tự.',
            'cmnd.regex' => 'CMND/CCCD phải gồm 9 hoặc 12 chữ số.',
        ];
    }
}
