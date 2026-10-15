@extends('client.layouts.app')

@section('title', 'Dịch vụ tiện ích tại Khách sạn')

@section('content')
<main class="container my-4">
    <div class="mb-4 text-center">
        <h1 class="fw-bold">Dịch vụ tiện ích</h1>
        <p class="text-muted">Các dịch vụ đi kèm giúp kỳ nghỉ của bạn thêm trọn vẹn</p>
    </div>

    <div class="row g-4">
        @foreach ($services as $dv)
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                    <div style="height: 200px; background: #f3f4f6;">
                        @if (! empty($dv->HinhAnh))
                            <img src="{{ asset($dv->HinhAnh) }}" alt="{{ $dv->TenDichVu }}" class="w-100 h-100" style="object-fit: cover;">
                        @else
                            <div class="d-flex justify-content-center align-items-center h-100 text-muted fs-1">
                                <i class="fa-solid fa-bell-concierge"></i>
                            </div>
                        @endif
                    </div>
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <h4 class="fw-bold mb-2">{{ $dv->TenDichVu }}</h4>
                            <p class="text-muted small mb-3">Dịch vụ chất lượng cao phục vụ tận tình tại phòng hoặc tại các khu tiện ích của khách sạn.</p>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <span class="text-muted small">Đơn giá:</span>
                            <span class="text-primary fw-bold fs-5">{{ format_currency($dv->GiaDichVu) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</main>
@endsection
