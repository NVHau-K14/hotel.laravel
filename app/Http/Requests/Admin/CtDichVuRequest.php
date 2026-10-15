<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Thêm chi tiết dịch vụ từ trang "Chi tiết DV" (CtDichVuController::add của bản gốc).
 * Đặt SỐ LƯỢNG cho dịch vụ trong đơn (không cộng dồn). Các quy tắc còn lại ở CtDichVuService::save().
 */
class CtDichVuRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'maCTDatPhong' => ['required', 'integer', 'min:1'],
            'maDichVu' => ['required', 'integer', 'min:1'],
            'soLuong' => ['required', 'integer', 'min:1'],
            'maCTDichVu' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        $sai = 'Vui lòng chọn dịch vụ, đơn đặt phòng và số lượng hợp lệ (> 0).';

        return ['required' => $sai, 'integer' => $sai, 'min' => $sai];
    }
}
