@extends('admin.layouts.app')

@section('title', "Thêm dịch vụ vào đơn #{$datPhong->MaCTDatPhong}")

@section('content')
<div class="container admin-wide">
    <div class="page-head">
        <div>
            <h1>Thêm dịch vụ vào đơn #{{ $datPhong->MaCTDatPhong }}</h1>
            <p>Phòng {{ $datPhong->MaPhong }} - Khách: {{ $datPhong->khachHang?->HoTen ?? 'Khách' }}</p>
        </div>
        <a href="{{ route('admin.dat-phong.show', $datPhong) }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại chi tiết đơn
        </a>
    </div>

    <div class="admin-panel p-4" style="max-width: 550px;">
        <form method="POST" action="{{ route('admin.ct-dich-vu.luu-vao-don', $datPhong) }}">
            @csrf
            <div class="mb-3">
                <label for="ct-dich-vu-them-vao-don-maDichVu" class="form-label fw-semibold">Chọn dịch vụ <span class="text-danger">*</span></label>
                <select id="ct-dich-vu-them-vao-don-maDichVu" name="maDichVu" class="form-select" required autofocus>
                    <option value="">-- Chọn dịch vụ cần thêm --</option>
                    @foreach ($dichVus as $dv)
                        <option value="{{ $dv->MaDichVu }}">
                            {{ $dv->TenDichVu }} ({{ format_currency($dv->GiaDichVu) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="ct-dich-vu-them-vao-don-soLuong" class="form-label fw-semibold">Số lượng <span class="text-danger">*</span></label>
                <input id="ct-dich-vu-them-vao-don-soLuong" type="number" name="soLuong" class="form-control" value="1" min="1" required>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-plus me-1"></i> Thêm vào đơn
                </button>
                <a href="{{ route('admin.dat-phong.show', $datPhong) }}" class="btn btn-secondary">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
