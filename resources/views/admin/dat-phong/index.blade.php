@extends('admin.layouts.app')

@section('title', 'Quản lý Đặt phòng')

@section('content')
@php
    // Nhãn + màu của trạng thái đơn
    $nhanTrangThai = [
        'CHO_ADMIN_DUYET' => ['badge bg-warning text-dark', 'Chờ duyệt'],
        'DA_XAC_NHAN' => ['badge bg-info text-white', 'Đã xác nhận'],
        'DANG_SU_DUNG' => ['badge bg-primary', 'Đang ở'],
        'DA_HOAN_THANH' => ['badge bg-success', 'Hoàn thành'],
        'DA_HUY' => ['badge bg-danger', 'Đã hủy'],
    ];
@endphp
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">Quản lý đặt phòng</h1>
        <a href="{{ route('admin.dat-phong.create') }}" class="btn btn-success">
            <i class="fa-solid fa-plus me-1"></i> Tạo đơn đặt phòng
        </a>
    </div>

    <!-- FILTER TABS -->
    <div class="card admin-panel border-0 shadow-sm p-4 mb-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('admin.dat-phong.index') }}" class="btn btn-sm {{ empty($trangThai) ? 'btn-primary' : 'btn-outline-secondary' }}">
                Tất cả ({{ $counts['tongDon'] ?? 0 }})
            </a>
            <a href="{{ route('admin.dat-phong.index', ['trangThai' => 'CHO_ADMIN_DUYET']) }}" class="btn btn-sm {{ $trangThai === 'CHO_ADMIN_DUYET' ? 'btn-warning' : 'btn-outline-warning' }}">
                Chờ duyệt ({{ $counts['choDuyet'] ?? 0 }})
            </a>
            <a href="{{ route('admin.dat-phong.index', ['trangThai' => 'DA_XAC_NHAN']) }}" class="btn btn-sm {{ $trangThai === 'DA_XAC_NHAN' ? 'btn-info text-white' : 'btn-outline-info' }}">
                Đã xác nhận ({{ $counts['daXacNhan'] ?? 0 }})
            </a>
            <a href="{{ route('admin.dat-phong.index', ['trangThai' => 'DANG_SU_DUNG']) }}" class="btn btn-sm {{ $trangThai === 'DANG_SU_DUNG' ? 'btn-primary' : 'btn-outline-primary' }}">
                Đang ở ({{ $counts['dangSuDung'] ?? 0 }})
            </a>
            <a href="{{ route('admin.dat-phong.index', ['trangThai' => 'DA_HOAN_THANH']) }}" class="btn btn-sm {{ $trangThai === 'DA_HOAN_THANH' ? 'btn-success' : 'btn-outline-success' }}">
                Hoàn thành ({{ $counts['daHoanThanh'] ?? 0 }})
            </a>
            <a href="{{ route('admin.dat-phong.index', ['trangThai' => 'DA_HUY']) }}" class="btn btn-sm {{ $trangThai === 'DA_HUY' ? 'btn-danger' : 'btn-outline-danger' }}">
                Đã hủy ({{ $counts['daHuy'] ?? 0 }})
            </a>
        </div>

        <form method="GET" action="{{ route('admin.dat-phong.index') }}" class="d-flex gap-2 mb-3" style="max-width: 400px;">
            @if (! empty($trangThai))
                <input type="hidden" name="trangThai" value="{{ $trangThai }}">
            @endif
            <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Tìm theo tên khách, sđt, phòng..." value="{{ $keyword }}">
            <button type="submit" class="btn btn-sm btn-primary">Tìm</button>
            @if (! empty($keyword))
                <a href="{{ route('admin.dat-phong.index', array_filter(['trangThai' => $trangThai])) }}" class="btn btn-sm btn-outline-secondary">Xóa lọc</a>
            @endif
        </form>

        <!-- BOOKING TABLE -->
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Số điện thoại</th>
                        <th>Phòng</th>
                        <th>Thời gian lưu trú</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php([$label, $text] = $nhanTrangThai[$item->TrangThai] ?? ['badge bg-secondary', $item->TrangThai])
                        <tr>
                            <td><span class="id-badge">#{{ $item->MaCTDatPhong }}</span></td>
                            <td class="fw-semibold">{{ $item->khachHang?->HoTen ?? 'Khách vãng lai' }}</td>
                            <td>{{ $item->khachHang?->DienThoai ?? '' }}</td>
                            <td>
                                <strong>Phòng {{ $item->MaPhong }}</strong>
                                <div class="small text-muted">{{ $item->phong?->loaiPhong?->TenLoaiPhong ?? '' }}</div>
                            </td>
                            <td>
                                <div class="small">
                                    <i class="fa-regular fa-calendar-check me-1 text-success"></i>{{ format_date($item->NgayNhan) }}
                                    <br>
                                    <i class="fa-regular fa-calendar-xmark me-1 text-danger"></i>{{ format_date($item->NgayTra) }}
                                </div>
                            </td>
                            <td>
                                <span class="{{ $label }}">{{ $text }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.dat-phong.show', $item) }}" class="btn btn-sm btn-outline-primary">
                                    Chi tiết & Xử lý
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy đơn đặt phòng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
