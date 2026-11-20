{{-- Gọi thao tác đổi dữ liệu bằng GET --}}
@extends('errors.khung')

@section('code', '405')

@section('content')
    <h1>Không thể hoàn tất thao tác</h1>
    <p>Thao tác này cần được gửi từ biểu mẫu.</p>
    <button type="button" class="btn btn-outline-secondary" onclick="history.back()">Quay lại trang trước</button>
@endsection
