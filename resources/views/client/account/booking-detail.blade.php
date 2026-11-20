@extends('client.layouts.app')

@section('title', "Chi tiết đặt phòng #{$datPhong->MaCTDatPhong}")

@php
    // Sửa lỗi L2: bản gốc dùng mã không tồn tại (DA_THANH_TOAN, DA_TRA_PHONG) và thiếu DA_HOAN_THANH
    $status = $datPhong->TrangThai ?? 'CHO_ADMIN_DUYET';
    [$statusBadge, $statusText] = match ($status) {
        'CHO_ADMIN_DUYET' => ['warning', 'Chờ duyệt'],
        'DA_XAC_NHAN' => ['info', 'Đã xác nhận'],
        'DANG_SU_DUNG' => ['success', 'Đang ở'],
        'DA_HOAN_THANH' => ['success', 'Hoàn tất'],
        'DA_HUY' => ['danger', 'Đã hủy'],
        default => ['', $status],
    };
@endphp

@section('content')
<main class="wrap wrap-narrow pb-5">
    <a href="{{ route('tai-khoan.lich-su') }}" class="back-link mt-3">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Lịch sử đặt phòng
    </a>

    <header class="page-intro pt-3 d-flex flex-wrap align-items-end justify-content-between gap-3">
        <div>
            <h1>Đơn #{{ $datPhong->MaCTDatPhong }}</h1>
            <p>Ngày tạo đơn: {{ format_date($datPhong->NgayThucHien) }}</p>
        </div>
        <span class="status-badge {{ $statusBadge }}">{{ $statusText }}</span>
    </header>

    <div class="account-grid pb-0">
        <section class="sheet" aria-labelledby="thong-tin-phong">
            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="door-plate door-plate-lg" aria-hidden="true">{{ $datPhong->MaPhong }}</span>
                <div>
                    <h2 class="sheet-title mb-0" id="thong-tin-phong">Phòng {{ $datPhong->MaPhong }}</h2>
                    <div class="text-muted">{{ $datPhong->phong?->loaiPhong?->TenLoaiPhong ?? 'Phòng tiêu chuẩn' }}</div>
                </div>
            </div>
            <dl class="detail-list">
                <div><dt>Ngày nhận phòng</dt><dd>{{ format_date($datPhong->NgayNhan) }}</dd></div>
                <div><dt>Ngày trả phòng</dt><dd>{{ format_date($datPhong->NgayTra) }}</dd></div>
                <div><dt>Số đêm</dt><dd>{{ $calc['days'] ?? 1 }} đêm</dd></div>
                <div><dt>Giá mỗi đêm</dt><dd>{{ format_currency($datPhong->GiaPhong) }}</dd></div>
            </dl>
        </section>

        <section class="sheet" aria-labelledby="tong-chi-phi">
            <h2 class="sheet-title" id="tong-chi-phi">Chi phí</h2>
            <dl class="summary-list">
                <div><dt>Tiền phòng ({{ $calc['days'] ?? 1 }} đêm)</dt><dd>{{ format_currency($calc['roomTotal'] ?? 0) }}</dd></div>
                <div><dt>Dịch vụ gọi thêm</dt><dd>{{ format_currency($calc['serviceTotal'] ?? 0) }}</dd></div>
                <div class="is-total"><dt>Tổng cộng</dt><dd>{{ format_currency($calc['grandTotal'] ?? 0) }}</dd></div>
            </dl>
        </section>
    </div>

    <section class="sheet mt-3" aria-labelledby="dich-vu-da-dung">
        <h2 class="sheet-title" id="dich-vu-da-dung">Dịch vụ đã sử dụng</h2>
        @if ($dichVuDaDung->isEmpty())
            <p class="text-muted mb-0">Đơn phòng này chưa gọi thêm dịch vụ nào.</p>
        @else
            <div class="table-responsive mt-3">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Dịch vụ</th>
                            <th scope="col" class="text-center">Số lượng</th>
                            <th scope="col" class="text-end">Đơn giá</th>
                            <th scope="col" class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dichVuDaDung as $dv)
                            <tr>
                                <td class="fw-semibold">{{ $dv->TenDichVu }}</td>
                                <td class="text-center">{{ $dv->SoLuong }}</td>
                                <td class="text-end text-muted">{{ format_currency($dv->GiaDichVu) }}</td>
                                <td class="text-end fw-semibold">{{ format_currency($dv->TongTienDichVu) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</main>
@endsection
