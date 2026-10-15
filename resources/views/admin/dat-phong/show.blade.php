@extends('admin.layouts.app')

@section('title', "Chi tiết đơn đặt phòng #{$item->MaCTDatPhong}")

@section('content')
@php
    $st = $item->TrangThai;
    $khach = $item->khachHang;
    $coTheSuaDichVu = in_array($st, ['DA_XAC_NHAN', 'DANG_SU_DUNG'], true);
@endphp
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-0">Đơn đặt phòng #{{ $item->MaCTDatPhong }}</h1>
            <p class="text-muted mb-0">Ngày tạo đơn: {{ format_date($item->NgayThucHien) }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dat-phong.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Danh sách
            </a>

            @if ($st === 'CHO_ADMIN_DUYET')
                <form method="POST" action="{{ route('admin.dat-phong.duyet', $item) }}" class="action-form" onsubmit="return confirm('Duyệt đơn đặt phòng này?')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-check me-1"></i> Duyệt đơn
                    </button>
                </form>
            @endif

            @if ($st === 'DA_XAC_NHAN')
                <form method="POST" action="{{ route('admin.dat-phong.nhan-phong', $item) }}" class="action-form" onsubmit="return confirm('Khách nhận phòng (Check-in)?')">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-door-open me-1"></i> Khách nhận phòng (Check-in)
                    </button>
                </form>
            @endif

            @if ($st === 'DANG_SU_DUNG')
                <form method="POST" action="{{ route('admin.dat-phong.tra-phong', $item) }}" class="action-form" onsubmit="return confirm('Trả phòng và lập hóa đơn thanh toán?')">
                    @csrf
                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                        <i class="fa-solid fa-receipt me-1"></i> Trả phòng & Lập hóa đơn (Check-out)
                    </button>
                </form>
            @endif

            @if (in_array($st, ['CHO_ADMIN_DUYET', 'DA_XAC_NHAN'], true))
                <form method="POST" action="{{ route('admin.dat-phong.huy', $item) }}" class="action-form" onsubmit="return confirm('Hủy đơn đặt phòng này?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fa-solid fa-ban me-1"></i> Hủy đơn
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        <!-- CỘT TRÁI: THÔNG TIN KHÁCH & PHÒNG -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-user me-2"></i>Thông tin khách hàng</h5>
                <p class="mb-1"><strong>Họ và tên:</strong> {{ $khach?->HoTen }}</p>
                <p class="mb-1"><strong>Số điện thoại:</strong> {{ $khach?->DienThoai }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ $khach?->Email ?? 'Chưa có' }}</p>
                <p class="mb-1"><strong>CMND/CCCD:</strong> {{ $khach?->CMND ?? 'Chưa có' }}</p>
                <p class="mb-0"><strong>Địa chỉ:</strong> {{ $khach?->DiaChi ?? 'Chưa có' }}</p>
            </div>

            <div class="card border-0 shadow-sm p-4">
                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-key me-2"></i>Thông tin phòng & Lưu trú</h5>
                <p class="mb-1"><strong>Phòng số:</strong> <span class="badge bg-primary fs-6">{{ $item->MaPhong }}</span></p>
                <p class="mb-1"><strong>Loại phòng:</strong> {{ $item->phong?->loaiPhong?->TenLoaiPhong }}</p>
                <p class="mb-1"><strong>Đơn giá:</strong> {{ format_currency($item->GiaPhong) }} / ngày</p>
                <hr>
                <p class="mb-1"><strong>Ngày nhận:</strong> {{ format_date($item->NgayNhan) }}</p>
                <p class="mb-1"><strong>Ngày trả:</strong> {{ format_date($item->NgayTra) }}</p>
                <p class="mb-1"><strong>Số ngày lưu trú:</strong> <span class="badge bg-secondary">{{ $calc['days'] ?? 1 }} ngày</span></p>
                <p class="mb-0"><strong>Tiền phòng:</strong> <span class="text-primary fw-bold">{{ format_currency($calc['roomTotal'] ?? 0) }}</span></p>
            </div>
        </div>

        <!-- CỘT PHẢI: DỊCH VỤ SỬ DỤNG & TỔNG TIỀN -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-bell-concierge me-2"></i>Dịch vụ đã sử dụng</h5>
                    @if ($coTheSuaDichVu)
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#addServiceBox">
                            <i class="fa-solid fa-plus me-1"></i> Gọi thêm dịch vụ
                        </button>
                    @endif
                </div>

                <!-- FORM GỌI THÊM DỊCH VỤ -->
                <div class="collapse mb-3" id="addServiceBox">
                    <div class="card card-body bg-light border-0">
                        <form method="POST" action="{{ route('admin.dat-phong.dich-vu.store', $item) }}" class="row g-2 align-items-center">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Chọn dịch vụ</label>
                                <select name="serviceId" class="form-select form-select-sm" required>
                                    <option value="">-- Dịch vụ --</option>
                                    @foreach ($allServices as $s)
                                        <option value="{{ $s->MaDichVu }}">
                                            {{ $s->TenDichVu }} ({{ format_currency($s->GiaDichVu) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Số lượng</label>
                                <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="1" required>
                            </div>
                            <div class="col-md-3 mt-auto">
                                <button type="submit" class="btn btn-sm btn-success w-100">Thêm</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- BẢNG DỊCH VỤ (mảng: lấy từ CTDICHVU hoặc từ bản chụp hóa đơn đã thanh toán) -->
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Tên dịch vụ</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                                @if ($coTheSuaDichVu)
                                    <th class="text-end">Xóa</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($calc['services'] ?? [] as $sv)
                                <tr>
                                    <td>{{ $sv['TenDichVu'] }}</td>
                                    <td>{{ format_currency($sv['GiaDichVu']) }}</td>
                                    <td><span class="badge bg-secondary">x{{ $sv['SoLuong'] }}</span></td>
                                    <td class="fw-semibold">{{ format_currency($sv['TongTienDichVu']) }}</td>
                                    @if ($coTheSuaDichVu)
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.dat-phong.dich-vu.destroy', [$item, $sv['MaDichVu']]) }}" class="action-form" onsubmit="return confirm('Xóa dịch vụ này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted text-center py-3">Chưa sử dụng dịch vụ nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TỔNG HỢP THANH TOÁN -->
            <div class="card border-0 shadow-sm p-4 bg-light">
                <h5 class="fw-bold mb-3">Tóm tắt thanh toán</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tiền phòng ({{ $calc['days'] ?? 1 }} ngày):</span>
                    <span class="fw-semibold">{{ format_currency($calc['roomTotal'] ?? 0) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tiền dịch vụ phát sinh:</span>
                    <span class="fw-semibold">{{ format_currency($calc['serviceTotal'] ?? 0) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-danger mb-0">TỔNG CỘNG:</h5>
                    <h3 class="fw-bold text-danger mb-0">{{ format_currency($calc['grandTotal'] ?? 0) }}</h3>
                </div>

                @if ($invoice)
                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge {{ $invoice->daThanhToan() ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $invoice->daThanhToan() ? 'ĐÃ THANH TOÁN' : 'CHƯA THANH TOÁN' }}
                            </span>
                        </div>
                        <a href="{{ route('admin.hoa-don.show', $invoice) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-receipt me-1"></i> Xem hóa đơn #{{ $invoice->MaHoaDon }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
