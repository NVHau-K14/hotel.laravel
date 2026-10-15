{{-- Dùng chung cho thêm ($item = null) và sửa --}}
@extends('admin.layouts.app')

@section('title', $item ? "Sửa phòng #{$item->MaPhong}" : 'Thêm phòng mới')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">{{ $item ? "Sửa phòng #{$item->MaPhong}" : 'Thêm phòng mới' }}</h1>
        <a href="{{ route('admin.phong.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4" style="max-width: 650px;">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $item ? route('admin.phong.update', $item) : route('admin.phong.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($item)
                @method('PUT')
            @endif
            <div class="mb-3">
                <label class="form-label fw-semibold">Mã phòng <span class="text-danger">*</span></label>
                <input type="number" name="maPhong" class="form-control" placeholder="Ví dụ: 101, 102..."
                       value="{{ old('maPhong', $item?->MaPhong) }}" {{ $item ? 'readonly' : 'required' }}>
                @if ($item)
                    <small class="text-muted">Mã số phòng không thể thay đổi sau khi tạo.</small>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Loại phòng <span class="text-danger">*</span></label>
                <select name="maLoaiPhong" class="form-select" required>
                    <option value="">-- Chọn loại phòng --</option>
                    @foreach ($loaiPhongs as $lp)
                        <option value="{{ $lp->MaLoaiPhong }}" @selected(old('maLoaiPhong', $item?->MaLoaiPhong) == $lp->MaLoaiPhong)>
                            {{ $lp->TenLoaiPhong }} ({{ format_currency($lp->GiaPhong) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Trạng thái phòng</label>
                <select name="trangThai" class="form-select">
                    {{-- Sửa lỗi L1: bỏ "Đang sử dụng", chỉ còn 4 trạng thái hợp lệ --}}
                    @foreach (\App\Models\Phong::TRANG_THAI as $st)
                        <option value="{{ $st }}" @selected(old('trangThai', $item?->TrangThai ?? 'Trống') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Đường dẫn ảnh đại diện (URL hoặc tải lên)</label>
                <input type="text" name="hinhAnh" class="form-control mb-2" placeholder="/Images/ChiTietPhong/Phong101/01.jpg"
                       value="{{ old('hinhAnh', $item?->HinhAnhDaiDien) }}">
                <input type="file" name="fileAnh" class="form-control" accept="image/*">
                @if (! empty($item?->HinhAnhDaiDien))
                    <div class="mt-2">
                        <small class="text-muted d-block mb-1">Ảnh hiện tại:</small>
                        <img src="{{ asset($item->HinhAnhDaiDien) }}" alt="Preview" style="max-height: 100px; border-radius: 6px;">
                    </div>
                @endif
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thông tin
                </button>
                <a href="{{ route('admin.phong.index') }}" class="btn btn-secondary">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>
@endsection
