{{-- TẠM THỜI (Giai đoạn 3): trang chờ cho module chưa chuyển. Xóa khi xong Giai đoạn 4. --}}
@extends('admin.layouts.app')

@section('title', $tenChucNang)

@section('content')
<div class="container admin-wide my-4">
    <h1 class="fw-bold mb-4">{{ $tenChucNang }}</h1>
    <div class="alert alert-info">Chức năng này đang được chuyển sang Laravel.</div>
</div>
@endsection
