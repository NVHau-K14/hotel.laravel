@extends('admin.layouts.app')

@section('title', "Chi tiết Hóa đơn #{$item->MaHoaDon}")

@section('content')
@php
    $datPhong = $item->datPhong;
    $khach = $datPhong?->khachHang;
    $tenLoaiPhong = $datPhong?->phong?->loaiPhong?->TenLoaiPhong;
@endphp
<div class="container my-4" style="max-width: 800px;">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4 print-hidden">
        <a href="{{ route('admin.hoa-don.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="fa-solid fa-print me-1"></i> In hóa đơn
            </button>
            @unless ($item->daThanhToan())
                <form method="POST" action="{{ route('admin.hoa-don.thanh-toan', $item) }}" class="action-form" onsubmit="return confirm('Xác nhận khách đã thanh toán số tiền này?')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-check-double me-1"></i> Xác nhận thanh toán
                    </button>
                </form>
            @endunless
        </div>
    </div>

    <div class="card p-5 border-0 shadow-sm bg-white" id="printableInvoice">
        <!-- HEADER HÓA ĐƠN -->
        <div class="d-flex justify-content-between border-bottom pb-4 mb-4">
            <div>
                <h3 class="fw-bold text-primary mb-1"><i class="fa-solid fa-hotel me-2"></i>KHÁCH SẠN NGHỈ DƯỠNG</h3>
                <p class="text-muted small mb-0">Địa chỉ: 123 Đường Bờ Biển, TP. Đà Nẵng</p>
                <p class="text-muted small mb-0">Hotline: 0905 123 456 - Email: contact@hotel.vn</p>
            </div>
            <div class="text-end">
                <h4 class="fw-bold text-dark mb-1">HÓA ĐƠN THANH TOÁN</h4>
                <p class="text-muted small mb-1">Số HD: <strong>#{{ $item->MaHoaDon }}</strong></p>
                <p class="text-muted small mb-0">Ngày lập: {{ format_date($item->NgayThuTien) }}</p>
            </div>
        </div>

        <!-- THÔNG TIN KHÁCH HÀNG & PHÒNG -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="fw-bold text-muted text-uppercase mb-2">Thông tin khách hàng</h6>
                <p class="mb-1 fw-bold">{{ $khach?->HoTen }}</p>
                <p class="mb-1 small">Điện thoại: {{ $khach?->DienThoai }}</p>
                <p class="mb-1 small">CMND/CCCD: {{ $khach?->CMND ?? 'Không có' }}</p>
            </div>
            <div class="col-6 text-end">
                <h6 class="fw-bold text-muted text-uppercase mb-2">Chi tiết lưu trú</h6>
                <p class="mb-1">Phòng: <strong>Phòng {{ $datPhong?->MaPhong }}</strong> ({{ $tenLoaiPhong }})</p>
                <p class="mb-1 small">Ngày nhận: {{ format_date($datPhong?->NgayNhan) }}</p>
                <p class="mb-1 small">Ngày trả: {{ format_date($datPhong?->NgayTra) }} ({{ $calc['days'] ?? 1 }} ngày)</p>
            </div>
        </div>

        <!-- BẢNG CHI TIẾT THANH TOÁN -->
        <table class="table table-bordered compact-table mb-4">
            <thead class="table-light">
                <tr>
                    <th>Khoản mục</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-end">Đơn giá</th>
                    <th class="text-end">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tiền phòng ({{ $tenLoaiPhong }})</td>
                    <td class="text-center">{{ $calc['days'] ?? 1 }} ngày</td>
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
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end fs-5">TỔNG CỘNG:</th>
                    <th class="text-end text-danger fs-4">{{ format_currency($item->SoTienThu) }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
            <div>
                <span>Trạng thái: </span>
                @if ($item->daThanhToan())
                    <span class="badge bg-success fs-6"><i class="fa-solid fa-check me-1"></i>ĐÃ THANH TOÁN</span>
                @else
                    <span class="badge bg-warning text-dark fs-6"><i class="fa-solid fa-hourglass-half me-1"></i>CHƯA THANH TOÁN</span>
                @endif
            </div>
            <div class="text-end">
                <small class="text-muted d-block">Thu ngân</small>
                <strong>{{ $item->nhanVien?->TenNhanVien ?? 'Quản trị viên' }}</strong>
            </div>
        </div>
    </div>
</div>
@endsection
