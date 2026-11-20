@extends('client.layouts.app')

@section('title', 'Sửa thông tin tài khoản')

@section('content')
<main class="client-section" style="max-width: 760px; margin: 2.5rem auto; padding: 0 1rem;">
    <div class="mb-3">
        <a href="{{ route('tai-khoan.ho-so') }}" class="text-decoration-none text-muted">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại tài khoản
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
    @endif

    <div class="card bg-white p-4 rounded shadow-sm border mb-4">
        <h4 class="fw-bold mb-1">Thông tin cá nhân</h4>
        <p class="text-muted small mb-3">Thông tin này được dùng cho mọi đơn đặt phòng của bạn.</p>
        <form method="POST" action="{{ route('tai-khoan.cap-nhat') }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                <input type="text" name="hoTen" class="form-control" maxlength="100" required value="{{ old('hoTen', $khachHang->HoTen) }}">
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Số điện thoại di động <span class="text-danger">*</span></label>
                    <input type="tel" name="dienThoai" class="form-control" inputmode="numeric" maxlength="15" required value="{{ old('dienThoai', $khachHang->DienThoai) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" maxlength="254" value="{{ old('email', $khachHang->Email) }}">
                </div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">CMND / CCCD</label>
                    <input type="text" name="cmnd" class="form-control" inputmode="numeric" maxlength="12" placeholder="9 hoặc 12 chữ số" value="{{ old('cmnd', $khachHang->CMND) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Địa chỉ</label>
                    <input type="text" name="diaChi" class="form-control" maxlength="255" value="{{ old('diaChi', $khachHang->DiaChi) }}">
                </div>
            </div>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thông tin
            </button>
        </form>
    </div>

    <div class="card bg-white p-4 rounded shadow-sm border">
        <h4 class="fw-bold mb-1">Đổi mật khẩu</h4>
        <p class="text-muted small mb-3">Ít nhất 8 ký tự, gồm chữ hoa, chữ thường và chữ số; không chứa số điện thoại hoặc email.</p>
        <form method="POST" action="{{ route('tai-khoan.doi-mat-khau') }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Mật khẩu hiện tại</label>
                <input type="password" name="matKhauCu" class="form-control" autocomplete="current-password" required>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mật khẩu mới</label>
                    <input type="password" name="matKhauMoi" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nhập lại mật khẩu mới</label>
                    <input type="password" name="xacNhanMatKhau" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-outline-primary px-4">
                <i class="fa-solid fa-key me-1"></i> Đổi mật khẩu
            </button>
        </form>
    </div>
</main>
@endsection
