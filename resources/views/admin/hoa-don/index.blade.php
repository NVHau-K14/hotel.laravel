@extends('admin.layouts.app')

@section('title', 'Quản lý Hóa đơn')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">Quản lý hóa đơn</h1>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4">
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
                                <strong>Phòng {{ $datPhong?->MaPhong }}</strong>
                                <div class="small text-muted">{{ $datPhong?->phong?->loaiPhong?->TenLoaiPhong }}</div>
                            </td>
                            <td>
                                <small>Ngày lập: {{ format_date($item->NgayThuTien) }}</small>
                            </td>
                            <td class="text-danger fw-bold fs-6">
                                {{ format_currency($item->SoTienThu) }}
                            </td>
                            <td>
                                @if ($item->daThanhToan())
                                    <span class="badge bg-success">Đã thanh toán</span>
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
