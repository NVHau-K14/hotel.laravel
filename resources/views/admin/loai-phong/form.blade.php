{{-- Dùng chung cho thêm ($item = null) và sửa --}}
@extends('admin.layouts.app')

@section('title', $item ? "Sửa loại phòng #{$item->MaLoaiPhong}" : 'Thêm loại phòng mới')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">{{ $item ? "Sửa loại phòng #{$item->MaLoaiPhong}" : 'Thêm loại phòng mới' }}</h1>
        <a href="{{ route('admin.loai-phong.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4" style="max-width: 600px;">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $item ? route('admin.loai-phong.update', $item) : route('admin.loai-phong.store') }}">
            @csrf
            @if ($item)
                @method('PUT')
            @endif
            <div class="mb-3">
                <label class="form-label fw-semibold">Tên loại phòng <span class="text-danger">*</span></label>
                <input type="text" name="tenLoaiPhong" class="form-control" placeholder="Ví dụ: Bình Dân, Vip, Thương Gia..."
                       value="{{ old('tenLoaiPhong', $item?->TenLoaiPhong) }}" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Giá phòng theo ngày (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" name="giaPhong" class="form-control" placeholder="Ví dụ: 300000"
                       value="{{ old('giaPhong', $item?->GiaPhong) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Mô tả tiện ích, trang thiết bị</label>
                <textarea name="moTa" class="form-control" rows="4" placeholder="Một giường đôi. Máy lạnh. View ngắm biển...">{{ old('moTa', $item?->MoTa) }}</textarea>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thông tin
                </button>
                <a href="{{ route('admin.loai-phong.index') }}" class="btn btn-secondary">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>
@endsection
