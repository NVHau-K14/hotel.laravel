<?php

namespace App\Providers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // URL tiếng Việt cho Route::resource: /phong/them, /phong/{phong}/sua
        Route::resourceVerbs([
            'create' => 'them',
            'edit' => 'sua',
        ]);

        // @quyen('PHONG') ... @endquyen: chỉ hiện khi nhân viên đang đăng nhập có quyền
        Blade::if('quyen', fn (string $maQuyen) => (bool) auth('admin')->user()?->coQuyen($maQuyen));

        // redirect()->...->thongBao('success', '...'): flash thông báo theo cấu trúc của bản gốc
        // (session "flash" = ['type' => success|danger|warning|info, 'message' => ...])
        RedirectResponse::macro('thongBao', function (string $loai, string $noiDung) {
            /** @var RedirectResponse $this */
            return $this->with('flash', ['type' => $loai, 'message' => $noiDung]);
        });
    }
}
