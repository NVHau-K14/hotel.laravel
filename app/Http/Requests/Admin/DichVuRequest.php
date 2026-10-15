<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\KiemTraAnhTaiLen;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Thêm/sửa dịch vụ (DichVuController + DichVuModel::validate của bản gốc).
 */
class DichVuRequest extends FormRequest
{
    use KiemTraAnhTaiLen;

    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'tenDichVu' => ['required', 'string', 'max:50'],
            'giaDichVu' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'hinhAnh' => ['nullable', 'string'],
            ...$this->rulesAnh(),
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng nhập tên dịch vụ và đơn giá hợp lệ.';
        $quaDai = 'Tên dịch vụ tối đa 50 ký tự và giá phải lớn hơn 0.';

        return [
            'string' => 'Dữ liệu gửi lên không hợp lệ.',
            'tenDichVu.required' => $thieu,
            'giaDichVu.required' => $thieu,
            'giaDichVu.integer' => $thieu,
            'giaDichVu.min' => $thieu,
            'tenDichVu.max' => $quaDai,
            'giaDichVu.max' => $quaDai,
            ...$this->messagesAnh(),
        ];
    }
}
