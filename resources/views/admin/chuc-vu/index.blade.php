@extends('admin.layouts.app')

@section('title', 'Quản lý Chức vụ & Phân quyền')

@section('content')
<div class="container admin-wide my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">Quản lý chức vụ & Phân quyền</h1>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        @foreach ($chucVus as $cv)
            <div class="col-md-6">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold text-primary mb-0">
                            <i class="fa-solid fa-id-badge me-2"></i>{{ $cv->TenChucVu }}
                        </h4>
                        <span class="badge bg-secondary">Mã CV: {{ $cv->MaChucVu }}</span>
                    </div>

                    <form method="POST" action="{{ route('admin.chuc-vu.quyen.update', $cv) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Danh sách quyền hạn:</label>
                            <div class="list-group">
                                @foreach ($allPermissions as $pCode => $pName)
                                    <label class="list-group-item d-flex align-items-center gap-2">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="permissions[]"
                                               value="{{ $pCode }}" @checked(in_array($pCode, $cv->permissions ?? []))>
                                        <span>
                                            <strong class="d-block small">{{ $pName }}</strong>
                                            <code class="text-muted small">{{ $pCode }}</code>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fa-solid fa-save me-1"></i> Lưu phân quyền cho {{ $cv->TenChucVu }}
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
