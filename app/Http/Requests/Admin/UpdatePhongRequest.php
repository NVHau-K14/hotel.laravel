<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\KiemTraAnhTaiLen;
use App\Models\Phong;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Sửa phòng (mã phòng không đổi được). Kiểm tra "đang có lịch đặt" nằm ở Phong::kiemTraDoiLoaiHoacTrangThai().
 */
class UpdatePhongRequest extends FormRequest
{
    use KiemTraAnhTaiLen;

    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'maLoaiPhong' => ['required', 'integer', 'min:1', StorePhongRequest::loaiPhongConHoatDong()],
            'trangThai' => ['sometimes', 'required', Rule::in(Phong::TRANG_THAI)],
            'hinhAnh' => ['nullable', 'string'],
            ...$this->rulesAnh(),
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng chọn loại phòng hợp lệ.';

        return [
            'string' => 'Dữ liệu gửi lên không hợp lệ.',
            'maLoaiPhong.required' => $thieu,
            'maLoaiPhong.integer' => $thieu,
            'maLoaiPhong.min' => $thieu,
            ...StorePhongRequest::messagesTrangThaiVaLoai(),
            ...$this->messagesAnh(),
        ];
    }
}
