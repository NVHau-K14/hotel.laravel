@extends('client.layouts.app')

@section('title', 'Đánh giá khách hàng')

@section('content')
<main class="client-section" style="max-width: 900px; margin: 2rem auto; padding: 0 1rem;">
    <div class="client-card bg-white p-4 rounded shadow-sm border mb-4">
        <h1 class="fw-bold mb-2 text-primary"><i class="fa-solid fa-comments me-2"></i>Đánh giá khách hàng</h1>
        <p class="text-muted">Cảm nhận và chia sẻ từ những vị khách đã lưu trú tại khách sạn của chúng tôi.</p>

        @auth('web')
            @if ($phongs->isNotEmpty())
                <div class="card bg-light border p-3 my-4">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-pen-fancy me-2 text-primary"></i>Gửi đánh giá của bạn</h5>
                    <form method="POST" action="{{ route('danh-gia.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Chọn phòng bạn đã từng ở <span class="text-danger">*</span></label>
                                <select name="maPhong" class="form-select" required>
                                    <option value="">-- Chọn phòng --</option>
                                    @foreach ($phongs as $p)
                                        <option value="{{ $p->MaPhong }}">
                                            Phòng {{ $p->MaPhong }} ({{ $p->loaiPhong?->TenLoaiPhong ?? 'Phòng' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Chất lượng phục vụ <span class="text-danger">*</span></label>
                                <select name="soSao" class="form-select" required>
                                    <option value="5">⭐⭐⭐⭐⭐ 5 sao - Rất tuyệt vời</option>
                                    <option value="4">⭐⭐⭐⭐ 4 sao - Tốt</option>
                                    <option value="3">⭐⭐⭐ 3 sao - Hài lòng</option>
                                    <option value="2">⭐⭐ 2 sao - Chưa tốt</option>
                                    <option value="1">⭐ 1 sao - Kém</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Nhận xét chi tiết <span class="text-danger">*</span></label>
                                <textarea name="noiDung" rows="3" class="form-control" placeholder="Chia sẻ trải nghiệm của bạn về phòng, tiện nghi và dịch vụ..." required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fa-solid fa-paper-plane me-1"></i> Gửi đánh giá ngay
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <div class="alert alert-info my-3">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Quý khách có thể gửi đánh giá cho các phòng sau khi hoàn tất kỳ nghỉ và đã qua ngày trả phòng.
                </div>
            @endif
        @else
            <div class="alert alert-warning my-3">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                Vui lòng <a href="{{ route('tai-khoan.dang-nhap') }}" class="fw-bold text-decoration-underline">Đăng nhập</a> để chia sẻ đánh giá về kỳ nghỉ của bạn.
            </div>
        @endauth

        <hr class="my-4">

        <h3 class="fw-bold mb-3">Tất cả nhận xét ({{ $items->count() }})</h3>
        <div class="review-list d-flex flex-column gap-3">
            @forelse ($items as $item)
                <article class="p-3 border rounded bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="badge bg-primary me-2">Phòng {{ $item->MaPhong }}</span>
                            <strong class="text-dark">{{ $item->khachHang?->HoTen ?? 'Khách hàng ẩn danh' }}</strong>
                        </div>
                        <div class="text-warning">
                            @for ($s = 1; $s <= 5; $s++)
                                <i class="fa-{{ $s <= $item->SoSao ? 'solid' : 'regular' }} fa-star"></i>
                            @endfor
                            <small class="text-muted ms-1">({{ $item->SoSao }}/5)</small>
                        </div>
                    </div>
                    <p class="mb-1 text-secondary" style="font-size: 0.95rem;">{!! nl2br(e($item->NoiDung)) !!}</p>
                    <small class="text-muted">
                        <i class="fa-regular fa-clock me-1"></i>{{ format_date($item->NgayDanhGia) }}
                    </small>
                </article>
            @empty
                <p class="text-muted text-center py-4">Chưa có đánh giá nào. Hãy là người đầu tiên trải nghiệm và chia sẻ nhé!</p>
            @endforelse
        </div>
    </div>
</main>
@endsection
