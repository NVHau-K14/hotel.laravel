<?php

namespace Tests\Feature;

use App\Services\AnhThuNhoService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Ảnh nhỏ WebP: tạo đúng bề rộng, không phóng to, bỏ qua đường dẫn không phải ảnh nội bộ.
 */
class AnhThuNhoServiceTest extends TestCase
{
    private string $anhThu = '/uploads/phong/kiem-thu-anh-nho.jpg';

    protected function tearDown(): void
    {
        $dichVu = app(AnhThuNhoService::class);
        $goc = $dichVu->duongDanGoc($this->anhThu);
        File::delete(public_path($goc));
        foreach (AnhThuNhoService::BE_RONG as $beRong) {
            File::delete(public_path($dichVu->duongDanThuNho($goc, $beRong)));
        }

        parent::tearDown();
    }

    public function test_tao_anh_nho_va_khong_phong_to_anh_nho_hon_be_rong(): void
    {
        $anh = imagecreatetruecolor(1000, 600);
        imagejpeg($anh, public_path(ltrim($this->anhThu, '/')));
        imagedestroy($anh);

        $dichVu = app(AnhThuNhoService::class);

        // Ảnh rộng 1000px: chỉ tạo bản 640px, không phóng lên 1600px
        $this->assertSame(1, $dichVu->tao($this->anhThu));
        $this->assertSame([640], array_keys($dichVu->anhCoSan($this->anhThu)));
        $this->assertSame(640, getimagesize(public_path($dichVu->anhCoSan($this->anhThu)[640]))[0]);

        // Chạy lại không tạo thêm
        $this->assertSame(0, $dichVu->tao($this->anhThu));

        $this->assertStringContainsString('anh-nho/640/uploads/phong/kiem-thu-anh-nho.webp', anh_url($this->anhThu));
        $this->assertStringContainsString('640w', anh_srcset($this->anhThu));
    }

    public function test_bo_qua_duong_dan_la_va_dung_anh_goc_khi_chua_co_anh_nho(): void
    {
        $dichVu = app(AnhThuNhoService::class);

        $this->assertNull($dichVu->duongDanGoc('/Images/../../.env'));
        $this->assertNull($dichVu->duongDanGoc('https://example.com/a.jpg'));
        $this->assertNull($dichVu->duongDanGoc('/Images/tai-lieu.pdf'));
        $this->assertSame(0, $dichVu->tao('/uploads/phong/khong-ton-tai.jpg'));

        $this->assertSame(asset('/uploads/phong/khong-ton-tai.jpg'), anh_url('/uploads/phong/khong-ton-tai.jpg'));
        $this->assertSame('', anh_srcset('/uploads/phong/khong-ton-tai.jpg'));
        $this->assertSame('', anh_url(null));
    }
}
