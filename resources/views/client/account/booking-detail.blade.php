@extends('client.layouts.app')

@section('title', "Chi tiết đặt phòng #{$datPhong->MaCTDatPhong}")

@php
    // Sửa lỗi L2: bản gốc dùng mã không tồn tại (DA_THANH_TOAN, DA_TRA_PHONG) và thiếu DA_HOAN_THANH
    $status = $datPhong->TrangThai ?? 'CHO_ADMIN_DUYET';
    [$statusBadge, $statusText] = match ($status) {
        'CHO_ADMIN_DUYET' => ['bg-warning text-dark', 'Chờ duyệt'],
        'DA_XAC_NHAN' => ['bg-info text-dark', 'Đã xác nhận'],
        'DANG_SU_DUNG' => ['bg-primary text-white', 'Đang ở'],
        'DA_HOAN_THANH' => ['bg-success text-white', 'Hoàn tất'],
        'DA_HUY' => ['bg-danger text-white', 'Đã hủy'],
        default => ['bg-secondary text-white', $status],
    };
@endphp

@section('content')
<main class="client-section" style="max-width: 900px; margin: 2rem auto; padding: 0 1rem;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1 text-primary">Chi tiết đặt phòng #{{ $datPhong->MaCTDatPhong }}</h1>
            <p class="text-muted mb-0">Ngày tạo đơn: {{ format_date($datPhong->NgayThucHien) }}</p>
        </div>
        <a href="{{ route('tai-khoan.lich-su') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại lịch sử
        </a>
    </div>

    <!-- Thông tin phòng & Thời gian lưu trú -->
    <div class="row g-4 mb-4">
        <div class="col-md-7">
            <div class="card bg-white p-4 rounded shadow-sm border h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-primary text-white rounded p-3 text-center fw-bold fs-3" style="min-width: 70px;">
                        {{ $datPhong->MaPhong }}
                    </div>
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Thông tin phòng</span>
                        <h3 class="fw-bold mb-0 text-dark">Phòng {{ $datPhong->MaPhong }}</h3>
                        <span class="text-primary fw-semibold">{{ $datPhong->phong?->loaiPhong?->TenLoaiPhong ?? 'Phòng tiêu chuẩn' }}</span>
                    </div>
                </div>

                <div class="d-flex flex-column gap-2 mt-2">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Giá mỗi đêm:</span>
                        <strong class="text-dark">{{ format_currency($datPhong->GiaPhong) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Trạng thái đơn:</span>
                        <div>
                            <span class="badge {{ $statusBadge }} px-3 py-1">{{ $statusText }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card bg-white p-4 rounded shadow-sm border h-100">
                <h5 class="fw-bold mb-3 text-dark"><i class="fa-regular fa-calendar-days me-2 text-primary"></i>Lịch lưu trú</h5>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Ngày nhận phòng:</span>
                        <strong class="text-dark">{{ format_date($datPhong->NgayNhan) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Ngày trả phòng:</span>
                        <strong class="text-dark">{{ format_date($datPhong->NgayTra) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Tổng số đêm:</span>
                        <strong class="text-primary fs-5">{{ $calc['days'] ?? 1 }} đêm</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách dịch vụ sử dụng -->
    <div class="card bg-white p-4 rounded shadow-sm border mb-4">
        <h4 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-bell-concierge me-2 text-primary"></i>Dịch vụ đã sử dụng</h4>
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tên dịch vụ</th>
                    <th class="text-center" style="width: 100px;">Số lượng</th>
                    <th class="text-end" style="width: 160px;">Đơn giá</th>
                    <th class="text-end" style="width: 180px;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dichVuDaDung as $dv)
                    <tr>
                        <td class="fw-semibold text-dark">{{ $dv->TenDichVu }}</td>
                        <td class="text-center"><span class="badge bg-secondary">{{ $dv->SoLuong }}</span></td>
                        <td class="text-end text-muted">{{ format_currency($dv->GiaDichVu) }}</td>
                        <td class="text-end fw-bold text-dark">{{ format_currency($dv->TongTienDichVu) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-3 text-muted">Đơn phòng này chưa gọi thêm dịch vụ nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Bảng tổng kết chi phí -->
    <div class="card bg-white p-4 rounded shadow-sm border" style="max-width: 450px; margin-left: auto;">
        <h5 class="fw-bold mb-3 text-dark">Tổng chi phí thanh toán</h5>
        <div class="d-flex flex-column gap-2">
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted">Tiền phòng ({{ $calc['days'] ?? 1 }} đêm):</span>
                <strong class="text-dark">{{ format_currency($calc['roomTotal'] ?? 0) }}</strong>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
                <span class="text-muted">Tiền dịch vụ thêm:</span>
                <strong class="text-dark">{{ format_currency($calc['serviceTotal'] ?? 0) }}</strong>
            </div>
            <div class="d-flex justify-content-between py-2 mt-1">
                <span class="fw-bold fs-5 text-dark">Tổng cộng:</span>
                <strong class="text-danger fs-4">{{ format_currency($calc['grandTotal'] ?? 0) }}</strong>
            </div>
        </div>
    </div>
</main>
@endsection
