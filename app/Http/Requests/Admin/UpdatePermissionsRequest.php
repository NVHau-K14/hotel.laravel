<?php

namespace App\Http\Requests\Admin;

use App\Models\ChucVu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Lưu danh sách quyền của một chức vụ. Không tick ô nào thì xóa hết quyền.
 */
class UpdatePermissionsRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(ChucVu::QUYEN))],
        ];
    }

    public function messages(): array
    {
        $sai = 'Danh sách quyền không hợp lệ.';

        return [
            'permissions.array' => $sai,
            'permissions.*.string' => $sai,
            'permissions.*.in' => $sai,
        ];
    }
}
