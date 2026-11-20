@extends('admin.layouts.app')

@section('title', "Chi tiết đơn đặt phòng #{$item->MaCTDatPhong}")

@section('content')
@php
    $st = $item->TrangThai;
    $khach = $item->khachHang;
    $coTheSuaDichVu = in_array($st, ['DA_XAC_NHAN', 'DANG_SU_DUNG'], true);
    [$mauTrangThai, $chuTrangThai, $buocTiepTheo] = match ($st) {
        'CHO_ADMIN_DUYET' => ['warning', 'Chờ duyệt', 'Gọi khách xác nhận rồi duyệt đơn để giữ phòng.'],
        'DA_XAC_NHAN' => ['info', 'Đã xác nhận', 'Khi khách đến quầy, bấm nhận phòng.'],
        'DANG_SU_DUNG' => ['success', 'Đang ở', 'Ghi dịch vụ khách gọi thêm; khi khách rời đi, trả phòng để lập hóa đơn.'],
        'DA_HOAN_THANH' => ['success', 'Hoàn thành', 'Đơn đã trả phòng.'],
        'DA_HUY' => ['danger', 'Đã hủy', 'Đơn đã hủy, không còn thao tác.'],
        default => ['', $st, ''],
    };
@endphp
<div class="container admin-wide">
    <a href="{{ route('admin.dat-phong.index') }}" class="back-link">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Danh sách đặt phòng
    </a>

    <div class="page-head mt-1">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <h1 class="mb-0">Đơn đặt phòng #{{ $item->MaCTDatPhong }}</h1>
                <span class="status-badge {{ $mauTrangThai }}">{{ $chuTrangThai }}</span>
            </div>
            <p>Tạo ngày {{ format_date($item->NgayThucHien) }}. {{ $buocTiepTheo }}</p>
        </div>

        <div class="page-head-actions">
            @if (in_array($st, ['CHO_ADMIN_DUYET', 'DA_XAC_NHAN'], true))
                <form method="POST" action="{{ route('admin.dat-phong.huy', $item) }}" class="action-form" onsubmit="return confirm('Hủy đơn đặt phòng này?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Hủy đơn</button>
                </form>
            @endif

            @if ($st === 'CHO_ADMIN_DUYET')
                <form method="POST" action="{{ route('admin.dat-phong.duyet', $item) }}" class="action-form" onsubmit="return confirm('Duyệt đơn đặt phòng này?')">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check" aria-hidden="true"></i>Duyệt đơn
                    </button>
                </form>
            @endif

            @if ($st === 'DA_XAC_NHAN')
                <form method="POST" action="{{ route('admin.dat-phong.nhan-phong', $item) }}" class="action-form" onsubmit="return confirm('Khách nhận phòng (Check-in)?')">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-door-open" aria-hidden="true"></i>Nhận phòng
                    </button>
                </form>
            @endif

            @if ($st === 'DANG_SU_DUNG')
                <form method="POST" action="{{ route('admin.dat-phong.tra-phong', $item) }}" class="action-form" onsubmit="return confirm('Trả phòng và lập hóa đơn thanh toán?')">
                    @csrf
                    <button type="submit" class="btn btn-brass">
                        <i class="fa-solid fa-receipt" aria-hidden="true"></i>Trả phòng và lập hóa đơn
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-3" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Phòng và thời gian lưu trú --}}
            <section class="admin-panel p-4 mb-4" aria-labelledby="luu-tru">
                <h2 class="visually-hidden" id="luu-tru">Phòng và lưu trú</h2>
                <div class="d-flex flex-wrap align-items-center gap-4">
                    <span class="door-plate door-plate-lg">{{ $item->MaPhong }}</span>
                    <dl class="detail-list flex-grow-1 mb-0 row g-0">
                        <div class="col-sm-6 border-0 pe-sm-3"><dt>Loại phòng</dt><dd>{{ $item->phong?->loaiPhong?->TenLoaiPhong }}</dd></div>
                        <div class="col-sm-6 border-0"><dt>Đơn giá</dt><dd>{{ format_currency($item->GiaPhong) }} / ngày</dd></div>
                        <div class="col-sm-6 pe-sm-3"><dt>Nhận phòng</dt><dd>{{ format_date($item->NgayNhan) }}</dd></div>
                        <div class="col-sm-6"><dt>Trả phòng</dt><dd>{{ format_date($item->NgayTra) }}</dd></div>
                    </dl>
                </div>
            </section>

            {{-- Dịch vụ đã dùng --}}
            <section class="admin-panel mb-4" aria-labelledby="dich-vu">
                <div class="panel-head">
                    <h2 class="panel-title" id="dich-vu">Dịch vụ đã sử dụng</h2>
                    @if ($coTheSuaDichVu)
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#addServiceBox" aria-expanded="false" aria-controls="addServiceBox">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i>Gọi thêm dịch vụ
                        </button>
                    @endif
                </div>

                @if ($coTheSuaDichVu)
                <div class="collapse" id="addServiceBox">
                    <form method="POST" action="{{ route('admin.dat-phong.dich-vu.store', $item) }}" class="row g-2 align-items-end p-3 border-bottom bg-light">
                        @csrf
                        <div class="col-sm-6">
                            <label class="form-label" for="chon-dich-vu">Dịch vụ</label>
                            <select id="chon-dich-vu" name="serviceId" class="form-select" required>
                                <option value="">Chọn dịch vụ</option>
                                @foreach ($allServices as $s)
                                    <option value="{{ $s->MaDichVu }}">{{ $s->TenDichVu }} ({{ format_currency($s->GiaDichVu) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label" for="so-luong">Số lượng</label>
                            <input type="number" id="so-luong" name="quantity" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-primary w-100">Thêm vào đơn</button>
                        </div>
                    </form>
                </div>
                @endif

                {{-- Mảng dịch vụ: lấy từ CTDICHVU hoặc từ bản chụp hóa đơn đã thanh toán --}}
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Dịch vụ</th>
                                <th scope="col" class="text-end">Đơn giá</th>
                                <th scope="col" class="text-center">Số lượng</th>
                                <th scope="col" class="text-end">Thành tiền</th>
                                @if ($coTheSuaDichVu)
                                    <th scope="col"><span class="visually-hidden">Xóa</span></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($calc['services'] ?? [] as $sv)
                                <tr>
                                    <td class="fw-semibold">{{ $sv['TenDichVu'] }}</td>
                                    <td class="text-end">{{ format_currency($sv['GiaDichVu']) }}</td>
                                    <td class="text-center">{{ $sv['SoLuong'] }}</td>
                                    <td class="text-end fw-semibold">{{ format_currency($sv['TongTienDichVu']) }}</td>
                                    @if ($coTheSuaDichVu)
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('admin.dat-phong.dich-vu.destroy', [$item, $sv['MaDichVu']]) }}" class="action-form" onsubmit="return confirm('Xóa dịch vụ này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Xóa {{ $sv['TenDichVu'] }}">
                                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">Chưa có dịch vụ nào cho đơn này.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            {{-- Khách hàng --}}
            <section class="admin-panel p-4 mb-4" aria-labelledby="khach-hang">
                <h2 class="panel-title mb-2" id="khach-hang">Khách hàng</h2>
                <dl class="detail-list mb-0">
                    <div><dt>Họ và tên</dt><dd>{{ $khach?->HoTen }}</dd></div>
                    <div><dt>Điện thoại</dt><dd><a href="tel:{{ $khach?->DienThoai }}">{{ $khach?->DienThoai }}</a></dd></div>
                    <div><dt>Email</dt><dd>{{ $khach?->Email ?? 'Chưa có' }}</dd></div>
                    <div><dt>CMND/CCCD</dt><dd>{{ $khach?->CMND ?? 'Chưa có' }}</dd></div>
                    <div><dt>Địa chỉ</dt><dd>{{ $khach?->DiaChi ?? 'Chưa có' }}</dd></div>
                </dl>
            </section>

            {{-- Thanh toán --}}
            <section class="admin-panel p-4" aria-labelledby="thanh-toan">
                <h2 class="panel-title mb-1" id="thanh-toan">Tóm tắt thanh toán</h2>
                <dl class="summary-list">
                    <div><dt>Tiền phòng ({{ $calc['days'] ?? 1 }} ngày)</dt><dd>{{ format_currency($calc['roomTotal'] ?? 0) }}</dd></div>
                    <div><dt>Dịch vụ phát sinh</dt><dd>{{ format_currency($calc['serviceTotal'] ?? 0) }}</dd></div>
                    <div class="is-total"><dt>Tổng cộng</dt><dd>{{ format_currency($calc['grandTotal'] ?? 0) }}</dd></div>
                </dl>

                @if ($invoice)
                    <div class="mt-3 pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span class="status-badge {{ $invoice->daThanhToan() ? 'success' : 'warning' }}">
                            {{ $invoice->daThanhToan() ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                        </span>
                        <a href="{{ route('admin.hoa-don.show', $invoice) }}" class="btn btn-sm btn-primary">Xem hóa đơn #{{ $invoice->MaHoaDon }}</a>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
