<?php

namespace App\Http\Requests\Admin;

use App\Models\DatPhong;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Admin tạo đơn đặt phòng (DatPhongController::add của bản gốc).
 * Các quy tắc nghiệp vụ (trùng lịch, quá khứ, khách bị khóa...) nằm ở DatPhongService::createBooking().
 */
class StoreDatPhongRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'maKhachHang' => ['required', 'integer', 'min:1'],
            'maPhong' => ['required', 'integer', 'min:1'],
            'ngayNhan' => ['required', 'date_format:Y-m-d'],
            'ngayTra' => ['required', 'date_format:Y-m-d', 'after:ngayNhan'],
            'trangThai' => ['nullable', Rule::in(DatPhong::TRANG_THAI_GIU_PHONG)],
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng điền đầy đủ khách hàng, phòng, ngày nhận và ngày trả.';
        $saiNgay = 'Ngày trả phòng phải sau ngày nhận phòng.';

        return [
            'required' => $thieu,
            'integer' => $thieu,
            'min' => $thieu,
            'ngayNhan.date_format' => $saiNgay,
            'ngayTra.date_format' => $saiNgay,
            'ngayTra.after' => $saiNgay,
            'trangThai.in' => 'Trạng thái đặt phòng không hợp lệ.',
        ];
    }
}
