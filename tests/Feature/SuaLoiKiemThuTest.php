<?php

namespace Tests\Feature;

use App\Models\ChucVu;
use App\Models\DatPhong;
use App\Models\HoaDon;
use App\Models\KhachHang;
use App\Models\LoaiPhong;
use App\Models\NhanVien;
use App\Models\Phong;
use App\Services\DatPhongService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;
use Tests\TestCase;

/**
 * Test cho các lỗi trong docs/BAO_CAO_KIEM_THU.md (mã BUG-xx) sau khi sửa.
 */
class SuaLoiKiemThuTest extends TestCase
{
    private function nv(string $username): NhanVien
    {
        return NhanVien::where('Username', $username)->firstOrFail();
    }

    private function khach(string $email): KhachHang
    {
        return KhachHang::where('Email', $email)->firstOrFail();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Câu thông báo nằm ở flash (lỗi nghiệp vụ) hoặc ở lỗi kiểm tra form
        TestResponse::macro('assertThongBao', function (string $thongBao) {
            Assert::assertContains($thongBao, [session('flash.message'), SuaLoiKiemThuTest::loiDauTien()]);

            return $this;
        });
    }

    /** Lỗi kiểm tra form đầu tiên trong session (ViewErrorBag hoặc mảng) */
    public static function loiDauTien(): ?string
    {
        $loi = session('errors');
        if (is_object($loi) && method_exists($loi, 'first')) {
            return $loi->first();
        }

        return is_array($loi) ? collect($loi)->flatten()->reject(fn ($v) => $v === ':message')->first() : null;
    }

    private function taoDon(array $giaTri): DatPhong
    {
        return DatPhong::create($giaTri + [
            'NgayThucHien' => today()->toDateString(),
            'MaKhachHang' => 1,
            'payment_status' => DatPhong::THANH_TOAN_CHUA_TAO,
            'GiaPhongDat' => 300000,
        ]);
    }

    // ---------- BUG-01: leo thang đặc quyền ----------

    public function test_bug01_nhan_vien_khong_phai_quan_ly_khong_sua_duoc_quan_ly(): void
    {
        // Cấp quyền Nhân viên cho chức vụ Tạp vụ
        $tapVu = ChucVu::where('TenChucVu', 'Tạp vụ')->firstOrFail();
        DB::table('CHUCVU_QUYEN')->insert(['MaChucVu' => $tapVu->MaChucVu, 'MaQuyen' => 'NHAN_VIEN']);
        $viet = $this->nv('viet');
        $maQuanLy = ChucVu::where('TenChucVu', ChucVu::QUAN_LY)->value('MaChucVu');

        $this->actingAs($this->nv('van'), 'admin');

        $this->put(route('admin.nhan-vien.update', $viet), [
            'tenNhanVien' => $viet->TenNhanVien, 'maChucVu' => $viet->MaChucVu, 'password' => 'HackMat123',
        ])->assertSessionHas('flash.message', 'Chỉ Quản lý mới được sửa hoặc xóa tài khoản Quản lý.');
        $this->assertTrue(Hash::check('Admin@1234', $viet->refresh()->Password));

        $this->post(route('admin.nhan-vien.store'), [
            'username' => 'qa_boss', 'password' => 'BossMoi123', 'tenNhanVien' => 'QA', 'maChucVu' => $maQuanLy,
        ])->assertSessionHas('flash.message', 'Chỉ Quản lý mới được gán chức vụ Quản lý.');
        $this->assertNull(NhanVien::where('Username', 'qa_boss')->first());

        $this->delete(route('admin.nhan-vien.destroy', $viet))
            ->assertSessionHas('flash.message', 'Chỉ Quản lý mới được sửa hoặc xóa tài khoản Quản lý.');

        // Không tự cấp thêm quyền cho chức vụ của mình
        DB::table('CHUCVU_QUYEN')->insert(['MaChucVu' => $tapVu->MaChucVu, 'MaQuyen' => 'CHUC_VU']);
        $this->actingAs($this->nv('van'), 'admin'); // nạp lại quyền
        $this->put(route('admin.chuc-vu.quyen.update', $tapVu), ['permissions' => ['DASHBOARD', 'THONG_KE']])
            ->assertSessionHas('flash.message', 'Chỉ Quản lý mới được sửa quyền của chức vụ Quản lý hoặc chức vụ của chính mình.');
    }

