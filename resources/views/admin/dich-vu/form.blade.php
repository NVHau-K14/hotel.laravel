{{-- Dùng chung cho thêm ($item = null) và sửa --}}
@extends('admin.layouts.app')

@section('title', $item ? "Sửa dịch vụ #{$item->MaDichVu}" : 'Thêm dịch vụ mới')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">{{ $item ? "Sửa dịch vụ #{$item->MaDichVu}" : 'Thêm dịch vụ mới' }}</h1>
        <a href="{{ route('admin.dich-vu.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4" style="max-width: 600px;">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $item ? route('admin.dich-vu.update', $item) : route('admin.dich-vu.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($item)
                @method('PUT')
            @endif
            <div class="mb-3">
                <label class="form-label fw-semibold">Tên dịch vụ <span class="text-danger">*</span></label>
                <input type="text" name="tenDichVu" class="form-control" placeholder="Ví dụ: Ăn sáng, Massage, Giặt ủi..."
                       value="{{ old('tenDichVu', $item?->TenDichVu) }}" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Giá dịch vụ (VNĐ) <span class="text-danger">*</span></label>
                <input type="number" name="giaDichVu" class="form-control" placeholder="Ví dụ: 30000"
                       value="{{ old('giaDichVu', $item?->GiaDichVu) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Hình ảnh minh họa</label>
                <input type="text" name="hinhAnh" class="form-control mb-2" placeholder="/Images/DichVu/BuaSang/service.jpg"
                       value="{{ old('hinhAnh', $item?->HinhAnh) }}">
                <input type="file" name="fileAnh" class="form-control" accept="image/*">
                @if (! empty($item?->HinhAnh))
                    <div class="mt-2">
                        <img src="{{ asset($item->HinhAnh) }}" alt="Preview" style="max-height: 80px; border-radius: 6px;">
                    </div>
                @endif
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Lưu dịch vụ
                </button>
                <a href="{{ route('admin.dich-vu.index') }}" class="btn btn-secondary">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>
@endsection
