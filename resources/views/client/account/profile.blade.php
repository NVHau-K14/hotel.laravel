@extends('client.layouts.app')

@section('title', 'Tài khoản của tôi')

@section('content')
<main class="client-section" style="max-width: 900px; margin: 2.5rem auto; padding: 0 1rem;">
    <!-- Hero Profile -->
    <div class="card bg-white p-4 rounded shadow-sm border mb-4 d-flex flex-row align-items-center gap-4">
        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-1" style="width: 80px; height: 80px; min-width: 80px;">
            {{ mb_substr($khachHang->HoTen ?? 'K', 0, 1) }}
        </div>
        <div class="flex-grow-1">
            <span class="badge bg-light text-primary border mb-1">Tài khoản khách hàng</span>
            <h2 class="fw-bold mb-1 text-dark">{{ $khachHang->HoTen }}</h2>
            <p class="text-muted mb-0">Chào mừng bạn đến với trang quản lý tài khoản cá nhân tại Khách sạn Vip.</p>
        </div>
        <div>
            <a class="btn btn-outline-primary" href="{{ route('tai-khoan.lich-su') }}">
                <i class="fa-solid fa-clock-rotate-left me-1"></i> Lịch sử đặt phòng
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Thông tin cá nhân -->
        <div class="col-md-7">
            <div class="card bg-white p-4 rounded shadow-sm border h-100">
                <h4 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-address-card me-2 text-primary"></i>Thông tin cá nhân</h4>
                <div class="d-flex flex-column gap-3">
                    <div class="p-2 border-bottom d-flex justify-content-between">
                        <span class="text-muted">Họ và tên:</span>
                        <strong class="text-dark">{{ $khachHang->HoTen }}</strong>
                    </div>
                    <div class="p-2 border-bottom d-flex justify-content-between">
                        <span class="text-muted">Số điện thoại:</span>
                        <strong class="text-dark">{{ $khachHang->DienThoai }}</strong>
                    </div>
                    <div class="p-2 border-bottom d-flex justify-content-between">
                        <span class="text-muted">Email liên hệ:</span>
                        <strong class="text-dark">{{ $khachHang->Email ?? 'Chưa cập nhật' }}</strong>
                    </div>
                    <div class="p-2 border-bottom d-flex justify-content-between">
                        <span class="text-muted">CMND / CCCD:</span>
                        <strong class="text-dark">{{ $khachHang->CMND ?? 'Chưa cập nhật' }}</strong>
                    </div>
                    <div class="p-2 d-flex justify-content-between">
                        <span class="text-muted">Địa chỉ:</span>
                        <strong class="text-dark">{{ $khachHang->DiaChi ?? 'Chưa cập nhật' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thao tác nhanh -->
        <div class="col-md-5">
            <div class="card bg-white p-4 rounded shadow-sm border h-100">
                <h4 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-bolt me-2 text-warning"></i>Truy cập nhanh</h4>
                <div class="d-flex flex-column gap-2">
                    <a class="btn btn-primary py-2 text-start" href="{{ route('phong.index') }}">
                        <i class="fa-solid fa-calendar-plus me-2"></i> Đặt phòng mới
                    </a>
                    <a class="btn btn-outline-secondary py-2 text-start" href="{{ route('tai-khoan.lich-su') }}">
                        <i class="fa-solid fa-receipt me-2 text-info"></i> Xem đơn đã đặt
                    </a>
                    <a class="btn btn-outline-secondary py-2 text-start" href="{{ route('danh-gia.index') }}">
                        <i class="fa-solid fa-star me-2 text-warning"></i> Gửi đánh giá phòng
                    </a>
                    <a class="btn btn-outline-secondary py-2 text-start" href="{{ route('dich-vu.index') }}">
                        <i class="fa-solid fa-mug-saucer me-2 text-success"></i> Xem dịch vụ đi kèm
                    </a>
                    <hr class="my-2">
                    <form method="POST" action="{{ route('tai-khoan.dang-xuat') }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn đăng xuất?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger py-2 text-start">
                            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
