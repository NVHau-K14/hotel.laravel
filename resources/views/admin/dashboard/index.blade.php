{{-- Giai đoạn 3: khung trang. Thẻ số liệu và đơn chờ duyệt làm ở Giai đoạn 4E. --}}
@extends('admin.layouts.app')

@section('title', 'Bảng điều khiển - Quản trị Khách sạn')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1">Bảng điều khiển</h1>
            <p class="text-muted mb-0">Xin chào, {{ auth('admin')->user()->TenNhanVien ?? 'Admin' }}! Dưới đây là tổng quan hoạt động khách sạn.</p>
        </div>
        <div>
            <span class="badge bg-primary fs-6 p-2">
                <i class="fa-regular fa-calendar-days me-1"></i> Hôm nay: {{ now()->format('d/m/Y') }}
            </span>
        </div>
    </div>
</div>
@endsection
