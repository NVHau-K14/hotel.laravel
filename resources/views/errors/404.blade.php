@extends('errors.khung')

@section('title', 'Không tìm thấy trang')

@section('content')
    <h1>Không tìm thấy trang</h1>
    <p>Trang bạn yêu cầu không tồn tại hoặc đã bị xóa.</p>
    <a href="{{ url('/') }}">Về trang chủ</a>
@endsection
