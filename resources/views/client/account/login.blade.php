@extends('client.layouts.app')

@section('title', 'Đăng nhập khách hàng')

@section('content')
<main class="container my-5 d-flex justify-content-center">
    <div class="card border-0 shadow-sm p-4 p-md-5" style="width: 100%; max-width: 440px; border-radius: 12px;">
        <div class="text-center mb-4">
            <i class="fa-solid fa-hotel text-primary fa-3x mb-2"></i>
            <h2 class="fw-bold">ĐĂNG NHẬP</h2>
            <p class="text-muted">Dành cho khách hàng</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('tai-khoan.dang-nhap.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Số điện thoại hoặc Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="account" class="form-control" placeholder="0912345678 hoặc teo@gmail.com" required autofocus value="{{ old('account') }}">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Mật khẩu (mặc định: 1)" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold fs-6">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Đăng nhập
            </button>
        </form>

        <div class="mt-4 pt-3 border-top text-center text-muted small">
            <span>Chưa có tài khoản? </span>
            <a href="{{ route('tai-khoan.dang-ky') }}" class="fw-bold text-primary text-decoration-none">Đăng ký ngay</a>
        </div>
    </div>
</main>
@endsection
