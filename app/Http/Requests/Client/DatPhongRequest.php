<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Khách (đã đăng nhập) gửi yêu cầu đặt phòng: chỉ chọn ngày. Thông tin liên hệ lấy từ tài khoản.
 * Trùng lịch, phòng bảo trì, giới hạn số đêm / số đơn chờ duyệt... do DatPhongService::datPhongTrucTuyen() kiểm tra.
 */
class DatPhongRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'ngayNhan' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'ngayTra' => ['required', 'date_format:Y-m-d', 'after:ngayNhan'],
        ];
    }

    public function messages(): array
    {
        $saiNgay = 'Ngày nhận/trả không hợp lệ. Ngày trả phải sau ngày nhận.';

        return [
            'required' => 'Vui lòng chọn ngày nhận và ngày trả phòng.',
            'date_format' => $saiNgay,
            'ngayTra.after' => $saiNgay,
            'ngayNhan.after_or_equal' => 'Ngày nhận phòng không được nằm trong quá khứ.',
        ];
    }
}
