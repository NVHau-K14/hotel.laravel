@extends('admin.layouts.app')

@section('title', 'Báo cáo Thống kê')

@php
    $tongNam = array_sum($monthlyRevenue);
    $thangCaoNhat = $tongNam > 0 ? array_search(max($monthlyRevenue), $monthlyRevenue, true) : null;
@endphp

@section('content')
<div class="container admin-wide">
    <div class="page-head">
        <div>
            <h1>Báo cáo thống kê</h1>
            <p>Doanh thu tính trên các hóa đơn đã thanh toán.</p>
        </div>
        <form method="GET" action="{{ route('admin.thong-ke.index') }}" class="d-flex gap-2 align-items-center">
            <label class="text-nowrap mb-0" for="chon-nam">Năm báo cáo</label>
            <select id="chon-nam" name="year" class="form-select" onchange="this.form.submit()">
                {{-- 5 năm gần nhất --}}
                @for ($y = now()->year; $y >= now()->year - 4; $y--)
                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                @endfor
            </select>
            <noscript><button type="submit" class="btn btn-outline-secondary">Xem</button></noscript>
        </form>
    </div>

    <section class="kpi-strip mb-4" aria-label="Số liệu tổng hợp">
        <div class="kpi kpi-lead">
            <span class="kpi-label"><i class="fa-solid fa-coins" aria-hidden="true"></i>Doanh thu năm {{ $year }}</span>
            <span class="kpi-value is-money">{{ format_currency($tongNam) }}</span>
            <div class="kpi-hint">
                @if ($thangCaoNhat)
                    Cao nhất: tháng {{ $thangCaoNhat }}
                @else
                    Chưa có hóa đơn đã thanh toán
                @endif
            </div>
        </div>
        <div class="kpi">
            <span class="kpi-label"><i class="fa-solid fa-calendar-day" aria-hidden="true"></i>Tháng này</span>
            <span class="kpi-value is-money">{{ format_currency($summary['doanhThuThangNay']) }}</span>
        </div>
        <div class="kpi">
            <span class="kpi-label"><i class="fa-solid fa-sack-dollar" aria-hidden="true"></i>Tổng đã thu</span>
            <span class="kpi-value is-money">{{ format_currency($summary['tongDoanhThu']) }}</span>
        </div>
        <div class="kpi">
            <span class="kpi-label"><i class="fa-solid fa-user-check" aria-hidden="true"></i>Khách hàng</span>
            <span class="kpi-value">{{ $summary['tongKhachHang'] }}</span>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-xl-8">
            <section class="admin-panel p-4 h-100" aria-labelledby="bieu-do">
                <h2 class="panel-title mb-3" id="bieu-do">Doanh thu theo tháng, năm {{ $year }}</h2>
                <div style="position: relative; height: 340px;">
                    <canvas id="revenueChart" role="img" aria-label="Biểu đồ cột doanh thu 12 tháng năm {{ $year }}; số liệu chi tiết ở bảng bên cạnh"></canvas>
                </div>
            </section>
        </div>
        <div class="col-xl-4">
            <section class="admin-panel h-100" aria-labelledby="bang-thang">
                <div class="panel-head">
                    <h2 class="panel-title" id="bang-thang">Chi tiết 12 tháng</h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Tháng</th>
                                <th scope="col" class="text-end">Doanh thu</th>
                                <th scope="col" class="text-end">Tỷ trọng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monthlyRevenue as $thang => $doanhThu)
                                <tr>
                                    <th scope="row" class="fw-normal">Tháng {{ $thang }}</th>
                                    <td class="text-end {{ $doanhThu > 0 ? 'fw-semibold' : 'text-muted' }}">{{ format_currency($doanhThu) }}</td>
                                    <td class="text-end text-muted">{{ $tongNam > 0 ? number_format($doanhThu * 100 / $tongNam, 1, ',', '.') . '%' : '–' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        var dinhDang = new Intl.NumberFormat('vi-VN');
        var gonGon = new Intl.NumberFormat('vi-VN', { notation: 'compact', maximumFractionDigits: 1 });
        Chart.defaults.font.family = '"Be Vietnam Pro", system-ui, sans-serif';
        Chart.defaults.color = '#5c6661';
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
                datasets: [{
                    label: 'Doanh thu',
                    data: @json(array_values($monthlyRevenue)),
                    backgroundColor: '#16856b',
                    hoverBackgroundColor: '#1f4a41',
                    borderRadius: { topLeft: 4, topRight: 4 },
                    borderSkipped: 'bottom',
                    maxBarThickness: 36,
                    categoryPercentage: .7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : { duration: 500 },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#15201c',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            title: function (items) { return 'Tháng ' + (items[0].dataIndex + 1); },
                            label: function (item) { return dinhDang.format(item.parsed.y) + ' đ'; }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, border: { color: '#dcdfd8' } },
                    y: {
                        beginAtZero: true,
                        border: { display: false },
                        grid: { color: '#ebede8' },
                        ticks: { maxTicksLimit: 5, callback: function (v) { return gonGon.format(v) + ' đ'; } }
                    }
                }
            }
        });
    })();
</script>
@endpush
