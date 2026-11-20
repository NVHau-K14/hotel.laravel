@extends('admin.layouts.app')

@section('title', 'Đổi mật khẩu quản trị')

@section('content')
<div class="container admin-wide">
    <div class="page-head">
        <div>
            <h1>Đổi mật khẩu</h1>
            <p>Cập nhật mật khẩu bảo mật cho tài khoản {{ auth('admin')->user()->Username }}</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="admin-panel p-4" style="max-width: 500px;">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.doi-mat-khau.update') }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="auth-change-password-matKhauCu" class="form-label fw-semibold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                <input id="auth-change-password-matKhauCu" type="password" name="matKhauCu" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label for="auth-change-password-matKhauMoi" class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                <input id="auth-change-password-matKhauMoi" type="password" name="matKhauMoi" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
                <div class="form-text">Ít nhất 8 ký tự, gồm chữ hoa, chữ thường và chữ số; không chứa tên đăng nhập.</div>
            </div>

            <div class="mb-3">
                <label for="auth-change-password-xacNhanMatKhau" class="form-label fw-semibold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                <input id="auth-change-password-xacNhanMatKhau" type="password" name="xacNhanMatKhau" class="form-control" required>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-key me-1"></i> Cập nhật mật khẩu
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>
@endsection
