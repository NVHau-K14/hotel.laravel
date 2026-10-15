@extends('client.layouts.app')

@section('title', 'Đặt phòng thành công')

@section('content')
<main class="container my-5 text-center" style="max-width: 650px;">
    <div class="card border-0 shadow-sm p-5" style="border-radius: 16px;">
        <div class="mb-4 text-success">
            <i class="fa-solid fa-circle-check fa-4x"></i>
        </div>
        <h2 class="fw-bold mb-2">Đặt phòng thành công!</h2>
        <p class="text-muted mb-4">Mã đơn đặt phòng của bạn là: <strong class="text-primary fs-4">#{{ $booking->MaCTDatPhong }}</strong></p>

        <div class="card bg-light border-0 p-3 text-start mb-4">
            <p class="mb-1"><strong>Khách hàng:</strong> {{ $booking->khachHang?->HoTen }}</p>
            <p class="mb-1"><strong>Số điện thoại:</strong> {{ $booking->khachHang?->DienThoai }}</p>
            <p class="mb-1"><strong>Phòng:</strong> Phòng {{ $booking->MaPhong }} ({{ $booking->phong?->loaiPhong?->TenLoaiPhong }})</p>
            <p class="mb-1"><strong>Thời gian:</strong> {{ format_date($booking->NgayNhan) }} ➔ {{ format_date($booking->NgayTra) }}</p>
            <p class="mb-0"><strong>Trạng thái:</strong> <span class="badge bg-warning text-dark">Chờ khách sạn duyệt</span></p>
        </div>

        <p class="small text-muted mb-4">
            Nhân viên lễ tân của khách sạn sẽ sớm liên hệ qua số điện thoại để xác nhận và hướng dẫn bạn nhận phòng. Xin cảm ơn quý khách!
        </p>

        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('trang-chu') }}" class="btn btn-outline-primary px-4">Về trang chủ</a>
            <a href="{{ route('phong.index') }}" class="btn btn-primary px-4">Xem thêm phòng khác</a>
        </div>
    </div>
</main>
@endsection
