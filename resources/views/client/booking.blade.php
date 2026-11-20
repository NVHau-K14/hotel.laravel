@extends('client.layouts.app')

@section('title', "Đặt phòng {$room->MaPhong}")

@section('content')
<main class="wrap">
    <a href="{{ route('phong.show', $room) }}" class="back-link mt-3 mb-2">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Chi tiết phòng {{ $room->MaPhong }}
    </a>

    <div class="booking-layout">
        <div class="sheet">
            <h1 class="sheet-title">Yêu cầu đặt phòng</h1>
            <p class="sheet-sub">Chọn ngày lưu trú. Khách sạn liên hệ theo thông tin tài khoản để xác nhận.</p>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">{{ $errors->first() }}</div>
            @endif

            @if (! $room->nhanDatPhong())
                <div class="alert alert-warning mb-0">
                    Phòng này đang bảo trì hoặc tạm ngừng nhận khách. Vui lòng
                    <a href="{{ route('phong.index') }}">chọn phòng khác</a>.
                </div>
            @else
            <form method="POST" action="{{ route('dat-phong.store', $room) }}" id="form-dat-phong">
                @csrf

                <h2 class="step-title"><span aria-hidden="true">1</span>Thời gian lưu trú</h2>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="ngay-nhan">Ngày nhận phòng <span class="field-required" aria-hidden="true">*</span></label>
                        <input type="date" id="ngay-nhan" name="ngayNhan" class="form-control" value="{{ old('ngayNhan', $ngayNhan) }}"
                               min="{{ today()->toDateString() }}" max="{{ today()->addDays(\App\Models\DatPhong::SO_NGAY_DAT_TRUOC_TOI_DA)->toDateString() }}" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="ngay-tra">Ngày trả phòng <span class="field-required" aria-hidden="true">*</span></label>
                        <input type="date" id="ngay-tra" name="ngayTra" class="form-control" value="{{ old('ngayTra', $ngayTra) }}"
                               min="{{ today()->addDay()->toDateString() }}" required>
                    </div>
                </div>
                <p class="form-text mt-2 mb-4">
                    Đặt trực tuyến tối đa {{ \App\Models\DatPhong::SO_DEM_TOI_DA }} đêm mỗi đơn và
                    {{ \App\Models\DatPhong::SO_DON_CHO_DUYET_TOI_DA }} đơn đang chờ duyệt cùng lúc.
                </p>

                {{-- Thông tin liên hệ lấy từ tài khoản, không sửa ở đây (sửa ở trang hồ sơ) --}}
                <h2 class="step-title"><span aria-hidden="true">2</span>Thông tin khách đặt</h2>
                <dl class="guest-info">
                    <div><dt>Họ và tên</dt><dd>{{ $clientUser->HoTen }}</dd></div>
                    <div><dt>Số điện thoại</dt><dd>{{ $clientUser->DienThoai }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $clientUser->Email ?: 'Chưa cập nhật' }}</dd></div>
                    <div><dt>CMND/CCCD</dt><dd>{{ $clientUser->CMND ?: 'Chưa cập nhật' }}</dd></div>
                </dl>
                <p class="form-text mt-2 mb-4">
                    Thông tin chưa đúng? <a href="{{ route('tai-khoan.sua') }}">Cập nhật hồ sơ</a> trước khi đặt.
                </p>

                <button type="submit" class="btn btn-primary booking-submit">
                    <span>Xác nhận gửi yêu cầu đặt phòng</span>
                </button>
            </form>
            @endif
        </div>

        <aside class="summary-card" aria-label="Tóm tắt phòng">
            <div class="summary-card-media">
                @if (! empty($room->HinhAnhDaiDien))
                    <img src="{{ asset($room->HinhAnhDaiDien) }}" alt="" width="640" height="400">
                @endif
                <span class="door-plate door-plate-on-image" aria-hidden="true">{{ $room->MaPhong }}</span>
            </div>
            <div class="summary-card-body">
                <h2>Phòng {{ $room->MaPhong }}</h2>
                <div class="text-muted">{{ $room->loaiPhong?->TenLoaiPhong }}</div>
                <dl class="summary-list">
                    <div><dt>Giá mỗi đêm</dt><dd>{{ format_currency($room->loaiPhong?->GiaPhong) }}</dd></div>
                    <div><dt>Số đêm</dt><dd data-so-dem>—</dd></div>
                    <div class="is-total"><dt>Tạm tính tiền phòng</dt><dd data-tam-tinh>—</dd></div>
                </dl>
                <p class="small text-muted mt-3 mb-0">Dịch vụ gọi thêm được cộng vào hóa đơn khi trả phòng.</p>
            </div>
        </aside>
    </div>
</main>
@endsection

@push('scripts')
<script>
    (function () {
        var gia = {{ (int) ($room->loaiPhong?->GiaPhong ?? 0) }};
        var nhan = document.getElementById('ngay-nhan');
        var tra = document.getElementById('ngay-tra');
        var oDem = document.querySelector('[data-so-dem]');
        var oTien = document.querySelector('[data-tam-tinh]');
        if (!nhan || !tra || !oDem) return;
        var dinhDang = new Intl.NumberFormat('vi-VN');
        function capNhat() {
            var a = new Date(nhan.value), b = new Date(tra.value);
            var dem = Math.round((b - a) / 86400000);
            if (!nhan.value || !tra.value || isNaN(dem) || dem < 1) {
                oDem.textContent = '—';
                oTien.textContent = '—';
                return;
            }
            oDem.textContent = dem + ' đêm';
            oTien.textContent = dinhDang.format(dem * gia) + ' đ';
        }
        nhan.addEventListener('change', function () {
            if (nhan.value) {
                var hom = new Date(nhan.value);
                hom.setDate(hom.getDate() + 1);
                tra.min = hom.toISOString().slice(0, 10);
            }
            capNhat();
        });
        tra.addEventListener('change', capNhat);
        capNhat();
    })();
</script>
@endpush
