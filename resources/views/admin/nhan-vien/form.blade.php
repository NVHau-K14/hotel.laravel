{{-- Dùng chung cho thêm ($item = null) và sửa --}}
@extends('admin.layouts.app')

@section('title', $item ? "Sửa nhân viên #{$item->MaNhanVien}" : 'Thêm nhân viên mới')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">{{ $item ? "Sửa nhân viên #{$item->MaNhanVien}" : 'Thêm nhân viên mới' }}</h1>
        <a href="{{ route('admin.nhan-vien.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4" style="max-width: 650px;">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $item ? route('admin.nhan-vien.update', $item) : route('admin.nhan-vien.store') }}">
            @csrf
            @if ($item)
                @method('PUT')
            @endif
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tên đăng nhập <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" value="{{ $item ? $item->Username : old('username') }}"
                           {{ $item ? 'readonly' : 'required' }} placeholder="vd: nguyenvanb">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mật khẩu {!! $item ? '(để trống nếu không đổi)' : '<span class="text-danger">*</span>' !!}</label>
                    <input type="password" name="password" class="form-control" placeholder="Mật khẩu" {{ $item ? '' : 'required' }}>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Họ và tên nhân viên <span class="text-danger">*</span></label>
                <input type="text" name="tenNhanVien" class="form-control" value="{{ old('tenNhanVien', $item?->TenNhanVien) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Chức vụ <span class="text-danger">*</span></label>
                <select name="maChucVu" class="form-select" required>
                    <option value="">-- Chọn chức vụ --</option>
                    @foreach ($chucVus as $cv)
                        <option value="{{ $cv->MaChucVu }}" @selected(old('maChucVu', $item?->MaChucVu) == $cv->MaChucVu)>
                            {{ $cv->TenChucVu }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngày sinh</label>
                    <input type="date" name="ngaySinh" class="form-control" value="{{ old('ngaySinh', $item?->NgaySinh?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">CMND/CCCD</label>
                    <input type="text" name="cmnd" class="form-control" value="{{ old('cmnd', $item?->CMND) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Ngày vào làm</label>
                <input type="date" name="ngayVaoLam" class="form-control" value="{{ old('ngayVaoLam', $item?->NgayVaoLam?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thông tin
                </button>
                <a href="{{ route('admin.nhan-vien.index') }}" class="btn btn-secondary">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>
@endsection
