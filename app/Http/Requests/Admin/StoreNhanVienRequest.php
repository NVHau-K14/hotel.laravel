<?php

namespace App\Http\Requests\Admin;

use App\Rules\MatKhauManh;
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
            'username' => ['required', 'string', 'regex:/^[a-z0-9_]{3,20}$/', 'unique:NHANVIEN,Username'],
            'password' => ['required', 'string', new MatKhauManh([$this->input('username')])],
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
            'username.regex' => 'Tên đăng nhập dài 3–20 ký tự, chỉ gồm chữ thường không dấu, chữ số và dấu gạch dưới.',
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
            // Nhân viên phải đủ 18 tuổi; ngày vào làm sau ngày sinh ít nhất 18 năm và không ở tương lai quá 1 năm
            'ngaySinh' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:-18 years', 'after:1900-01-01'],
            'ngayVaoLam' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:+1 year', function (string $attr, mixed $value, \Closure $fail) {
                $ngaySinh = request()->input('ngaySinh');
                if ($value && $ngaySinh && strtotime($ngaySinh) && strtotime($value) < strtotime($ngaySinh.' +18 years')) {
                    $fail('Ngày vào làm phải sau ngày nhân viên đủ 18 tuổi.');
                }
            }],
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
            'ngaySinh.before_or_equal' => 'Nhân viên phải đủ 18 tuổi.',
            'ngaySinh.after' => $saiNgay,
            'ngayVaoLam.date_format' => $saiNgay,
            'ngayVaoLam.before_or_equal' => $saiNgay,
        ];
    }
}
