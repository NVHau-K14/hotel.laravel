@extends('admin.layouts.app')

@section('title', "Chi tiết Hóa đơn #{$item->MaHoaDon}")

@section('content')
@php
    $datPhong = $item->datPhong;
    $khach = $datPhong?->khachHang;
    $tenLoaiPhong = $datPhong?->phong?->loaiPhong?->TenLoaiPhong;
@endphp
<div class="container admin-wide" style="max-width: 900px;">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4 print-hidden">
        <a href="{{ route('admin.hoa-don.index') }}" class="back-link">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Danh sách hóa đơn
        </a>
        <div class="d-flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fa-solid fa-print" aria-hidden="true"></i>In hóa đơn
            </button>
            @if ($item->thanhToanDuoc())
                <form method="POST" action="{{ route('admin.hoa-don.thanh-toan', $item) }}" class="action-form" onsubmit="return confirm('Xác nhận khách đã thanh toán số tiền này?')">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check-double" aria-hidden="true"></i>Xác nhận thanh toán
                    </button>
                </form>
            @endif
        </div>
    </div>

    <article class="invoice-sheet p-4 p-md-5" id="printableInvoice">
        {{-- Đầu hóa đơn --}}
        <header class="d-flex flex-wrap justify-content-between gap-3 pb-4 mb-4 border-bottom">
            <div class="d-flex gap-3">
                <span class="brand-mark" style="color: var(--brass-ink); background: var(--brass-tint);" aria-hidden="true">V</span>
                <div>
                    <div class="h3 mb-1">Khách sạn Nghỉ dưỡng Vip</div>
                    <p class="text-muted small mb-0">123 Đường Bờ Biển, TP. Đà Nẵng</p>
                    <p class="text-muted small mb-0">0909 123 456, contact@khachsanvip.vn</p>
                </div>
            </div>
            <div class="text-sm-end">
                <h1 class="h2 mb-1">Hóa đơn #{{ $item->MaHoaDon }}</h1>
                <p class="text-muted small mb-0">Ngày lập: {{ format_date($item->NgayThuTien) }}</p>
            </div>
        </header>

        {{-- Khách hàng và lưu trú --}}
        <div class="row g-4 mb-4">
            <div class="col-sm-6">
                <h2 class="panel-title text-muted mb-2">Khách hàng</h2>
                <p class="mb-1 fw-semibold">{{ $khach?->HoTen }}</p>
                <p class="mb-0 small">Điện thoại: {{ $khach?->DienThoai }}</p>
                <p class="mb-0 small">CMND/CCCD: {{ $khach?->CMND ?? 'Không có' }}</p>
            </div>
            <div class="col-sm-6 text-sm-end">
                <h2 class="panel-title text-muted mb-2">Lưu trú</h2>
                <p class="mb-1">Phòng {{ $datPhong?->MaPhong }} ({{ $tenLoaiPhong }})</p>
                <p class="mb-0 small">Nhận phòng: {{ format_date($datPhong?->NgayNhan) }}</p>
                <p class="mb-0 small">Trả phòng: {{ format_date($datPhong?->NgayTra) }} ({{ $calc['days'] ?? 1 }} đêm{{ ($calc['lateNights'] ?? 0) > 0 ? ', trả muộn '.$calc['lateNights'].' đêm' : '' }})</p>
            </div>
        </div>

        {{-- Chi tiết khoản thu --}}
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Khoản mục</th>
                        <th scope="col" class="text-center">Số lượng</th>
                        <th scope="col" class="text-end">Đơn giá</th>
                        <th scope="col" class="text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Tiền phòng ({{ $tenLoaiPhong }})</td>
                        <td class="text-center">{{ $calc['days'] ?? 1 }} đêm</td>
                        <td class="text-end">{{ format_currency($calc['roomRate'] ?? 0) }}</td>
                        <td class="text-end fw-semibold">{{ format_currency($calc['roomTotal'] ?? 0) }}</td>
                    </tr>
                    @foreach ($calc['services'] ?? [] as $sv)
                        <tr>
                            <td>Dịch vụ: {{ $sv['TenDichVu'] }}</td>
                            <td class="text-center">{{ $sv['SoLuong'] }}</td>
                            <td class="text-end">{{ format_currency($sv['GiaDichVu']) }}</td>
                            <td class="text-end fw-semibold">{{ format_currency($sv['TongTienDichVu']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <dl class="summary-list ms-auto mt-2" style="max-width: 360px;">
            <div class="is-total"><dt>Tổng cộng</dt><dd>{{ format_currency($item->SoTienThu) }}</dd></div>
        </dl>

        <footer class="d-flex flex-wrap justify-content-between align-items-end gap-3 mt-4 pt-4 border-top">
            <div>
                <div class="small text-muted mb-1">Trạng thái</div>
                @if ($item->daThanhToan())
                    <span class="status-badge success">ĐÃ THANH TOÁN</span>
                @elseif ($item->daHuy())
                    <span class="status-badge">ĐÃ HỦY (đơn đặt phòng đã hủy)</span>
                @else
                    <span class="status-badge warning">CHƯA THANH TOÁN</span>
                @endif
            </div>
            <div class="text-end">
                <div class="small text-muted">Thu ngân</div>
                <strong>{{ $item->nhanVien?->TenNhanVien ?? 'Quản trị viên' }}</strong>
            </div>
        </footer>
    </article>
</div>
@endsection
