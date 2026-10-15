<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ChucVuController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DichVuController;
use App\Http\Controllers\Admin\KhachHangController;
use App\Http\Controllers\Admin\LoaiPhongController;
use App\Http\Controllers\Admin\NhanVienController;
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

    Route::prefix('khach-hang')->name('khach-hang.')->controller(KhachHangController::class)
        ->middleware('quyen:KHACH_HANG')
        ->group(function () {
            $khongCo = fn () => redirect()->route('admin.khach-hang.index')->thongBao('danger', 'Khách hàng không tồn tại.');
            Route::get('/', 'index')->name('index');
            Route::post('{khach_hang}/doi-trang-thai', 'toggle')->name('doi-trang-thai')->missing($khongCo);
            Route::get('{khach_hang}/lich-su', 'history')->name('lich-su')->missing($khongCo);
        });

    Route::resource('nhan-vien', NhanVienController::class)->except('show')
        ->middleware('quyen:NHAN_VIEN')
        ->missing(fn () => redirect()->route('admin.nhan-vien.index')->thongBao('danger', 'Nhân viên không tồn tại.'));

    Route::prefix('chuc-vu')->name('chuc-vu.')->controller(ChucVuController::class)
        ->middleware('quyen:CHUC_VU')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('{chuc_vu}/quyen', 'updatePermissions')->name('quyen.update')
                ->missing(fn () => redirect()->route('admin.chuc-vu.index'));
        });

    // TẠM THỜI (Giai đoạn 3): trang chờ cho các module, Giai đoạn 4 thay bằng controller thật
    $moduleChuaLam = [
        'dat-phong' => ['DAT_PHONG', 'Quản lý đặt phòng'],
        'ct-dich-vu' => ['CT_DICH_VU', 'Chi tiết sử dụng dịch vụ'],
        'hoa-don' => ['HOA_DON', 'Quản lý hóa đơn'],
        'phan-cong' => ['PHAN_CONG', 'Quản lý phân công công việc'],
        'danh-gia' => ['DANH_GIA', 'Quản lý đánh giá khách hàng'],
        'thong-ke' => ['THONG_KE', 'Báo cáo thống kê'],
    ];
    Route::view('dat-phong/{dat_phong}', 'admin.dang-xay-dung', ['tenChucNang' => 'Chi tiết đơn đặt phòng'])
        ->name('dat-phong.show')->middleware('quyen:DAT_PHONG');
    foreach ($moduleChuaLam as $uri => [$maQuyen, $tenChucNang]) {
        Route::view($uri, 'admin.dang-xay-dung', ['tenChucNang' => $tenChucNang])
            ->name("$uri.index")
            ->middleware("quyen:$maQuyen");
    }
});
