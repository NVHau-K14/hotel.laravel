<?php

use App\Http\Controllers\Client\AccountController;
use App\Http\Controllers\Client\BookingController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\ReviewController;
use App\Http\Controllers\Client\RoomController;
use App\Http\Controllers\Client\ServiceController;
use Illuminate\Support\Facades\Route;

/*
| Route phía khách hàng. Route quản trị nằm ở routes/admin.php.
*/

Route::get('/', [HomeController::class, 'index'])->name('trang-chu');
Route::get('lien-he', [HomeController::class, 'contact'])->name('lien-he');

// Phòng và đặt phòng. Phòng/đơn không tồn tại → chuyển trang kèm thông báo như bản gốc (không trả 404)
Route::get('phong', [RoomController::class, 'index'])->name('phong.index');
Route::get('phong/{phong}', [RoomController::class, 'show'])->name('phong.show')
    ->missing(fn () => redirect()->route('phong.index')->thongBao('danger', 'Không tìm thấy thông tin phòng.'));

// Đặt phòng bắt buộc đăng nhập tài khoản khách hàng (chưa có tài khoản thì đăng ký; không đăng nhập chỉ xem được)
Route::controller(BookingController::class)->name('dat-phong.')->middleware('auth:web')->group(function () {
    $khongCoPhong = fn () => redirect()->route('phong.index')->thongBao('danger', 'Vui lòng chọn phòng hợp lệ để đặt.');
    Route::get('phong/{phong}/dat-phong', 'create')->name('create')->missing($khongCoPhong);
    Route::post('phong/{phong}/dat-phong', 'store')->name('store')->missing($khongCoPhong);
    Route::get('dat-phong/{datPhong}/thanh-cong', 'success')->name('thanh-cong')
        ->missing(fn () => redirect()->route('trang-chu'));
});

Route::get('dich-vu', [ServiceController::class, 'index'])->name('dich-vu.index');

Route::get('danh-gia', [ReviewController::class, 'index'])->name('danh-gia.index');
Route::post('danh-gia', [ReviewController::class, 'store'])->name('danh-gia.store')->middleware('auth:web');

// Tài khoản khách hàng
Route::prefix('tai-khoan')->name('tai-khoan.')->controller(AccountController::class)->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('dang-nhap', 'showLogin')->name('dang-nhap');
        Route::post('dang-nhap', 'login')->name('dang-nhap.store');
        Route::get('dang-ky', 'showRegister')->name('dang-ky');
        Route::post('dang-ky', 'register')->name('dang-ky.store');
    });

    Route::middleware('auth:web')->group(function () {
        Route::get('/', 'profile')->name('ho-so');
        Route::get('sua', 'edit')->name('sua');
        Route::put('/', 'update')->name('cap-nhat');
        Route::put('mat-khau', 'changePassword')->name('doi-mat-khau');
        Route::get('lich-su', 'history')->name('lich-su');
        Route::get('don/{datPhong}', 'detail')->name('chi-tiet')
            ->missing(fn () => redirect()->route('tai-khoan.lich-su')
                ->thongBao('danger', 'Không tìm thấy đơn đặt phòng hoặc bạn không có quyền truy cập.'));
        Route::post('dang-xuat', 'logout')->name('dang-xuat');
    });
});
