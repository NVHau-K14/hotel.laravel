@extends('client.layouts.app')

@section('title', 'Đăng nhập khách hàng')

@section('content')
<main class="auth-split">
    <div class="auth-art" aria-hidden="true">
        <img src="{{ asset('Images/ChiTietPhong/Phong301/01.jpg') }}" alt="" width="1200" height="800" decoding="async">
        <blockquote>Đăng nhập để đặt phòng nhanh và theo dõi các đơn đã gửi.</blockquote>
    </div>

    <div class="auth-form">
        <div>
            <h1>Đăng nhập</h1>
            <p class="lead-text">Dùng số điện thoại hoặc email đã đăng ký.</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2 mb-3" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('tai-khoan.dang-nhap.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="dn-tai-khoan">Số điện thoại hoặc email</label>
                    <input type="text" id="dn-tai-khoan" name="account" class="form-control" autocomplete="username" spellcheck="false"
                           placeholder="0912345678 hoặc ten@gmail.com" required autofocus value="{{ old('account') }}">
                </div>
                <div class="mb-4">
                    <label class="form-label" for="dn-mat-khau">Mật khẩu</label>
                    <input type="password" id="dn-mat-khau" name="password" class="form-control" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">Đăng nhập</button>
            </form>

            <div class="auth-switch">
                Chưa có tài khoản? <a href="{{ route('tai-khoan.dang-ky') }}" class="fw-semibold">Tạo tài khoản</a>
            </div>
        </div>
    </div>
</main>
@endsection
