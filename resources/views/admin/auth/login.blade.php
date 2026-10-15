@extends('admin.layouts.app', ['hideNav' => true])

@section('title', 'Đăng nhập Quản trị')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card p-4 shadow-sm" style="width: 100%; max-width: 420px; border-radius: 12px;">
        <div class="text-center mb-4">
            <i class="fa-solid fa-hotel text-primary fa-3x mb-2"></i>
            <h2 class="fw-bold">QUẢN TRỊ KHÁCH SẠN</h2>
            <p class="text-muted">Đăng nhập tài khoản nhân viên</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.dang-nhap.store') }}">
            @csrf
            <div class="mb-3">
                <label for="admin-username" class="form-label fw-semibold">Tên đăng nhập</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                    <input type="text" id="admin-username" name="username" class="form-control" placeholder="Tên đăng nhập" autocomplete="username" required autofocus value="{{ old('username') }}">
                </div>
            </div>

            <div class="mb-4">
                <label for="admin-password" class="form-label fw-semibold">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" id="admin-password" name="password" class="form-control" placeholder="Mật khẩu" autocomplete="current-password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="fa-solid fa-right-to-bracket me-2"></i>Đăng nhập
            </button>
        </form>

        <div class="mt-4 pt-3 border-top text-center text-muted small">
            <span>Tài khoản mẫu: <strong>viet</strong> / <strong>1</strong> (Quản lý) | <strong>binh</strong> / <strong>1</strong> (Lễ tân)</span>
            <div class="mt-2">
                <a href="{{ route('trang-chu') }}" class="text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i>Về trang dành cho khách hàng
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
