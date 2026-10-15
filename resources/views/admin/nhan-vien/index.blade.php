@extends('admin.layouts.app')

@section('title', 'Quản lý Nhân viên')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">Quản lý nhân viên</h1>
        <a href="{{ route('admin.nhan-vien.create') }}" class="btn btn-success">
            <i class="fa-solid fa-user-plus me-1"></i> Thêm nhân viên
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4">
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>Mã NV</th>
                        <th>Tài khoản</th>
                        <th>Họ và tên</th>
                        <th>Chức vụ</th>
                        <th>Ngày sinh</th>
                        <th>CMND</th>
                        <th>Ngày vào làm</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td><span class="id-badge">{{ $item->MaNhanVien }}</span></td>
                            <td class="fw-bold">{{ $item->Username }}</td>
                            <td>{{ $item->TenNhanVien }}</td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $item->chucVu?->TenChucVu ?? 'Chưa gán' }}</span>
                            </td>
                            <td>{{ format_date($item->NgaySinh) }}</td>
                            <td>{{ $item->CMND ?? '' }}</td>
                            <td>{{ format_date($item->NgayVaoLam) }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('admin.nhan-vien.edit', $item) }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                                </a>
                                <form method="POST" action="{{ route('admin.nhan-vien.destroy', $item) }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn xóa nhân viên này?')">
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
                            <td colspan="8" class="text-center py-4 text-muted">Chưa có nhân viên nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
