{{-- Dùng cho cả danh sách và "Hôm nay" (homNay) --}}
@extends('admin.layouts.app')

@section('title', $tieuDe)

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Quản lý phân công công việc</h1>
            <p class="text-muted mb-0">Lịch làm việc và phân công nhiệm vụ cho nhân viên khách sạn</p>
        </div>
        <a href="{{ route('admin.phan-cong.create') }}" class="btn btn-success">
            <i class="fa-solid fa-plus me-1"></i> Thêm phân công
        </a>
    </div>

    <!-- Bộ lọc tìm kiếm -->
    <div class="card admin-panel border-0 shadow-sm p-3 mb-4">
        <form method="GET" action="{{ route('admin.phan-cong.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm theo loại công việc..." value="{{ $keyword }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="ngay" class="form-control" value="{{ $ngay }}">
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Tìm kiếm
                </button>
                <a href="{{ route('admin.phan-cong.index') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate-left me-1"></i> Tất cả
                </a>
                <a href="{{ route('admin.phan-cong.hom-nay') }}" class="btn btn-outline-info">
                    <i class="fa-solid fa-calendar-day me-1"></i> Hôm nay
                </a>
            </div>
        </form>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 100px;">Mã PC</th>
                    <th style="width: 150px;">Ngày phân công</th>
                    <th>Loại công việc</th>
                    <th>Nhân viên thực hiện</th>
                    <th>Chức vụ</th>
                    <th class="text-end" style="width: 160px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td><span class="id-badge">#{{ $item->MaPhanCong }}</span></td>
                        <td>
                            <span class="fw-semibold text-primary">
                                <i class="fa-regular fa-calendar me-1"></i> {{ format_date($item->NgayPhanCong) }}
                            </span>
                        </td>
                        <td class="fw-bold fs-6">{{ $item->LoaiCongViec }}</td>
                        <td>
                            @if (! empty($item->nhanVien?->TenNhanVien))
                                <div class="fw-semibold text-dark">{{ $item->nhanVien->TenNhanVien }}</div>
                                <small class="text-muted">({{ '@'.$item->nhanVien->Username }})</small>
                            @else
                                <span class="text-muted fst-italic">Chưa chỉ định</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $item->nhanVien?->chucVu?->TenChucVu ?? '-' }}</span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('admin.phan-cong.edit', $item) }}">
                                <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                            </a>
                            <form method="POST" action="{{ route('admin.phan-cong.destroy', $item) }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn xóa phân công này?')">
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
                        <td colspan="6" class="text-center py-4 text-muted">Chưa có phân công nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
