@extends('client.layouts.app')

@section('title', 'Đặt phòng thành công')

@section('content')
<main class="wrap">
    <div class="confirm-sheet">
        <span class="confirm-mark" aria-hidden="true"><i class="fa-solid fa-check"></i></span>
        <h1>Đã gửi yêu cầu đặt phòng</h1>
        <p class="text-muted mb-1">Mã đơn của bạn</p>
        <strong class="confirm-code">#{{ $booking->MaCTDatPhong }}</strong>

        <dl class="summary-list">
            <div><dt>Khách hàng</dt><dd>{{ $booking->khachHang?->HoTen }}</dd></div>
            <div><dt>Số điện thoại</dt><dd>{{ $booking->khachHang?->DienThoai }}</dd></div>
            <div><dt>Phòng</dt><dd>Phòng {{ $booking->MaPhong }} ({{ $booking->phong?->loaiPhong?->TenLoaiPhong }})</dd></div>
            <div><dt>Lưu trú</dt><dd>{{ format_date($booking->NgayNhan) }} – {{ format_date($booking->NgayTra) }}</dd></div>
            <div><dt>Trạng thái</dt><dd><span class="status-badge warning">Chờ khách sạn duyệt</span></dd></div>
        </dl>

        <p class="small text-muted mt-4 mb-4">
            Lễ tân sẽ gọi vào số điện thoại trên để xác nhận và hướng dẫn nhận phòng.
            Bạn có thể theo dõi đơn trong mục lịch sử đặt phòng.
        </p>

        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="{{ route('tai-khoan.lich-su') }}" class="btn btn-primary">Xem lịch sử đặt phòng</a>
            <a href="{{ route('phong.index') }}" class="btn btn-outline-secondary">Xem thêm phòng khác</a>
        </div>
    </div>
</main>
@endsection
