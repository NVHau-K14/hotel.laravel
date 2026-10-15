<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Thêm dịch vụ vào một đơn — dùng cho cả hai nơi của bản gốc:
 *  - "Gọi thêm dịch vụ" ở chi tiết đơn (dat-phong/addService: ô serviceId, quantity)
 *  - trang "Thêm dịch vụ vào đơn" (ct-dich-vu/saveToBooking: ô maDichVu, soLuong)
 * Lỗi thì quay về trang chi tiết đơn. Số lượng > 10.000, dịch vụ không tồn tại... do CtDichVuService kiểm tra.
 */
class ThemDichVuVaoDonRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /** Tên ô theo từng form: [ô dịch vụ, ô số lượng] */
    public function tenO(): array
    {
        return $this->routeIs('admin.dat-phong.dich-vu.store') ? ['serviceId', 'quantity'] : ['maDichVu', 'soLuong'];
    }

    public function rules(): array
    {
        [$oDichVu, $oSoLuong] = $this->tenO();

        return [
            $oDichVu => ['required', 'integer', 'min:1'],
            $oSoLuong => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        $sai = 'Thông tin dịch vụ hoặc số lượng không hợp lệ.';

        return ['required' => $sai, 'integer' => $sai, 'min' => $sai];
    }

    /**
     * Lỗi → về trang chi tiết đơn (bản gốc luôn chuyển về dat-phong&a=detail).
     */
    protected function getRedirectUrl(): string
    {
        return route('admin.dat-phong.show', $this->route('dat_phong'));
    }
}
