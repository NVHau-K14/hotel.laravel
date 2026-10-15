@extends('admin.layouts.app')

@section('title', 'Quản lý Khách hàng')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">Quản lý khách hàng</h1>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.khach-hang.index') }}" class="d-flex gap-2 mb-3" style="max-width: 400px;">
            <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Tìm theo tên, email, SĐT, CCCD..." value="{{ $keyword }}">
            <button type="submit" class="btn btn-sm btn-primary">Tìm</button>
            @if (! empty($keyword))
                <a href="{{ route('admin.khach-hang.index') }}" class="btn btn-sm btn-outline-secondary">Xóa</a>
            @endif
        </form>

        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Họ và tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>CCCD/CMND</th>
                        <th>Địa chỉ</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td><span class="id-badge">{{ $item->MaKhachHang }}</span></td>
                            <td class="fw-bold">{{ $item->HoTen }}</td>
                            <td>{{ $item->Email }}</td>
                            <td>{{ $item->DienThoai }}</td>
                            <td>{{ $item->CMND ?? 'Chưa có' }}</td>
                            <td>{{ $item->DiaChi ?? 'Chưa có' }}</td>
                            <td>
                                @if ($item->biKhoa())
                                    <span class="badge bg-danger">Đã khóa</span>
                                @else
                                    <span class="badge bg-success">Hoạt động</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.khach-hang.lich-su', $item) }}" class="btn btn-sm btn-outline-info me-1">
                                    Lịch sử
                                </a>
                                <form method="POST" action="{{ route('admin.khach-hang.doi-trang-thai', $item) }}" class="action-form" onsubmit="return confirm('Xác nhận đổi trạng thái tài khoản này?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $item->biKhoa() ? 'btn-outline-success' : 'btn-outline-warning' }}">
                                        {{ $item->biKhoa() ? 'Mở khóa' : 'Khóa' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Không có khách hàng nào phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
