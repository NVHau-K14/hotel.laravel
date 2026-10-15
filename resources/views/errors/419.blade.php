{{-- Hết hạn / sai mã CSRF --}}
@extends('errors.khung')

@section('content')
    <h1>Không thể hoàn tất thao tác</h1>
    <p>Phiên biểu mẫu không hợp lệ. Vui lòng tải lại trang rồi thử lại.</p>
    <button onclick="history.back()">Quay lại</button>
@endsection
