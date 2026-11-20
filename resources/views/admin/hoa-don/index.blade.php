@extends('admin.layouts.app')

@section('title', 'Quản lý Hóa đơn')

@section('content')
<div class="container admin-wide">
    <div class="page-head">
        <div>
            <h1>Quản lý hóa đơn</h1>
            <p>Hóa đơn được lập khi trả phòng; mở từng hóa đơn để xác nhận thanh toán hoặc in.</p>
        </div>
    </div>

    <div class="admin-panel p-4">
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>Mã HD</th>
                        <th>Khách hàng</th>
                        <th>Phòng</th>
                        <th>Thời gian</th>
                        <th>Tổng tiền thu</th>
                        <th>Trạng thái</th>
                        <th>Thu ngân</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php($datPhong = $item->datPhong)
                        <tr>
                            <td><span class="id-badge">#{{ $item->MaHoaDon }}</span></td>
                            <td class="fw-semibold">
                                {{ $datPhong?->khachHang?->HoTen }}
                                <div class="small text-muted">{{ $datPhong?->khachHang?->DienThoai }}</div>
                            </td>
                            <td>
                                <span class="door-plate me-2" aria-hidden="true">{{ $datPhong?->MaPhong }}</span><span class="visually-hidden">Phòng {{ $datPhong?->MaPhong }}</span>
                                <div class="small text-muted">{{ $datPhong?->phong?->loaiPhong?->TenLoaiPhong }}</div>
                            </td>
                            <td>
                                <small>Ngày lập: {{ format_date($item->NgayThuTien) }}</small>
                            </td>
                            <td class="fw-semibold text-nowrap">
                                {{ format_currency($item->SoTienThu) }}
                            </td>
                            <td>
                                @if ($item->daThanhToan())
                                    <span class="badge bg-success">Đã thanh toán</span>
                                @elseif ($item->daHuy())
                                    <span class="badge bg-secondary">Đã hủy</span>
                                @else
                                    <span class="badge bg-warning text-dark">Chưa thanh toán</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $item->nhanVien?->TenNhanVien ?? 'Hệ thống' }}</small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.hoa-don.show', $item) }}" class="btn btn-sm btn-outline-primary">
                                    Xem hóa đơn
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Chưa có hóa đơn nào trên hệ thống.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
