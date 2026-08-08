@php
    try {
        $s = \ME\Accounts\Http\Controllers\AccountsDashboardController::stats();
    } catch (\Throwable $e) {
        $s = [
            'accounts' => collect(), 'totalAccounts' => 0, 'totalBalance' => 0,
            'todayExpenses' => 0, 'monthExpenses' => 0,
            'pendingIouCount' => 0, 'pendingIouAmount' => 0,
            'totalCreditorsDue' => 0,
            'last30' => collect(), 'expenseByCategory' => collect(),
            'recentExpenses' => collect(), 'recentIous' => collect(),
        ];
    }

    $widgetId = 'ac_widget_' . uniqid();

    $trendLabels = $s['last30']->pluck('date')->toJson();
    $trendData = $s['last30']->pluck('amount')->toJson();
    $catLabels = $s['expenseByCategory']->pluck('name')->toJson();
    $catData = $s['expenseByCategory']->pluck('amount')->toJson();
    $acctLabels = $s['accounts']->take(6)->pluck('name')->toJson();
    $acctData = $s['accounts']->take(6)->pluck('current_balance')->toJson();

    $statCardLinks = [
        'accounts' => \Illuminate\Support\Facades\Route::has('admin.accounts.index') ? route('admin.accounts.index') : null,
        'expenses' => \Illuminate\Support\Facades\Route::has('admin.expenses.index') ? route('admin.expenses.index') : null,
        'ious' => \Illuminate\Support\Facades\Route::has('admin.ious.index') ? route('admin.ious.index') : null,
        'creditors' => \Illuminate\Support\Facades\Route::has('admin.creditors.index') ? route('admin.creditors.index') : null,
        'deposits' => \Illuminate\Support\Facades\Route::has('admin.deposits.index') ? route('admin.deposits.index') : null,
        'withdrawals' => \Illuminate\Support\Facades\Route::has('admin.withdrawals.index') ? route('admin.withdrawals.index') : null,
        'reports' => \Illuminate\Support\Facades\Route::has('admin.expenses.reports') ? route('admin.expenses.reports') : null,
        'statement' => \Illuminate\Support\Facades\Route::has('admin.accounts.statement') ? route('admin.accounts.statement') : null,
        'dashboard' => \Illuminate\Support\Facades\Route::has('admin.accountsDashboard') ? route('admin.accountsDashboard') : null,
    ];
@endphp

