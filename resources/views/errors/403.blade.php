{{-- Trang 403 (Auth::requirePermission của bản gốc) --}}
@extends('errors.khung')

@section('title', '403 - Truy cập bị từ chối')
@section('code', '403')

@section('content')
    <h1>Truy cập bị từ chối</h1>
    <p>{{ $exception->getMessage() ?: 'Bạn không có quyền thực hiện chức năng này.' }}</p>
    @if ($nhanVien = auth('admin')->user())
        <a class="btn btn-primary" href="{{ $nhanVien->trangDauTien() }}">Quay lại trang làm việc</a>
    @else
        <a class="btn btn-primary" href="{{ route('trang-chu') }}">Về trang chủ</a>
    @endif
@endsection
