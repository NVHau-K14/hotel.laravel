<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Thêm nhân viên (NhanVienController::add + NhanVienModel::create/validateEmployee của bản gốc).
 */
class StoreNhanVienRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:20', 'unique:NHANVIEN,Username'],
            'password' => ['required', 'string'],
            ...self::rulesThongTin(),
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng điền đầy đủ tài khoản, mật khẩu, họ tên và chức vụ.';

        return [
            'username.required' => $thieu,
            'password.required' => $thieu,
            'tenNhanVien.required' => $thieu,
            'maChucVu.required' => $thieu,
            'maChucVu.integer' => $thieu,
            'maChucVu.min' => $thieu,
            'username.max' => 'Tên đăng nhập tối đa 20 ký tự và mật khẩu không được để trống.',
            'username.unique' => 'Tên đăng nhập đã tồn tại.',
            ...self::messagesThongTin(),
        ];
    }

    /**
     * Luật dùng chung cho thêm và sửa: họ tên, chức vụ, CMND, ngày sinh, ngày vào làm.
     */
    public static function rulesThongTin(): array
    {
        return [
            'tenNhanVien' => ['required', 'string', 'max:100'],
            'maChucVu' => ['required', 'integer', 'min:1', 'exists:CHUCVU,MaChucVu'],
            'cmnd' => ['nullable', 'string', 'max:20'],
            'ngaySinh' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'ngayVaoLam' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public static function messagesThongTin(): array
    {
        $sai = 'Họ tên hoặc số giấy tờ nhân viên không hợp lệ.';
        $saiNgay = 'Ngày sinh hoặc ngày vào làm không hợp lệ.';

        return [
            'string' => 'Dữ liệu gửi lên không hợp lệ.',
            'tenNhanVien.max' => $sai,
            'cmnd.max' => $sai,
            // Bản gốc để MySQL báo lỗi khóa ngoại (1452) khi chức vụ không tồn tại
            'maChucVu.exists' => 'Dữ liệu không hợp lệ, quá dài hoặc bản ghi liên quan không còn tồn tại. Vui lòng kiểm tra lại biểu mẫu.',
            'ngaySinh.date_format' => $saiNgay,
            'ngaySinh.before_or_equal' => $saiNgay,
            'ngayVaoLam.date_format' => $saiNgay,
        ];
    }
}
