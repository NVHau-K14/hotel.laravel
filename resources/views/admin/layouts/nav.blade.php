@php
    $nhanVien = auth('admin')->user();

    // [mã quyền, tên route, mẫu tên route để tô "active", icon, nhãn] — chỉ hiện mục có quyền
    $menu = [
        ['DASHBOARD', 'admin.dashboard', 'admin.dashboard', 'fa-gauge-high', 'Dashboard'],
        ['PHONG', 'admin.phong.index', 'admin.phong.*', 'fa-key', 'Phòng'],
        ['LOAI_PHONG', 'admin.loai-phong.index', 'admin.loai-phong.*', 'fa-bed', 'Loại phòng'],
        ['DAT_PHONG', 'admin.dat-phong.index', 'admin.dat-phong.*', 'fa-calendar-check', 'Đặt phòng'],
        ['DICH_VU', 'admin.dich-vu.index', 'admin.dich-vu.*', 'fa-mug-saucer', 'Dịch vụ'],
        ['CT_DICH_VU', 'admin.ct-dich-vu.index', 'admin.ct-dich-vu.*', 'fa-list-check', 'Chi tiết DV'],
        ['HOA_DON', 'admin.hoa-don.index', 'admin.hoa-don.*', 'fa-file-invoice-dollar', 'Hóa đơn'],
        ['KHACH_HANG', 'admin.khach-hang.index', 'admin.khach-hang.*', 'fa-user-check', 'Khách hàng'],
        ['NHAN_VIEN', 'admin.nhan-vien.index', 'admin.nhan-vien.*', 'fa-users', 'Nhân viên'],
        ['PHAN_CONG', 'admin.phan-cong.index', 'admin.phan-cong.*', 'fa-clipboard-user', 'Phân công'],
        ['CHUC_VU', 'admin.chuc-vu.index', 'admin.chuc-vu.*', 'fa-id-badge', 'Chức vụ'],
        ['DANH_GIA', 'admin.danh-gia.index', 'admin.danh-gia.*', 'fa-star', 'Đánh giá'],
        ['THONG_KE', 'admin.thong-ke.index', 'admin.thong-ke.*', 'fa-chart-line', 'Thống kê'],
    ];
@endphp
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-brand d-flex align-items-center gap-2 px-3 py-3" href="{{ route('admin.dashboard') }}">
            <span class="brand-icon"><i class="fa-solid fa-hotel"></i></span>
            <span>KHÁCH SẠN ADMIN</span>
        </a>

        <nav class="admin-menu px-2">
            @foreach ($menu as [$maQuyen, $tenRoute, $mauActive, $icon, $nhan])
                @quyen($maQuyen)
                <a class="admin-menu-link {{ request()->routeIs($mauActive) ? 'active' : '' }}" href="{{ route($tenRoute) }}">
                    <i class="fa-solid {{ $icon }}"></i>
                    <span>{{ $nhan }}</span>
                </a>
                @endquyen
            @endforeach
        </nav>
    </aside>

    <header class="admin-topbar shadow-sm">
        <div class="d-flex align-items-center gap-2 text-muted fw-semibold">
            <i class="fa-solid fa-layer-group text-primary"></i>
            <span>Hệ thống quản lý khách sạn</span>
        </div>

        <div class="dropdown ms-auto">
            <button class="admin-account dropdown-toggle border-0 bg-transparent d-flex align-items-center gap-2"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                <span class="admin-avatar">
                    <i class="fa-solid fa-user-tie"></i>
                </span>
                <span class="account-text text-start">
                    <strong>{{ $nhanVien?->TenNhanVien ?? 'Admin' }}</strong>
                    <br>
                    <small class="text-muted">{{ $nhanVien?->chucVu?->TenChucVu ?? 'Nhân viên' }}</small>
                </span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end admin-account-menu shadow">
                <li>
                    <a class="dropdown-item" href="{{ route('trang-chu') }}" target="_blank">
                        <i class="fa-solid fa-globe me-2 text-info"></i>Xem trang khách
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.doi-mat-khau') }}">
                        <i class="fa-solid fa-key me-2 text-warning"></i>Đổi mật khẩu
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('admin.dang-xuat') }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn đăng xuất?')">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger fw-bold">
                            <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </header>
</div>
