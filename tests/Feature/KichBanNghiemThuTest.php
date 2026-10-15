<?php

namespace Tests\Feature;

use App\Models\CtDichVu;
use App\Models\DatPhong;
use App\Models\DichVu;
use App\Models\HoaDon;
use App\Models\KhachHang;
use App\Models\NhanVien;
use App\Models\Phong;
use Tests\TestCase;

/**
 * Kịch bản nghiệm thu (mục 6, Giai đoạn 6 của kế hoạch):
 * khách đặt phòng 104 → admin duyệt → nhận phòng → thêm dịch vụ → trả phòng → thanh toán.
 */
class KichBanNghiemThuTest extends TestCase
{
    private KhachHang $teo;

    private NhanVien $viet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teo = KhachHang::where('Email', 'teo@gmail.com')->firstOrFail();
        $this->viet = NhanVien::where('Username', 'viet')->firstOrFail();
    }

    /** Dữ liệu form đặt phòng của khách teo */
    private function formDatPhong(string $ngayNhan, string $ngayTra): array
    {
        return [
            'ngayNhan' => $ngayNhan,
            'ngayTra' => $ngayTra,
            'hoTen' => 'Lê Văn Tèo',
            'dienThoai' => '0912345678',
            'email' => 'teo@gmail.com',
            'cmnd' => '',
            'diaChi' => '',
        ];
    }

    public function test_dat_phong_104_xu_ly_tron_luong_va_hoa_don_bang_1260000(): void
    {
        $homNay = today()->toDateString();
        $sau2Ngay = today()->addDays(2)->toDateString();

        // 1. Khách teo đặt phòng 104 (Bình Dân, 300.000 đ/đêm), nhận hôm nay, trả sau 2 ngày
        $response = $this->actingAs($this->teo, 'web')
            ->post(route('dat-phong.store', 104), $this->formDatPhong($homNay, $sau2Ngay));

        $don = DatPhong::orderByDesc('MaCTDatPhong')->firstOrFail();
        $response->assertRedirect(route('dat-phong.thanh-cong', $don));
        $this->assertSame(DatPhong::CHO_ADMIN_DUYET, $don->TrangThai);
        $this->assertSame(300000, (int) $don->GiaPhongDat);
        $this->assertSame($this->teo->MaKhachHang, (int) $don->MaKhachHang);
        $this->assertSame(104, (int) $don->MaPhong);
        $this->assertSame($homNay, $don->NgayNhan->toDateString());
        $this->assertSame($sau2Ngay, $don->NgayTra->toDateString());

        $this->get(route('dat-phong.thanh-cong', $don))
            ->assertOk()
            ->assertSee('#'.$don->MaCTDatPhong)
            ->assertSee('Phòng 104 (Bình Dân)');

        // 2. Đặt thêm một đơn trùng ngày cho phòng 104 → bị chặn, không tạo đơn
        $soDon = DatPhong::count();
        $this->from(route('dat-phong.create', 104))
            ->post(route('dat-phong.store', 104), $this->formDatPhong(today()->addDay()->toDateString(), today()->addDays(4)->toDateString()))
            ->assertRedirect(route('dat-phong.create', 104))
            ->assertSessionHasErrors(['datPhong' => 'Phòng đã có lịch đặt trong khoảng ngày này. Vui lòng chọn ngày hoặc phòng khác.']);
        $this->assertSame($soDon, DatPhong::count());

        // 3. Admin viet duyệt đơn
        $this->actingAs($this->viet, 'admin')
            ->post(route('admin.dat-phong.duyet', $don))
            ->assertRedirect(route('admin.dat-phong.show', $don))
            ->assertSessionHas('flash.message', "Đã duyệt đơn đặt phòng #{$don->MaCTDatPhong} thành công.");
        $don->refresh();
        $this->assertSame(DatPhong::DA_XAC_NHAN, $don->TrangThai);
        $this->assertSame($this->viet->MaNhanVien, (int) $don->MaNhanVien);
        $this->assertSame(Phong::DA_DAT, Phong::find(104)->TrangThai);

        // 4. Khách nhận phòng
        $this->post(route('admin.dat-phong.nhan-phong', $don))
            ->assertRedirect(route('admin.dat-phong.show', $don));
        $this->assertSame(DatPhong::DANG_SU_DUNG, $don->refresh()->TrangThai);
        $this->assertSame(Phong::DANG_THUE, Phong::find(104)->TrangThai);

        // Vẫn không đặt trùng được khi khách đang ở
        $this->actingAs($this->teo, 'web')
            ->post(route('dat-phong.store', 104), $this->formDatPhong($homNay, today()->addDay()->toDateString()))
            ->assertSessionHasErrors('datPhong');
        $this->assertSame($soDon, DatPhong::count());

        // 5. Thêm dịch vụ: Ăn sáng × 2 (ở chi tiết đơn) và Massage × 1 (trang "Thêm dịch vụ vào đơn")
        $anSang = DichVu::where('TenDichVu', 'Ăn sáng')->value('MaDichVu');
        $massage = DichVu::where('TenDichVu', 'Massage')->value('MaDichVu');

        $this->actingAs($this->viet, 'admin')
            ->post(route('admin.dat-phong.dich-vu.store', $don), ['serviceId' => $anSang, 'quantity' => 2])
            ->assertSessionHas('flash.message', 'Đã thêm dịch vụ vào đơn đặt phòng.');
        $this->post(route('admin.ct-dich-vu.luu-vao-don', $don), ['maDichVu' => $massage, 'soLuong' => 1])
            ->assertSessionHas('flash.message', 'Đã thêm dịch vụ vào đơn thành công.');

        $dichVus = CtDichVu::where('MaCTDatPhong', $don->MaCTDatPhong)->pluck('TongTienDichVu', 'MaDichVu');
        $this->assertSame(60000, (int) $dichVus[$anSang]);
        $this->assertSame(600000, (int) $dichVus[$massage]);

        // 6. Trả phòng → tạo hóa đơn chưa thanh toán, tổng 1.260.000 đ (600.000 + 60.000 + 600.000)
        $this->post(route('admin.dat-phong.tra-phong', $don));
        $hoaDon = HoaDon::where('MaCTDatPhong', $don->MaCTDatPhong)->firstOrFail();
        $this->assertSame(HoaDon::CHUA_THANH_TOAN, $hoaDon->TrangThai);
        $this->assertSame(1260000, (int) $hoaDon->SoTienThu);

        $this->get(route('admin.hoa-don.show', $hoaDon))
            ->assertOk()
            ->assertSee('1.260.000 đ');

        // 7. Thanh toán → hóa đơn DA_THANH_TOAN, đơn DA_HOAN_THANH, payment_status DA_THANH_TOAN, phòng trống lại
        $this->post(route('admin.hoa-don.thanh-toan', $hoaDon))
            ->assertRedirect(route('admin.hoa-don.show', $hoaDon));
        $this->assertSame(HoaDon::DA_THANH_TOAN, $hoaDon->refresh()->TrangThai);
        $don->refresh();
        $this->assertSame(DatPhong::DA_HOAN_THANH, $don->TrangThai);
        $this->assertSame(DatPhong::THANH_TOAN_XONG, $don->payment_status);
        $this->assertSame(Phong::TRONG, Phong::find(104)->TrangThai);

        // Hóa đơn đã thanh toán giữ số tiền cũ dù đổi giá dịch vụ (bản chụp ChiTietJSON)
        DichVu::whereKey($massage)->update(['GiaDichVu' => 900000]);

        // 8. Khách xem chi tiết đơn của mình
        $this->actingAs($this->teo, 'web')
            ->get(route('tai-khoan.chi-tiet', $don))
            ->assertOk()
            ->assertSee('Hoàn tất')
            ->assertSee('2 đêm')
            ->assertSee('1.260.000 đ');
    }

    public function test_khach_vang_lai_dat_trung_ngay_cung_bi_chan(): void
    {
        $ngayNhan = today()->addDays(10)->toDateString();
        $ngayTra = today()->addDays(12)->toDateString();
        $khach = ['hoTen' => 'Khách Vãng Lai', 'dienThoai' => '0901111222'];

        // Lần đầu: khách vãng lai đặt được, tạo khách mới không mật khẩu
        $this->post(route('dat-phong.store', 104), ['ngayNhan' => $ngayNhan, 'ngayTra' => $ngayTra] + $khach)
            ->assertSessionHasNoErrors();
        $khachMoi = KhachHang::where('DienThoai', '0901111222')->firstOrFail();
        $this->assertNull($khachMoi->MatKhau);

        // Khách khác đặt chồng một phần khoảng ngày → bị chặn, không tạo thêm khách
        $soKhach = KhachHang::count();
        $this->post(route('dat-phong.store', 104), [
            'ngayNhan' => today()->addDays(11)->toDateString(),
            'ngayTra' => today()->addDays(13)->toDateString(),
            'hoTen' => 'Người Khác',
            'dienThoai' => '0903333444',
        ])->assertSessionHasErrors(['datPhong' => 'Phòng đã có lịch đặt trong khoảng ngày này. Vui lòng chọn ngày hoặc phòng khác.']);
        $this->assertSame($soKhach, KhachHang::count());

        // Ngày trả của đơn trước = ngày nhận của đơn sau → không trùng
        $this->post(route('dat-phong.store', 104), [
            'ngayNhan' => $ngayTra,
            'ngayTra' => today()->addDays(13)->toDateString(),
            'hoTen' => 'Người Khác',
            'dienThoai' => '0903333444',
        ])->assertSessionHasNoErrors();
    }

    public function test_don_da_hoan_thanh_khong_con_giu_phong(): void
    {
        // Như bản gốc: đơn đã hoàn tất (hoặc đã hủy) không còn chặn lịch của phòng
        $ngayNhan = today()->addDays(5)->toDateString();
        $ngayTra = today()->addDays(6)->toDateString();
        DatPhong::create([
            'NgayThucHien' => today()->toDateString(),
            'MaKhachHang' => 2,
            'NgayNhan' => $ngayNhan,
            'NgayTra' => $ngayTra,
            'MaPhong' => 104,
            'TrangThai' => DatPhong::DA_HOAN_THANH,
            'payment_status' => DatPhong::THANH_TOAN_XONG,
        ]);

        $this->actingAs($this->teo, 'web')
            ->post(route('dat-phong.store', 104), $this->formDatPhong($ngayNhan, $ngayTra))
            ->assertSessionHasNoErrors();
    }
}
