<?php

use App\Http\Controllers\Client\AccountController;
use Illuminate\Support\Facades\Route;

/*
| Route phía khách hàng. Route quản trị nằm ở routes/admin.php.
*/

// Tài khoản khách hàng
Route::prefix('tai-khoan')->name('tai-khoan.')->controller(AccountController::class)->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::get('dang-nhap', 'showLogin')->name('dang-nhap');
        Route::post('dang-nhap', 'login')->name('dang-nhap.store');
        Route::get('dang-ky', 'showRegister')->name('dang-ky');
        Route::post('dang-ky', 'register')->name('dang-ky.store');
    });

    Route::post('dang-xuat', 'logout')->name('dang-xuat')->middleware('auth:web');
});

// TẠM THỜI (Giai đoạn 3): trang chờ, Giai đoạn 5 thay bằng controller thật
Route::view('/', 'client.dang-xay-dung', ['tenTrang' => 'Trang chủ'])->name('trang-chu');
Route::view('lien-he', 'client.dang-xay-dung', ['tenTrang' => 'Liên hệ'])->name('lien-he');
Route::view('phong', 'client.dang-xay-dung', ['tenTrang' => 'Danh sách phòng nghỉ'])->name('phong.index');
Route::view('dich-vu', 'client.dang-xay-dung', ['tenTrang' => 'Dịch vụ tiện ích'])->name('dich-vu.index');
Route::view('danh-gia', 'client.dang-xay-dung', ['tenTrang' => 'Đánh giá khách hàng'])->name('danh-gia.index');
Route::middleware('auth:web')->group(function () {
    Route::view('tai-khoan', 'client.dang-xay-dung', ['tenTrang' => 'Tài khoản của tôi'])->name('tai-khoan.ho-so');
    Route::view('tai-khoan/lich-su', 'client.dang-xay-dung', ['tenTrang' => 'Lịch sử đặt phòng của tôi'])->name('tai-khoan.lich-su');
});
