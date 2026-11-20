@extends('client.layouts.app')

@section('title', 'Đánh giá khách hàng')

@php($diemTrungBinh = $items->count() ? round($items->avg('SoSao'), 1) : null)

@section('content')
<main class="wrap">
    <header class="page-intro">
        <h1>Khách nói gì về kỳ nghỉ</h1>
        @if ($diemTrungBinh)
            <div class="rating-summary">
                <strong>{{ number_format($diemTrungBinh, 1, ',', '.') }}</strong>
                <span class="text-muted">trên 5, từ {{ $items->count() }} nhận xét</span>
            </div>
        @else
            <p>Cảm nhận từ những vị khách đã lưu trú tại khách sạn.</p>
        @endif
    </header>

    <div class="reviews-layout">
        <section aria-label="Tất cả nhận xét">
            <h2 class="visually-hidden">Tất cả nhận xét ({{ $items->count() }})</h2>
            @if ($items->isEmpty())
                <div class="empty-state">
                    <h2>Chưa có nhận xét</h2>
                    <p>Sau khi trả phòng, bạn có thể là người đầu tiên chia sẻ trải nghiệm tại đây.</p>
                </div>
            @else
                <ul class="review-list">
                    @foreach ($items as $item)
                        <li class="review">
                            <blockquote>{!! nl2br(e($item->NoiDung)) !!}</blockquote>
                            <footer>
                                <strong>{{ $item->khachHang?->HoTen ?? 'Khách sạn Vip (phản hồi)' }}</strong>
                                <span class="stars" role="img" aria-label="{{ $item->SoSao }} trên 5 sao">
                                    @for ($s = 1; $s <= 5; $s++)
                                        <i class="fa-{{ $s <= $item->SoSao ? 'solid' : 'regular' }} fa-star" aria-hidden="true"></i>
                                    @endfor
                                </span>
                                <span>Phòng {{ $item->MaPhong }}</span>
                                <time datetime="{{ \Illuminate\Support\Carbon::parse($item->NgayDanhGia)->toDateString() }}">{{ format_date($item->NgayDanhGia) }}</time>
                            </footer>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="sheet" style="position: sticky; top: 96px;">
            @auth('web')
                @if ($phongs->isNotEmpty())
                    <h2 class="sheet-title">Gửi đánh giá</h2>
                    <p class="sheet-sub">Dành cho các phòng bạn đã ở, sau khi trả phòng và thanh toán xong.</p>
                    <form method="POST" action="{{ route('danh-gia.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="dg-phong">Phòng đã ở</label>
                            <select id="dg-phong" name="maPhong" class="form-select" required>
                                <option value="">Chọn phòng</option>
                                @foreach ($phongs as $p)
                                    <option value="{{ $p->MaPhong }}">Phòng {{ $p->MaPhong }} ({{ $p->loaiPhong?->TenLoaiPhong ?? 'Phòng' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="dg-sao">Chất lượng phục vụ</label>
                            <select id="dg-sao" name="soSao" class="form-select" required>
                                <option value="5">5 sao, rất tuyệt vời</option>
                                <option value="4">4 sao, tốt</option>
                                <option value="3">3 sao, hài lòng</option>
                                <option value="2">2 sao, chưa tốt</option>
                                <option value="1">1 sao, kém</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="dg-noi-dung">Nhận xét</label>
                            <textarea id="dg-noi-dung" name="noiDung" rows="4" class="form-control" placeholder="Phòng, tiện nghi, thái độ phục vụ…" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Gửi đánh giá</button>
                    </form>
                @else
                    <h2 class="sheet-title">Gửi đánh giá</h2>
                    <p class="text-muted mb-0">Bạn có thể đánh giá một phòng ngay sau khi trả phòng và thanh toán xong hóa đơn.</p>
                @endif
            @else
                <h2 class="sheet-title">Đã từng ở đây?</h2>
                <p class="text-muted">Đăng nhập để chia sẻ đánh giá về kỳ nghỉ của bạn.</p>
                <a href="{{ route('tai-khoan.dang-nhap') }}" class="btn btn-primary w-100">Đăng nhập</a>
            @endauth
        </aside>
    </div>
</main>
@endsection
