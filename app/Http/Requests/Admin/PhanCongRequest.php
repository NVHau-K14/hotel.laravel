<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Thêm/sửa phân công (PhanCongController + PhanCongModel của bản gốc).
 * Chỉ khi THÊM mới chặn ngày trong quá khứ; khi sửa thì không.
 */
class PhanCongRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        $ngay = ['required', 'date_format:Y-m-d'];
        if ($this->isMethod('post')) {
            $ngay[] = 'after_or_equal:today';
        }

        return [
            'ngayPhanCong' => $ngay,
            'loaiCongViec' => ['required', 'string', 'max:50'],
            'maNhanVien' => ['nullable', 'integer', 'exists:NHANVIEN,MaNhanVien'],
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng chọn ngày phân công và nhập loại công việc.';
        $sai = 'Ngày hoặc nội dung phân công không hợp lệ (tối đa 50 ký tự).';

        return [
            'string' => 'Dữ liệu gửi lên không hợp lệ.',
            'ngayPhanCong.required' => $thieu,
            'loaiCongViec.required' => $thieu,
            'ngayPhanCong.date_format' => $sai,
            'loaiCongViec.max' => $sai,
            'ngayPhanCong.after_or_equal' => 'Không được phân công ngày trong quá khứ.',
            'maNhanVien.integer' => 'Dữ liệu gửi lên không hợp lệ.',
            'maNhanVien.exists' => 'Dữ liệu không hợp lệ, quá dài hoặc bản ghi liên quan không còn tồn tại. Vui lòng kiểm tra lại biểu mẫu.',
        ];
    }
}
