{{-- Thẻ phòng dùng chung cho trang chủ và danh sách phòng. Nhận $p, tùy chọn $ngayNhan / $ngayTra --}}
@php
    $ngayNhan = $ngayNhan ?? null;
    $ngayTra = $ngayTra ?? null;
    $linkDat = route('dat-phong.create', array_filter(['phong' => $p->MaPhong, 'ngayNhan' => $ngayNhan, 'ngayTra' => $ngayTra]));
@endphp
<article class="room-card">
    <div class="room-card-media">
        @if (! empty($p->HinhAnhDaiDien))
            <img src="{{ anh_url($p->HinhAnhDaiDien) }}" alt="Phòng {{ $p->MaPhong }}" loading="lazy" decoding="async" width="600" height="450">
        @else
            <span class="placeholder-plate"><i class="fa-solid fa-bed fa-2x" aria-hidden="true"></i></span>
        @endif
        <span class="door-plate door-plate-on-image" aria-hidden="true">{{ $p->MaPhong }}</span>
    </div>
    <div class="room-card-body">
        <h3>
            <a href="{{ route('phong.show', $p) }}">
                <span class="visually-hidden">Phòng {{ $p->MaPhong }}, </span>{{ $p->loaiPhong?->TenLoaiPhong ?? 'Phòng tiêu chuẩn' }}
            </a>
        </h3>
        <p class="room-price"><strong>{{ format_currency($p->loaiPhong?->GiaPhong) }}</strong> / đêm</p>
        <div class="room-card-foot">
            @if ($p->nhanDatPhong())
                <span class="text-muted small">Phòng số {{ $p->MaPhong }}</span>
                <a class="btn btn-sm btn-primary" href="{{ $linkDat }}">Đặt phòng</a>
            @else
                <span class="status-badge danger">Đang bảo trì</span>
            @endif
        </div>
    </div>
</article>
