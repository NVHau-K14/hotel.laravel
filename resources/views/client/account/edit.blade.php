@extends('client.layouts.app')

@section('title', 'Sửa thông tin tài khoản')

@section('content')
<main class="wrap wrap-tight pb-5">
    <a href="{{ route('tai-khoan.ho-so') }}" class="back-link mt-3">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Tài khoản của tôi
    </a>
    <header class="page-intro pt-3">
        <h1>Sửa thông tin</h1>
    </header>

    @if ($errors->any())
        <div class="alert alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="sheet" aria-labelledby="sua-thong-tin">
        <h2 class="sheet-title" id="sua-thong-tin">Thông tin cá nhân</h2>
        <p class="sheet-sub">Dùng cho mọi đơn đặt phòng của bạn.</p>
        <form method="POST" action="{{ route('tai-khoan.cap-nhat') }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label" for="tk-ho-ten">Họ và tên <span class="field-required" aria-hidden="true">*</span></label>
                <input type="text" id="tk-ho-ten" name="hoTen" class="form-control" maxlength="100" autocomplete="name" required value="{{ old('hoTen', $khachHang->HoTen) }}">
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="form-label" for="tk-dien-thoai">Số điện thoại di động <span class="field-required" aria-hidden="true">*</span></label>
                    <input type="tel" id="tk-dien-thoai" name="dienThoai" class="form-control" inputmode="numeric" maxlength="15" autocomplete="tel" required value="{{ old('dienThoai', $khachHang->DienThoai) }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="tk-email">Email</label>
                    <input type="email" id="tk-email" name="email" class="form-control" maxlength="254" autocomplete="email" spellcheck="false" value="{{ old('email', $khachHang->Email) }}">
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <label class="form-label" for="tk-cmnd">CMND / CCCD</label>
                    <input type="text" id="tk-cmnd" name="cmnd" class="form-control" inputmode="numeric" maxlength="12" autocomplete="off" placeholder="9 hoặc 12 chữ số" value="{{ old('cmnd', $khachHang->CMND) }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="tk-dia-chi">Địa chỉ</label>
                    <input type="text" id="tk-dia-chi" name="diaChi" class="form-control" maxlength="255" autocomplete="street-address" value="{{ old('diaChi', $khachHang->DiaChi) }}">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Lưu thông tin</button>
        </form>
    </section>

    <section class="sheet" aria-labelledby="doi-mat-khau">
        <h2 class="sheet-title" id="doi-mat-khau">Đổi mật khẩu</h2>
        <p class="sheet-sub">Ít nhất 8 ký tự, gồm chữ hoa, chữ thường và chữ số; không chứa số điện thoại hoặc email.</p>
        <form method="POST" action="{{ route('tai-khoan.doi-mat-khau') }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label" for="mk-cu">Mật khẩu hiện tại</label>
                <input type="password" id="mk-cu" name="matKhauCu" class="form-control" autocomplete="current-password" required>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <label class="form-label" for="mk-moi">Mật khẩu mới</label>
                    <input type="password" id="mk-moi" name="matKhauMoi" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="mk-nhap-lai">Nhập lại mật khẩu mới</label>
                    <input type="password" id="mk-nhap-lai" name="xacNhanMatKhau" class="form-control" minlength="8" maxlength="72" autocomplete="new-password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-outline-primary">Đổi mật khẩu</button>
        </form>
    </section>
</main>
@endsection
