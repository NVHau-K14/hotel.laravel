@extends('client.layouts.app')

@section('title', 'Liên hệ - Khách sạn Nghỉ dưỡng Vip')

@section('content')
<main class="client-section" style="max-width: 800px; margin: 3rem auto; padding: 0 1rem;">
    <div class="client-card contact-card bg-white p-5 rounded shadow-sm border">
        <h1 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-headset me-2"></i>Liên hệ khách sạn</h1>
        <p class="text-muted fs-5 mb-4">Chúng tôi luôn sẵn sàng hỗ trợ quý khách 24/7. Vui lòng liên hệ với chúng tôi qua các kênh thông tin dưới đây:</p>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="p-3 border rounded bg-light d-flex align-items-center gap-3">
                    <div class="bg-primary text-white p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Địa chỉ</small>
                        <div class="fw-semibold text-dark">123 Đường Ven Biển, Quận 1, TP. Hồ Chí Minh</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 border rounded bg-light d-flex align-items-center gap-3">
                    <div class="bg-success text-white p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Hotline đặt phòng</small>
                        <div class="fw-semibold text-dark">0909 123 456 / (028) 3822 8888</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 border rounded bg-light d-flex align-items-center gap-3">
                    <div class="bg-info text-white p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Email hỗ trợ</small>
                        <div class="fw-semibold text-dark">contact@khachsanvip.vn</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="p-3 border rounded bg-light d-flex align-items-center gap-3">
                    <div class="bg-warning text-white p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Giờ phục vụ</small>
                        <div class="fw-semibold text-dark">Mở cửa 24/7 tất cả các ngày</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center pt-2">
            <a class="btn btn-primary px-4 py-2 fw-semibold" href="{{ route('phong.index') }}">
                <i class="fa-solid fa-calendar-check me-2"></i>Xem phòng & Đặt ngay
            </a>
        </div>
    </div>
</main>
@endsection
