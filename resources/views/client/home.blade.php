@extends('client.layouts.app')

@section('title', 'Trang chủ - Khách sạn Nghỉ dưỡng Vip')

@section('content')
<main>
    <section class="hero">
        <div class="wrap">
            <div class="hero-grid">
                <div class="hero-copy">
                    <p class="hero-place">123 Đường Bờ Biển, Đà Nẵng</p>
                    <h1>Nghỉ ngơi bên biển Đà Nẵng</h1>
                    <p>Xem phòng còn trống theo ngày bạn đến, gửi yêu cầu đặt phòng trực tuyến và lễ tân sẽ gọi lại để xác nhận.</p>
                </div>
                <figure class="hero-media">
                    <img src="{{ anh_url('Images/ChiTietPhong/Phong301/01.jpg', 1600) }}" srcset="{{ anh_srcset('Images/ChiTietPhong/Phong301/01.jpg') }}" sizes="(min-width: 992px) 60vw, 100vw" alt="Phòng ngủ với giường đôi, vách gỗ sồi và tủ âm tường"
                         width="1200" height="930" fetchpriority="high" decoding="async">
                </figure>
            </div>

            {{-- Tìm phòng trống: gửi sang trang danh sách phòng với bộ lọc ngày --}}
            <div class="booking-bar">
                <form method="GET" action="{{ route('phong.index') }}">
                    <div>
                        <label class="form-label" for="tim-loai-phong">Loại phòng</label>
                        <select id="tim-loai-phong" name="maLoaiPhong" class="form-select">
                            <option value="">Tất cả loại phòng</option>
                            @foreach ($loaiPhongs as $lp)
                                <option value="{{ $lp->MaLoaiPhong }}">{{ $lp->TenLoaiPhong }} ({{ format_currency($lp->GiaPhong) }}/đêm)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="tim-ngay-nhan">Ngày nhận phòng</label>
                        <input id="tim-ngay-nhan" type="date" name="ngayNhan" class="form-control" min="{{ today()->toDateString() }}" value="{{ today()->toDateString() }}">
                    </div>
                    <div>
                        <label class="form-label" for="tim-ngay-tra">Ngày trả phòng</label>
                        <input id="tim-ngay-tra" type="date" name="ngayTra" class="form-control" min="{{ today()->addDay()->toDateString() }}" value="{{ today()->addDay()->toDateString() }}">
                    </div>
                    <button type="submit" class="btn btn-brass">Tìm phòng trống</button>
                </form>
                <p class="booking-bar-note">Đặt trực tuyến tối đa {{ \App\Models\DatPhong::SO_DEM_TOI_DA }} đêm mỗi đơn. Cần ở lâu hơn, gọi lễ tân 0909 123 456.</p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="wrap">
            <div class="section-head">
                <div>
                    <h2>Phòng nghỉ</h2>
                    <p>Giá tính theo đêm, chưa gồm dịch vụ gọi thêm trong thời gian lưu trú.</p>
                </div>
                <a class="text-link" href="{{ route('phong.index') }}">Xem tất cả phòng</a>
            </div>
            <div class="room-grid">
                @foreach ($phongs as $p)
                    @include('client.partials.room-card', ['p' => $p])
                @endforeach
            </div>
        </div>
    </section>

    @if ($dichVus->isNotEmpty())
    <section class="section pt-0">
        <div class="wrap">
            <div class="section-head">
                <div>
                    <h2>Dịch vụ trong kỳ nghỉ</h2>
                    <p>Gọi thêm khi đang lưu trú; chi phí được cộng vào hóa đơn lúc trả phòng.</p>
                </div>
                <a class="text-link" href="{{ route('dich-vu.index') }}">Xem bảng giá dịch vụ</a>
            </div>
            <div class="service-grid">
                @foreach ($dichVus->take(4) as $dv)
                    <figure class="service-item">
                        <div class="service-item-media">
                            @if (! empty($dv->HinhAnh))
                                <img src="{{ anh_url($dv->HinhAnh) }}" alt="" loading="lazy" decoding="async" width="500" height="400">
                            @else
                                <span class="placeholder-plate"><i class="fa-solid fa-bell-concierge" aria-hidden="true"></i></span>
                            @endif
                        </div>
                        <figcaption>
                            <h3>{{ $dv->TenDichVu }}</h3>
                            <span class="price">{{ format_currency($dv->GiaDichVu) }}</span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</main>
@endsection
