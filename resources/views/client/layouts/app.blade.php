{{-- Layout trang khách hàng --}}
@php
    $khachHang = auth('web')->user();
    $menuKhach = [
        ['trang-chu', 'trang-chu', 'Trang chủ'],
        ['phong.index', 'phong.*', 'Phòng nghỉ'],
        ['dich-vu.index', 'dich-vu.*', 'Dịch vụ'],
        ['danh-gia.index', 'danh-gia.*', 'Đánh giá'],
        ['lien-he', 'lien-he', 'Liên hệ'],
    ];
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f1f2ee">
    <title>@yield('title', 'Khách sạn Nghỉ dưỡng Vip')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
</head>
<body class="client-page">
<a class="skip-link" href="#noi-dung">Bỏ qua menu, tới nội dung</a>

<header class="site-header">
    <div class="site-header-inner">
        <a class="site-brand" href="{{ route('trang-chu') }}">
            <span class="brand-mark" aria-hidden="true">V</span>
            <span>
                <strong>Khách sạn Vip</strong>
                <small>Nghỉ dưỡng ven biển Đà Nẵng</small>
            </span>
        </a>

        <button class="nav-toggle" type="button" data-nav-toggle aria-controls="site-menu" aria-expanded="false" aria-label="Mở menu">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <div class="site-menu d-lg-flex align-items-center flex-grow-1 gap-3" id="site-menu">
            <nav aria-label="Menu chính">
                <ul class="site-nav">
                    @foreach ($menuKhach as [$tenRoute, $mauActive, $nhan])
                        <li>
                            <a href="{{ route($tenRoute) }}" @if (request()->routeIs($mauActive)) aria-current="page" @endif>{{ $nhan }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="site-actions">
                @if ($khachHang)
                    <div class="dropdown">
                        <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar" aria-hidden="true">{{ mb_substr($khachHang->HoTen ?? 'K', 0, 1) }}</span>
                            <span>{{ $khachHang->HoTen }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('tai-khoan.ho-so') }}">Tài khoản của tôi</a></li>
                            <li><a class="dropdown-item" href="{{ route('tai-khoan.lich-su') }}">Lịch sử đặt phòng</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('tai-khoan.dang-xuat') }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn đăng xuất?')">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a class="btn btn-outline-secondary" href="{{ route('tai-khoan.dang-nhap') }}">Đăng nhập</a>
                    <a class="btn btn-primary" href="{{ route('tai-khoan.dang-ky') }}">Đăng ký</a>
                @endif
            </div>
        </div>
    </div>
</header>

@if (session('flash'))
    @php($flash = session('flash'))
    <div class="flash-msg" role="status" aria-live="polite">
        <div class="alert alert-{{ $flash['type'] === 'error' ? 'danger' : $flash['type'] }}">
            {{ $flash['message'] }}
        </div>
    </div>
@endif

<div id="noi-dung" tabindex="-1" class="d-flex flex-column flex-grow-1">
    @yield('content')
</div>

<footer class="site-footer">
    <div class="site-footer-inner">
        <div>
            <h2>Khách sạn Nghỉ dưỡng Vip</h2>
            <p>Phòng nghỉ tiện nghi bên bờ biển Đà Nẵng. Đặt phòng trực tuyến, lễ tân gọi lại để xác nhận.</p>
        </div>
        <div>
            <h3>Liên hệ</h3>
            <ul>
                <li><a href="tel:0909123456">0909 123 456</a></li>
                <li><a href="mailto:contact@khachsanvip.vn">contact@khachsanvip.vn</a></li>
                <li>123 Đường Bờ Biển, TP. Đà Nẵng</li>
            </ul>
        </div>
        <div>
            <h3>Khám phá</h3>
            <ul>
                <li><a href="{{ route('phong.index') }}">Phòng nghỉ</a></li>
                <li><a href="{{ route('dich-vu.index') }}">Dịch vụ</a></li>
                <li><a href="{{ route('danh-gia.index') }}">Đánh giá của khách</a></li>
                <li><a href="{{ route('admin.dashboard') }}" title="Trang quản trị dành cho nhân viên">Dành cho nhân viên</a></li>
            </ul>
        </div>
    </div>
    <div class="site-footer-base">© {{ now()->year }} Khách sạn Nghỉ dưỡng Vip.</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        var nut = document.querySelector('[data-nav-toggle]');
        if (!nut) return;
        var dauTrang = nut.closest('.site-header');
        nut.addEventListener('click', function () {
            var mo = !dauTrang.classList.contains('nav-open');
            dauTrang.classList.toggle('nav-open', mo);
            nut.setAttribute('aria-expanded', mo ? 'true' : 'false');
        });
    })();
</script>
@stack('scripts')
</body>
</html>
