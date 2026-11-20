@extends('client.layouts.app')

@section('title', 'Đăng ký tài khoản khách hàng')

@section('content')
<main class="auth-split">
    <div class="auth-art" aria-hidden="true">
        <img src="{{ asset('Images/ChiTietPhong/Phong201/02.jpg') }}" alt="" width="1200" height="800" decoding="async">
        <blockquote>Một tài khoản để đặt phòng, xem lịch sử lưu trú và gửi đánh giá.</blockquote>
    </div>

    <div class="auth-form">
        <div>
            <h1>Tạo tài khoản</h1>
            <p class="lead-text">Mất khoảng một phút. Các mục có dấu * là bắt buộc.</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2 mb-3" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('tai-khoan.dang-ky.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="dk-ho-ten">Họ và tên <span class="field-required" aria-hidden="true">*</span></label>
                    <input type="text" id="dk-ho-ten" name="hoTen" class="form-control" autocomplete="name" placeholder="Nguyễn Văn A" required value="{{ old('hoTen') }}">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="dk-dien-thoai">Số điện thoại di động <span class="field-required" aria-hidden="true">*</span></label>
                    <input type="tel" id="dk-dien-thoai" name="dienThoai" class="form-control" placeholder="0901234567" required
                           inputmode="numeric" maxlength="15" autocomplete="tel" value="{{ old('dienThoai') }}" aria-describedby="dk-dien-thoai-goi-y">
                    <div class="form-text" id="dk-dien-thoai-goi-y">Đã từng đặt phòng tại khách sạn? Dùng đúng số điện thoại cũ để nhận lại lịch sử đặt phòng.</div>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="dk-mat-khau">Mật khẩu <span class="field-required" aria-hidden="true">*</span></label>
                        <input type="password" id="dk-mat-khau" name="password" class="form-control" required
                               minlength="8" maxlength="72" autocomplete="new-password" aria-describedby="dk-mat-khau-goi-y">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="dk-nhap-lai">Nhập lại mật khẩu <span class="field-required" aria-hidden="true">*</span></label>
                        <input type="password" id="dk-nhap-lai" name="password_confirmation" class="form-control" required
                               minlength="8" maxlength="72" autocomplete="new-password">
                    </div>
                </div>
                <div class="form-text mt-1 mb-3" id="dk-mat-khau-goi-y">Ít nhất 8 ký tự, gồm chữ hoa, chữ thường và chữ số; không chứa số điện thoại hoặc email.</div>

                <div class="mb-3">
                    <label class="form-label" for="dk-email">Email</label>
                    <input type="email" id="dk-email" name="email" class="form-control" autocomplete="email" spellcheck="false" placeholder="email@example.com" value="{{ old('email') }}">
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label" for="dk-cmnd">CMND / CCCD</label>
                        <input type="text" id="dk-cmnd" name="cmnd" class="form-control" placeholder="9 hoặc 12 chữ số" inputmode="numeric" maxlength="12" autocomplete="off" value="{{ old('cmnd') }}">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="dk-dia-chi">Địa chỉ</label>
                        <input type="text" id="dk-dia-chi" name="diaChi" class="form-control" autocomplete="address-level1" placeholder="Tỉnh / Thành phố" value="{{ old('diaChi') }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">Tạo tài khoản</button>
            </form>

            <div class="auth-switch">
                Đã có tài khoản? <a href="{{ route('tai-khoan.dang-nhap') }}" class="fw-semibold">Đăng nhập</a>
            </div>
        </div>
    </div>
</main>
@endsection
