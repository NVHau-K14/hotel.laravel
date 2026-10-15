@extends('admin.layouts.app')

@section('title', 'Thêm chi tiết dịch vụ')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Thêm chi tiết dịch vụ</h1>
            <p class="text-muted mb-0">Thêm bản ghi sử dụng dịch vụ cho đơn đặt phòng</p>
        </div>
        <a href="{{ route('admin.ct-dich-vu.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4" style="max-width: 600px;">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.ct-dich-vu.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Đơn đặt phòng <span class="text-danger">*</span></label>
                <select name="maCTDatPhong" class="form-select" required>
                    <option value="">-- Chọn đơn đặt phòng --</option>
                    @foreach ($datPhongs as $dp)
                        <option value="{{ $dp->MaCTDatPhong }}" @selected(old('maCTDatPhong', $maCTDatPhongChon) == $dp->MaCTDatPhong)>
                            Đơn #{{ $dp->MaCTDatPhong }} - Phòng {{ $dp->MaPhong }} (Khách: {{ $dp->khachHang?->HoTen ?? 'Khách' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Dịch vụ <span class="text-danger">*</span></label>
                <select name="maDichVu" class="form-select" required>
                    <option value="">-- Chọn dịch vụ --</option>
                    @foreach ($dichVus as $dv)
                        <option value="{{ $dv->MaDichVu }}" @selected(old('maDichVu') == $dv->MaDichVu)>
                            {{ $dv->TenDichVu }} ({{ format_currency($dv->GiaDichVu) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Số lượng <span class="text-danger">*</span></label>
                <input type="number" name="soLuong" class="form-control" value="{{ old('soLuong', 1) }}" min="1" required>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Lưu chi tiết
                </button>
                <a href="{{ route('admin.ct-dich-vu.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection
