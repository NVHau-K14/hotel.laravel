<?php

namespace Tests\Feature;

use App\Models\DatPhong;
use App\Models\KhachHang;
use App\Models\Phong;
use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Quy tắc khi khách đặt phòng trên website (Form Request + DatPhongService).
 */
class DatPhongKhachTest extends TestCase
{
    private function form(array $thayDoi = []): array
    {
        return array_merge([
            'ngayNhan' => today()->addDay()->toDateString(),
            'ngayTra' => today()->addDays(2)->toDateString(),
            'hoTen' => 'Nguyễn Văn Khách',
            'dienThoai' => '0905555666',
        ], $thayDoi);
    }

    /**
     * Mỗi dòng: hàm trả về dữ liệu sai (gọi sau khi ứng dụng đã khởi động vì cần ngày hôm nay) → câu báo lỗi giống bản gốc.
     */
    public static function duLieuSai(): array
    {
        return [
            'thiếu họ tên' => [fn () => ['hoTen' => ''], 'Vui lòng điền đầy đủ họ tên, số điện thoại, ngày nhận và ngày trả.'],
            'ngày trả trước ngày nhận (R01)' => [fn () => ['ngayTra' => today()->toDateString()], 'Ngày nhận/trả không hợp lệ. Ngày trả phải sau ngày nhận.'],
            'ngày nhận ở quá khứ (R02)' => [fn () => ['ngayNhan' => today()->subDay()->toDateString()], 'Ngày nhận phòng không được nằm trong quá khứ.'],
            'số điện thoại sai (R03)' => [fn () => ['dienThoai' => 'abc'], 'Vui lòng nhập họ tên và số điện thoại hợp lệ.'],
            'email sai (R04)' => [fn () => ['email' => 'khong-phai-email'], 'Email, địa chỉ hoặc số giấy tờ không hợp lệ hoặc quá dài.'],
            'SĐT đã có tài khoản (R38)' => [fn () => ['dienThoai' => '0912345678'], 'Số điện thoại hoặc email đã được đăng ký. Vui lòng đăng nhập tài khoản này.'],
        ];
    }

    #[DataProvider('duLieuSai')]
    public function test_du_lieu_sai_bi_chan_dung_thong_bao(Closure $thayDoi, string $thongBao): void
    {
        $soDon = DatPhong::count();

        // Quay lại form đặt phòng, câu lỗi hiện trong khung báo lỗi của form
        $this->from(route('dat-phong.create', 101))
            ->followingRedirects()
            ->post(route('dat-phong.store', 101), $this->form($thayDoi()))
            ->assertOk()
            ->assertSee('<div class="alert alert-danger mb-4">'.e($thongBao).'</div>', false);

        $this->assertSame($soDon, DatPhong::count());
    }

    public function test_phong_bao_tri_khong_dat_duoc(): void
    {
        Phong::whereKey(105)->update(['TrangThai' => Phong::BAO_TRI]);

        $this->post(route('dat-phong.store', 105), $this->form())
            ->assertSessionHasErrors(['datPhong' => 'Phòng đang bảo trì hoặc loại phòng đã ngừng sử dụng.']);
    }

    public function test_phong_khong_ton_tai_ve_danh_sach_phong(): void
    {
        $this->get(route('dat-phong.create', 999))
            ->assertRedirect(route('phong.index'))
            ->assertSessionHas('flash.message', 'Vui lòng chọn phòng hợp lệ để đặt.');
    }

    public function test_khach_vang_lai_dat_tiep_trong_phien_dung_lai_khach_cu(): void
    {
        $this->post(route('dat-phong.store', 101), $this->form())
            ->assertSessionHas('guest_customer_id');
        $don1 = DatPhong::orderByDesc('MaCTDatPhong')->firstOrFail();
        $this->assertTrue(session("guest_booking_ids.{$don1->MaCTDatPhong}"));

        // Cùng phiên, đặt phòng khác với họ tên mới → không tạo khách mới, thông tin được cập nhật
        $soKhach = KhachHang::count();
        $this->post(route('dat-phong.store', 102), $this->form(['hoTen' => 'Tên Đã Sửa']));
        $don2 = DatPhong::orderByDesc('MaCTDatPhong')->firstOrFail();

        $this->assertSame($soKhach, KhachHang::count());
        $this->assertSame((int) $don1->MaKhachHang, (int) $don2->MaKhachHang);
        $this->assertSame('Tên Đã Sửa', KhachHang::find($don2->MaKhachHang)->HoTen);
    }

    public function test_chi_chu_don_hoac_phien_da_dat_moi_xem_duoc_trang_thanh_cong(): void
    {
        // Đơn mẫu #2 là của khách Tí
        $this->get(route('dat-phong.thanh-cong', 2))->assertRedirect(route('trang-chu'));

        $this->withSession(['guest_booking_ids' => [2 => true]])
            ->get(route('dat-phong.thanh-cong', 2))->assertOk();

        $this->actingAs(KhachHang::where('Email', 'ti@gmail.com')->firstOrFail(), 'web')
            ->get(route('dat-phong.thanh-cong', 2))->assertOk();
    }

    public function test_khach_khong_xem_duoc_don_cua_nguoi_khac(): void
    {
        $this->actingAs(KhachHang::where('Email', 'teo@gmail.com')->firstOrFail(), 'web')
            ->get(route('tai-khoan.chi-tiet', 2))
            ->assertRedirect(route('tai-khoan.lich-su'))
            ->assertSessionHas('flash.message', 'Không tìm thấy đơn đặt phòng hoặc bạn không có quyền truy cập.');
    }
}
