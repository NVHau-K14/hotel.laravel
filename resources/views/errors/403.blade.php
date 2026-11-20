{{-- Trang 403 (Auth::requirePermission của bản gốc) --}}
<!doctype html>
<html lang="vi">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>403 - Truy cập bị từ chối</title>
<body>
<div style="text-align:center;margin-top:50px;font-family:sans-serif;">
    <h2>403 - Truy cập bị từ chối</h2>
    <p>{{ $exception->getMessage() ?: 'Bạn không có quyền thực hiện chức năng này.' }}</p>
    @if ($nhanVien = auth('admin')->user())
        <a href="{{ $nhanVien->trangDauTien() }}">Quay lại trang làm việc</a>
    @else
        <a href="{{ route('trang-chu') }}">Về trang chủ</a>
    @endif
</div>
</body>
</html>
