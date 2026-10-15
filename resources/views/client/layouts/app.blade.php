{{-- Layout trang khách hàng (views/client/layouts/header.php + footer.php của bản gốc) --}}
@php($khachHang = auth('web')->user())
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Khách sạn Nghỉ dưỡng Vip')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    {{-- app.css tự @import Bootstrap 5.3.3 --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    <style>
        .client-nav { display: flex; align-items: center; gap: 1rem; padding: 1rem 2rem; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .client-brand { font-weight: bold; font-size: 1.25rem; color: #1e40af; text-decoration: none; }
        .spacer { flex: 1; }
        .client-nav a { text-decoration: none; color: #4b5563; font-weight: 500; }
        .client-nav a:hover, .client-nav a.active { color: #1e40af; }
        .client-nav-cta { background: #1e40af; color: #fff !important; padding: 0.4rem 1rem; border-radius: 6px; }
        .flash-msg { margin: 1rem 2rem; padding: 0.75rem 1.25rem; border-radius: 6px; font-weight: 500; }
        .flash-success { background: #def7ec; color: #03543f; border: 1px solid #bcf0da; }
        .flash-danger { background: #fde8e8; color: #9b1c1c; border: 1px solid #fbd5d5; }
        .flash-warning { background: #fef08a; color: #713f12; border: 1px solid #fde047; }
    </style>
</head>
<body class="client-page">

<nav class="client-nav">
    <a class="client-brand" href="{{ route('trang-chu') }}">
        <i class="fa-solid fa-hotel me-1"></i> Khách sạn Vip
    </a>
    <a href="{{ route('trang-chu') }}" class="{{ request()->routeIs('trang-chu') ? 'active' : '' }}">Trang chủ</a>
    <a href="{{ route('phong.index') }}" class="{{ request()->routeIs('phong.*') ? 'active' : '' }}">Phòng</a>
    <a href="{{ route('dich-vu.index') }}" class="{{ request()->routeIs('dich-vu.*') ? 'active' : '' }}">Dịch vụ</a>
    <a href="{{ route('danh-gia.index') }}" class="{{ request()->routeIs('danh-gia.*') ? 'active' : '' }}">Đánh giá</a>
    <a href="{{ route('lien-he') }}" class="{{ request()->routeIs('lien-he') ? 'active' : '' }}">Liên hệ</a>

    <span class="spacer"></span>

    @if ($khachHang)
        <span class="nav-greeting text-muted">
            <i class="fa-solid fa-user-circle me-1 text-primary"></i> Xin chào, <strong>{{ $khachHang->HoTen }}</strong>
        </span>
        <a href="{{ route('tai-khoan.ho-so') }}" class="{{ request()->routeIs('tai-khoan.ho-so') ? 'active' : '' }}">Tài khoản</a>
        <a href="{{ route('tai-khoan.lich-su') }}" class="{{ request()->routeIs('tai-khoan.lich-su') ? 'active' : '' }}">Lịch sử đặt</a>
        <form method="POST" action="{{ route('tai-khoan.dang-xuat') }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn đăng xuất?')">
            @csrf
            <button type="submit" class="action-link">Đăng xuất</button>
        </form>
    @else
        <a href="{{ route('tai-khoan.dang-nhap') }}">Đăng nhập</a>
        <a class="client-nav-cta" href="{{ route('tai-khoan.dang-ky') }}">Đăng ký</a>
    @endif

    <a href="{{ route('admin.dashboard') }}" style="font-size: 0.85rem; border-left: 1px solid #ddd; padding-left: 1rem;" title="Trang quản trị dành cho nhân viên">
        <i class="fa-solid fa-user-shield me-1"></i> Admin
    </a>
</nav>

@if (session('flash'))
    @php($flash = session('flash'))
    <div class="flash-msg flash-{{ $flash['type'] === 'error' ? 'danger' : $flash['type'] }}">
        {{ $flash['message'] }}
    </div>
@endif

@yield('content')

<footer class="client-footer">
    <div>
        <strong><i class="fa-solid fa-hotel me-1"></i> Khách sạn Nghỉ dưỡng Vip</strong>
        <p>Không gian nghỉ dưỡng tiện nghi, đặt phòng nhanh và quản lý lịch đặt rõ ràng.</p>
    </div>
    <div>
        <span><i class="fa-solid fa-phone me-1"></i> Hotline: 0909 123 456</span>
        <span><i class="fa-solid fa-envelope me-1"></i> Email: contact@khachsanvip.vn</span>
        <span><i class="fa-solid fa-location-dot me-1"></i> Địa chỉ: 123 Đường Bờ Biển, TP. Đà Nẵng</span>
    </div>
</footer>

</body>
</html>
