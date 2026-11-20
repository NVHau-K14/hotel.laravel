@extends('admin.layouts.app')

@section('title', 'Quản lý loại phòng')

@section('content')
<div class="container admin-wide">
    <div class="page-head">
        <div>
            <h1>Quản lý loại phòng</h1>
            <p>Loại phòng quyết định giá và mô tả hiển thị cho khách.</p>
        </div>
        <a href="{{ route('admin.loai-phong.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Thêm loại phòng
        </a>
    </div>

    <div class="admin-panel p-4">
        <div class="table-responsive">
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
                        <td class="fw-semibold">{{ $item->TenLoaiPhong }}</td>
                        <td class="fw-semibold text-nowrap">{{ format_currency($item->GiaPhong) }}</td>
                        <td class="text-muted">{{ $item->MoTa ?? 'Chưa có mô tả' }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.loai-phong.edit', $item) }}">
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

    @if ($daXoa->isNotEmpty())
        <div class="admin-panel p-4 mt-4">
            <h5 class="fw-bold mb-1">Loại phòng đã ngừng sử dụng</h5>
            <p class="text-muted small mb-3">Các phòng thuộc những loại này không hiện ở trang khách và không nhận đặt mới.</p>
            <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <tbody>
                    @foreach ($daXoa as $item)
                        <tr>
                            <td style="width: 80px;"><span class="id-badge">{{ $item->MaLoaiPhong }}</span></td>
                            <td class="fw-bold">{{ $item->TenLoaiPhong }}</td>
                            <td>{{ format_currency($item->GiaPhong) }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.loai-phong.khoi-phuc', $item) }}" class="action-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="fa-solid fa-rotate-left me-1"></i>Khôi phục
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif
</div>
@endsection
