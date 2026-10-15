<?php

namespace Tests\Unit;

use App\Services\DatPhongService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Hàm tiện ích không cần database: định dạng tiền, ngày và kiểm tra ngày hợp lệ.
 */
class HelperTest extends TestCase
{
    public function test_format_currency_dinh_dang_kieu_viet_nam(): void
    {
        $this->assertSame('1.260.000 đ', format_currency(1260000));
        $this->assertSame('0 đ', format_currency(null));
        $this->assertSame('0 đ', format_currency(''));
    }

    public function test_format_date_nhan_chuoi_va_carbon(): void
    {
        $this->assertSame('07/05/2026', format_date('2026-05-07'));
        $this->assertSame('07/05/2026', format_date(Carbon::parse('2026-05-07')));
        $this->assertSame('', format_date(null));
    }

    public function test_ngay_hop_le_chi_nhan_ngay_co_that_dang_y_m_d(): void
    {
        $this->assertTrue(DatPhongService::ngayHopLe('2026-02-28'));
        $this->assertFalse(DatPhongService::ngayHopLe('2026-02-30'));
        $this->assertFalse(DatPhongService::ngayHopLe('15/10/2026'));
        $this->assertFalse(DatPhongService::ngayHopLe(null));
    }
}
