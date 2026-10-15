{{-- Layout trang quản trị (views/admin/layouts/header.php + nav.php + footer.php của bản gốc) --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản trị Khách sạn')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body>
@if (session('flash'))
    @php($flash = session('flash'))
    <div style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
        <div class="alert alert-{{ $flash['type'] === 'error' ? 'danger' : $flash['type'] }} alert-dismissible fade show shadow" role="alert">
            {{ $flash['message'] }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

{{-- Trang đăng nhập truyền hideNav = true để ẩn menu --}}
@unless ($hideNav ?? false)
    @include('admin.layouts.nav')
@endunless

@yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
