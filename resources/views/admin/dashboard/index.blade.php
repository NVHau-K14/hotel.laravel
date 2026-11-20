@extends('admin.layouts.app')

@section('title', 'Bảng điều khiển - Quản trị Khách sạn')

@php
    $tongPhong = max(1, $phongStats['tongPhong']);
    $tyLe = fn (int $so) => round($so * 100 / $tongPhong, 1);
    $soDonCho = $donChoDuyet->count();
@endphp

@section('content')
<div class="container admin-wide">
    <div class="page-head">
        <div>
            <h1>Chào {{ auth('admin')->user()->TenNhanVien ?? 'bạn' }}</h1>
            <p>
                @if ($soDonCho)
                    Có {{ $soDonCho }} đơn đặt phòng đang chờ bạn duyệt.
                @else
                    Không có đơn nào đang chờ duyệt.
                @endif
            </p>
        </div>
        @quyen('DAT_PHONG')
        <div class="page-head-actions">
            <a href="{{ route('admin.dat-phong.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus" aria-hidden="true"></i>Tạo đơn tại quầy
            </a>
        </div>
        @endquyen
    </div>

    {{-- Số liệu chính --}}
    <section class="kpi-strip mb-4" aria-label="Số liệu chính">
        <div class="kpi kpi-lead">
            <span class="kpi-label"><i class="fa-solid fa-coins" aria-hidden="true"></i>Doanh thu tháng {{ now()->month }}</span>
            <span class="kpi-value is-money">{{ format_currency($summary['doanhThuThangNay']) }}</span>
            <div class="kpi-hint">Tổng đã thu: {{ format_currency($summary['tongDoanhThu']) }}</div>
        </div>
        <div class="kpi">
            <span class="kpi-label"><i class="fa-solid fa-door-open" aria-hidden="true"></i>Phòng trống</span>
            <span class="kpi-value">{{ $phongStats['phongTrong'] }}</span>
            <div class="kpi-hint">trên tổng {{ $phongStats['tongPhong'] }} phòng</div>
        </div>
        <div class="kpi">
            <span class="kpi-label"><i class="fa-solid fa-bed" aria-hidden="true"></i>Đang thuê / đã đặt</span>
            <span class="kpi-value">{{ $phongStats['phongDangThue'] }}</span>
            <div class="kpi-hint">{{ $tyLe($phongStats['phongDangThue']) }}% công suất</div>
        </div>
        <div class="kpi">
            <span class="kpi-label"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>Đơn chờ duyệt</span>
            <span class="kpi-value">{{ $soDonCho }}</span>
            <div class="kpi-hint">{{ $phongStats['phongBaoTri'] }} phòng đang bảo trì</div>
        </div>
    </section>

    {{-- Công suất phòng --}}
    <section class="admin-panel p-4 mb-4" aria-labelledby="cong-suat">
        <div class="d-flex flex-wrap justify-content-between align-items-baseline gap-2">
            <h2 class="panel-title" id="cong-suat">Tình trạng {{ $phongStats['tongPhong'] }} phòng</h2>
            @quyen('PHONG')
                <a class="small" href="{{ route('admin.phong.index') }}">Mở sơ đồ phòng</a>
            @endquyen
        </div>
        <div class="occupancy-bar" role="img"
             aria-label="Trống {{ $phongStats['phongTrong'] }}, đang thuê hoặc đã đặt {{ $phongStats['phongDangThue'] }}, bảo trì {{ $phongStats['phongBaoTri'] }}">
            <span style="width: {{ $tyLe($phongStats['phongDangThue']) }}%; background: var(--jade);"></span>
            <span style="width: {{ $tyLe($phongStats['phongTrong']) }}%; background: #b9cfc6;"></span>
            <span style="width: {{ $tyLe($phongStats['phongBaoTri']) }}%; background: var(--bad);"></span>
        </div>
        <ul class="occupancy-legend">
            <li><i style="background: var(--jade);"></i>Đang thuê / đã đặt: {{ $phongStats['phongDangThue'] }}</li>
            <li><i style="background: #b9cfc6;"></i>Trống: {{ $phongStats['phongTrong'] }}</li>
            <li><i style="background: var(--bad);"></i>Bảo trì: {{ $phongStats['phongBaoTri'] }}</li>
        </ul>
    </section>

    {{-- Đơn chờ duyệt --}}
    <section class="admin-panel" aria-labelledby="don-cho-duyet">
        <div class="panel-head">
            <div>
                <h2 class="panel-title" id="don-cho-duyet">Đơn đặt phòng chờ duyệt ({{ $soDonCho }})</h2>
                <p>Duyệt sớm để giữ phòng cho khách; đơn quá hạn nhận phòng sẽ tự hủy.</p>
            </div>
            <a href="{{ route('admin.dat-phong.index') }}" class="btn btn-sm btn-outline-secondary">Xem tất cả đơn</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Mã đơn</th>
                        <th scope="col">Phòng</th>
                        <th scope="col">Khách hàng</th>
                        <th scope="col">Lưu trú</th>
                        <th scope="col" class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($donChoDuyet as $d)
                        <tr>
                            <td><span class="code-badge">#{{ $d->MaCTDatPhong }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="door-plate">{{ $d->MaPhong }}</span>
                                    <span class="text-muted small">{{ $d->phong?->loaiPhong?->TenLoaiPhong }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $d->khachHang?->HoTen }}</div>
                                <div class="small text-muted">{{ $d->khachHang?->DienThoai }}</div>
                            </td>
                            <td class="text-nowrap">{{ format_date($d->NgayNhan) }} – {{ format_date($d->NgayTra) }}</td>
                            <td class="text-end table-actions">
                                <a href="{{ route('admin.dat-phong.show', $d) }}" class="btn btn-sm btn-outline-secondary">Chi tiết</a>
                                <form method="POST" action="{{ route('admin.dat-phong.duyet', $d) }}" class="action-form" onsubmit="return confirm('Xác nhận duyệt đơn đặt phòng này?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fa-solid fa-check" aria-hidden="true"></i>Duyệt
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fa-solid fa-circle-check fa-2x mb-2 d-block" aria-hidden="true"></i>
                                Không có đơn nào chờ duyệt. Đơn mới từ khách sẽ hiện ở đây.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
