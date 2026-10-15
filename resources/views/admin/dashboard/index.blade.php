@extends('admin.layouts.app')

@section('title', 'Bảng điều khiển - Quản trị Khách sạn')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Bảng điều khiển</h1>
            <p class="text-muted mb-0">Xin chào, {{ auth('admin')->user()->TenNhanVien ?? 'Admin' }}! Dưới đây là tổng quan hoạt động khách sạn.</p>
        </div>
        <div>
            <span class="badge bg-primary fs-6 p-2">
                <i class="fa-regular fa-calendar-days me-1"></i> Hôm nay: {{ now()->format('d/m/Y') }}
            </span>
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm bg-success bg-opacity-10 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold">Phòng trống</span>
                        <h2 class="fw-bold text-success mb-0 mt-1">{{ $phongStats['phongTrong'] }}</h2>
                    </div>
                    <i class="fa-solid fa-door-open fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm bg-warning bg-opacity-10 border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold">Đang thuê / Đã đặt</span>
                        <h2 class="fw-bold text-warning mb-0 mt-1">{{ $phongStats['phongDangThue'] }}</h2>
                    </div>
                    <i class="fa-solid fa-bed fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm bg-danger bg-opacity-10 border-start border-danger border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold">Bảo trì</span>
                        <h2 class="fw-bold text-danger mb-0 mt-1">{{ $phongStats['phongBaoTri'] }}</h2>
                    </div>
                    <i class="fa-solid fa-wrench fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 border-0 shadow-sm bg-info bg-opacity-10 border-start border-info border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted fw-semibold">Tổng doanh thu</span>
                        <h3 class="fw-bold text-info mb-0 mt-1">{{ format_currency($summary['tongDoanhThu']) }}</h3>
                    </div>
                    <i class="fa-solid fa-money-bill-wave fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- PENDING BOOKINGS CARD -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-primary">
                <i class="fa-solid fa-clock-rotate-left me-2"></i>Đơn đặt phòng chờ duyệt ({{ $donChoDuyet->count() }})
            </h5>
            <a href="{{ route('admin.dat-phong.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả đơn</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Số điện thoại</th>
                            <th>Phòng</th>
                            <th>Ngày nhận - trả</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($donChoDuyet as $d)
                            <tr>
                                <td><span class="badge bg-secondary">#{{ $d->MaCTDatPhong }}</span></td>
                                <td class="fw-semibold">{{ $d->khachHang?->HoTen }}</td>
                                <td>{{ $d->khachHang?->DienThoai }}</td>
                                <td>
                                    <strong>Phòng {{ $d->MaPhong }}</strong>
                                    <small class="text-muted">({{ $d->phong?->loaiPhong?->TenLoaiPhong }})</small>
                                </td>
                                <td>
                                    <small>{{ format_date($d->NgayNhan) }} ➔ {{ format_date($d->NgayTra) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.dat-phong.show', $d) }}" class="btn btn-sm btn-outline-secondary me-1">Chi tiết</a>
                                    <form method="POST" action="{{ route('admin.dat-phong.duyet', $d) }}" class="action-form" onsubmit="return confirm('Xác nhận duyệt đơn đặt phòng này?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fa-solid fa-check me-1"></i>Duyệt
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-circle-check text-success fa-2x mb-2 d-block"></i>
                                    Hiện không có đơn đặt phòng nào đang chờ duyệt.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
