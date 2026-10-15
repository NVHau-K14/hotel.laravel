@extends('admin.layouts.app')

@section('title', 'Quản lý loại phòng')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">Quản lý loại phòng</h1>
        <a href="{{ route('admin.loai-phong.create') }}" class="btn btn-success">
            <i class="fa-solid fa-plus me-1"></i> Thêm loại phòng
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 80px;">Mã</th>
                    <th>Tên loại phòng</th>
                    <th>Giá niêm yết (VNĐ/ngày)</th>
                    <th>Mô tả tiện nghi</th>
                    <th class="text-end" style="width: 160px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td><span class="id-badge">{{ $item->MaLoaiPhong }}</span></td>
                        <td class="fw-bold fs-6">{{ $item->TenLoaiPhong }}</td>
                        <td class="text-primary fw-bold">{{ format_currency($item->GiaPhong) }}</td>
                        <td class="text-muted">{{ $item->MoTa ?? 'Chưa có mô tả' }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('admin.loai-phong.edit', $item) }}">
                                <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                            </a>
                            <form method="POST" action="{{ route('admin.loai-phong.destroy', $item) }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn xóa loại phòng này?')">
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
                        <td colspan="5" class="text-center py-4 text-muted">Chưa có loại phòng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
