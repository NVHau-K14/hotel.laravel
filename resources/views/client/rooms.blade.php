@extends('client.layouts.app')

@section('title', 'Danh sách phòng nghỉ')

@section('content')
<main class="container my-4">
    <div class="mb-4 text-center">
        <h1 class="fw-bold">Danh sách phòng nghỉ</h1>
        <p class="text-muted">Chọn phòng phù hợp nhất cho chuyến đi của bạn</p>
    </div>

    <!-- FILTER BAR -->
    <div class="card p-4 border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <form method="GET" action="{{ route('phong.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Loại phòng</label>
                <select name="maLoaiPhong" class="form-select form-select-sm">
                    <option value="">Tất cả loại phòng</option>
                    @foreach ($loaiPhongs as $lp)
                        <option value="{{ $lp->MaLoaiPhong }}" @selected($maLoaiPhong == $lp->MaLoaiPhong)>
                            {{ $lp->TenLoaiPhong }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold">Giá từ</label>
                <input type="number" name="giaTu" class="form-control form-control-sm" placeholder="Từ" value="{{ $giaTu }}">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold">Giá đến</label>
                <input type="number" name="giaDen" class="form-control form-control-sm" placeholder="Đến" value="{{ $giaDen }}">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold">Ngày nhận</label>
                <input type="date" name="ngayNhan" class="form-control form-control-sm" value="{{ $ngayNhan }}">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold">Ngày trả</label>
                <input type="date" name="ngayTra" class="form-control form-control-sm" value="{{ $ngayTra }}">
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-sm btn-primary w-100 py-1">Tìm</button>
            </div>
        </form>
    </div>

    @if ($dateError)
        <div class="alert alert-danger">{{ $dateError }}</div>
    @endif

    <!-- ROOM GRID -->
    <div class="room-card-grid">
        @forelse ($items as $p)
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
                    <p class="text-muted">{{ $p->loaiPhong?->TenLoaiPhong }}</p>
                    <strong class="text-primary fs-5">{{ format_currency($p->loaiPhong?->GiaPhong) }} / đêm</strong>
                    <div class="card-actions mt-3">
                        <a class="btn small" href="{{ route('phong.show', $p) }}">Chi tiết</a>
                        @if ($p->TrangThai !== \App\Models\Phong::BAO_TRI)
                            {{-- Mang theo khoảng ngày đang lọc sang trang đặt phòng --}}
                            <a class="btn small secondary" href="{{ route('dat-phong.create', array_filter(['phong' => $p->MaPhong, 'ngayNhan' => $ngayNhan, 'ngayTra' => $ngayTra])) }}">
                                Đặt ngay
                            </a>
                        @else
                            <span class="btn small secondary disabled" style="opacity: 0.6;">Bảo trì</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="text-center py-5 w-100">
                <i class="fa-solid fa-hotel fa-3x text-muted mb-3 d-block"></i>
                <h5>Không tìm thấy phòng nào phù hợp với yêu cầu tìm kiếm.</h5>
                <p class="text-muted">Vui lòng thử chọn khoảng ngày khác hoặc tìm kiếm với mức giá rộng hơn.</p>
                <a href="{{ route('phong.index') }}" class="btn btn-outline-primary">Xem tất cả phòng</a>
            </div>
        @endforelse
    </div>
</main>
@endsection
