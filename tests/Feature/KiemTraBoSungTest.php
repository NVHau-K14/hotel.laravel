<?php

namespace Tests\Feature;

use App\Models\CtDichVu;
use App\Models\DanhGia;
use App\Models\DatPhong;
use App\Models\DichVu;
use App\Models\HoaDon;
use App\Models\KhachHang;
use App\Models\LoaiPhong;
use App\Models\NhanVien;
use App\Services\DatPhongService;
use App\Services\HoaDonService;
use App\Services\ThongKeService;
use Tests\TestCase;

/**
 * Các tình huống nghiệm thu bổ sung (TC11, TC14–TC18, TC20 trong báo cáo):
 * thanh toán sai trạng thái, thanh toán lặp, bản chụp hóa đơn, khóa dịch vụ sau thanh toán,
 * điều kiện đánh giá, xóa mềm loại phòng và doanh thu chỉ tính hóa đơn đã thanh toán.
 */
class KiemTraBoSungTest extends TestCase
{
    private NhanVien $viet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viet = NhanVien::where('Username', 'viet')->firstOrFail();
    }

    /** Tạo đơn của khách teo cho phòng $maPhong, nhận hôm nay, trả sau 2 ngày, đã nhận phòng */
    private function donDangO(int $maPhong = 104): DatPhong
    {
        $datPhong = app(DatPhongService::class);
        $ma = $datPhong->createBooking(1, $maPhong, today()->toDateString(), today()->addDays(2)->toDateString());
        $datPhong->updateStatus($ma, DatPhong::DA_XAC_NHAN, $this->viet->MaNhanVien);
        $datPhong->updateStatus($ma, DatPhong::DANG_SU_DUNG, $this->viet->MaNhanVien);

        return DatPhong::findOrFail($ma);
    }

    /** Trả phòng và thanh toán qua route quản trị, trả về hóa đơn */
    private function traPhongVaThanhToan(DatPhong $don): HoaDon
    {
        $this->actingAs($this->viet, 'admin')->post(route('admin.dat-phong.tra-phong', $don));
        $hoaDon = HoaDon::where('MaCTDatPhong', $don->MaCTDatPhong)->firstOrFail();
        $this->post(route('admin.hoa-don.thanh-toan', $hoaDon));

        return $hoaDon->refresh();
    }

    public function test_tc11_khong_thu_tien_khi_don_chua_nhan_phong(): void
    {
        $datPhong = app(DatPhongService::class);
        $ma = $datPhong->createBooking(1, 104, today()->addDays(3)->toDateString(), today()->addDays(5)->toDateString());
        $datPhong->updateStatus($ma, DatPhong::DA_XAC_NHAN, $this->viet->MaNhanVien);

        // Trả phòng khi đơn mới xác nhận → không lập hóa đơn
        $this->actingAs($this->viet, 'admin')->post(route('admin.dat-phong.tra-phong', $ma));
        $this->assertSame(0, HoaDon::where('MaCTDatPhong', $ma)->count());

        // Có hóa đơn tạm nhưng đơn chưa nhận phòng → không thanh toán được
        $maHoaDon = app(HoaDonService::class)->createOrUpdateInvoice($ma, $this->viet->MaNhanVien);
        $this->post(route('admin.hoa-don.thanh-toan', $maHoaDon));
        $this->assertSame(HoaDon::CHUA_THANH_TOAN, HoaDon::find($maHoaDon)->TrangThai);
        $this->assertSame(DatPhong::DA_XAC_NHAN, DatPhong::find($ma)->TrangThai);
    }

    public function test_tc14_gui_lap_thanh_toan_khong_thu_tien_hai_lan(): void
    {
        $don = $this->donDangO();
        $hoaDon = $this->traPhongVaThanhToan($don);
        $this->assertSame(HoaDon::DA_THANH_TOAN, $hoaDon->TrangThai);

        $this->post(route('admin.hoa-don.thanh-toan', $hoaDon));

        $this->assertSame(1, HoaDon::where('MaCTDatPhong', $don->MaCTDatPhong)->where('TrangThai', HoaDon::DA_THANH_TOAN)->count());
        $this->assertSame(600000, (int) $hoaDon->refresh()->SoTienThu);
    }

    public function test_tc15_doi_gia_dich_vu_khong_lam_doi_hoa_don_da_thanh_toan(): void
    {
        $don = $this->donDangO();
        $this->actingAs($this->viet, 'admin')
            ->post(route('admin.dat-phong.dich-vu.store', $don), ['serviceId' => 1, 'quantity' => 2]);
        $hoaDon = $this->traPhongVaThanhToan($don);
        $this->assertSame(660000, (int) $hoaDon->SoTienThu);

        DichVu::whereKey(1)->update(['GiaDichVu' => 99000]);

        $this->assertSame(660000, (int) $hoaDon->refresh()->SoTienThu);
        $this->get(route('admin.hoa-don.show', $hoaDon))
            ->assertOk()
            ->assertSee('660.000')
            ->assertDontSee('99.000');
    }

    public function test_tc16_khong_them_dich_vu_sau_khi_da_thanh_toan(): void
    {
        $don = $this->donDangO();
        $this->traPhongVaThanhToan($don);
        $soDong = CtDichVu::where('MaCTDatPhong', $don->MaCTDatPhong)->count();

        $this->post(route('admin.dat-phong.dich-vu.store', $don), ['serviceId' => 4, 'quantity' => 1]);

        $this->assertSame($soDong, CtDichVu::where('MaCTDatPhong', $don->MaCTDatPhong)->count());
    }

    public function test_tc17_khong_danh_gia_don_chua_hoan_tat(): void
    {
        $this->donDangO(105);
        $soDanhGia = DanhGia::count();

        $this->actingAs(KhachHang::findOrFail(1), 'web')
            ->post(route('danh-gia.store'), ['maPhong' => 105, 'soSao' => 5, 'noiDung' => 'Đánh giá khi chưa trả phòng'])
            ->assertSessionHas('flash.message', 'Bạn chỉ được đánh giá phòng đã đặt và đã hoàn thành ngày trả phòng.');

        $this->assertSame($soDanhGia, DanhGia::count());
    }

    public function test_tc18_xoa_loai_phong_con_phong_chi_danh_dau_da_xoa(): void
    {
        $this->actingAs($this->viet, 'admin')->delete(route('admin.loai-phong.destroy', 1));

        $loaiPhong = LoaiPhong::find(1);
        $this->assertNotNull($loaiPhong);
        $this->assertTrue((bool) $loaiPhong->DaXoa);
    }

    public function test_tc20_doanh_thu_chi_tinh_hoa_don_da_thanh_toan(): void
    {
        $thongKe = app(ThongKeService::class);
        $doanhThuThang = fn () => $thongKe->getMonthlyRevenue((int) now()->year)[(int) now()->month];
        $truoc = $doanhThuThang();

        $don = $this->donDangO();
        $maHoaDon = app(HoaDonService::class)->checkout($don->MaCTDatPhong, $this->viet->MaNhanVien);
        $this->assertSame($truoc, $doanhThuThang());

        app(HoaDonService::class)->payInvoice($maHoaDon);
        $this->assertSame($truoc + 600000, $doanhThuThang());
    }
}
