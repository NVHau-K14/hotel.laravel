<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\KiemTraAnhTaiLen;
use App\Models\Phong;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Thêm phòng (PhongController::add + PhongModel::validateRoom của bản gốc).
 */
class StorePhongRequest extends FormRequest
{
    use KiemTraAnhTaiLen;

    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'maPhong' => ['required', 'integer', 'min:1', 'max:2147483647', Rule::unique('PHONG', 'MaPhong')],
            'maLoaiPhong' => ['required', 'integer', 'min:1', self::loaiPhongConHoatDong()],
            // Không gửi ô trạng thái thì mặc định "Trống"
            'trangThai' => ['sometimes', 'required', Rule::in(Phong::TRANG_THAI)],
            'hinhAnh' => ['nullable', 'string'],
            ...$this->rulesAnh(),
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng nhập mã phòng và chọn loại phòng hợp lệ.';

        return [
            'string' => 'Dữ liệu gửi lên không hợp lệ.',
            'maPhong.required' => $thieu,
            'maPhong.integer' => $thieu,
            'maPhong.min' => $thieu,
            'maPhong.max' => 'Dữ liệu không hợp lệ, quá dài hoặc bản ghi liên quan không còn tồn tại. Vui lòng kiểm tra lại biểu mẫu.',
            'maPhong.unique' => 'Mã phòng '.$this->input('maPhong').' đã tồn tại trên hệ thống.',
            'maLoaiPhong.required' => $thieu,
            'maLoaiPhong.integer' => $thieu,
            'maLoaiPhong.min' => $thieu,
            ...self::messagesTrangThaiVaLoai(),
            ...$this->messagesAnh(),
        ];
    }

    /**
     * Loại phòng phải tồn tại và chưa bị xóa mềm.
     */
    public static function loaiPhongConHoatDong(): \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists('LOAIPHONG', 'MaLoaiPhong')
            ->where(fn (Builder $q) => $q->where('DaXoa', false)->orWhereNull('DaXoa'));
    }

    public static function messagesTrangThaiVaLoai(): array
    {
        return [
            'trangThai.required' => 'Trạng thái phòng không hợp lệ.',
            'trangThai.in' => 'Trạng thái phòng không hợp lệ.',
            'maLoaiPhong.exists' => 'Loại phòng không tồn tại hoặc đã ngừng sử dụng.',
        ];
    }
}
