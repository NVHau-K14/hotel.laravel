@extends('client.layouts.app')

@section('title', 'Trang chủ - Khách sạn Nghỉ dưỡng Vip')

@section('content')
<main>
    <section class="client-hero">
        <div>
            <p class="eyebrow">Trải nghiệm kỳ nghỉ tuyệt vời</p>
            <h1>Khách sạn Nghỉ dưỡng Vip</h1>
            <p>Chọn phòng, kiểm tra lịch trống và gửi yêu cầu đặt phòng trực tuyến tiện lợi chỉ trong vài bước.</p>
            <div class="hero-actions">
                <a class="btn" href="{{ route('phong.index') }}">Khám phá phòng ngay</a>
                <a class="btn secondary" href="{{ route('dich-vu.index') }}">Xem dịch vụ</a>
            </div>
        </div>
    </section>

    <!-- PHÒNG NỔI BẬT -->
    <section class="client-section">
        <div class="section-heading">
            <h2>Phòng nổi bật</h2>
            <a href="{{ route('phong.index') }}">Xem tất cả phòng &rarr;</a>
        </div>
        <div class="room-card-grid">
            @foreach ($phongs as $p)
                <article class="client-card room-card">
                    <div class="room-visual image-visual">
                        @if (! empty($p->HinhAnhDaiDien))
                            <img src="{{ asset($p->HinhAnhDaiDien) }}" alt="Phòng {{ $p->MaPhong }}">
                        @else
                            <span>P.{{ $p->MaPhong }}</span>
                        @endif
                    </div>
                    <div class="room-card-body">
                        <h3>Phòng {{ $p->MaPhong }}</h3>
                        <p class="text-muted">{{ $p->loaiPhong?->TenLoaiPhong ?? 'Bình Dân' }}</p>
                        <strong class="text-primary fs-5">{{ format_currency($p->loaiPhong?->GiaPhong) }} / đêm</strong>
                        <div class="card-actions mt-3">
                            <a class="btn small" href="{{ route('phong.show', $p) }}">Chi tiết</a>
                            @if ($p->TrangThai !== \App\Models\Phong::BAO_TRI)
                                <a class="btn small secondary" href="{{ route('dat-phong.create', $p) }}">Đặt phòng</a>
                            @else
                                <span class="btn small secondary disabled" style="opacity: 0.6;">Bảo trì</span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <!-- DỊCH VỤ -->
    <section class="client-section">
        <div class="section-heading">
            <h2>Dịch vụ tiện ích</h2>
            <a href="{{ route('dich-vu.index') }}">Xem tất cả dịch vụ &rarr;</a>
        </div>
        <div class="service-grid service-grid-wide">
            @foreach ($dichVus as $dv)
                <article class="client-card service-card split-service-card">
                    <div class="service-info">
                        <h3>{{ $dv->TenDichVu }}</h3>
                        <p>Dịch vụ tiêu chuẩn mang lại trải nghiệm tiện nghi nhất cho bạn.</p>
                        <strong class="text-primary">{{ format_currency($dv->GiaDichVu) }}</strong>
                    </div>
                    <div class="service-visual service-photo">
                        @if (! empty($dv->HinhAnh))
                            <img src="{{ asset($dv->HinhAnh) }}" alt="{{ $dv->TenDichVu }}">
                        @else
                            <span>DV</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</main>
@endsection
