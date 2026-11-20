@extends('client.layouts.app')

@section('title', 'Liên hệ - Khách sạn Nghỉ dưỡng Vip')

@section('content')
<main class="wrap">
    <header class="page-intro">
        <h1>Liên hệ</h1>
        <p>Lễ tân trực 24/7. Gọi điện là cách nhanh nhất để đổi ngày, hỏi phòng trống hoặc đặt dịch vụ.</p>
    </header>

    <div class="contact-layout">
        <dl class="contact-list">
            <div>
                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                <dt>Hotline đặt phòng</dt>
                <dd><a href="tel:0909123456">0909 123 456</a></dd>
            </div>
            <div>
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <dt>Email</dt>
                <dd><a href="mailto:contact@khachsanvip.vn">contact@khachsanvip.vn</a></dd>
            </div>
            <div>
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <dt>Địa chỉ</dt>
                <dd>123 Đường Bờ Biển, TP. Đà Nẵng</dd>
            </div>
            <div>
                <i class="fa-solid fa-clock" aria-hidden="true"></i>
                <dt>Giờ phục vụ</dt>
                <dd>24/7, tất cả các ngày trong năm</dd>
            </div>
        </dl>

        <div>
            <figure class="contact-figure">
                <img src="{{ asset('Images/ChiTietPhong/Phong201/02.jpg') }}" alt="Phòng studio có bàn làm việc dài cạnh cửa sổ và bếp nhỏ" loading="lazy" decoding="async" width="800" height="1000">
            </figure>
            <a class="btn btn-primary btn-lg w-100 mt-3" href="{{ route('phong.index') }}">Xem phòng và đặt ngay</a>
        </div>
    </div>
</main>
@endsection
