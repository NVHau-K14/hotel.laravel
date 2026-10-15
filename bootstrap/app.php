<?php

use App\Http\Middleware\DangXuatKhachBiKhoa;
use App\Http\Middleware\KiemTraQuyen;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // Trang quản trị: tiền tố URL /admin, tên route admin.*
        then: function () {
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'quyen' => KiemTraQuyen::class,
        ]);

        $middleware->web(append: [
            DangXuatKhachBiKhoa::class,
        ]);
        // Phải chạy TRƯỚC middleware auth, nếu không khách bị khóa vẫn qua được auth:web ở request này
        $middleware->prependToPriorityList(
            before: \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            prepend: DangXuatKhachBiKhoa::class,
        );

        // Không cắt khoảng trắng ở các ô mật khẩu (bản gốc không trim mật khẩu)
        $middleware->trimStrings(except: ['matKhauCu', 'matKhauMoi', 'xacNhanMatKhau']);

        // Chưa đăng nhập: /admin/... về trang đăng nhập admin, còn lại về trang đăng nhập khách
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin', 'admin/*')) {
                session()->flash('flash', ['type' => 'danger', 'message' => 'Vui lòng đăng nhập để tiếp tục.']);

                return route('admin.dang-nhap');
            }

            session()->flash('flash', ['type' => 'warning', 'message' => 'Vui lòng đăng nhập tài khoản khách hàng để tiếp tục.']);

            return route('tai-khoan.dang-nhap');
        });

        // Đã đăng nhập mà vào trang đăng nhập/đăng ký
        $middleware->redirectUsersTo(fn (Request $request) => match (true) {
            $request->is('admin', 'admin/*') => route('admin.dashboard'),
            $request->routeIs('tai-khoan.dang-ky*') => route('trang-chu'),
            default => route('tai-khoan.lich-su'),
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Lỗi nghiệp vụ là thông báo cho người dùng, không cần ghi log
        $exceptions->dontReport([DomainException::class]);

        // Gửi form lỗi → quay lại form kèm dữ liệu cũ và thông báo; xem trang lỗi (GET) → trang 422
        $traVeLoi = function (Request $request, string $thongBao) {
            if ($request->isMethod('GET')) {
                return response()->view('errors.422', ['thongBao' => $thongBao], 422);
            }

            return back()
                ->withInput($request->except(['password', 'matKhauCu', 'matKhauMoi', 'xacNhanMatKhau']))
                ->with('flash', ['type' => 'danger', 'message' => $thongBao]);
        };

        $exceptions->render(fn (DomainException $e, Request $request) => $traVeLoi($request, $e->getMessage()));

        // Đổi lỗi MySQL thường gặp thành câu tiếng Việt (handle_request_error của bản gốc)
        $exceptions->render(function (QueryException $e, Request $request) use ($traVeLoi) {
            $thongBao = match ($e->errorInfo[1] ?? null) {
                1451 => 'Không thể xóa vì dữ liệu này đang được sử dụng trong lịch sử đặt phòng, hóa đơn hoặc phân công.',
                1062 => 'Dữ liệu đã tồn tại. Vui lòng kiểm tra tên đăng nhập, email, số điện thoại hoặc mã bản ghi.',
                1452, 1406, 1264, 1292 => 'Dữ liệu không hợp lệ, quá dài hoặc bản ghi liên quan không còn tồn tại. Vui lòng kiểm tra lại biểu mẫu.',
                default => null,
            };

            // null: lỗi khác, để Laravel xử lý mặc định
            return $thongBao === null ? null : $traVeLoi($request, $thongBao);
        });
    })->create();
