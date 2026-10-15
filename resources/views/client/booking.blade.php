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
        <p class="text-muted text-center mb-4">Điền thông tin của bạn để khách sạn giữ phòng và liên hệ xác nhận</p>

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

        <form method="POST" action="{{ route('dat-phong.store', $room) }}">
            @csrf

            <h5 class="fw-bold text-primary mb-3">1. Thời gian lưu trú</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngày nhận phòng <span class="text-danger">*</span></label>
                    <input type="date" name="ngayNhan" class="form-control" value="{{ old('ngayNhan', $ngayNhan) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngày trả phòng <span class="text-danger">*</span></label>
                    <input type="date" name="ngayTra" class="form-control" value="{{ old('ngayTra', $ngayTra) }}" required>
                </div>
            </div>

            {{-- Khách đã đăng nhập: điền sẵn thông tin tài khoản --}}
            <h5 class="fw-bold text-primary mb-3">2. Thông tin khách đặt</h5>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="hoTen" class="form-control" placeholder="Nguyễn Văn A"
                           value="{{ old('hoTen', $clientUser?->HoTen) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="tel" name="dienThoai" class="form-control" placeholder="0901234567"
                           value="{{ old('dienThoai', $clientUser?->DienThoai) }}" required>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com"
                           value="{{ old('email', $clientUser?->Email) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">CMND / Căn cước công dân</label>
                    <input type="text" name="cmnd" class="form-control" placeholder="Số CCCD"
                           value="{{ old('cmnd', $clientUser?->CMND) }}">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Địa chỉ</label>
                <input type="text" name="diaChi" class="form-control" placeholder="Thành phố / Tỉnh"
                       value="{{ old('diaChi', $clientUser?->DiaChi) }}">
            </div>

            <button type="submit" class="btn btn-primary booking-submit w-100 py-3 fs-5 fw-bold shadow-sm">
                <i class="fa-solid fa-check-circle" aria-hidden="true"></i>
                <span>Xác nhận gửi yêu cầu đặt phòng</span>
            </button>
        </form>
    </div>
</main>
@endsection
