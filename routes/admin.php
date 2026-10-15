<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DichVuController;
use App\Http\Controllers\Admin\LoaiPhongController;
use App\Http\Controllers\Admin\PhongController;
use Illuminate\Support\Facades\Route;

/*
| Route trang quản trị. Được nạp trong bootstrap/app.php với tiền tố URL "/admin",
| tên route "admin." và middleware "web".
*/

// Đăng nhập / đăng xuất / đổi mật khẩu
Route::controller(AuthController::class)->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('dang-nhap', 'showLogin')->name('dang-nhap');
        Route::post('dang-nhap', 'login')->name('dang-nhap.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('dang-xuat', 'logout')->name('dang-xuat');
        Route::get('doi-mat-khau', 'showChangePassword')->name('doi-mat-khau');
        Route::put('doi-mat-khau', 'changePassword')->name('doi-mat-khau.update');
    });
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('quyen:DASHBOARD');

    // Bản ghi không tồn tại → về danh sách kèm thông báo (giống bản gốc), không trả 404
    Route::resource('loai-phong', LoaiPhongController::class)->except('show')
        ->middleware('quyen:LOAI_PHONG')
        ->missing(fn () => redirect()->route('admin.loai-phong.index')->thongBao('danger', 'Loại phòng không tồn tại.'));

    Route::resource('phong', PhongController::class)->except('show')
        ->middleware('quyen:PHONG')
        ->missing(fn () => redirect()->route('admin.phong.index')->thongBao('danger', 'Không tìm thấy thông tin phòng.'));

    Route::resource('dich-vu', DichVuController::class)->except('show')
        ->middleware('quyen:DICH_VU')
        ->missing(fn () => redirect()->route('admin.dich-vu.index')->thongBao('danger', 'Không tìm thấy dịch vụ.'));

    // TẠM THỜI (Giai đoạn 3): trang chờ cho các module, Giai đoạn 4 thay bằng controller thật
    $moduleChuaLam = [
        'dat-phong' => ['DAT_PHONG', 'Quản lý đặt phòng'],
        'ct-dich-vu' => ['CT_DICH_VU', 'Chi tiết sử dụng dịch vụ'],
        'hoa-don' => ['HOA_DON', 'Quản lý hóa đơn'],
        'khach-hang' => ['KHACH_HANG', 'Quản lý khách hàng'],
        'nhan-vien' => ['NHAN_VIEN', 'Quản lý nhân viên'],
        'phan-cong' => ['PHAN_CONG', 'Quản lý phân công công việc'],
        'chuc-vu' => ['CHUC_VU', 'Quản lý chức vụ & Phân quyền'],
        'danh-gia' => ['DANH_GIA', 'Quản lý đánh giá khách hàng'],
        'thong-ke' => ['THONG_KE', 'Báo cáo thống kê'],
    ];
    foreach ($moduleChuaLam as $uri => [$maQuyen, $tenChucNang]) {
        Route::view($uri, 'admin.dang-xay-dung', ['tenChucNang' => $tenChucNang])
            ->name("$uri.index")
            ->middleware("quyen:$maQuyen");
    }
});
