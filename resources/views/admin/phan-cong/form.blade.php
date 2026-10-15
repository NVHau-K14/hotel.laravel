{{-- Dùng chung cho thêm ($item = null) và sửa --}}
@extends('admin.layouts.app')

@section('title', $item ? "Sửa phân công #{$item->MaPhanCong}" : 'Thêm phân công mới')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">{{ $item ? "Sửa phân công #{$item->MaPhanCong}" : 'Thêm phân công mới' }}</h1>
            <p class="text-muted mb-0">Chỉ định nhiệm vụ cho nhân sự khách sạn</p>
        </div>
        <a href="{{ route('admin.phan-cong.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4" style="max-width: 600px;">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $item ? route('admin.phan-cong.update', $item) : route('admin.phan-cong.store') }}">
            @csrf
            @if ($item)
                @method('PUT')
            @endif
            <div class="mb-3">
                <label class="form-label fw-semibold">Ngày phân công <span class="text-danger">*</span></label>
                <input type="date" name="ngayPhanCong" class="form-control"
                       value="{{ old('ngayPhanCong', $item?->NgayPhanCong?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Loại công việc <span class="text-danger">*</span></label>
                <input type="text" name="loaiCongViec" class="form-control"
                       placeholder="Ví dụ: Dọn phòng 101, Trực sảnh ca tối, Bảo trì máy giặt..."
                       value="{{ old('loaiCongViec', $item?->LoaiCongViec) }}" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nhân viên thực hiện</label>
                <select name="maNhanVien" class="form-select">
                    <option value="">-- Chọn nhân viên (tùy chọn) --</option>
                    @foreach ($nhanViens as $nv)
                        <option value="{{ $nv->MaNhanVien }}" @selected(old('maNhanVien', $item?->MaNhanVien) == $nv->MaNhanVien)>
                            {{ $nv->TenNhanVien }} ({{ $nv->chucVu?->TenChucVu ?? 'Nhân viên' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Lưu phân công
                </button>
                <a href="{{ route('admin.phan-cong.index') }}" class="btn btn-secondary">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>
@endsection
