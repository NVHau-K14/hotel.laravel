@extends('client.layouts.app')

@section('title', 'Tài khoản của tôi')

@section('content')
<main class="wrap wrap-narrow">
    <header class="account-head">
        <span class="monogram" aria-hidden="true">{{ mb_substr($khachHang->HoTen ?? 'K', 0, 1) }}</span>
        <div>
            <h1>{{ $khachHang->HoTen }}</h1>
            <p>Tài khoản khách hàng</p>
        </div>
        <div class="page-head-actions">
            <a class="btn btn-primary" href="{{ route('phong.index') }}">Đặt phòng mới</a>
        </div>
    </header>

    <div class="account-grid">
        <section class="sheet" aria-labelledby="thong-tin-ca-nhan">
            <div class="d-flex justify-content-between align-items-baseline gap-2 mb-2">
                <h2 class="sheet-title" id="thong-tin-ca-nhan">Thông tin cá nhân</h2>
                <a href="{{ route('tai-khoan.sua') }}" class="small fw-semibold">Sửa</a>
            </div>
            <dl class="detail-list">
                <div><dt>Họ và tên</dt><dd>{{ $khachHang->HoTen }}</dd></div>
                <div><dt>Số điện thoại</dt><dd>{{ $khachHang->DienThoai }}</dd></div>
                <div><dt>Email</dt><dd>{{ $khachHang->Email ?? 'Chưa cập nhật' }}</dd></div>
                <div><dt>CMND / CCCD</dt><dd>{{ $khachHang->CMND ?? 'Chưa cập nhật' }}</dd></div>
                <div><dt>Địa chỉ</dt><dd>{{ $khachHang->DiaChi ?? 'Chưa cập nhật' }}</dd></div>
            </dl>
        </section>

        <nav class="sheet" aria-label="Lối tắt tài khoản">
            <ul class="quick-links">
                <li><a href="{{ route('tai-khoan.lich-su') }}"><i class="fa-solid fa-receipt" aria-hidden="true"></i>Lịch sử đặt phòng</a></li>
                <li><a href="{{ route('tai-khoan.sua') }}"><i class="fa-solid fa-user-pen" aria-hidden="true"></i>Sửa thông tin, đổi mật khẩu</a></li>
                <li><a href="{{ route('danh-gia.index') }}"><i class="fa-solid fa-star" aria-hidden="true"></i>Gửi đánh giá phòng</a></li>
                <li><a href="{{ route('dich-vu.index') }}"><i class="fa-solid fa-mug-saucer" aria-hidden="true"></i>Bảng giá dịch vụ</a></li>
                <li>
                    <form method="POST" action="{{ route('tai-khoan.dang-xuat') }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn đăng xuất?')">
                        @csrf
                        <button type="submit" class="is-danger"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Đăng xuất</button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</main>
@endsection
