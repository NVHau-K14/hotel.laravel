@extends('errors.khung')

@section('title', 'Không tìm thấy trang')
@section('code', '404')

@section('content')
    <h1>Không tìm thấy trang</h1>
    <p>Trang bạn yêu cầu không tồn tại hoặc đã bị xóa.</p>
    <a class="btn btn-primary" href="{{ url('/') }}">Về trang chủ</a>
@endsection
