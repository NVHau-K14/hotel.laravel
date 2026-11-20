@extends('client.layouts.app')

@section('title', 'Danh sách phòng nghỉ')

@section('content')
<main class="wrap pb-5">
    <header class="page-intro">
        <h1>Phòng nghỉ</h1>
        <p>Chọn ngày nhận và trả phòng để chỉ xem những phòng còn trống trong khoảng thời gian đó.</p>
    </header>

    <div class="filter-bar">
        <form method="GET" action="{{ route('phong.index') }}">
            <div>
                <label class="form-label" for="loc-loai-phong">Loại phòng</label>
                <select id="loc-loai-phong" name="maLoaiPhong" class="form-select">
                    <option value="">Tất cả loại phòng</option>
                    @foreach ($loaiPhongs as $lp)
                        <option value="{{ $lp->MaLoaiPhong }}" @selected($maLoaiPhong == $lp->MaLoaiPhong)>{{ $lp->TenLoaiPhong }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="loc-khoang-gia">Giá mỗi đêm</label>
                <select id="loc-khoang-gia" name="khoangGia" class="form-select">
                    <option value="">Mọi mức giá</option>
                    @foreach (\App\Models\Phong::KHOANG_GIA as $ma => [$nhan])
                        <option value="{{ $ma }}" @selected($khoangGia === $ma)>{{ $nhan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="loc-ngay-nhan">Ngày nhận</label>
                <input id="loc-ngay-nhan" type="date" name="ngayNhan" class="form-control" value="{{ $ngayNhan }}" min="{{ today()->toDateString() }}">
            </div>
            <div>
                <label class="form-label" for="loc-ngay-tra">Ngày trả</label>
                <input id="loc-ngay-tra" type="date" name="ngayTra" class="form-control" value="{{ $ngayTra }}" min="{{ today()->addDay()->toDateString() }}">
            </div>
            <button type="submit" class="btn btn-primary">Lọc phòng</button>
        </form>
    </div>

    @if ($dateError)
        <div class="alert alert-danger mt-3" role="alert">{{ $dateError }}</div>
    @endif

    <p class="result-count" role="status">
        {{ $items->count() }} phòng
        @if ($ngayNhan && $ngayTra)
            còn trống từ {{ format_date($ngayNhan) }} đến {{ format_date($ngayTra) }}
        @endif
    </p>

    @if ($items->isEmpty())
        <div class="empty-state">
            <h2>Không có phòng phù hợp</h2>
            <p>Thử chọn khoảng ngày khác hoặc bỏ bớt điều kiện lọc giá và loại phòng.</p>
            <a href="{{ route('phong.index') }}" class="btn btn-outline-primary">Xem tất cả phòng</a>
        </div>
    @else
        <h2 class="visually-hidden">Kết quả</h2>
        <div class="room-grid">
            @foreach ($items as $p)
                @include('client.partials.room-card', ['p' => $p, 'ngayNhan' => $ngayNhan, 'ngayTra' => $ngayTra])
            @endforeach
        </div>
    @endif
</main>
@endsection
