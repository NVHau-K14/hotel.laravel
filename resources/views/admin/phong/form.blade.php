{{-- Dùng chung cho thêm ($item = null) và sửa --}}
@extends('admin.layouts.app')

@section('title', $item ? "Sửa phòng #{$item->MaPhong}" : 'Thêm phòng mới')

@section('content')
<div class="container admin-wide">
    <div class="page-head">
        <div>
            <h1>{{ $item ? "Sửa phòng #{$item->MaPhong}" : 'Thêm phòng mới' }}</h1>
            <p>Trạng thái đã đặt và đang thuê tự cập nhật theo đơn đặt phòng.</p>
        </div>
        <a href="{{ route('admin.phong.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
    </div>

    <div class="admin-panel p-4" style="max-width: 650px;">
        @if ($errors->any())
            <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $item ? route('admin.phong.update', $item) : route('admin.phong.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($item)
                @method('PUT')
            @endif
            <div class="mb-3">
                <label for="phong-form-maPhong" class="form-label fw-semibold">Mã phòng <span class="text-danger">*</span></label>
                <input id="phong-form-maPhong" type="number" name="maPhong" class="form-control" placeholder="Ví dụ: 101, 102…"
                       value="{{ old('maPhong', $item?->MaPhong) }}" {{ $item ? 'readonly' : 'required' }}>
                @if ($item)
                    <small class="text-muted">Mã số phòng không thể thay đổi sau khi tạo.</small>
                @endif
            </div>

            <div class="mb-3">
                <label for="phong-form-maLoaiPhong" class="form-label fw-semibold">Loại phòng <span class="text-danger">*</span></label>
                <select id="phong-form-maLoaiPhong" name="maLoaiPhong" class="form-select" required>
                    <option value="">-- Chọn loại phòng --</option>
                    @foreach ($loaiPhongs as $lp)
                        <option value="{{ $lp->MaLoaiPhong }}" @selected(old('maLoaiPhong', $item?->MaLoaiPhong) == $lp->MaLoaiPhong)>
                            {{ $lp->TenLoaiPhong }} ({{ format_currency($lp->GiaPhong) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="phong-form-trangThai" class="form-label fw-semibold">Trạng thái phòng</label>
                <select id="phong-form-trangThai" name="trangThai" class="form-select">
                    {{-- "Đã đặt" / "Đang thuê" do hệ thống tự tính từ đơn đặt phòng, admin chỉ chọn Trống hoặc Bảo trì --}}
                    @php($trangThaiForm = ($item?->TrangThai === \App\Models\Phong::BAO_TRI) ? \App\Models\Phong::BAO_TRI : \App\Models\Phong::TRONG)
                    @foreach (\App\Models\Phong::TRANG_THAI_CHON_TAY as $st)
                        <option value="{{ $st }}" @selected(old('trangThai', $trangThaiForm) === $st)>{{ $st === \App\Models\Phong::TRONG ? 'Hoạt động (tự cập nhật theo đơn)' : $st }}</option>
                    @endforeach
                </select>
                @if ($item && in_array($item->TrangThai, [\App\Models\Phong::DA_DAT, \App\Models\Phong::DANG_THUE], true))
                    <div class="form-text">Hiện tại: <strong>{{ $item->TrangThai }}</strong> (theo đơn đặt phòng).</div>
                @endif
            </div>

            <div class="mb-3">
                <label for="phong-form-hinhAnh" class="form-label fw-semibold">Đường dẫn ảnh đại diện (URL hoặc tải lên)</label>
                <input id="phong-form-hinhAnh" type="text" name="hinhAnh" class="form-control mb-2" placeholder="/Images/ChiTietPhong/Phong101/01.jpg"
                       value="{{ old('hinhAnh', $item?->HinhAnhDaiDien) }}">
                <input type="file" name="fileAnh" class="form-control" accept="image/*">
                @if (! empty($item?->HinhAnhDaiDien))
                    <div class="mt-2">
                        <small class="text-muted d-block mb-1">Ảnh hiện tại:</small>
                        <img src="{{ anh_url($item->HinhAnhDaiDien) }}" alt="Ảnh hiện tại" width="133" height="100" style="max-height: 100px; width: auto; border-radius: 6px;">
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
