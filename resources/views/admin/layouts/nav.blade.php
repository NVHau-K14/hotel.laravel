@php
    $nhanVien = auth('admin')->user();

    // Nhóm menu theo công việc: [nhãn nhóm, [[mã quyền, tên route, mẫu active, icon, nhãn], ...]] — chỉ hiện mục có quyền
    $nhomMenu = [
        ['Vận hành hằng ngày', [
            ['DASHBOARD', 'admin.dashboard', 'admin.dashboard', 'fa-gauge-high', 'Dashboard'],
            ['DAT_PHONG', 'admin.dat-phong.index', 'admin.dat-phong.*', 'fa-calendar-check', 'Đặt phòng'],
            ['PHONG', 'admin.phong.index', 'admin.phong.*', 'fa-key', 'Phòng'],
            ['CT_DICH_VU', 'admin.ct-dich-vu.index', 'admin.ct-dich-vu.*', 'fa-list-check', 'Chi tiết DV'],
            ['HOA_DON', 'admin.hoa-don.index', 'admin.hoa-don.*', 'fa-file-invoice-dollar', 'Hóa đơn'],
        ]],
        ['Danh mục', [
            ['LOAI_PHONG', 'admin.loai-phong.index', 'admin.loai-phong.*', 'fa-bed', 'Loại phòng'],
            ['DICH_VU', 'admin.dich-vu.index', 'admin.dich-vu.*', 'fa-mug-saucer', 'Dịch vụ'],
        ]],
        ['Khách và nhân sự', [
            ['KHACH_HANG', 'admin.khach-hang.index', 'admin.khach-hang.*', 'fa-user-check', 'Khách hàng'],
            ['NHAN_VIEN', 'admin.nhan-vien.index', 'admin.nhan-vien.*', 'fa-users', 'Nhân viên'],
            ['PHAN_CONG', 'admin.phan-cong.index', 'admin.phan-cong.*', 'fa-clipboard-user', 'Phân công'],
            ['CHUC_VU', 'admin.chuc-vu.index', 'admin.chuc-vu.*', 'fa-id-badge', 'Chức vụ'],
        ]],
        ['Chất lượng và báo cáo', [
            ['DANH_GIA', 'admin.danh-gia.index', 'admin.danh-gia.*', 'fa-star', 'Đánh giá'],
            ['THONG_KE', 'admin.thong-ke.index', 'admin.thong-ke.*', 'fa-chart-line', 'Thống kê'],
        ]],
    ];

    $thu = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'][now()->dayOfWeek];
    $tenNhanVien = $nhanVien?->TenNhanVien ?? 'Admin';
@endphp
<aside class="admin-sidebar" id="admin-sidebar" aria-label="Menu quản trị">
    <a class="admin-brand" href="{{ route('admin.dashboard') }}">
        <span class="brand-mark" aria-hidden="true">V</span>
        <span class="brand-name">
            <strong>Khách sạn Vip</strong>
            <span>Bàn làm việc nhân viên</span>
        </span>
    </a>

    <nav class="admin-menu">
        @foreach ($nhomMenu as [$nhanNhom, $mucs])
            @php($mucCoQuyen = array_filter($mucs, fn ($m) => $nhanVien?->coQuyen($m[0]) ?? false))
            @if (count($mucCoQuyen))
                <div class="menu-group">
                    <div class="menu-group-label">{{ $nhanNhom }}</div>
                    @foreach ($mucs as [$maQuyen, $tenRoute, $mauActive, $icon, $nhan])
                        @quyen($maQuyen)
                        <a class="admin-menu-link {{ request()->routeIs($mauActive) ? 'active' : '' }}"
                           href="{{ route($tenRoute) }}"
                           @if (request()->routeIs($mauActive)) aria-current="page" @endif>
                            <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                            <span>{{ $nhan }}</span>
                        </a>
                        @endquyen
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>

    <div class="admin-sidebar-foot">
        <a href="{{ route('trang-chu') }}" target="_blank" rel="noopener">
            <i class="fa-solid fa-arrow-up-right-from-square me-1" aria-hidden="true"></i>Mở trang dành cho khách
        </a>
    </div>
</aside>
<div class="sidebar-backdrop" data-sidebar-close aria-hidden="true"></div>

<header class="admin-topbar">
    <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="Mở menu">
        <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>

    <div class="topbar-date">
        <span>{{ $thu }}, </span><strong>{{ now()->format('d/m/Y') }}</strong>
    </div>

    <div class="d-flex align-items-center gap-1 ms-auto">
        <div class="dropdown">
            <button class="admin-account" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="admin-avatar" aria-hidden="true">{{ mb_substr($tenNhanVien, 0, 1) }}</span>
                <span class="account-text">
                    <strong>{{ $tenNhanVien }}</strong>
                    <small>{{ $nhanVien?->chucVu?->TenChucVu ?? 'Nhân viên' }}</small>
                </span>
                <i class="fa-solid fa-chevron-down small text-muted ms-1" aria-hidden="true"></i>
                <span class="visually-hidden">Mở menu tài khoản</span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end admin-account-menu">
                <li>
                    <a class="dropdown-item" href="{{ route('trang-chu') }}" target="_blank" rel="noopener">
                        <i class="fa-solid fa-globe" aria-hidden="true"></i>Xem trang khách
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.doi-mat-khau') }}">
                        <i class="fa-solid fa-key" aria-hidden="true"></i>Đổi mật khẩu
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('admin.dang-xuat') }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn đăng xuất?')">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>Đăng xuất
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
