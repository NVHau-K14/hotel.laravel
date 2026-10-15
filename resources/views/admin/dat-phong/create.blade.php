@extends('admin.layouts.app')

@section('title', 'Tạo đơn đặt phòng mới')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">Tạo đơn đặt phòng mới</h1>
        <a href="{{ route('admin.dat-phong.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="card admin-panel border-0 shadow-sm p-4" style="max-width: 650px;">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.dat-phong.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Khách hàng <span class="text-danger">*</span></label>
                <select name="maKhachHang" class="form-select" required>
                    <option value="">-- Chọn khách hàng --</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->MaKhachHang }}" @selected(old('maKhachHang') == $c->MaKhachHang)>
                            {{ $c->HoTen }} - SĐT: {{ $c->DienThoai }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Chọn phòng <span class="text-danger">*</span></label>
                <select name="maPhong" class="form-select" required>
                    <option value="">-- Chọn phòng --</option>
                    @foreach ($rooms as $r)
                        <option value="{{ $r->MaPhong }}" @selected(old('maPhong') == $r->MaPhong)>
                            Phòng {{ $r->MaPhong }} ({{ $r->loaiPhong?->TenLoaiPhong }} - {{ format_currency($r->loaiPhong?->GiaPhong) }}/ngày) - [{{ $r->TrangThai }}]
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngày nhận phòng <span class="text-danger">*</span></label>
                    <input type="date" name="ngayNhan" class="form-control" value="{{ old('ngayNhan', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Ngày trả phòng <span class="text-danger">*</span></label>
                    <input type="date" name="ngayTra" class="form-control" value="{{ old('ngayTra', now()->addDay()->format('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Trạng thái khởi tạo</label>
                <select name="trangThai" class="form-select">
                    <option value="DA_XAC_NHAN" @selected(old('trangThai') === 'DA_XAC_NHAN')>Đã xác nhận (Đặt trước)</option>
                    <option value="DANG_SU_DUNG" @selected(old('trangThai') === 'DANG_SU_DUNG')>Khách nhận phòng ngay (Check-in)</option>
                    <option value="CHO_ADMIN_DUYET" @selected(old('trangThai') === 'CHO_ADMIN_DUYET')>Chờ duyệt</option>
                </select>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-check me-1"></i> Tạo đơn đặt phòng
                </button>
                <a href="{{ route('admin.dat-phong.index') }}" class="btn btn-secondary">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>
@endsection
