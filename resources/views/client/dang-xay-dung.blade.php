{{-- TẠM THỜI (Giai đoạn 3): trang chờ cho trang khách chưa chuyển. Xóa khi xong Giai đoạn 5. --}}
@extends('client.layouts.app')

@section('title', $tenTrang)

@section('content')
<main class="container my-4">
    <div class="mb-4 text-center">
        <h1 class="fw-bold">{{ $tenTrang }}</h1>
        <p class="text-muted">Trang này đang được chuyển sang Laravel.</p>
    </div>
</main>
@endsection
