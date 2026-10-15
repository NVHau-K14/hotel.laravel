<?php

namespace Tests\Feature;

use App\Models\NhanVien;
use App\Models\Phong;
use Tests\TestCase;

/**
 * Phân quyền trang quản trị: middleware "quyen:MÃ", menu theo quyền, chuyển hướng khi chưa đăng nhập.
 */
class PhanQuyenTest extends TestCase
{
    private function nhanVien(string $username): NhanVien
    {
        return NhanVien::where('Username', $username)->firstOrFail();
    }

    public function test_vung_dang_nhap_that_roi_vao_trang_phong_bi_403(): void
    {
        // Đăng nhập qua form (mật khẩu bcrypt trong seeder)
        $this->post(route('admin.dang-nhap.store'), ['username' => 'vung', 'password' => '1'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->nhanVien('vung'), 'admin');

        $this->get('/admin/phong')
            ->assertForbidden()
            ->assertSee('Bạn không có quyền thực hiện chức năng này (PHONG).')
            ->assertSee('Quay lại bảng điều khiển');
    }

    public function test_vung_chi_thay_menu_dashboard(): void
    {
        $response = $this->actingAs($this->nhanVien('vung'), 'admin')->get(route('admin.dashboard'));

        $response->assertOk()->assertSee('fa-gauge-high', false);
        foreach (['admin.phong.index', 'admin.loai-phong.index', 'admin.nhan-vien.index', 'admin.chuc-vu.index', 'admin.thong-ke.index'] as $tenRoute) {
            $response->assertDontSee('href="'.route($tenRoute).'"', false);
        }
    }

    public function test_vung_khong_thao_tac_duoc_du_lieu_phong(): void
    {
        $this->actingAs($this->nhanVien('vung'), 'admin');

        $this->delete(route('admin.phong.destroy', 101))->assertForbidden();
        $this->post(route('admin.phong.store'), ['maPhong' => 106, 'maLoaiPhong' => 1, 'trangThai' => 'Trống'])->assertForbidden();

        $this->assertNotNull(Phong::find(101));
        $this->assertNull(Phong::find(106));
    }

    public function test_le_tan_binh_chi_vao_duoc_module_duoc_cap(): void
    {
        $this->actingAs($this->nhanVien('binh'), 'admin');

        foreach (['admin.dat-phong.index', 'admin.hoa-don.index', 'admin.khach-hang.index', 'admin.dich-vu.index', 'admin.ct-dich-vu.index'] as $tenRoute) {
            $this->get(route($tenRoute))->assertOk();
        }
        foreach (['admin.phong.index', 'admin.nhan-vien.index', 'admin.chuc-vu.index', 'admin.thong-ke.index'] as $tenRoute) {
            $this->get(route($tenRoute))->assertForbidden();
        }
    }

    public function test_quan_ly_viet_vao_duoc_moi_trang_danh_sach(): void
    {
        $this->actingAs($this->nhanVien('viet'), 'admin');

        $danhSach = ['dashboard', 'phong.index', 'loai-phong.index', 'dat-phong.index', 'dich-vu.index', 'ct-dich-vu.index',
            'hoa-don.index', 'khach-hang.index', 'nhan-vien.index', 'phan-cong.index', 'phan-cong.hom-nay',
            'chuc-vu.index', 'danh-gia.index', 'thong-ke.index'];
        foreach ($danhSach as $tenRoute) {
            $this->get(route('admin.'.$tenRoute))->assertOk();
        }
    }

    public function test_chua_dang_nhap_bi_chuyen_ve_trang_dang_nhap_dung_phia(): void
    {
        $this->get('/admin/phong')
            ->assertRedirect(route('admin.dang-nhap'))
            ->assertSessionHas('flash.message', 'Vui lòng đăng nhập để tiếp tục.');

        $this->get(route('tai-khoan.lich-su'))
            ->assertRedirect(route('tai-khoan.dang-nhap'))
            ->assertSessionHas('flash.message', 'Vui lòng đăng nhập tài khoản khách hàng để tiếp tục.');
    }

    public function test_dang_nhap_sai_mat_khau(): void
    {
        $this->from(route('admin.dang-nhap'))
            ->post(route('admin.dang-nhap.store'), ['username' => 'vung', 'password' => 'sai'])
            ->assertRedirect(route('admin.dang-nhap'))
            ->assertSessionHasErrors();
        $this->assertGuest('admin');
    }
}
