{{-- Layout trang quản trị --}}
@php($hienMenu = ! ($hideNav ?? false))
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f1f2ee">
    <title>@yield('title', 'Quản trị Khách sạn')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body class="admin-body">
<a class="skip-link" href="#noi-dung">Bỏ qua menu, tới nội dung</a>

@if (session('flash'))
    @php($flash = session('flash'))
    <div class="toast-stack" role="status" aria-live="polite">
        <div class="alert alert-{{ $flash['type'] === 'error' ? 'danger' : $flash['type'] }} alert-dismissible fade show" role="alert">
            {{ $flash['message'] }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng thông báo"></button>
        </div>
    </div>
@endif

@if ($hienMenu)
    @include('admin.layouts.nav')
    <main class="admin-main" id="noi-dung" tabindex="-1">
        @yield('content')
    </main>
@else
    <main id="noi-dung" class="d-flex flex-column flex-grow-1" tabindex="-1">
        @yield('content')
    </main>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Thông báo góc trên tự đóng sau 6 giây (vẫn đóng tay được)
    document.querySelectorAll('.toast-stack .alert').forEach(function (el) {
        setTimeout(function () { bootstrap.Alert.getOrCreateInstance(el).close(); }, 6000);
    });
</script>
@if ($hienMenu)
<script>
    (function () {
        var nut = document.querySelector('[data-sidebar-toggle]');
        var phu = document.querySelector('[data-sidebar-close]');
        if (!nut) return;
        function dat(mo) {
            document.body.classList.toggle('sidebar-open', mo);
            nut.setAttribute('aria-expanded', mo ? 'true' : 'false');
        }
        nut.addEventListener('click', function () { dat(!document.body.classList.contains('sidebar-open')); });
        if (phu) phu.addEventListener('click', function () { dat(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') dat(false); });
    })();
</script>
@endif
@stack('scripts')
</body>
</html>
