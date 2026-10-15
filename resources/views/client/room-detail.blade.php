@extends('client.layouts.app')

@section('title', "Chi tiết phòng {$room->MaPhong} - {$room->loaiPhong?->TenLoaiPhong}")

@section('content')
<main class="container my-4" style="max-width: 900px;">
    <div class="mb-3">
        <a href="{{ route('phong.index') }}" class="text-decoration-none text-muted">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách phòng
        </a>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
        <div class="row g-0">
            <div class="col-md-7">
                @if (! empty($room->HinhAnhDaiDien))
                    <img src="{{ asset($room->HinhAnhDaiDien) }}" alt="Phòng {{ $room->MaPhong }}" class="w-100 h-100" style="object-fit: cover; min-height: 380px;">
                @else
                    <div class="d-flex justify-content-center align-items-center h-100 bg-light" style="min-height: 380px;">
                        <span class="fs-1 text-muted">Phòng {{ $room->MaPhong }}</span>
                    </div>
                @endif
            </div>
            <div class="col-md-5 p-4 d-flex flex-column justify-content-between">
                <div>
                    <span class="badge bg-primary mb-2">{{ $room->loaiPhong?->TenLoaiPhong }}</span>
                    <h2 class="fw-bold mb-2">Phòng số {{ $room->MaPhong }}</h2>
                    <h4 class="text-danger fw-bold mb-3">{{ format_currency($room->loaiPhong?->GiaPhong) }} <small class="text-muted fs-6">/ đêm</small></h4>

                    <h6 class="fw-bold mt-4">Mô tả & Tiện nghi:</h6>
                    <p class="text-muted">{!! nl2br(e($room->loaiPhong?->MoTa ?? 'Đầy đủ tiện nghi cơ bản, máy lạnh, wifi tốc độ cao.')) !!}</p>

                    <div class="mt-3">
                        <span class="badge {{ ($room->TrangThai === \App\Models\Phong::TRONG || empty($room->TrangThai)) ? 'bg-success' : 'bg-warning text-dark' }}">
                            Trạng thái: {{ $room->TrangThai ?? 'Trống' }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    @if ($room->TrangThai !== \App\Models\Phong::BAO_TRI)
                        <a href="{{ route('dat-phong.create', $room) }}" class="btn btn-primary w-100 py-2 fs-6 fw-bold">
                            <i class="fa-solid fa-calendar-check me-2"></i>Đặt phòng này ngay
                        </a>
                    @else
                        <button class="btn btn-secondary w-100 py-2" disabled>Phòng đang bảo trì</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
