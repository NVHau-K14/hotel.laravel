@extends('client.layouts.app')

@section('title', 'Lịch sử đặt phòng của tôi')

@php
    // Nhãn + màu badge theo trạng thái đơn
    $nhanTrangThai = [
        'CHO_ADMIN_DUYET' => ['bg-warning text-dark', 'Chờ duyệt'],
        'DA_XAC_NHAN' => ['bg-info text-white', 'Đã xác nhận'],
        'DANG_SU_DUNG' => ['bg-primary', 'Đang ở'],
        'DA_HOAN_THANH' => ['bg-success', 'Hoàn tất'],
        'DA_HUY' => ['bg-danger', 'Đã hủy'],
    ];
@endphp

@section('content')
<main class="container my-4" style="max-width: 950px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Lịch sử đặt phòng của bạn</h2>
            <p class="text-muted mb-0">Quản lý và theo dõi các đơn đặt phòng của bạn tại khách sạn</p>
        </div>
        <a href="{{ route('phong.index') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Đặt thêm phòng
        </a>
    </div>

    <div class="card border-0 shadow-sm p-4" style="border-radius: 12px;">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mã đơn</th>
                        <th>Phòng</th>
                        <th>Thời gian lưu trú</th>
                        <th>Giá niêm yết</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $b)
                        @php([$badgeClass, $badgeText] = $nhanTrangThai[$b->TrangThai] ?? ['bg-secondary', $b->TrangThai])
                        <tr>
                            <td><span class="badge bg-secondary fs-6">#{{ $b->MaCTDatPhong }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if (! empty($b->phong?->HinhAnhDaiDien))
                                        <img src="{{ asset($b->phong->HinhAnhDaiDien) }}" alt="Phong" style="width:50px; height:38px; object-fit:cover; border-radius:4px;">
                                    @endif
                                    <div>
                                        <strong>Phòng {{ $b->MaPhong }}</strong>
                                        <div class="small text-muted">{{ $b->phong?->loaiPhong?->TenLoaiPhong }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <i class="fa-regular fa-calendar-check text-success me-1"></i>{{ format_date($b->NgayNhan) }}
                                    <br>
                                    <i class="fa-regular fa-calendar-xmark text-danger me-1"></i>{{ format_date($b->NgayTra) }}
                                </div>
                            </td>
                            <td class="fw-bold text-primary">
                                {{ format_currency($b->GiaPhong) }} / đêm
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('tai-khoan.chi-tiet', $b) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-eye me-1"></i>Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            {{-- Sửa lỗi L3: bảng có 6 cột (bản gốc ghi colspan="5") --}}
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-calendar-xmark fa-3x text-muted mb-3 d-block"></i>
                                Bạn chưa có đơn đặt phòng nào.
                                <div class="mt-2">
                                    <a href="{{ route('phong.index') }}" class="btn btn-sm btn-outline-primary">Xem danh sách phòng</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
