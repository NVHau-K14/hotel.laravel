@extends('client.layouts.app')

@section('title', 'Lịch sử đặt phòng của tôi')

@php
    // Nhãn + màu nhãn theo trạng thái đơn
    $nhanTrangThai = [
        'CHO_ADMIN_DUYET' => ['warning', 'Chờ duyệt'],
        'DA_XAC_NHAN' => ['info', 'Đã xác nhận'],
        'DANG_SU_DUNG' => ['success', 'Đang ở'],
        'DA_HOAN_THANH' => ['success', 'Hoàn tất'],
        'DA_HUY' => ['danger', 'Đã hủy'],
    ];
@endphp

@section('content')
<main class="wrap pb-5">
    <header class="page-intro d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div>
            <h1>Lịch sử đặt phòng</h1>
            <p>Theo dõi trạng thái các đơn bạn đã gửi.</p>
        </div>
        <a href="{{ route('phong.index') }}" class="btn btn-primary">Đặt thêm phòng</a>
    </header>

    @if ($bookings->isEmpty())
        <div class="empty-state">
            <h2>Bạn chưa có đơn đặt phòng nào</h2>
            <p>Chọn phòng và ngày lưu trú, đơn của bạn sẽ hiện ở đây để theo dõi.</p>
            <a href="{{ route('phong.index') }}" class="btn btn-outline-primary">Xem danh sách phòng</a>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle booking-table">
                    <thead>
                        <tr>
                            <th scope="col">Mã đơn</th>
                            <th scope="col">Phòng</th>
                            <th scope="col">Lưu trú</th>
                            <th scope="col" class="text-end">Giá mỗi đêm</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col"><span class="visually-hidden">Thao tác</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $b)
                            @php([$mauNhan, $chuNhan] = $nhanTrangThai[$b->TrangThai] ?? ['', $b->TrangThai])
                            <tr>
                                <td><span class="code-badge">#{{ $b->MaCTDatPhong }}</span></td>
                                <td>
                                    <div class="room-mini">
                                        @if (! empty($b->phong?->HinhAnhDaiDien))
                                            <img src="{{ asset($b->phong->HinhAnhDaiDien) }}" alt="" loading="lazy" width="64" height="48">
                                        @endif
                                        <div>
                                            <strong>Phòng {{ $b->MaPhong }}</strong>
                                            <small class="d-block">{{ $b->phong?->loaiPhong?->TenLoaiPhong }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-nowrap">{{ format_date($b->NgayNhan) }} – {{ format_date($b->NgayTra) }}</td>
                                <td class="text-end text-nowrap">{{ format_currency($b->GiaPhong) }}</td>
                                <td><span class="status-badge {{ $mauNhan }}">{{ $chuNhan }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('tai-khoan.chi-tiet', $b) }}" class="btn btn-sm btn-outline-secondary">Chi tiết<span class="visually-hidden"> đơn #{{ $b->MaCTDatPhong }}</span></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</main>
@endsection
