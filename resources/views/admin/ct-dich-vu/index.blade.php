@extends('admin.layouts.app')

@section('title', 'Quản lý chi tiết sử dụng dịch vụ')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Chi tiết sử dụng dịch vụ</h1>
            <p class="text-muted mb-0">Theo dõi tất cả dịch vụ được khách hàng sử dụng theo từng đơn đặt phòng</p>
        </div>
        <a href="{{ route('admin.ct-dich-vu.create') }}" class="btn btn-success">
            <i class="fa-solid fa-plus me-1"></i> Thêm chi tiết dịch vụ
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 100px;">Mã CTDV</th>
                    <th>Dịch vụ</th>
                    <th>Mã đặt phòng</th>
                    <th>Phòng</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Tổng tiền</th>
                    <th class="text-end" style="width: 120px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td><span class="id-badge">#{{ $item->MaCTDichVu ?? '-' }}</span></td>
                        <td class="fw-bold text-dark fs-6">{{ $item->TenDichVu }}</td>
                        <td>
                            <a href="{{ route('admin.dat-phong.show', $item->MaCTDatPhong) }}" class="fw-semibold text-primary">
                                #{{ $item->MaCTDatPhong }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                Phòng {{ $item->MaPhong ?? '-' }}
                            </span>
                        </td>
                        <td><span class="badge bg-secondary">{{ $item->SoLuong }}</span></td>
                        <td class="text-muted">{{ format_currency($item->GiaDichVu) }}</td>
                        <td class="text-primary fw-bold fs-6">{{ format_currency($item->TongTienDichVu) }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.ct-dich-vu.destroy', [$item->MaCTDatPhong, $item->MaDichVu]) }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn xóa dịch vụ này khỏi đơn?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa-solid fa-trash me-1"></i>Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Chưa có chi tiết dịch vụ nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
