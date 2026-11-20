@extends('admin.layouts.app', ['hideNav' => true])

@section('title', 'Đăng nhập Quản trị')

@section('content')
<div class="admin-login">
    <section class="admin-login-art" aria-hidden="true" style="--anh-nen: url('{{ anh_url('Images/ChiTietPhong/Phong301/01.jpg', 1600) }}')">
        <div class="d-flex align-items-center gap-3">
            <span class="brand-mark">V</span>
            <span class="brand-name">
                <strong style="color:#f3ece0">Khách sạn Vip</strong>
                <span>Bàn làm việc nhân viên</span>
            </span>
        </div>
        <div>
            <h1>Mỗi ca trực bắt đầu từ đây.</h1>
            <p>Duyệt đơn, nhận và trả phòng, ghi dịch vụ, lập hóa đơn cho khách trong cùng một nơi.</p>
        </div>
        <span class="small" style="color:#b9c2bd">123 Đường Bờ Biển, Đà Nẵng</span>
    </section>

    <section class="admin-login-form">
        <div>
            <h2>Đăng nhập</h2>
            <p class="text-muted mb-4">Dùng tài khoản nhân viên do quản lý cấp.</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.dang-nhap.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="admin-username" class="form-label">Tên đăng nhập</label>
                    <input type="text" id="admin-username" name="username" class="form-control" autocomplete="username" spellcheck="false" required autofocus value="{{ old('username') }}">
                </div>

                <div class="mb-4">
                    <label for="admin-password" class="form-label">Mật khẩu</label>
                    <input type="password" id="admin-password" name="password" class="form-control" autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Đăng nhập</button>
            </form>

            <div class="demo-accounts">
                Tài khoản mẫu cho môi trường thử nghiệm:
                <dl>
                    <dt>viet</dt><dd>Admin@1234 (Quản lý)</dd>
                    <dt>binh</dt><dd>Nhanvien@1234 (Lễ tân)</dd>
                </dl>
            </div>

            <a href="{{ route('trang-chu') }}" class="back-link mt-3">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Về trang dành cho khách
            </a>
        </div>
    </section>
</div>
@endsection
