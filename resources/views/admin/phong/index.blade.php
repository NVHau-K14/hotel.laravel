@extends('admin.layouts.app')

@section('title', 'Quản lý phòng')

@section('content')
<div class="container admin-wide my-4">
    <h1 class="fw-bold mb-4">Quản lý phòng</h1>

    <div class="card admin-panel border-0 shadow-sm p-4">
        <!-- STATUS GRID -->
        <div class="status-grid three-cols mb-4">
            <div class="status-card success">
                <span>Phòng trống</span>
                <strong>{{ $counts['phongTrong'] ?? 0 }}</strong>
            </div>
            <div class="status-card warning">
                <span>Đang thuê / Đã đặt</span>
                <strong>{{ $counts['phongDangThue'] ?? 0 }}</strong>
            </div>
            <div class="status-card danger-soft">
                <span>Bảo trì</span>
                <strong>{{ $counts['phongBaoTri'] ?? 0 }}</strong>
            </div>
        </div>

        <!-- FILTER TOOLBAR -->
        <div class="admin-toolbar room-toolbar mb-4">
            <form class="toolbar-filter room-filter" method="GET" action="{{ route('admin.phong.index') }}">
                <div class="form-row">
                    <label>Mã phòng</label>
                    <input type="text" name="keyword" placeholder="Nhập mã phòng..." value="{{ $keyword }}">
                </div>

                <div class="form-row">
                    <label>Loại phòng</label>
                    <select name="maLoaiPhong">
                        <option value="">Tất cả loại phòng</option>
                        @foreach ($loaiPhongs as $lp)
                            <option value="{{ $lp->MaLoaiPhong }}" @selected($maLoaiPhong == $lp->MaLoaiPhong)>
                                {{ $lp->TenLoaiPhong }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <label>Giá từ</label>
                    <input type="number" name="giaTu" placeholder="300000" value="{{ $giaTu }}">
                </div>

                <div class="form-row">
                    <label>Giá đến</label>
                    <input type="number" name="giaDen" placeholder="4000000" value="{{ $giaDen }}">
                </div>

                <div class="form-row">
                    <label>Ngày nhận</label>
                    <input type="date" name="ngayNhan" value="{{ $ngayNhan }}">
                </div>

                <div class="form-row">
                    <label>Ngày trả</label>
                    <input type="date" name="ngayTra" value="{{ $ngayTra }}">
                </div>

                <div class="filter-actions">
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Tìm kiếm
                    </button>
                    <a class="btn btn-secondary" href="{{ route('admin.phong.index') }}">
                        <i class="fa-solid fa-rotate-right me-1"></i> Làm mới
                    </a>
                </div>
            </form>

            <a class="btn btn-success" href="{{ route('admin.phong.create') }}">
                <i class="fa-solid fa-plus me-1"></i> Thêm phòng
            </a>
        </div>

        @if ($dateError)
            <div class="alert alert-danger">{{ $dateError }}</div>
        @endif

        <!-- ROOM TABLE -->
        <table class="table admin-table room-table align-middle">
            <thead>
                <tr>
                    <th>Mã phòng</th>
                    <th>Hình ảnh</th>
                    <th>Loại phòng</th>
                    <th>Giá phòng</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php
                        $st = $item->TrangThai ?? 'Trống';
                        $badgeClass = 'success';
                        if ($st === 'Bảo trì') {
                            $badgeClass = 'danger';
                        } elseif (in_array($st, ['Đang thuê', 'Đang sử dụng', 'Đã đặt'])) {
                            $badgeClass = 'warning';
                        }
                    @endphp
                    <tr>
                        <td><span class="id-badge">{{ $item->MaPhong }}</span></td>
                        <td>
                            <div class="image-thumb">
                                @if (! empty($item->HinhAnhDaiDien))
                                    <img src="{{ asset($item->HinhAnhDaiDien) }}" alt="Anh phong" style="width:60px; height:45px; object-fit:cover; border-radius:6px;">
                                @else
                                    <span class="text-muted small">Chưa có ảnh</span>
                                @endif
                            </div>
                        </td>
                        <td class="fw-semibold">{{ $item->loaiPhong?->TenLoaiPhong ?? '' }}</td>
                        <td class="text-primary fw-bold">{{ format_currency($item->loaiPhong?->GiaPhong ?? 0) }}</td>
                        <td>
                            <span class="status-badge {{ $badgeClass }}">
                                {{ $st }}
                            </span>
                        </td>
                        <td class="table-actions text-end">
                            <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('admin.phong.edit', $item) }}">
                                <i class="fa-solid fa-pen-to-square me-1"></i>Sửa
                            </a>
                            <form method="POST" action="{{ route('admin.phong.destroy', $item) }}" class="action-form" onsubmit="return confirm('Bạn có chắc muốn xóa phòng {{ $item->MaPhong }} không?')">
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
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-folder-open fa-2x mb-2 d-block"></i>
                            Không tìm thấy phòng phù hợp với điều kiện tìm kiếm.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
