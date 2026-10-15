@extends('admin.layouts.app')

@section('title', 'Quản lý dịch vụ')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">Quản lý dịch vụ</h1>
        <a href="{{ route('admin.dich-vu.create') }}" class="btn btn-success">
            <i class="fa-solid fa-plus me-1"></i> Thêm dịch vụ
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 80px;">Mã</th>
                    <th style="width: 100px;">Hình ảnh</th>
                    <th>Tên dịch vụ</th>
                    <th>Giá dịch vụ (VNĐ)</th>
                    <th class="text-end" style="width: 160px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td><span class="id-badge">{{ $item->MaDichVu }}</span></td>
                        <td>
                            @if (! empty($item->HinhAnh))
                                <img src="{{ asset($item->HinhAnh) }}" alt="Dich vu" style="width: 60px; height: 45px; object-fit: cover; border-radius: 6px;">
                            @else
                                <span class="text-muted small">Không có ảnh</span>
                            @endif
                        </td>
                        <td class="fw-bold fs-6">{{ $item->TenDichVu }}</td>
                        <td class="text-primary fw-bold">{{ format_currency($item->GiaDichVu) }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('admin.dich-vu.edit', $item) }}">
                                <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                            </a>
                            <form method="POST" action="{{ route('admin.dich-vu.destroy', $item) }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn xóa dịch vụ này?')">
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
                        <td colspan="5" class="text-center py-4 text-muted">Chưa có dịch vụ nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
