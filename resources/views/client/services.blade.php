@extends('client.layouts.app')

@section('title', 'Dịch vụ tiện ích tại Khách sạn')

@section('content')
<main class="wrap pb-5">
    <header class="page-intro">
        <h1>Dịch vụ</h1>
        <p>Gọi lễ tân để dùng dịch vụ trong thời gian lưu trú. Chi phí được cộng vào hóa đơn khi trả phòng.</p>
    </header>

    @if ($services->isEmpty())
        <div class="empty-state">
            <h2>Chưa có dịch vụ</h2>
            <p>Khách sạn đang cập nhật danh sách dịch vụ. Vui lòng liên hệ lễ tân để được hỗ trợ.</p>
        </div>
    @else
        <div class="service-grid">
            @foreach ($services as $dv)
                <figure class="service-item">
                    <div class="service-item-media">
                        @if (! empty($dv->HinhAnh))
                            <img src="{{ asset($dv->HinhAnh) }}" alt="" loading="lazy" decoding="async" width="500" height="400">
                        @else
                            <span class="placeholder-plate"><i class="fa-solid fa-bell-concierge" aria-hidden="true"></i></span>
                        @endif
                    </div>
                    <figcaption>
                        <h2 class="h3">{{ $dv->TenDichVu }}</h2>
                        <span class="price">{{ format_currency($dv->GiaDichVu) }}</span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    @endif
</main>
@endsection
