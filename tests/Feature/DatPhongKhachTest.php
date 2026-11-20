<?php

namespace Tests\Feature;

use App\Models\DatPhong;
use App\Models\KhachHang;
use App\Models\LoaiPhong;
use App\Models\Phong;
use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Quy tắc khi khách đặt phòng trên website: bắt buộc đăng nhập, thông tin lấy từ tài khoản,
 * giới hạn chống giữ chỗ ảo (Form Request + DatPhongService).
 */
class DatPhongKhachTest extends TestCase
{
    private function teo(): KhachHang
    {
        return KhachHang::where('Email', 'teo@gmail.com')->firstOrFail();
    }

    private function form(array $thayDoi = []): array
    {
        return array_merge([
            'ngayNhan' => today()->addDay()->toDateString(),
            'ngayTra' => today()->addDays(2)->toDateString(),
        ], $thayDoi);
    }

    public function test_chua_dang_nhap_chi_xem_duoc_va_bi_chuyen_sang_dang_nhap(): void
    {
        $this->get(route('phong.show', 104))->assertOk()->assertSee('Đăng nhập để đặt phòng');

        $this->get(route('dat-phong.create', 104))
            ->assertRedirect(route('tai-khoan.dang-nhap'))
            ->assertSessionHas('flash.message', 'Vui lòng đăng nhập để đặt phòng. Chưa có tài khoản? Hãy đăng ký miễn phí.');

        $soDon = DatPhong::count();
        $this->post(route('dat-phong.store', 104), $this->form())->assertRedirect(route('tai-khoan.dang-nhap'));
        $this->assertSame($soDon, DatPhong::count());
    }

    public function test_dang_nhap_xong_quay_lai_dung_trang_dat_phong(): void
    {
        $this->get(route('dat-phong.create', 104));

        $this->post(route('tai-khoan.dang-nhap.store'), ['account' => 'teo@gmail.com', 'password' => 'Khach@1234'])
            ->assertRedirect(route('dat-phong.create', 104));
    }

    public function test_dat_phong_khong_sua_ho_so_tai_khoan(): void
    {
        $teo = $this->teo();
        $this->actingAs($teo, 'web')
            ->post(route('dat-phong.store', 104), $this->form(['hoTen' => 'Người Khác', 'dienThoai' => '0987654321']))
            ->assertSessionHasNoErrors();

        $teo->refresh();
        $this->assertSame('Lê Văn Tèo', $teo->HoTen);
        $this->assertSame('0912345678', $teo->DienThoai);
        $this->assertSame($teo->MaKhachHang, (int) DatPhong::orderByDesc('MaCTDatPhong')->value('MaKhachHang'));
    }

    /**
     * Mỗi dòng: hàm trả về dữ liệu sai → câu báo lỗi.
     */
    public static function duLieuSai(): array
    {
        return [
            'thiếu ngày' => [fn () => ['ngayNhan' => ''], 'Vui lòng chọn ngày nhận và ngày trả phòng.'],
            'ngày trả trước ngày nhận' => [fn () => ['ngayTra' => today()->toDateString()], 'Ngày nhận/trả không hợp lệ. Ngày trả phải sau ngày nhận.'],
            'ngày nhận ở quá khứ' => [fn () => ['ngayNhan' => today()->subDay()->toDateString()], 'Ngày nhận phòng không được nằm trong quá khứ.'],
            'quá 30 đêm' => [fn () => ['ngayTra' => today()->addDays(40)->toDateString()], 'Mỗi lần đặt trực tuyến tối đa 30 đêm. Vui lòng liên hệ khách sạn để đặt dài ngày.'],
            'đặt trước quá 365 ngày' => [fn () => ['ngayNhan' => today()->addDays(400)->toDateString(), 'ngayTra' => today()->addDays(401)->toDateString()], 'Chỉ nhận đặt phòng trước tối đa 365 ngày.'],
        ];
    }

    #[DataProvider('duLieuSai')]
    public function test_du_lieu_sai_bi_chan_dung_thong_bao(Closure $thayDoi, string $thongBao): void
    {
        $soDon = DatPhong::count();

        $this->actingAs($this->teo(), 'web')
            ->from(route('dat-phong.create', 104))
            ->followingRedirects()
            ->post(route('dat-phong.store', 104), $this->form($thayDoi()))
            ->assertOk()
            ->assertSee('<div class="alert alert-danger mb-4">'.e($thongBao).'</div>', false);

        $this->assertSame($soDon, DatPhong::count());
    }

