{{-- Khung trang lỗi đơn giản (giống trang lỗi của handle_request_error trong bản gốc) --}}
<!doctype html>
<html lang="vi">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title', 'Không thể hoàn tất')</title>
<body>
<main style="max-width:640px;margin:3rem auto;padding:1rem;font-family:Arial,sans-serif">
    @yield('content')
</main>
</body>
</html>
