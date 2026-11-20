@extends('client.layouts.app')

@section('title', "Đặt phòng {$room->MaPhong}")

@section('content')
<main class="container my-4" style="max-width: 800px;">
    <div class="mb-3">
        <a href="{{ route('phong.show', $room) }}" class="text-decoration-none text-muted">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại chi tiết phòng
        </a>
    </div>

    <div class="card border-0 shadow-sm p-4 p-md-5" style="border-radius: 12px;">
        <h2 class="fw-bold mb-1 text-center">Yêu cầu đặt phòng</h2>
        <p class="text-muted text-center mb-4">Chọn ngày lưu trú, khách sạn sẽ liên hệ theo thông tin tài khoản để xác nhận</p>

        @if ($errors->any())
            <div class="alert alert-danger mb-4">{{ $errors->first() }}</div>
        @endif

        <!-- TÓM TẮT PHÒNG -->
        <div class="card bg-light border-0 p-3 mb-4">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1">Phòng số {{ $room->MaPhong }} - {{ $room->loaiPhong?->TenLoaiPhong }}</h5>
                    <span class="text-muted">Đơn giá: <strong>{{ format_currency($room->loaiPhong?->GiaPhong) }}</strong> / đêm</span>
                </div>
                <span class="badge bg-primary fs-6">Phòng {{ $room->MaPhong }}</span>
            </div>
        </div>

        @if (! $room->nhanDatPhong())
            <div class="alert alert-warning mb-0">
                Phòng này đang bảo trì hoặc tạm ngừng nhận khách. Vui lòng
                <a href="{{ route('phong.index') }}">chọn phòng khác</a>.
            </div>
        @else
        <form method="POST" action="{{ route('dat-phong.store', $room) }}">
            @csrf

            <h5 class="fw-bold text-primary mb-3">1. Thời gian lưu trú</h5>
            <div class="row g-3 mb-2">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngày nhận phòng <span class="text-danger">*</span></label>
                    <input type="date" name="ngayNhan" class="form-control" value="{{ old('ngayNhan', $ngayNhan) }}"
                           min="{{ today()->toDateString() }}" max="{{ today()->addDays(\App\Models\DatPhong::SO_NGAY_DAT_TRUOC_TOI_DA)->toDateString() }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngày trả phòng <span class="text-danger">*</span></label>
                    <input type="date" name="ngayTra" class="form-control" value="{{ old('ngayTra', $ngayTra) }}"
                           min="{{ today()->addDay()->toDateString() }}" required>
                </div>
            </div>
            <p class="small text-muted mb-4">
                Đặt trực tuyến tối đa {{ \App\Models\DatPhong::SO_DEM_TOI_DA }} đêm mỗi đơn và
                {{ \App\Models\DatPhong::SO_DON_CHO_DUYET_TOI_DA }} đơn đang chờ duyệt cùng lúc.
            </p>

            {{-- Thông tin liên hệ lấy từ tài khoản, không sửa ở đây (sửa ở trang hồ sơ) --}}
            <h5 class="fw-bold text-primary mb-3">2. Thông tin khách đặt</h5>
            <div class="card border p-3 mb-4">
                <div class="row g-2 small">
                    <div class="col-sm-6"><span class="text-muted">Họ và tên:</span> <strong>{{ $clientUser->HoTen }}</strong></div>
                    <div class="col-sm-6"><span class="text-muted">Số điện thoại:</span> <strong>{{ $clientUser->DienThoai }}</strong></div>
                    <div class="col-sm-6"><span class="text-muted">Email:</span> <strong>{{ $clientUser->Email ?: 'Chưa cập nhật' }}</strong></div>
                    <div class="col-sm-6"><span class="text-muted">CMND/CCCD:</span> <strong>{{ $clientUser->CMND ?: 'Chưa cập nhật' }}</strong></div>
                </div>
                <div class="mt-2 small">
                    Thông tin chưa đúng? <a href="{{ route('tai-khoan.sua') }}">Cập nhật hồ sơ</a> trước khi đặt.
                </div>
            </div>

            <button type="submit" class="btn btn-primary booking-submit w-100 py-3 fs-5 fw-bold shadow-sm">
                <i class="fa-solid fa-check-circle" aria-hidden="true"></i>
                <span>Xác nhận gửi yêu cầu đặt phòng</span>
            </button>
        </form>
        @endif
    </div>
</main>
@endsection
