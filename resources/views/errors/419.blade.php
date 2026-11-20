{{-- Hết hạn / sai mã CSRF --}}
@extends('errors.khung')

@section('code', '419')

@section('content')
    <h1>Không thể hoàn tất thao tác</h1>
    <p>Phiên biểu mẫu không hợp lệ. Vui lòng tải lại trang rồi thử lại.</p>
    <button type="button" class="btn btn-outline-secondary" onclick="history.back()">Quay lại trang trước</button>
@endsection
