<?php

namespace App\Http\Requests\Client;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Khách gửi đánh giá (ReviewController::submit của bản gốc).
 * Phòng phải có lần lưu trú hoàn tất chưa đánh giá: kiểm tra ở ReviewController + DanhGia::taoDanhGia().
 */
class GuiDanhGiaRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'maPhong' => ['required', 'integer', 'min:1'],
            'noiDung' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        $chonPhong = 'Vui lòng chọn phòng cần đánh giá.';

        return [
            'maPhong.required' => $chonPhong,
            'maPhong.integer' => $chonPhong,
            'maPhong.min' => $chonPhong,
            'noiDung.required' => 'Vui lòng nhập nội dung đánh giá.',
            'noiDung.string' => 'Vui lòng nhập nội dung đánh giá.',
            'noiDung.max' => 'Đánh giá cần nội dung tối đa 255 ký tự và ngày hợp lệ.',
        ];
    }

    /**
     * Bản gốc báo lỗi bằng thông báo flash rồi quay về trang đánh giá (không hiện lỗi trong form).
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            redirect()->route('danh-gia.index')->thongBao('danger', $validator->errors()->first())
        );
    }
}
