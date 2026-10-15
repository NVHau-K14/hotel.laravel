@extends('admin.layouts.app')

@section('title', "Lịch sử đặt phòng - {$customer->HoTen}")

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-0">Lịch sử đặt phòng: {{ $customer->HoTen }}</h1>
            <p class="text-muted mb-0">Email: {{ $customer->Email }} | SĐT: {{ $customer->DienThoai }}</p>
        </div>
        <a href="{{ route('admin.khach-hang.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Phòng</th>
                    <th>Ngày nhận</th>
                    <th>Ngày trả</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $b)
                    <tr>
                        <td><span class="id-badge">#{{ $b->MaCTDatPhong }}</span></td>
                        <td>Phòng {{ $b->MaPhong }} ({{ $b->phong?->loaiPhong?->TenLoaiPhong }})</td>
                        <td>{{ format_date($b->NgayNhan) }}</td>
                        <td>{{ format_date($b->NgayTra) }}</td>
                        {{-- L4: giữ nguyên như bản gốc, hiện mã trạng thái --}}
                        <td><span class="badge bg-secondary">{{ $b->TrangThai }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.dat-phong.show', $b->MaCTDatPhong) }}" class="btn btn-sm btn-outline-primary">
                                Xem đơn
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Khách hàng chưa có đơn đặt phòng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
