{{-- Dùng chung cho thêm ($item = null) và sửa --}}
@extends('admin.layouts.app')

@section('title', $item ? "Sửa đánh giá #{$item->ID}" : 'Thêm đánh giá')

@section('content')
@php
    $soSao = (int) old('soSao', $item?->SoSao ?? 5);
    $nhanSao = [5 => '⭐⭐⭐⭐⭐ 5 sao - Rất tốt', 4 => '⭐⭐⭐⭐ 4 sao - Tốt', 3 => '⭐⭐⭐ 3 sao - Bình thường', 2 => '⭐⭐ 2 sao - Chưa tốt', 1 => '⭐ 1 sao - Kém'];
@endphp
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">{{ $item ? "Sửa đánh giá #{$item->ID}" : 'Thêm đánh giá mới' }}</h1>
            <p class="text-muted mb-0">Cập nhật thông tin đánh giá chất lượng phòng</p>
        </div>
        <a href="{{ route('admin.danh-gia.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4" style="max-width: 650px;">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $item ? route('admin.danh-gia.update', $item) : route('admin.danh-gia.store') }}">
            @csrf
            @if ($item)
                @method('PUT')
            @endif
            <div class="mb-3">
                <label class="form-label fw-semibold">Phòng được đánh giá <span class="text-danger">*</span></label>
                <select name="maPhong" class="form-select" required>
                    <option value="">-- Chọn phòng --</option>
                    @foreach ($phongs as $p)
                        <option value="{{ $p->MaPhong }}" @selected(old('maPhong', $item?->MaPhong) == $p->MaPhong)>
                            Phòng {{ $p->MaPhong }} ({{ $p->loaiPhong?->TenLoaiPhong ?? 'Phòng' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Số sao đánh giá <span class="text-danger">*</span></label>
                <select name="soSao" class="form-select" required>
                    @foreach ($nhanSao as $sao => $nhan)
                        <option value="{{ $sao }}" @selected($soSao === $sao)>{{ $nhan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Ngày đánh giá</label>
                <input type="date" name="ngayDanhGia" class="form-control" value="{{ old('ngayDanhGia', $item?->NgayDanhGia?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nội dung nhận xét <span class="text-danger">*</span></label>
                <textarea name="noiDung" rows="4" class="form-control" placeholder="Nhập cảm nhận, nhận xét về phòng..." required>{{ old('noiDung', $item?->NoiDung) }}</textarea>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Lưu đánh giá
                </button>
                <a href="{{ route('admin.danh-gia.index') }}" class="btn btn-secondary">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>
@endsection
