<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Thêm/sửa loại phòng (LoaiPhongController + LoaiPhongModel::validate của bản gốc).
 */
class LoaiPhongRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            // Không trùng tên với loại phòng đang hoạt động khác
            'tenLoaiPhong' => ['required', 'string', 'max:50', Rule::unique('LOAIPHONG', 'TenLoaiPhong')
                ->ignore($this->route('loai_phong')?->MaLoaiPhong, 'MaLoaiPhong')
                ->where(fn ($q) => $q->where('DaXoa', false)->orWhereNull('DaXoa'))],
            'giaPhong' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'moTa' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng nhập tên loại phòng và đơn giá hợp lệ.';
        $quaDai = 'Tên loại phòng tối đa 50 ký tự, mô tả tối đa 255 ký tự và giá phải lớn hơn 0.';

        return [
            'string' => 'Dữ liệu gửi lên không hợp lệ.',
            'tenLoaiPhong.required' => $thieu,
            'giaPhong.required' => $thieu,
            'giaPhong.integer' => $thieu,
            'giaPhong.min' => $thieu,
            'tenLoaiPhong.max' => $quaDai,
            'tenLoaiPhong.unique' => 'Đã có loại phòng tên "'.$this->input('tenLoaiPhong').'".',
            'giaPhong.max' => $quaDai,
            'moTa.max' => $quaDai,
        ];
    }
}
