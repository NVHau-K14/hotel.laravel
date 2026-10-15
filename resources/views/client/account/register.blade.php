@extends('client.layouts.app')

@section('title', 'Đăng ký tài khoản khách hàng')

@section('content')
<main class="container my-5 d-flex justify-content-center">
    <div class="card border-0 shadow-sm p-4 p-md-5" style="width: 100%; max-width: 500px; border-radius: 12px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold">ĐĂNG KÝ TÀI KHOẢN</h2>
            <p class="text-muted">Tạo tài khoản để theo dõi lịch sử đặt phòng</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('tai-khoan.dang-ky.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                <input type="text" name="hoTen" class="form-control" placeholder="Nguyễn Văn A" required value="{{ old('hoTen') }}">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="tel" name="dienThoai" class="form-control" placeholder="0901234567" required value="{{ old('dienThoai') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="email@example.com" value="{{ old('email') }}">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">CMND / CCCD</label>
                    <input type="text" name="cmnd" class="form-control" placeholder="Số CCCD" value="{{ old('cmnd') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Địa chỉ</label>
                    <input type="text" name="diaChi" class="form-control" placeholder="Tỉnh / Thành phố" value="{{ old('diaChi') }}">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold fs-6 mt-3">
                <i class="fa-solid fa-user-plus me-2"></i> Hoàn tất đăng ký
            </button>
        </form>

        <div class="mt-4 pt-3 border-top text-center text-muted small">
            <span>Đã có tài khoản? </span>
            <a href="{{ route('tai-khoan.dang-nhap') }}" class="fw-bold text-primary text-decoration-none">Đăng nhập</a>
        </div>
    </div>
</main>
@endsection
