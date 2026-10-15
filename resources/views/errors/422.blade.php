{{-- Lỗi nghiệp vụ khi xem trang (GET). Gửi form lỗi thì quay lại form, xem bootstrap/app.php --}}
@extends('errors.khung')

@section('content')
    <h1>Không thể hoàn tất thao tác</h1>
    <p>{{ $thongBao ?? 'Không thể hoàn tất thao tác. Vui lòng thử lại hoặc liên hệ quản trị viên.' }}</p>
    <button onclick="history.back()">Quay lại</button>
@endsection
