@extends('client.layouts.app')

@section('title', "Chi tiết phòng {$room->MaPhong} - {$room->loaiPhong?->TenLoaiPhong}")

@php
    $anhPhong = $gallery ?? (empty($room->HinhAnhDaiDien) ? [] : [$room->HinhAnhDaiDien]);
    $conTrong = $room->TrangThai === \App\Models\Phong::TRONG || empty($room->TrangThai);
@endphp

@section('content')
<main class="wrap">
    <a href="{{ route('phong.index') }}" class="back-link mt-3 mb-2">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Danh sách phòng
    </a>

    <div class="room-detail">
        <section aria-label="Ảnh phòng">
            <div class="gallery-main">
                @if (count($anhPhong))
                    <img id="anh-chinh" src="{{ anh_url($anhPhong[0], 1600) }}" srcset="{{ anh_srcset($anhPhong[0]) }}" sizes="(min-width: 992px) 64vw, 100vw" alt="Phòng {{ $room->MaPhong }}, ảnh 1" width="1200" height="900" fetchpriority="high">
                @else
                    <div class="d-grid h-100" style="place-items:center">
                        <span class="door-plate door-plate-lg">{{ $room->MaPhong }}</span>
                    </div>
                @endif
            </div>

            @if (count($anhPhong) > 1)
                <div class="gallery-thumbs">
                    @foreach ($anhPhong as $i => $anh)
                        <button type="button" class="gallery-thumb" data-anh="{{ anh_url($anh, 1600) }}" data-mo-ta="Phòng {{ $room->MaPhong }}, ảnh {{ $i + 1 }}"
                                aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" aria-label="Xem ảnh {{ $i + 1 }}">
                            <img src="{{ anh_url($anh) }}" alt="" loading="lazy" decoding="async" width="200" height="150">
                        </button>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="room-aside">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <span class="door-plate door-plate-lg" aria-hidden="true">{{ $room->MaPhong }}</span>
                <span class="status-badge {{ $conTrong ? 'success' : 'warning' }}">{{ $room->TrangThai ?? 'Trống' }}</span>
            </div>
            <h1>Phòng {{ $room->MaPhong }}</h1>
            <div class="room-type">{{ $room->loaiPhong?->TenLoaiPhong }}</div>

            <div class="room-rate">
                <strong>{{ format_currency($room->loaiPhong?->GiaPhong) }}</strong>
                <span>/ đêm</span>
            </div>

            <h2>Mô tả và tiện nghi</h2>
            <p class="room-desc">{!! nl2br(e($room->loaiPhong?->MoTa ?? 'Đầy đủ tiện nghi cơ bản, máy lạnh, wifi tốc độ cao.')) !!}</p>

            <div class="mt-4">
                @if ($room->nhanDatPhong())
                    <a href="{{ route('dat-phong.create', $room) }}" class="btn btn-primary btn-lg w-100">
                        {{ auth('web')->check() ? 'Đặt phòng này' : 'Đăng nhập để đặt phòng' }}
                    </a>
                    <p class="small text-muted mt-2 mb-0">Chưa cần thanh toán khi gửi yêu cầu; lễ tân gọi lại để xác nhận.</p>
                @else
                    <button class="btn btn-secondary btn-lg w-100" disabled>Phòng đang bảo trì</button>
                @endif
            </div>
        </aside>
    </div>
</main>
@endsection

@push('scripts')
<script>
    (function () {
        var anhChinh = document.getElementById('anh-chinh');
        var nut = document.querySelectorAll('.gallery-thumb');
        if (!anhChinh || !nut.length) return;
        nut.forEach(function (n) {
            n.addEventListener('click', function () {
                anhChinh.src = n.dataset.anh;
                anhChinh.alt = n.dataset.moTa;
                nut.forEach(function (k) { k.setAttribute('aria-pressed', k === n ? 'true' : 'false'); });
            });
        });
    })();
</script>
@endpush
