@extends('admin.layouts.app')

@section('title', 'Quản lý phòng')

@section('content')
<div class="container admin-wide">
    <div class="page-head">
        <div>
            <h1>Quản lý phòng</h1>
            <p>Tình trạng phòng hôm nay và bộ lọc phòng còn trống theo ngày.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.phong.create') }}">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>Thêm phòng
        </a>
    </div>

    <div class="admin-panel p-4">
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
                    <label for="phong-index-keyword">Mã phòng</label>
                    <input id="phong-index-keyword" type="text" name="keyword" placeholder="Nhập mã phòng…" value="{{ $keyword }}">
                </div>

                <div class="form-row">
                    <label for="phong-index-maLoaiPhong">Loại phòng</label>
                    <select id="phong-index-maLoaiPhong" name="maLoaiPhong">
                        <option value="">Tất cả loại phòng</option>
                        @foreach ($loaiPhongs as $lp)
                            <option value="{{ $lp->MaLoaiPhong }}" @selected($maLoaiPhong == $lp->MaLoaiPhong)>
                                {{ $lp->TenLoaiPhong }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <label for="phong-index-khoangGia">Khoảng giá</label>
                    <select id="phong-index-khoangGia" name="khoangGia">
                        <option value="">Tất cả mức giá</option>
                        @foreach (\App\Models\Phong::KHOANG_GIA as $ma => [$nhan])
                            <option value="{{ $ma }}" @selected($khoangGia === $ma)>{{ $nhan }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <label for="phong-index-ngayNhan">Ngày nhận</label>
                    <input id="phong-index-ngayNhan" type="date" name="ngayNhan" value="{{ $ngayNhan }}">
                </div>

                <div class="form-row">
                    <label for="phong-index-ngayTra">Ngày trả</label>
                    <input id="phong-index-ngayTra" type="date" name="ngayTra" value="{{ $ngayTra }}">
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
        </div>

        @if ($dateError)
            <div class="alert alert-danger">{{ $dateError }}</div>
        @endif

        <!-- ROOM TABLE -->
        <div class="table-responsive">
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
                        <td><span class="door-plate">{{ $item->MaPhong }}</span></td>
                        <td>
                            <div class="image-thumb">
                                @if (! empty($item->HinhAnhDaiDien))
                                    <img src="{{ anh_url($item->HinhAnhDaiDien) }}" loading="lazy" alt="" width="64" height="46" style="width:60px; height:45px; object-fit:cover; border-radius:6px;">
                                @else
                                    <span class="text-muted small">Chưa có ảnh</span>
                                @endif
                            </div>
                        </td>
                        <td class="fw-semibold">{{ $item->loaiPhong?->TenLoaiPhong ?? '' }}</td>
                        <td class="fw-semibold text-nowrap">{{ format_currency($item->loaiPhong?->GiaPhong ?? 0) }}</td>
                        <td>
                            <span class="status-badge {{ $badgeClass }}">
                                {{ $st }}
                            </span>
                        </td>
                        <td class="table-actions text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.phong.edit', $item) }}">
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
</div>
@endsection
