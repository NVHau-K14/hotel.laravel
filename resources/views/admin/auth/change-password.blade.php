@extends('admin.layouts.app')

@section('title', 'Đổi mật khẩu quản trị')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Đổi mật khẩu</h1>
            <p class="text-muted mb-0">Cập nhật mật khẩu bảo mật cho tài khoản {{ auth('admin')->user()->Username }}</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4" style="max-width: 500px;">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.doi-mat-khau.update') }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                <input type="password" name="matKhauCu" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                <input type="password" name="matKhauMoi" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                <input type="password" name="xacNhanMatKhau" class="form-control" required>
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