    public function test_toi_da_3_don_cho_duyet_cung_luc(): void
    {
        $this->actingAs($this->teo(), 'web');
        foreach ([104, 105, 203] as $phong) {
            $this->post(route('dat-phong.store', $phong), $this->form(['ngayNhan' => today()->addDays(10)->toDateString(), 'ngayTra' => today()->addDays(11)->toDateString()]))
                ->assertSessionHasNoErrors();
        }

        $this->post(route('dat-phong.store', 204), $this->form())
            ->assertSessionHasErrors(['datPhong' => 'Bạn đang có 3 đơn chờ duyệt. Vui lòng chờ khách sạn xác nhận trước khi đặt thêm.']);
    }

    public function test_phong_bao_tri_khong_dat_duoc_va_khong_hien_form(): void
    {
        Phong::whereKey(105)->update(['TrangThai' => Phong::BAO_TRI]);
        $this->actingAs($this->teo(), 'web');

        $this->get(route('dat-phong.create', 105))->assertOk()->assertDontSee('Xác nhận gửi yêu cầu đặt phòng');
        $this->post(route('dat-phong.store', 105), $this->form())
            ->assertSessionHasErrors(['datPhong' => 'Phòng đang bảo trì hoặc loại phòng đã ngừng sử dụng.']);
    }

    public function test_phong_thuoc_loai_da_xoa_khong_hien_o_trang_khach(): void
    {
        // Loại Vip (3) không có đơn nào → xóa mềm được
        LoaiPhong::whereKey(3)->update(['DaXoa' => true]);

        $this->get(route('phong.index'))->assertOk()->assertDontSee('Phòng 301')->assertSee('Phòng 101');
        $this->get(route('phong.show', 301))->assertRedirect(route('phong.index'));
    }

    public function test_phong_khong_ton_tai_ve_danh_sach_phong(): void
    {
        $this->actingAs($this->teo(), 'web')
            ->get(route('dat-phong.create', 999))
            ->assertRedirect(route('phong.index'))
            ->assertSessionHas('flash.message', 'Vui lòng chọn phòng hợp lệ để đặt.');
    }

    public function test_chi_chu_don_moi_xem_duoc_trang_thanh_cong(): void
    {
        // Đơn mẫu #2 là của khách Tí
        $this->actingAs($this->teo(), 'web')
            ->get(route('dat-phong.thanh-cong', 2))->assertRedirect(route('trang-chu'));
        $this->actingAs(KhachHang::where('Email', 'ti@gmail.com')->firstOrFail(), 'web')
            ->get(route('dat-phong.thanh-cong', 2))->assertOk();
    }

    public function test_khach_khong_xem_duoc_don_cua_nguoi_khac(): void
    {
        $this->actingAs($this->teo(), 'web')
            ->get(route('tai-khoan.chi-tiet', 2))
            ->assertRedirect(route('tai-khoan.lich-su'))
            ->assertSessionHas('flash.message', 'Không tìm thấy đơn đặt phòng hoặc bạn không có quyền truy cập.');
    }

    public function test_loc_phong_theo_khoang_gia_va_tu_choi_ngay_qua_khu(): void
    {
        $this->get(route('phong.index', ['khoangGia' => 'duoi-500k']))
            ->assertOk()->assertSee('Phòng 101')->assertDontSee('Phòng 301');
        // Giá trị lạ (số âm, khoảng không có trong danh sách) bị bỏ qua
        $this->get(route('phong.index', ['khoangGia' => '-100']))->assertOk()->assertSee('Phòng 301');

        $this->get(route('phong.index', ['ngayNhan' => today()->subDays(3)->toDateString(), 'ngayTra' => today()->subDay()->toDateString()]))
            ->assertOk()->assertSee('Ngày nhận phòng không được nằm trong quá khứ.');
    }

    public function test_chi_tiet_phong_hien_bo_anh_trong_thu_muc_rieng_cua_phong(): void
    {
        $phong = Phong::findOrFail(101);
        $phong->update(['HinhAnhDaiDien' => '/Images/ChiTietPhong/Phong101/01.jpg']);

        $this->get(route('phong.show', $phong))
            ->assertOk()
            ->assertSee('Images/ChiTietPhong/Phong101/02.jpg', false)
            ->assertDontSee('Images/ChiTietPhong/Phong102/', false);

        // Ảnh tải lên nằm chung thư mục uploads: chỉ hiện đúng ảnh đại diện, không kéo ảnh phòng khác
        $phong->update(['HinhAnhDaiDien' => '/uploads/phong/anh-101.jpg']);

        $this->get(route('phong.show', $phong))
            ->assertOk()
            ->assertSee('uploads/phong/anh-101.jpg', false)
            ->assertDontSee('class="gallery-thumb"', false);
    }
}