<style>
    .ac-stat-card { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:18px 20px; transition:.2s; height:100%; }
    .ac-stat-card:hover { transform:translateY(-3px); box-shadow:0 8px 20px rgba(0,0,0,0.10); }
    .ac-stat-icon { width:46px; height:46px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; color:#fff; margin-bottom:10px; }
    .ac-stat-val { font-size:22px; font-weight:700; color:#1e293b; margin:0; }
    .ac-stat-lbl { font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#94a3b8; font-weight:600; }
    .ac-stat-bar { height:5px; border-radius:3px; background:#f1f5f9; margin-top:10px; overflow:hidden; }
    .ac-stat-bar > span { display:block; height:100%; border-radius:3px; }
    .ac-section-title { font-size:15px; font-weight:700; color:#1e293b; border-left:4px solid #6366f1; padding-left:10px; margin-bottom:14px; }
    .ac-chart-card { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:18px 20px; height:100%; }
    .ac-quick-btn { display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:8px; background:#f8fafc; color:#334155; font-weight:600; font-size:13px; text-decoration:none; margin-bottom:8px; transition:.2s; }
    .ac-quick-btn:hover { background:#eef2ff; color:#4f46e5; }
    .ac-recent-table th { font-size:11px; text-transform:uppercase; color:#94a3b8; border-top:0; }
    .ac-recent-table td { font-size:13px; vertical-align:middle; }
    .ac-badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="ac-section-title" style="border:0; padding:0; font-size:18px;">
        <i class="fa-solid fa-wallet"></i> Accounts Overview
    </div>
    @if($statCardLinks['dashboard'] && !request()->is('admin/v2'))
    <a href="{{ $statCardLinks['dashboard'] }}" class="btn btn-outline-primary btn-sm">Accounts Dashboard</a>
    @endif
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-3">
    <div class="col-lg col-md-6 col-6">
        @php $href = $statCardLinks['accounts']; @endphp
        <{{ $href ? 'a' : 'div' }} @if($href) href="{{ $href }}" @endif class="ac-stat-card d-block text-decoration-none">
            <div class="ac-stat-icon" style="background:#6366f1;"><i class="fa-solid fa-building-columns"></i></div>
            <p class="ac-stat-val">{{ priceFullFormat($s['totalBalance']) }}</p>
            <div class="ac-stat-lbl">Total Balance ({{ $s['totalAccounts'] }} Accounts)</div>
        </{{ $href ? 'a' : 'div' }}>
    </div>
    <div class="col-lg col-md-6 col-6">
        @php $href = $statCardLinks['expenses']; @endphp
        <{{ $href ? 'a' : 'div' }} @if($href) href="{{ $href }}" @endif class="ac-stat-card d-block text-decoration-none">
            <div class="ac-stat-icon" style="background:#f43f5e;"><i class="fa-solid fa-calendar-day"></i></div>
            <p class="ac-stat-val">{{ priceFullFormat($s['todayExpenses']) }}</p>
            <div class="ac-stat-lbl">Today's Expenses</div>
        </{{ $href ? 'a' : 'div' }}>
    </div>
    <div class="col-lg col-md-6 col-6">
        @php $href = $statCardLinks['expenses']; @endphp
        <{{ $href ? 'a' : 'div' }} @if($href) href="{{ $href }}" @endif class="ac-stat-card d-block text-decoration-none">
            <div class="ac-stat-icon" style="background:#f59e0b;"><i class="fa-solid fa-calendar"></i></div>
            <p class="ac-stat-val">{{ priceFullFormat($s['monthExpenses']) }}</p>
            <div class="ac-stat-lbl">This Month Expenses</div>
        </{{ $href ? 'a' : 'div' }}>
    </div>
    <div class="col-lg col-md-6 col-6">
        @php $href = $statCardLinks['ious']; @endphp
        <{{ $href ? 'a' : 'div' }} @if($href) href="{{ $href }}" @endif class="ac-stat-card d-block text-decoration-none">
            <div class="ac-stat-icon" style="background:#a855f7;"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            <p class="ac-stat-val">{{ priceFullFormat($s['pendingIouAmount']) }}</p>
            <div class="ac-stat-lbl">Pending I.O.U ({{ $s['pendingIouCount'] }})</div>
        </{{ $href ? 'a' : 'div' }}>
    </div>
    <div class="col-lg col-md-6 col-6">
        @php $href = $statCardLinks['creditors']; @endphp
        <{{ $href ? 'a' : 'div' }} @if($href) href="{{ $href }}" @endif class="ac-stat-card d-block text-decoration-none">
            <div class="ac-stat-icon" style="background:#10b981;"><i class="fa-solid fa-people-carry-box"></i></div>
            <p class="ac-stat-val">{{ priceFullFormat($s['totalCreditorsDue']) }}</p>
            <div class="ac-stat-lbl">Creditors Due</div>
        </{{ $href ? 'a' : 'div' }}>
    </div>
</div>

{{-- Charts Row 1 --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="ac-chart-card">
            <div class="ac-section-title">Expenses — Last 30 Days</div>
            <div id="{{ $widgetId }}_trend"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ac-chart-card">
            <div class="ac-section-title">Expense By Category (This Month)</div>
            @if($s['expenseByCategory']->count())
            <div id="{{ $widgetId }}_cat"></div>
            @else
            <p class="text-muted small text-center mt-5">No expense data this month</p>
            @endif
        </div>
    </div>
</div>

{{-- Charts Row 2 --}}
<div class="row g-3 mb-3">
    <div class="col-lg-5">
        <div class="ac-chart-card">
            <div class="ac-section-title">Top Accounts By Balance</div>
            @if($s['accounts']->count())
            <div id="{{ $widgetId }}_accts"></div>
            @else
            <p class="text-muted small text-center mt-5">No accounts found</p>
            @endif
        </div>
    </div>
    <div class="col-lg-4">
        <div class="ac-chart-card">
            <div class="ac-section-title">Quick Links</div>
            @if($statCardLinks['expenses'])<a href="{{$statCardLinks['expenses']}}" class="ac-quick-btn"><i class="fa-solid fa-receipt"></i> Expenses</a>@endif
            @if($statCardLinks['ious'])<a href="{{$statCardLinks['ious']}}" class="ac-quick-btn"><i class="fa-solid fa-hand-holding-dollar"></i> I.O.U</a>@endif
            @if($statCardLinks['deposits'])<a href="{{$statCardLinks['deposits']}}" class="ac-quick-btn"><i class="fa-solid fa-money-bill-transfer"></i> Deposits</a>@endif
            @if($statCardLinks['withdrawals'])<a href="{{$statCardLinks['withdrawals']}}" class="ac-quick-btn"><i class="fa-solid fa-money-bill-wave"></i> Withdrawals</a>@endif
            @if($statCardLinks['creditors'])<a href="{{$statCardLinks['creditors']}}" class="ac-quick-btn"><i class="fa-solid fa-people-carry-box"></i> Creditors</a>@endif
            @if($statCardLinks['statement'])<a href="{{$statCardLinks['statement']}}" class="ac-quick-btn"><i class="fa-solid fa-file-invoice"></i> Account Statement</a>@endif
            @if($statCardLinks['reports'])<a href="{{$statCardLinks['reports']}}" class="ac-quick-btn"><i class="fa-solid fa-chart-line"></i> Expense Reports</a>@endif
        </div>
    </div>
    <div class="col-lg-3">
        <div class="ac-chart-card">
            <div class="ac-section-title">Snapshot</div>
            <p class="mb-2 small"><i class="fa-solid fa-circle text-primary" style="font-size:8px;"></i> Accounts: <b>{{ $s['totalAccounts'] }}</b></p>
            <p class="mb-2 small"><i class="fa-solid fa-circle text-danger" style="font-size:8px;"></i> Pending I.O.U: <b>{{ $s['pendingIouCount'] }}</b></p>
            <p class="mb-2 small"><i class="fa-solid fa-circle text-warning" style="font-size:8px;"></i> Creditors Due: <b>{{ priceFullFormat($s['totalCreditorsDue']) }}</b></p>
            <p class="mb-0 small"><i class="fa-solid fa-circle text-success" style="font-size:8px;"></i> Total Balance: <b>{{ priceFullFormat($s['totalBalance']) }}</b></p>
        </div>
    </div>
</div>

{{-- Recent Tables --}}
<div class="row g-3">
    <div class="col-lg-6">
        <div class="ac-chart-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="ac-section-title mb-0">Recent Expenses</div>
                @if($statCardLinks['expenses'])<a href="{{$statCardLinks['expenses']}}" class="small">View All →</a>@endif
            </div>
            <div class="table-responsive">
                <table class="table ac-recent-table">
                    <thead><tr><th>Date</th><th>Company</th><th>Category</th><th class="text-right">Amount</th></tr></thead>
                    <tbody>
                        @forelse($s['recentExpenses'] as $exp)
                        <tr>
                            <td>{{ $exp->transaction_date->format('d M') }}</td>
                            <td>{{ $exp->company_name ?: '-' }}</td>
                            <td>{{ $exp->category?->name ?: '-' }}</td>
                            <td class="text-right">{{ priceFullFormat($exp->amount) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No recent expenses</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ac-chart-card">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="ac-section-title mb-0">Recent Pending I.O.U</div>
                @if($statCardLinks['ious'])<a href="{{$statCardLinks['ious']}}" class="small">View All →</a>@endif
            </div>
            <div class="table-responsive">
                <table class="table ac-recent-table">
                    <thead><tr><th>Date</th><th>Receiver</th><th>Account</th><th class="text-right">Amount</th></tr></thead>
                    <tbody>
                        @forelse($s['recentIous'] as $iou)
                        <tr>
                            <td>{{ $iou->transaction_date->format('d M') }}</td>
                            <td>{{ $iou->receiver_name ?: '-' }}</td>
                            <td>{{ $iou->account?->name ?: '-' }}</td>
                            <td class="text-right">{{ priceFullFormat($iou->amount) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">No pending I.O.U</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function renderAccountsCharts() {
        var trendEl = document.querySelector('#{{ $widgetId }}_trend');
        if (trendEl) {
            new ApexCharts(trendEl, {
                chart: { type: 'area', height: 260, toolbar: { show: false } },
                series: [{ name: 'Expenses', data: {!! $trendData !!} }],
                xaxis: { categories: {!! $trendLabels !!}, labels: { rotate: -45, style: { fontSize: '10px' } } },
                colors: ['#6366f1'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } }
            }).render();
        }

        var catEl = document.querySelector('#{{ $widgetId }}_cat');
        if (catEl) {
            new ApexCharts(catEl, {
                chart: { type: 'donut', height: 260 },
                series: {!! $catData !!},
                labels: {!! $catLabels !!},
                colors: ['#6366f1', '#10b981', '#f43f5e', '#f59e0b', '#a855f7', '#0ea5e9'],
                legend: { position: 'bottom', fontSize: '11px' }
            }).render();
        }

        var acctEl = document.querySelector('#{{ $widgetId }}_accts');
        if (acctEl) {
            new ApexCharts(acctEl, {
                chart: { type: 'bar', height: 260, toolbar: { show: false } },
                plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
                series: [{ name: 'Balance', data: {!! $acctData !!} }],
                xaxis: { categories: {!! $acctLabels !!} },
                colors: ['#10b981'],
                dataLabels: { enabled: false }
            }).render();
        }
    }

    if (typeof ApexCharts === 'undefined') {
        var script = document.createElement('script');
        script.src = '{{ asset("admin/assets/js/apexcharts/apexcharts.min.js") }}';
        script.onload = renderAccountsCharts;
        document.head.appendChild(script);
    } else {
        renderAccountsCharts();
    }
})();
</script>
