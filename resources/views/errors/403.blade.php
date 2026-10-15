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
    <a href="{{ route('admin.dashboard') }}">Quay lại bảng điều khiển</a>
</div>
</body>
</html>
