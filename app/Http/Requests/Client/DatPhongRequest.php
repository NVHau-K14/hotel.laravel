<?php

namespace App\Http\Requests\Client;

use App\Models\KhachHang;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Khách gửi yêu cầu đặt phòng (BookingController::create + validate_stay + validate_customer của bản gốc).
 * Trùng lịch, phòng bảo trì, SĐT/email đã thuộc tài khoản khác... do DatPhongService::datPhongTrucTuyen() kiểm tra.
 */
class DatPhongRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function rules(): array
    {
        // Thứ tự như bản gốc: kiểm tra ngày (validate_stay) trước, thông tin khách (validate_customer) sau
        return [
            'ngayNhan' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'ngayTra' => ['required', 'date_format:Y-m-d', 'after:ngayNhan'],
            'hoTen' => ['required', 'string', 'max:255'],
            'dienThoai' => ['required', 'string', 'regex:'.KhachHang::REGEX_DIEN_THOAI],
            'email' => ['nullable', 'string', 'email:filter', 'max:254'],
            'diaChi' => ['nullable', 'string', 'max:255'],
            'cmnd' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        $thieu = 'Vui lòng điền đầy đủ họ tên, số điện thoại, ngày nhận và ngày trả.';
        $saiNgay = 'Ngày nhận/trả không hợp lệ. Ngày trả phải sau ngày nhận.';
        $hoTenSdt = 'Vui lòng nhập họ tên và số điện thoại hợp lệ.';
        $lienHe = 'Email, địa chỉ hoặc số giấy tờ không hợp lệ hoặc quá dài.';

        return [
            'string' => 'Dữ liệu gửi lên không hợp lệ.',
            'required' => $thieu,
            'date_format' => $saiNgay,
            'ngayTra.after' => $saiNgay,
            'ngayNhan.after_or_equal' => 'Ngày nhận phòng không được nằm trong quá khứ.',
            'hoTen.max' => $hoTenSdt,
            'dienThoai.regex' => $hoTenSdt,
            'email.email' => $lienHe,
            'email.max' => $lienHe,
            'diaChi.max' => $lienHe,
            'cmnd.max' => $lienHe,
        ];
    }

    /**
     * Thông tin liên hệ của khách để lưu vào KHACHHANG.
     *
     * @return array{hoTen: string, email: ?string, dienThoai: string, diaChi: ?string, cmnd: ?string}
     */
    public function thongTinKhach(): array
    {
        return $this->only(['hoTen', 'email', 'dienThoai', 'diaChi', 'cmnd']) + array_fill_keys(['email', 'diaChi', 'cmnd'], null);
    }
}
