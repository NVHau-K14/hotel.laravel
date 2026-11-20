@extends('admin.layouts.app')

@section('title', 'Quản lý Chức vụ & Phân quyền')

@section('content')
<div class="container admin-wide">
    <div class="page-head">
        <div>
            <h1>Quản lý chức vụ & Phân quyền</h1>
            <p>Mỗi chức vụ có một bộ quyền; nhân viên nhận quyền theo chức vụ của mình.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
    @endif

    <div class="row g-4">
        @foreach ($chucVus as $cv)
            <div class="col-md-6">
                <section class="admin-panel p-4 h-100" aria-labelledby="chuc-vu-{{ $cv->MaChucVu }}">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <h2 class="h3 mb-0" id="chuc-vu-{{ $cv->MaChucVu }}">{{ $cv->TenChucVu }}</h2>
                        <span class="code-badge">Mã {{ $cv->MaChucVu }}</span>
                    </div>

                    <form method="POST" action="{{ route('admin.chuc-vu.quyen.update', $cv) }}">
                        @csrf
                        @method('PUT')
                        <fieldset class="mb-3">
                            <legend class="form-label">Quyền được cấp</legend>
                            <div class="permission-grid">
                                @foreach ($allPermissions as $pCode => $pName)
                                    <label class="permission-option">
                                        <input class="form-check-input flex-shrink-0" type="checkbox" name="permissions[]"
                                               value="{{ $pCode }}" @checked(in_array($pCode, $cv->permissions ?? []))>
                                        <span>
                                            <strong class="d-block small">{{ $pName }}</strong>
                                            <code class="text-muted small">{{ $pCode }}</code>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <button type="submit" class="btn btn-primary w-100">
                            Lưu quyền cho {{ $cv->TenChucVu }}
                        </button>
                    </form>
                </section>
            </div>
        @endforeach
    </div>
</div>
@endsection