    // ---------- BUG-03, BUG-09: đăng ký, nhận lại khách vãng lai, chuẩn mật khẩu / SĐT ----------

    public function test_bug03_khach_vang_lai_cu_dang_ky_nhan_lai_lich_su(): void
    {
        $vangLai = KhachHang::create(['HoTen' => 'Vãng Lai', 'Email' => '', 'DienThoai' => '0909000111', 'DiaChi' => '', 'CMND' => '', 'TrangThai' => KhachHang::HOAT_DONG]);
        $this->taoDon(['MaKhachHang' => $vangLai->MaKhachHang, 'MaPhong' => 105, 'NgayNhan' => today()->subDays(5)->toDateString(), 'NgayTra' => today()->subDays(4)->toDateString(), 'TrangThai' => DatPhong::DA_HOAN_THANH]);
        $soKhach = KhachHang::count();

        $this->post(route('tai-khoan.dang-ky.store'), [
            'hoTen' => 'Vãng Lai Đã Đăng Ký', 'dienThoai' => '090 900 0111', 'password' => 'MatKhau123', 'password_confirmation' => 'MatKhau123',
        ])->assertSessionHas('flash.message', 'Đăng ký thành công! Các đơn đặt phòng trước đây của bạn đã được gắn vào tài khoản.');

        $this->assertSame($soKhach, KhachHang::count());
        $this->assertAuthenticatedAs($vangLai->refresh(), 'web');
        $this->assertSame('Vãng Lai Đã Đăng Ký', $vangLai->HoTen);
    }

    public function test_bug09_dang_ky_kiem_tra_mat_khau_va_so_dien_thoai(): void
    {
        $hopLe = ['hoTen' => 'Khách Mới', 'dienThoai' => '0909123123', 'password' => 'MatKhau123', 'password_confirmation' => 'MatKhau123'];
        $truongHop = [
            'mật khẩu 1 ký tự' => [['password' => '1', 'password_confirmation' => '1'], 'Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường và chữ số.'],
            'thiếu chữ hoa' => [['password' => 'matkhau123', 'password_confirmation' => 'matkhau123'], 'Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường và chữ số.'],
            'chứa SĐT' => [['password' => 'A0909123123b', 'password_confirmation' => 'A0909123123b'], 'Mật khẩu không được chứa tên đăng nhập, số điện thoại hoặc email.'],
            'nhập lại sai' => [['password_confirmation' => 'MatKhau124'], 'Mật khẩu nhập lại không khớp.'],
            'SĐT toàn dấu gạch' => [['dienThoai' => '0-------'], 'Số điện thoại phải là số di động Việt Nam hợp lệ (10 số, ví dụ 0912345678).'],
            'SĐT đầu số sai' => [['dienThoai' => '0112345678'], 'Số điện thoại phải là số di động Việt Nam hợp lệ (10 số, ví dụ 0912345678).'],
            'CCCD sai' => [['cmnd' => '12ab'], 'CMND/CCCD phải gồm 9 hoặc 12 chữ số.'],
            'SĐT đã có tài khoản' => [['dienThoai' => '0912345678'], 'Số điện thoại hoặc email đã được đăng ký. Vui lòng đăng nhập tài khoản này.'],
        ];
        $soKhach = KhachHang::count();
        foreach ($truongHop as $ten => [$thayDoi, $thongBao]) {
            $this->post(route('tai-khoan.dang-ky.store'), array_merge($hopLe, $thayDoi));
            $this->assertSame($thongBao, self::loiDauTien(), $ten);
        }
        $this->assertSame($soKhach, KhachHang::count());
        $this->assertGuest('web');
    }

