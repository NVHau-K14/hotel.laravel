{{-- Gọi thao tác đổi dữ liệu bằng GET --}}
@extends('errors.khung')

@section('content')
    <h1>Không thể hoàn tất thao tác</h1>
    <p>Thao tác này cần được gửi từ biểu mẫu.</p>
    <button onclick="history.back()">Quay lại</button>
@endsection