    // ---------- BUG-08: giới hạn đăng nhập sai ----------

    public function test_bug08_sai_mat_khau_5_lan_bi_khoa_tam(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('tai-khoan.dang-nhap.store'), ['account' => 'teo@gmail.com', 'password' => 'Sai12345']);
        }
        $this->post(route('tai-khoan.dang-nhap.store'), ['account' => 'teo@gmail.com', 'password' => 'Khach@1234'])
            ->assertSessionHasErrors('account');
        $this->assertGuest('web');
        $this->assertStringContainsString('Bạn đã nhập sai quá nhiều lần', session('errors')->first('account'));

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.dang-nhap.store'), ['username' => 'viet', 'password' => 'Sai12345']);
        }
        $this->post(route('admin.dang-nhap.store'), ['username' => 'viet', 'password' => 'Admin@1234']);
        $this->assertGuest('admin');
    }

    // ---------- BUG-11: nhân viên không có quyền Dashboard ----------

    public function test_bug11_nhan_vien_khong_co_dashboard_vao_trang_duoc_phep(): void
    {
        $baoVe = ChucVu::where('TenChucVu', 'Bảo vệ')->firstOrFail();
        $baoVe->capNhatQuyen(['PHAN_CONG']);

        $this->post(route('admin.dang-nhap.store'), ['username' => 'vung', 'password' => 'Nhanvien@1234'])
            ->assertRedirect(route('admin.phan-cong.index'));
    }

    // ---------- BUG-04: xóa loại phòng còn đơn ----------

    public function test_bug04_khong_xoa_duoc_loai_phong_con_don_va_khoi_phuc_duoc(): void
    {
        $this->actingAs($this->nv('viet'), 'admin');

        // Loại Thương Gia (2) có khách đang ở phòng 201
        $this->delete(route('admin.loai-phong.destroy', 2))
            ->assertSessionHas('flash.message', 'Loại phòng này còn phòng đang có lịch đặt. Hãy xử lý hết các đơn trước khi xóa.');
        $this->assertFalse(LoaiPhong::find(2)->DaXoa);

        // Loại Vip (3) không có đơn → xóa mềm được, rồi khôi phục
        $this->delete(route('admin.loai-phong.destroy', 3));
        $this->assertTrue(LoaiPhong::find(3)->DaXoa);
        $this->post(route('admin.loai-phong.khoi-phuc', 3));
        $this->assertFalse(LoaiPhong::find(3)->DaXoa);
    }

    // ---------- BUG-06: khách ở quá ngày trả ----------

    public function test_bug06_khach_o_qua_han_tinh_them_dem_va_chan_don_moi(): void
    {
        $don = $this->taoDon(['MaPhong' => 204, 'NgayNhan' => today()->subDays(3)->toDateString(), 'NgayTra' => today()->subDay()->toDateString(), 'TrangThai' => DatPhong::DANG_SU_DUNG]);

        // Khách mới đặt phòng 204 từ hôm nay → bị chặn vì khách cũ chưa trả phòng
        $this->actingAs($this->khach('ti@gmail.com'), 'web')
            ->post(route('dat-phong.store', 204), ['ngayNhan' => today()->toDateString(), 'ngayTra' => today()->addDay()->toDateString()])
            ->assertSessionHasErrors('datPhong');

        // Trả phòng hôm nay: 3 đêm thực tế
        $this->actingAs($this->nv('viet'), 'admin')->post(route('admin.dat-phong.tra-phong', $don));
        $hoaDon = HoaDon::where('MaCTDatPhong', $don->MaCTDatPhong)->firstOrFail();
        $this->assertSame(900000, (int) $hoaDon->SoTienThu);

        $this->post(route('admin.hoa-don.thanh-toan', $hoaDon));
        $this->assertSame(today()->toDateString(), $don->refresh()->NgayTra->toDateString());
    }

    // ---------- BUG-07: hủy đơn thì hủy hóa đơn; chỉ thanh toán được đơn đang ở ----------

    public function test_bug07_huy_don_huy_hoa_don_va_an_nut_thanh_toan(): void
    {
        $don = $this->taoDon(['MaPhong' => 205, 'NgayNhan' => today()->addDay()->toDateString(), 'NgayTra' => today()->addDays(2)->toDateString(), 'TrangThai' => DatPhong::DA_XAC_NHAN]);
        $hoaDon = HoaDon::create(['NgayThuTien' => today()->toDateString(), 'SoTienThu' => 300000, 'MaCTDatPhong' => $don->MaCTDatPhong, 'TrangThai' => HoaDon::CHUA_THANH_TOAN]);

        $this->actingAs($this->nv('viet'), 'admin');
        $this->get(route('admin.hoa-don.show', $hoaDon))->assertOk()->assertDontSee('Xác nhận thanh toán');

        $this->post(route('admin.dat-phong.huy', $don));
        $this->assertSame(HoaDon::DA_HUY, $hoaDon->refresh()->TrangThai);
        $this->get(route('admin.hoa-don.show', $hoaDon))->assertSee('ĐÃ HỦY');
    }

    // ---------- BUG-10, BUG-13: đơn quá hạn, trạng thái phòng theo ngày ----------

    public function test_bug10_bug13_don_qua_han_tu_huy_va_don_tuong_lai_khong_lam_phong_da_dat(): void
    {
        $quaHan = $this->taoDon(['MaPhong' => 105, 'NgayNhan' => today()->subDays(3)->toDateString(), 'NgayTra' => today()->subDay()->toDateString(), 'TrangThai' => DatPhong::CHO_ADMIN_DUYET]);
        $tuongLai = $this->taoDon(['MaPhong' => 302, 'NgayNhan' => today()->addDays(60)->toDateString(), 'NgayTra' => today()->addDays(61)->toDateString(), 'TrangThai' => DatPhong::DA_XAC_NHAN]);
        app(DatPhongService::class)->syncRoomStatus(302);

        $this->assertSame(Phong::TRONG, Phong::find(302)->TrangThai);

        $this->actingAs($this->nv('viet'), 'admin')->get(route('admin.dashboard'))->assertOk();
        $this->assertSame(DatPhong::DA_HUY, $quaHan->refresh()->TrangThai);
        $this->assertSame(DatPhong::DA_XAC_NHAN, $tuongLai->refresh()->TrangThai);

        // Phòng có đơn tương lai vẫn sửa được thông tin (ảnh...) khi không đổi loại / không bật bảo trì
        $this->put(route('admin.phong.update', 302), ['maLoaiPhong' => 3, 'trangThai' => Phong::TRONG])
            ->assertSessionHas('flash.message', 'Cập nhật thông tin phòng 302 thành công!');
    }

    // ---------- BUG-14: không đặt tay trạng thái "Đang thuê" ----------

    public function test_bug14_chi_chon_duoc_trong_hoac_bao_tri(): void
    {
        $this->actingAs($this->nv('viet'), 'admin')
            ->post(route('admin.phong.store'), ['maPhong' => 501, 'maLoaiPhong' => 1, 'trangThai' => Phong::DANG_THUE])
            ->assertSessionHasErrors('trangThai');
        $this->assertNull(Phong::find(501));
    }

    // ---------- BUG-15: đánh giá ngay sau khi trả phòng sớm ----------

    public function test_bug15_tra_phong_som_danh_gia_duoc_ngay(): void
    {
        $don = $this->taoDon(['MaPhong' => 104, 'NgayNhan' => today()->toDateString(), 'NgayTra' => today()->addDays(2)->toDateString(), 'TrangThai' => DatPhong::DA_HOAN_THANH]);

        $this->actingAs($this->khach('teo@gmail.com'), 'web')
            ->post(route('danh-gia.store'), ['maPhong' => 104, 'noiDung' => 'Rất tốt', 'soSao' => 5])
            ->assertSessionHas('flash.type', 'success');
        $this->assertTrue($don->danhGias()->exists());
    }

    // ---------- BUG-16, 18, 19, 20, 21: kiểm tra dữ liệu phía admin ----------

    public function test_kiem_tra_du_lieu_admin(): void
    {
        $this->actingAs($this->nv('viet'), 'admin');

        $this->post(route('admin.danh-gia.store'), ['noiDung' => 'x', 'maPhong' => 101, 'soSao' => 99])->assertThongBao('Số sao phải từ 1 đến 5.');
        $this->post(route('admin.danh-gia.store'), ['noiDung' => 'x', 'maPhong' => 101, 'soSao' => 5, 'ngayDanhGia' => today()->addDays(30)->toDateString()])
            ->assertThongBao('Ngày đánh giá không được ở tương lai.');

        // BUG-18: xóa dịch vụ không có trong đơn đang ở (#3)
        $this->delete(route('admin.ct-dich-vu.destroy', [3, 5]))->assertThongBao('Dịch vụ này không có trong đơn đặt phòng.');

        // BUG-19: trùng tên loại phòng; tên dài 22 ký tự hợp lệ
        $this->post(route('admin.loai-phong.store'), ['tenLoaiPhong' => 'Bình Dân', 'giaPhong' => 100000])->assertThongBao('Đã có loại phòng tên "Bình Dân".');
        $this->post(route('admin.loai-phong.store'), ['tenLoaiPhong' => 'Phòng Gia Đình Cao Cấp', 'giaPhong' => 100000]);
        $this->assertNotNull(LoaiPhong::where('TenLoaiPhong', 'Phòng Gia Đình Cao Cấp')->first());

        // BUG-20: ngày vào làm trước khi đủ 18 tuổi
        $this->post(route('admin.nhan-vien.store'), ['username' => 'qa_nv', 'password' => 'NhanVien123', 'tenNhanVien' => 'QA', 'maChucVu' => 2, 'ngaySinh' => '2000-01-01', 'ngayVaoLam' => '1990-01-01'])
            ->assertThongBao('Ngày vào làm phải sau ngày nhân viên đủ 18 tuổi.');

        // BUG-21: ảnh phải là đường dẫn nội bộ
        $this->post(route('admin.phong.store'), ['maPhong' => 502, 'maLoaiPhong' => 1, 'hinhAnh' => 'https://evil.example/track.gif'])
            ->assertThongBao('Đường dẫn ảnh phải bắt đầu bằng /Images/ hoặc /uploads/ và là file ảnh của website.');
        $this->assertNull(Phong::find(502));
    }

    // ---------- BUG-05: khách tự sửa hồ sơ, đổi mật khẩu ----------

    public function test_khach_sua_ho_so_va_doi_mat_khau(): void
    {
        $teo = $this->khach('teo@gmail.com');
        $this->actingAs($teo, 'web');

        $this->put(route('tai-khoan.cap-nhat'), ['hoTen' => 'Lê Văn Tèo Mới', 'dienThoai' => '+84 912 345 678', 'email' => 'TEO@gmail.com'])
            ->assertRedirect(route('tai-khoan.ho-so'));
        $teo->refresh();
        $this->assertSame('Lê Văn Tèo Mới', $teo->HoTen);
        $this->assertSame('0912345678', $teo->DienThoai);
        $this->assertSame('teo@gmail.com', $teo->Email);

        $this->put(route('tai-khoan.doi-mat-khau'), ['matKhauCu' => 'Khach@1234', 'matKhauMoi' => 'MoiHon2026', 'xacNhanMatKhau' => 'MoiHon2026'])
            ->assertSessionHas('flash.message', 'Đổi mật khẩu thành công!');
        $this->assertTrue(Hash::check('MoiHon2026', $teo->refresh()->MatKhau));
    }
}
