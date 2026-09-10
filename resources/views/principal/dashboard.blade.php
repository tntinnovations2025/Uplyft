@extends('principal.layouts.app')
@section('title', 'Executive Command Center — Principal Dashboard')
@section('breadcrumb', 'Executive Analytics & Governance')

@section('content')
<!-- Include Chart.js for High-Fidelity Interactive Analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ── Ink & Amber Executive Dashboard Styling System ── */
.exec-header-card {
    background: #F9F8F5;
    border: 1px solid #E1DFD7;
    border-radius: 18px;
    padding: 20px 24px;
    margin-bottom: 20px;
    box-shadow: none;
    position: relative;
    overflow: hidden;
}

.exec-header-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #D48A2E;
}

.exec-kpi-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

@media (max-width: 1280px) {
    .exec-kpi-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    .exec-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .exec-kpi-grid { grid-template-columns: 1fr; }
}

.exec-kpi-card {
    background: #F9F8F5;
    border: 1px solid #E1DFD7;
    border-radius: 16px;
    padding: 16px 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: none;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
}

.exec-kpi-card:hover {
    transform: translateY(-2px);
    border-color: #D3D0C5;
    box-shadow: none;
}

.exec-kpi-card.clickable {
    cursor: pointer;
}

.exec-kpi-card.clickable:active {
    transform: translateY(0px) scale(0.985);
    transition-duration: 0.1s;
}

.exec-kpi-card .kpi-click-hint {
    position: absolute;
    bottom: 6px;
    right: 8px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #A19E92;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.exec-kpi-card.clickable:hover .kpi-click-hint {
    opacity: 1;
}

.exec-card-glass {
    background: #F9F8F5;
    border: 1px solid #E1DFD7;
    border-radius: 16px;
    padding: 20px;
    box-shadow: none;
    transition: all 0.2s ease;
}

.exec-card-glass:hover {
    border-color: #D3D0C5;
    box-shadow: none;
}

.exec-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.2px;
}

.exec-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.exec-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
}

.exec-table th {
    padding: 10px 12px;
    font-size: 10.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #68665D;
    border-bottom: 1px solid #E1DFD7;
    font-weight: 700;
    background: #F4F3EE;
    font-family: 'Manrope', sans-serif;
    white-space: nowrap;
}

.exec-table td {
    padding: 10px 12px;
    font-size: 11.5px;
    border-bottom: 1px solid #EAE8E1;
    color: #1B1A17;
    background: #F9F8F5;
    vertical-align: middle;
}

.exec-table tr:hover td {
    background: #F2EFEB;
}
</style>

<!-- ── COMMAND CENTER HEADER BAR (With Embedded Fee Recovery Donut Graph) ── -->
<div class="exec-header-card">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px">
        
        <!-- Left Column: Identity & Actions -->
        <div style="flex:1;min-width:320px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap">
                <span class="exec-badge-pill" style="background:#F8E9D3;color:#8A5A10;border:1px solid #E8CEAA">
                    <x-icon name="crown" class="w-3.5 h-3.5 mr-1 text-amber-700" /> {{ ucfirst($institute?->subscription_tier ?? 'Enterprise') }} Edition
                </span>
                <span class="exec-badge-pill" style="background:#E3EFE2;color:#2E6E42;border:1px solid #C7DEC5">
                    <x-icon name="bolt" class="w-3.5 h-3.5 mr-1 text-emerald-700" /> ACTIVE: {{ $activeTerm ? $activeTerm->name : 'No Term' }}
                </span>
                <span style="font-size:12px;color:#68665D;font-weight:600;display:inline-flex;align-items:center;gap:5px">
                    <x-icon name="building-columns" class="w-4 h-4 text-stone-700" /> <strong>{{ $institute?->name ?? 'Institutional Campus' }}</strong>
                </span>
            </div>
            <h1 style="font-family:'Manrope',sans-serif;font-size:24px;font-weight:800;letter-spacing:-0.5px;color:#1B1A17;margin:0;line-height:1.2">
                Welcome back, {{ $user->first_name ?? auth()->user()->first_name }}
            </h1>
            <p style="color:#68665D;font-size:12.5px;margin-top:4px;font-weight:500;margin-bottom:12px">
                Executive Command Center — Real-time institutional diagnostics: financial cashflow trends, faculty risk monitoring &amp; academic progress.
            </p>

            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                <div style="padding-right:12px;border-right:1px solid #E1DFD7">
                    <div style="font-size:10px;text-transform:uppercase;color:#A19E92;font-weight:800;letter-spacing:0.5px">Today's Date</div>
                    <div style="font-size:13px;font-weight:800;color:#1B1A17">{{ now()->format('D, d M Y') }}</div>
                </div>
                <a href="{{ route('principal.invoices.index') }}" class="btn btn-secondary btn-sm" style="padding:8px 14px;font-size:12px">
                    <span><x-icon name="receipt" class="w-3.5 h-3.5" /></span> Fee Ledger
                </a>
                <a href="{{ route('principal.timetables.index') }}" class="btn btn-primary btn-sm" style="padding:8px 14px;font-size:12px">
                    <span><x-icon name="calendar-days" class="w-3.5 h-3.5" /></span> Timetable Matrix
                </a>
            </div>
        </div>

        <!-- Right Column: Embedded Fee Recovery Donut Graph (Replaced Telemetry Box) -->
        <div style="background:#F9F8F5;border:1px solid #E1DFD7;border-radius:16px;padding:12px 18px;display:flex;align-items:center;gap:18px;min-width:320px">
            
            <!-- Donut Chart Container -->
            <div style="position:relative;width:115px;height:115px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <canvas id="recoveryDonutChart"></canvas>
                <div style="position:absolute;text-align:center">
                    <div style="font-family:'Manrope',sans-serif;font-size:19px;font-weight:800;color:#1B1A17">{{ $feeRecoveryRate }}%</div>
                    <div style="font-size:9px;text-transform:uppercase;color:#68665D;font-weight:700">Recovery</div>
                </div>
            </div>

            <!-- Fee Recovery Legend & Breakdown -->
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                    <strong style="font-family:'Manrope',sans-serif;font-size:13.5px;color:#1B1A17">Fee Recovery &amp; Arrears</strong>
                    <a href="{{ route('principal.invoices.index') }}" style="font-size:11px;color:#8A5A10;font-weight:700;text-decoration:none">Manage &rarr;</a>
                </div>
                <div style="font-size:11px;color:#68665D;margin-bottom:6px">
                    Total billing: <strong>PKR {{ number_format($totalInvoiced) }}</strong>
                </div>

                <div style="display:flex;flex-direction:column;gap:3px">
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:11.5px">
                        <span style="display:flex;align-items:center;gap:4px;color:#1B1A17"><span style="width:6px;height:6px;border-radius:50%;background:#2E6E42"></span> Paid</span>
                        <strong style="color:#1B1A17">PKR {{ number_format($totalPaid) }}</strong>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:11.5px">
                        <span style="display:flex;align-items:center;gap:4px;color:#1B1A17"><span style="width:6px;height:6px;border-radius:50%;background:#D48A2E"></span> Pending</span>
                        <strong style="color:#8A5A10">PKR {{ number_format($totalPending) }}</strong>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;font-size:11.5px">
                        <span style="display:flex;align-items:center;gap:4px;color:#A2412C;font-weight:700"><span style="width:6px;height:6px;border-radius:50%;background:#A2412C"></span> Overdue</span>
                        <strong style="color:#A2412C">PKR {{ number_format($totalOverdue) }}</strong>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

@if(!$activeTerm)
<div class="alert alert-warning" style="margin-bottom:18px;padding:12px 18px;border-radius:12px;background:#F8E9D3;border:1px solid #E8CEAA">
    <div style="display:flex;align-items:center;gap:12px">
        <span style="font-size:18px"><x-icon name="triangle-exclamation" class="w-5 h-5 text-amber-800" /></span>
        <div>
            <strong style="font-size:13.5px;color:#8A5A10">Active Academic Session Required</strong>
            <p style="font-size:12px;margin-top:2px;color:#8A5A10">Please configure and activate an academic term (e.g. 2025–2026) to synchronize automatic timetable allocations and attendance tracking.</p>
        </div>
    </div>
    <a href="{{ route('principal.academic-terms.index') }}" class="btn btn-primary btn-sm" style="white-space:nowrap;padding:8px 18px;font-size:12.5px">
        <span><x-icon name="arrow-trend-up" class="w-3.5 h-3.5 mr-1" /></span> <span>Take me there</span> <span>&rarr;</span>
    </a>
</div>
@endif

<!-- ── TOP 5 EXECUTIVE KPI METRICS (PERFECT 5-COLUMN EDGE-TO-EDGE GRID) ── -->
<div class="exec-kpi-grid">
    
    <!-- KPI 1: Financial Collections YTD -->
    <div id="kpi-finance" class="exec-kpi-card clickable" style="border-top:3px solid #D48A2E" onclick="document.getElementById('section-finance').scrollIntoView({behavior:'smooth',block:'start'})">
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:10.5px;color:#68665D;text-transform:uppercase;font-weight:800;letter-spacing:0.5px">Total Fee Inflow</span>
                <div class="exec-icon-box" style="background:#F8E9D3;color:#8A5A10">
                    <x-icon name="receipt" class="w-4 h-4" />
                </div>
            </div>
            <div style="font-family:'Manrope',sans-serif;font-size:22px;font-weight:800;color:#1B1A17;letter-spacing:-0.5px;margin-bottom:4px">
                PKR {{ number_format($totalPaid) }}
            </div>
        </div>
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid #f1f5f9;font-size:11px;color:#64748b">
                <span>Recovery Rate</span>
                <span class="exec-badge-pill" style="background:#ecfdf5;color:#047857;padding:2px 6px">
                    {{ $feeRecoveryRate }}% collected
                </span>
            </div>
        </div>
        <span class="kpi-click-hint">View Details &#8595;</span>
    </div>

    <!-- KPI 2: Upcoming / Due Fee -->
    <div id="kpi-upcoming" class="exec-kpi-card clickable" style="border-top:3px solid #D48A2E" onclick="document.getElementById('section-invoices').scrollIntoView({behavior:'smooth',block:'start'})">
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:10.5px;color:#68665D;text-transform:uppercase;font-weight:800;letter-spacing:0.5px">Upcoming / Due Fee</span>
                <div class="exec-icon-box" style="background:#F8E9D3;color:#8A5A10">
                    <x-icon name="hourglass-half" class="w-4 h-4" />
                </div>
            </div>
            <div style="font-family:'Manrope',sans-serif;font-size:22px;font-weight:800;color:#8A5A10;letter-spacing:-0.5px;margin-bottom:4px">
                PKR {{ number_format($upcomingFeeAmount) }}
            </div>
        </div>
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid #EAE8E1;font-size:11px;color:#68665D">
                <span>Due Date</span>
                <span class="exec-badge-pill" style="background:#F8E9D3;color:#8A5A10;padding:2px 6px">
                    {{ Carbon\Carbon::parse($upcomingDueDate)->format('d M Y') }} ({{ $daysLeft }}d left)
                </span>
            </div>
        </div>
        <span class="kpi-click-hint">View Details &#8595;</span>
    </div>

    <!-- KPI 3: Academic & Attendance Warnings -->
    <div id="kpi-warnings" class="exec-kpi-card clickable" style="border-top:3px solid {{ $academicWarningsCount > 0 ? '#A2412C' : '#2E6E42' }}" onclick="document.getElementById('section-faculty-risk').scrollIntoView({behavior:'smooth',block:'start'})">
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:10.5px;color:#68665D;text-transform:uppercase;font-weight:800;letter-spacing:0.5px">Risk &amp; Warnings</span>
                <div class="exec-icon-box" style="background:{{ $academicWarningsCount > 0 ? '#F6E4E1' : '#E3EFE2' }};color:{{ $academicWarningsCount > 0 ? '#A2412C' : '#2E6E42' }}">
                    @if ($academicWarningsCount > 0)
                        <x-icon name="triangle-exclamation" class="w-4 h-4" />
                    @else
                        <x-icon name="check" class="w-4 h-4" />
                    @endif
                </div>
            </div>
            <div style="font-family:'Manrope',sans-serif;font-size:22px;font-weight:800;color:{{ $academicWarningsCount > 0 ? '#A2412C' : '#2E6E42' }};letter-spacing:-0.5px;margin-bottom:4px">
                {{ $academicWarningsCount }}
            </div>
        </div>
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid #EAE8E1;font-size:11px;color:#68665D">
                <span>Faculty Risk</span>
                <span class="exec-badge-pill" style="background:{{ $criticalRiskCount > 0 ? '#F6E4E1' : '#E3EFE2' }};color:{{ $criticalRiskCount > 0 ? '#A2412C' : '#2E6E42' }};padding:2px 6px">
                    {{ $criticalRiskCount }} Teacher(s) &lt; {{ round($minAttThreshold) }}%
                </span>
            </div>
        </div>
        <span class="kpi-click-hint">View Details &#8595;</span>
    </div>

    <!-- KPI 4: Faculty Attendance Rate -->
    <div id="kpi-faculty" class="exec-kpi-card clickable" style="border-top:3px solid #2E6E42" onclick="document.getElementById('section-faculty-risk').scrollIntoView({behavior:'smooth',block:'start'})">
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:10.5px;color:#68665D;text-transform:uppercase;font-weight:800;letter-spacing:0.5px">Faculty Presence</span>
                <div class="exec-icon-box" style="background:#E3EFE2;color:#2E6E42">
                    <x-icon name="chalkboard-user" class="w-4 h-4" />
                </div>
            </div>
            <div style="font-family:'Manrope',sans-serif;font-size:22px;font-weight:800;color:{{ $overallTeacherAttendance >= 85 ? '#2E6E42' : '#8A5A10' }};letter-spacing:-0.5px;margin-bottom:4px">
                {{ $overallTeacherAttendance }}%
            </div>
        </div>
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid #EAE8E1;font-size:11px;color:#68665D">
                <span>Active Staff</span>
                <strong style="color:#1B1A17">{{ count($facultyList) }} Staff Members</strong>
            </div>
        </div>
        <span class="kpi-click-hint">View Details &#8595;</span>
    </div>

    <!-- KPI 5: Enrolled Student Strength -->
    <div id="kpi-students" class="exec-kpi-card clickable" style="border-top:3px solid #3A529C" onclick="document.getElementById('section-students').scrollIntoView({behavior:'smooth',block:'start'})">
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:10.5px;color:#68665D;text-transform:uppercase;font-weight:800;letter-spacing:0.5px">Enrolled Students</span>
                <div class="exec-icon-box" style="background:#E7ECF6;color:#3A529C">
                    <x-icon name="user-graduate" class="w-4 h-4" />
                </div>
            </div>
            <div style="font-family:'Manrope',sans-serif;font-size:22px;font-weight:800;color:#3A529C;letter-spacing:-0.5px;margin-bottom:4px">
                {{ number_format($studentsCount) }}
            </div>
        </div>
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid #EAE8E1;font-size:11px;color:#68665D">
                <span>Across Classes</span>
                <strong style="color:#1B1A17">{{ $classesCount }} Classes</strong>
            </div>
        </div>
        <span class="kpi-click-hint">View Details &#8595;</span>
    </div>

</div>

<!-- ── SECTION 1: WIDESCREEN FINANCIAL TREND GRAPH (FULL 100% WIDTH) ── -->
<div id="section-finance" class="exec-card-glass" style="margin-bottom:20px;display:flex;flex-direction:column;justify-content:space-between">
    <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #E1DFD7;padding-bottom:12px;margin-bottom:14px;flex-wrap:wrap;gap:10px">
        <div>
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:28px;height:28px;border-radius:8px;background:#F8E9D3;color:#8A5A10;display:flex;align-items:center;justify-content:center;font-size:13px"><x-icon name="arrow-trend-up" class="w-4 h-4" /></div>
                <h2 style="font-family:'Manrope',sans-serif;font-size:17px;font-weight:800;color:#1B1A17;margin:0">Financial Inflow &amp; Revenue Trends</h2>
                @if(!$hasRealFinancialData)
                    <span style="background:#F8E9D3;border:1px solid #E8CEAA;color:#8A5A10;font-size:10px;font-weight:800;padding:2px 8px;border-radius:9999px;letter-spacing:0.5px">DEMO</span>
                @else
                    <span style="background:#E3EFE2;border:1px solid #C7DEC5;color:#2E6E42;font-size:10px;font-weight:800;padding:2px 8px;border-radius:9999px;letter-spacing:0.5px">REAL LEDGER</span>
                @endif
            </div>
            <div style="font-size:12px;color:#68665D;margin-top:2px">
                6-month fee collections (PKR), operating expenses, tax paid, net profit &amp; target money
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <button type="button" onclick="document.getElementById('importFinanceModal').style.display='flex'" class="btn btn-secondary btn-sm" style="padding:6px 12px;font-size:11.5px;background:#F9F8F5;border:1px solid #E1DFD7;color:#1B1A17;border-radius:8px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
                <span><x-icon name="plus" class="w-3.5 h-3.5" /></span> Import Previous Expenses &amp; Income
            </button>
            <span class="exec-badge-pill {{ $isFinancialsUp ? 'badge-green' : 'badge-yellow' }}">
                {{ $isFinancialsUp ? '▲ Growing (+' . $growthPercent . '%)' : '▼ Decreasing (' . $growthPercent . '%)' }}
            </span>
        </div>
    </div>
    
    <!-- Interactive Widescreen Chart Canvas -->
    <div style="position:relative;height:260px;width:100%;flex:1">
        <canvas id="financialTrendChart"></canvas>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-around;margin-top:12px;padding-top:12px;border-top:1px solid #EAE8E1;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:6px">
            <div style="width:10px;height:10px;border-radius:3px;background:#D48A2E"></div>
            <span style="font-size:12px;color:#68665D;font-weight:600">Money Earned (Inflow)</span>
        </div>
        <div style="display:flex;align-items:center;gap:6px">
            <div style="width:10px;height:10px;border-radius:3px;background:#2E6E42;border:1px dashed #2E6E42"></div>
            <span style="font-size:12px;color:#68665D;font-weight:600">Target Money</span>
        </div>
        <div style="display:flex;align-items:center;gap:6px">
            <div style="width:10px;height:10px;border-radius:3px;background:#A2412C"></div>
            <span style="font-size:12px;color:#68665D;font-weight:600">Money Spent (Expenses)</span>
        </div>
        <div style="display:flex;align-items:center;gap:6px">
            <div style="width:10px;height:10px;border-radius:3px;background:#8A5A10"></div>
            <span style="font-size:12px;color:#68665D;font-weight:600">Tax Paid</span>
        </div>
        <div style="display:flex;align-items:center;gap:6px">
            <div style="width:10px;height:10px;border-radius:3px;background:#3A529C"></div>
            <span style="font-size:12px;color:#68665D;font-weight:600">Total Net Profit</span>
        </div>
    </div>
</div>

<!-- Modal: Import Previous Expenses & Income CSV -->
<div id="importFinanceModal" style="display:none;position:fixed;inset:0;background:rgba(14,14,17,0.65);backdrop-filter:blur(6px);z-index:999;align-items:center;justify-content:center;padding:16px">
    <div style="background:#F9F8F5;border:1px solid #E1DFD7;border-radius:14px;max-width:480px;width:100%;padding:20px;position:relative">
        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #E1DFD7;padding-bottom:10px;margin-bottom:14px">
            <h3 style="font-family:'Manrope',sans-serif;font-size:16px;font-weight:800;color:#1B1A17;margin:0;display:flex;align-items:center;gap:8px">
                <span><x-icon name="arrow-trend-up" class="w-4 h-4 text-amber-700" /></span> Import Historical Expenses &amp; Income
            </h3>
            <button type="button" onclick="document.getElementById('importFinanceModal').style.display='none'" style="background:none;border:none;font-size:18px;color:#68665D;cursor:pointer">&times;</button>
        </div>

        <p style="font-size:12px;color:#68665D;line-height:1.4;margin-bottom:12px">
            Upload your previous financial records (Money Spent, Money Earned, Tax Paid, Notes) via CSV file to immediately generate real dynamic financial graphs.
        </p>

        <form method="POST" action="{{ route('principal.bulk-import.finance') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:11px;font-weight:800;color:#68665D;text-transform:uppercase;margin-bottom:6px">Select CSV File</label>
                <input type="file" name="file" accept=".csv, .txt, .xlsx, .xls" required style="width:100%;padding:8px;border:1px solid #E1DFD7;border-radius:8px;font-size:12px;background:#ffffff">
            </div>

            <div style="background:#F2EFEB;border:1px solid #E1DFD7;border-radius:8px;padding:10px;margin-bottom:14px;font-size:11px;color:#1B1A17">
                <strong>CSV Header Format Required:</strong><br>
                <code>Category, Type, Amount, Date, Notes</code><br>
                <span style="color:#68665D;font-size:10px">Example: Electricity Bill, Expense, 45000, 2026-08-15, Monthly Utility</span><br>
                <span style="color:#68665D;font-size:10px">Example: Federal Income Tax, Expense, 12000, 2026-08-20, Tax Paid</span>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
                <a href="{{ route('principal.bulk-import.sample', ['type' => 'finance']) }}" style="font-size:11px;color:#8A5A10;font-weight:700;text-decoration:none"><x-icon name="arrow-right" class="w-3 h-3 inline mr-1" /> Download Sample CSV</a>
                <div style="display:flex;gap:8px">
                    <button type="button" onclick="document.getElementById('importFinanceModal').style.display='none'" class="btn btn-secondary btn-sm" style="padding:6px 12px;font-size:11px">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="padding:6px 14px;font-size:11px;">Import &amp; Generate Graph &rarr;</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ── SECTION 2: FACULTY ATTENDANCE & CRITICAL RISK MONITOR ── -->
<div id="section-faculty-risk" class="exec-card-glass" style="margin-bottom:20px">
    <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #E1DFD7;padding-bottom:12px;margin-bottom:14px;flex-wrap:wrap;gap:10px">
        <div>
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:28px;height:28px;border-radius:8px;background:#F6E4E1;color:#A2412C;display:flex;align-items:center;justify-content:center;font-size:13px"><x-icon name="triangle-exclamation" class="w-4 h-4" /></div>
                <h2 style="font-family:'Manrope',sans-serif;font-size:17px;font-weight:800;color:#1B1A17;margin:0">Faculty Attendance &amp; Critical Risk Monitor</h2>
                @if($criticalRiskCount > 0)
                    <span class="exec-badge-pill" style="background:#F6E4E1;color:#A2412C;border:1px solid #EAC8C1">
                        <x-icon name="circle-info" class="w-3.5 h-3.5 mr-1" /> {{ $criticalRiskCount }} Teacher(s) At Critical Risk (&lt;{{ round($minAttThreshold) }}%)
                    </span>
                @else
                    <span class="exec-badge-pill badge-green"><x-icon name="check" class="w-3.5 h-3.5 mr-1" /> All Faculty Safe</span>
                @endif
            </div>
            <div style="font-size:12px;color:#68665D;margin-top:2px">
                Real-time faculty presence tracking with automatic threshold warnings for teachers falling below the institute minimum of {{ round($minAttThreshold) }}%.
            </div>
        </div>
        <a href="{{ route('principal.staff.index') }}" class="btn btn-secondary btn-sm" style="padding:6px 12px;font-size:11.5px">
            <span><x-icon name="users" class="w-3.5 h-3.5" /></span> View Staff Roster
        </a>
    </div>

    @if($criticalRiskCount > 0)
    <div style="background:#F6E4E1;border:1px solid #EAC8C1;border-radius:10px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px">
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:18px"><x-icon name="triangle-exclamation" class="w-5 h-5 text-rose-700" /></span>
            <div>
                <strong style="font-size:13px;color:#A2412C">Mandatory Attendance Compliance Alert</strong>
                <p style="font-size:11.5px;color:#A2412C;margin-top:1px;margin-bottom:0">
                    Faculty highlighted in red have attendance rates below <strong>{{ round($minAttThreshold) }}%</strong>. This impacts timetable integrity and curriculum delivery.
                </p>
            </div>
        </div>
        <div style="font-size:11.5px;font-weight:700;color:#A2412C">
            Threshold: {{ round($minAttThreshold) }}% Minimum
        </div>
    </div>

    <!-- Critical Faculty Only -->
    <div style="display:flex;flex-direction:column;gap:10px">
        @foreach($facultyList as $fac)
        @if($fac['is_critical'])
        <div style="padding:12px 16px;border-radius:10px;border:1px solid #EAC8C1;background:#F6E4E1;transition:all 0.2s">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:10px">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:36px;height:36px;border-radius:50%;background:#A2412C;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px">
                        {{ $fac['initials'] }}
                    </div>
                    <div>
                        <div style="display:flex;align-items:center;gap:8px">
                            <strong style="font-size:14px;color:#1B1A17">{{ $fac['name'] }}</strong>
                            <span style="font-size:11px;color:#68665D;font-weight:600">({{ $fac['qualification'] }})</span>
                            <span class="exec-badge-pill" style="background:#A2412C;color:#ffffff;padding:2px 6px">CRITICAL RISK</span>
                        </div>
                        <div style="font-size:11.5px;color:#68665D;margin-top:1px">
                            Dept: <span style="color:#1B1A17;font-weight:600">{{ $fac['department'] }}</span>
                        </div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:14px">
                    <div style="text-align:right">
                        <div style="font-family:'Manrope',sans-serif;font-size:18px;font-weight:800;color:#A2412C">
                            {{ number_format($fac['attendance_rate'], 1) }}%
                        </div>
                        <div style="font-size:10.5px;color:#68665D;font-weight:600">
                            Below {{ round($minAttThreshold) }}% minimum
                        </div>
                    </div>

                    <a href="mailto:{{ $fac['email'] }}?subject=Attendance%20Warning%20Notice%20-%20Uplyft%20Academia" class="btn btn-sm btn-danger" style="padding:5px 10px;font-size:11px">
                        <x-icon name="envelope" class="w-3 h-3 mr-1" /> Issue Notice
                    </a>
                </div>
            </div>

            <!-- Progress Bar -->
            <div style="width:100%;height:7px;background:#E1DFD7;border-radius:9999px;overflow:hidden;position:relative">
                <div style="height:100%;width:{{ min(100, $fac['attendance_rate']) }}%;background:#A2412C;border-radius:9999px;transition:width 1s ease-in-out"></div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
    @else
    <!-- Everything's Fine: show 2 lowest-attendance profiles -->
    <div style="display:flex;align-items:center;gap:12px;background:#EAF6EE;border:1px solid #C4E5CE;border-radius:10px;padding:14px 16px;margin-bottom:14px">
        <div style="width:38px;height:38px;border-radius:50%;background:#2E6E42;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0"><x-icon name="check" class="w-5 h-5" /></div>
        <div>
            <strong style="font-size:14px;color:#1B5E33">Everything's Fine. Let's Grow.</strong>
            <p style="font-size:12px;color:#2E6E42;margin-top:2px;margin-bottom:0">
                All faculty are above the {{ round($minAttThreshold) }}% attendance minimum. No critical risks detected.
            </p>
        </div>
    </div>

    @if(count($facultyList) > 0)
    <div style="display:flex;flex-direction:column;gap:10px">
        @foreach($facultyList as $fac)
        <div style="padding:12px 16px;border-radius:10px;border:1px solid #E1DFD7;background:#F9F8F5;transition:all 0.2s">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:10px">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:36px;height:36px;border-radius:50%;background:#D48A2E;color:#1A1200;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px">
                        {{ $fac['initials'] }}
                    </div>
                    <div>
                        <div style="display:flex;align-items:center;gap:8px">
                            <strong style="font-size:14px;color:#1B1A17">{{ $fac['name'] }}</strong>
                            <span style="font-size:11px;color:#68665D;font-weight:600">({{ $fac['qualification'] }})</span>
                            <span class="exec-badge-pill" style="background:#2E6E42;color:#ffffff;padding:2px 6px">REGULAR</span>
                        </div>
                        <div style="font-size:11.5px;color:#68665D;margin-top:1px">
                            Dept: <span style="color:#1B1A17;font-weight:600">{{ $fac['department'] }}</span>
                        </div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:14px">
                    <div style="text-align:right">
                        <div style="font-family:'Manrope',sans-serif;font-size:18px;font-weight:800;color:#2E6E42">
                            {{ number_format($fac['attendance_rate'], 1) }}%
                        </div>
                        <div style="font-size:10.5px;color:#68665D;font-weight:600">
                            Above {{ round($minAttThreshold) }}% minimum
                        </div>
                    </div>

                    <a href="{{ route('principal.staff.index') }}" class="btn btn-sm btn-ghost" style="padding:5px 10px;font-size:11px">
                        View Profile
                    </a>
                </div>
            </div>

            <!-- Progress Bar -->
            <div style="width:100%;height:7px;background:#E1DFD7;border-radius:9999px;overflow:hidden;position:relative">
                <div style="height:100%;width:{{ min(100, $fac['attendance_rate']) }}%;background:#2E6E42;border-radius:9999px;transition:width 1s ease-in-out"></div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p style="font-size:12.5px;color:#68665D;text-align:center;padding:10px 0">No faculty registered yet.</p>
    @endif
    @endif
</div>

<!-- ── SECTION 3: CLASS-LEVEL ATTENDANCE & ACADEMIC PROGRESS (2 Columns) ── -->
<div style="display:grid;grid-template-columns:1.5fr 1.5fr;gap:18px;margin-bottom:20px">
    
    <!-- Class Attendance Breakdown -->
    <div id="section-students" class="exec-card-glass">
        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #E1DFD7;padding-bottom:12px;margin-bottom:14px">
            <div>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:28px;height:28px;border-radius:8px;background:#E7ECF6;color:#3A529C;display:flex;align-items:center;justify-content:center;font-size:13px"><x-icon name="school" class="w-4 h-4" /></div>
                    <h2 style="font-family:'Manrope',sans-serif;font-size:16px;font-weight:800;color:#1B1A17;margin:0">Section &amp; Class Attendance</h2>
                </div>
                <div style="font-size:11.5px;color:#68665D;margin-top:2px">
                    Average student presence across active academic levels
                </div>
            </div>
            <span class="exec-badge-pill" style="background:#E7ECF6;color:#3A529C;border:1px solid #CAD5EE">Overall: {{ $overallStudentAttendance }}%</span>
        </div>

        <div style="display:flex;flex-direction:column;gap:12px">
            @foreach($sectionAttendanceList as $sAtt)
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px">
                    <div>
                        <strong style="font-size:13px;color:#1B1A17">{{ $sAtt['name'] }}</strong>
                        <span style="font-size:11px;color:#68665D;margin-left:6px">Incharge: {{ $sAtt['incharge'] }}</span>
                    </div>
                    <div style="font-family:'Manrope',sans-serif;font-size:14px;font-weight:800;color:{{ $sAtt['color'] == '#10b981' ? '#2E6E42' : ($sAtt['color'] == '#ef4444' ? '#A2412C' : '#D48A2E') }}">
                        {{ number_format($sAtt['rate'], 1) }}%
                    </div>
                </div>
                <div style="width:100%;height:6px;background:#E1DFD7;border-radius:9999px;overflow:hidden">
                    <div style="height:100%;width:{{ $sAtt['rate'] }}%;background:{{ $sAtt['color'] == '#10b981' ? '#2E6E42' : ($sAtt['color'] == '#ef4444' ? '#A2412C' : '#D48A2E') }};border-radius:9999px"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Academic Term & Syllabus Progress -->
    <div class="exec-card-glass" style="display:flex;flex-direction:column;justify-content:space-between">
        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #E1DFD7;padding-bottom:12px;margin-bottom:14px">
            <div>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:28px;height:28px;border-radius:8px;background:#E3EFE2;color:#2E6E42;display:flex;align-items:center;justify-content:center;font-size:13px"><x-icon name="bullseye" class="w-4 h-4" /></div>
                    <h2 style="font-family:'Manrope',sans-serif;font-size:16px;font-weight:800;color:#1B1A17;margin:0">Academic Term Progress</h2>
                </div>
                <div style="font-size:11.5px;color:#68665D;margin-top:2px">
                    Active Session: {{ $activeTerm ? $activeTerm->name : 'N/A' }}
                </div>
            </div>
            <a href="{{ route('principal.academic-terms.index') }}" class="btn btn-ghost btn-sm" style="font-size:11px;padding:4px 10px">Session Setup &rarr;</a>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-around;padding:6px 0">
            <!-- Radial Circular Progress Ring -->
            <div style="position:relative;width:130px;height:130px;display:flex;align-items:center;justify-content:center">
                <svg width="120" height="120" viewBox="0 0 120 120">
                    <circle cx="60" cy="60" r="50" fill="none" stroke="#E1DFD7" stroke-width="10" />
                    <circle cx="60" cy="60" r="50" fill="none" stroke="#2E6E42" stroke-width="10" 
                            stroke-dasharray="314.159" stroke-dashoffset="{{ 314.159 * (1 - ($termProgressPercent / 100)) }}"
                            stroke-linecap="round" transform="rotate(-90 60 60)" style="transition:stroke-dashoffset 1s ease" />
                </svg>
                <div style="position:absolute;text-align:center">
                    <div style="font-family:'Manrope',sans-serif;font-size:24px;font-weight:800;color:#1B1A17">{{ $termProgressPercent }}%</div>
                    <div style="font-size:9.5px;text-transform:uppercase;color:#68665D;font-weight:700">Term Elapsed</div>
                </div>
            </div>

            <!-- Key Academic Metrics -->
            <div style="display:flex;flex-direction:column;gap:10px">
                <div>
                    <div style="font-size:10.5px;text-transform:uppercase;color:#A19E92;font-weight:800">Timetable Capacity</div>
                    <div style="font-size:15px;font-weight:800;color:#1B1A17">{{ $slotsCount }} Matrix Slots</div>
                </div>
                <div>
                    <div style="font-size:10.5px;text-transform:uppercase;color:#A19E92;font-weight:800">Active Offerings</div>
                    <div style="font-size:15px;font-weight:800;color:#1B1A17">{{ $classesCount }} Classes Configured</div>
                </div>
                <div>
                    <div style="font-size:10.5px;text-transform:uppercase;color:#A19E92;font-weight:800">Term Timeline</div>
                    <div style="font-size:12px;font-weight:700;color:#2E6E42">
                        {{ $activeTerm ? $activeTerm->start_date->format('M Y') . ' — ' . $activeTerm->end_date->format('M Y') : 'Session Not Started' }}
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:10px">
            <a href="{{ route('principal.timetables.index') }}" class="btn btn-primary" style="flex:1;justify-content:center;font-size:11.5px;padding:8px">
                <span><x-icon name="calendar-days" class="w-3.5 h-3.5 inline mr-1" /></span> Timetable Engine
            </a>
            <a href="{{ route('principal.classes-subjects.index') }}" class="btn btn-secondary" style="flex:1;justify-content:center;font-size:11.5px;padding:8px">
                <span><x-icon name="layer-group" class="w-3.5 h-3.5 inline mr-1" /></span> Classes &amp; Sections
            </a>
        </div>
    </div>
</div>

<!-- ── SECTION 4: EXECUTIVE ROSTER & ADVISORS ── -->
<div class="exec-card-glass" style="margin-bottom:20px">
    <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #E1DFD7;padding-bottom:12px;margin-bottom:14px">
        <div>
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:28px;height:28px;border-radius:8px;background:#F8E9D3;color:#8A5A10;display:flex;align-items:center;justify-content:center;font-size:13px"><x-icon name="user-tie" class="w-4 h-4" /></div>
                <h2 style="font-family:'Manrope',sans-serif;font-size:16px;font-weight:800;color:#1B1A17;margin:0">Executive Faculty In-Charges &amp; Supervisors</h2>
            </div>
            <div style="font-size:11.5px;color:#68665D;margin-top:2px">
                Key academic personnel appointed for departmental oversight
            </div>
        </div>
        <a href="{{ route('principal.staff.index') }}" class="btn btn-ghost btn-sm" style="font-size:11px;padding:4px 10px">Manage Appointments &rarr;</a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:12px">
        @foreach(array_slice($facultyList, 0, 3) as $incharge)
        <div style="padding:12px 14px;border:1px solid #E1DFD7;border-radius:10px;background:#F9F8F5;display:flex;align-items:center;gap:12px">
            <div style="width:40px;height:40px;border-radius:50%;background:#17191C;color:#F0B45D;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0">
                {{ $incharge['initials'] }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:800;font-size:13.5px;color:#1B1A17;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ $incharge['name'] }}
                </div>
                <div style="font-size:11.5px;color:#8A5A10;font-weight:600;margin-top:1px">
                    {{ $incharge['department'] }}
                </div>
                <div style="font-size:11px;color:#68665D;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;align-items:center;gap:4px">
                    <x-icon name="envelope" class="w-3 h-3 text-neutral-400" /> {{ $incharge['email'] }}
                </div>
            </div>
            <div>
                <span class="exec-badge-pill {{ $incharge['is_critical'] ? 'badge-yellow' : 'badge-green' }}">
                    {{ number_format($incharge['attendance_rate'], 0) }}% Pres.
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- ── SECTION 5: RECENT FINANCIAL INVOICES STREAM ── -->
<div id="section-invoices" class="exec-card-glass">
    <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #E1DFD7;padding-bottom:12px;margin-bottom:14px">
        <div>
            <div style="display:flex;align-items:center;gap:8px">
                <div style="width:28px;height:28px;border-radius:8px;background:#E3EFE2;color:#2E6E42;display:flex;align-items:center;justify-content:center;font-size:13px"><x-icon name="receipt" class="w-4 h-4" /></div>
                <h2 style="font-family:'Manrope',sans-serif;font-size:16px;font-weight:800;color:#1B1A17;margin:0">Recent Student Fee Vouchers &amp; Receipts</h2>
            </div>
            <div style="font-size:11.5px;color:#68665D;margin-top:2px">
                Live transaction flow and verification audit
            </div>
        </div>
        <a href="{{ route('principal.invoices.index') }}" class="btn btn-primary btn-sm" style="font-size:11.5px;padding:6px 12px">Open Full Billing Ledger &rarr;</a>
    </div>

    @if($recentInvoices->count() > 0)
    <div style="overflow-x:auto">
        <table class="exec-table">
            <thead>
                <tr>
                    <th>Invoice / Voucher</th>
                    <th>Student Name</th>
                    <th>Fee Month</th>
                    <th>Amount (PKR)</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentInvoices as $inv)
                <tr>
                    <td>
                        <strong style="color:#1B1A17;font-family:monospace;font-size:12px">INV-{{ str_pad($inv->id, 5, '0', STR_PAD_LEFT) }}</strong>
                        <div style="font-size:10.5px;color:#68665D">{{ $inv->title ?? 'Tuition Fee' }}</div>
                    </td>
                    <td>
                        <div style="font-weight:700;color:#1B1A17;font-size:12.5px">{{ $inv->student ? $inv->student->first_name . ' ' . $inv->student->last_name : 'General Student' }}</div>
                        <div style="font-size:10.5px;color:#68665D">Roll: {{ $inv->student->roll_number ?? 'N/A' }}</div>
                    </td>
                    <td>
                        <span class="exec-badge-pill badge-neutral">{{ $inv->fee_month ?? now()->format('M Y') }}</span>
                    </td>
                    <td>
                        <strong style="color:#1B1A17;font-size:13px">PKR {{ number_format($inv->amount_pkr) }}</strong>
                    </td>
                    <td>
                        <span style="font-size:11.5px;color:{{ Carbon\Carbon::parse($inv->due_date)->isPast() && $inv->status !== 'paid' ? '#A2412C' : '#68665D' }};font-weight:600">
                            {{ Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}
                        </span>
                    </td>
                    <td>
                        @if($inv->status === 'paid')
                            <span class="exec-badge-pill badge-green">Paid <x-icon name="check" class="w-3 h-3 ml-1 inline" /></span>
                        @elseif(Carbon\Carbon::parse($inv->due_date)->isPast())
                            <span class="exec-badge-pill" style="background:#F6E4E1;color:#A2412C;border:1px solid #EAC8C1">Overdue</span>
                        @else
                            <span class="exec-badge-pill badge-yellow">Unpaid</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('principal.invoices.index') }}" class="btn btn-ghost btn-sm" style="padding:3px 10px;font-size:11px">
                            View &rarr;
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div style="text-align:center;padding:24px;color:#68665D">
        <div style="font-size:28px;color:#A19E92"><x-icon name="receipt" class="w-8 h-8 mx-auto" /></div>
        <p style="font-size:12.5px;margin-top:4px">No fee invoices generated yet for this term.</p>
        <a href="{{ route('principal.invoices.index') }}" class="btn btn-primary btn-sm" style="margin-top:8px;font-size:11.5px;padding:6px 12px">Generate Invoices &rarr;</a>
    </div>
    @endif
</div>

<!-- ── JAVASCRIPT: CHART.JS INITIALIZATIONS ── -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Line / Area Chart: Financial Trends
    const trendCtx = document.getElementById('financialTrendChart');
    if (trendCtx) {
        const ctx = trendCtx.getContext('2d');
        
        const revGradient = ctx.createLinearGradient(0, 0, 0, 240);
        revGradient.addColorStop(0, 'rgba(212, 138, 46, 0.25)');
        revGradient.addColorStop(1, 'rgba(212, 138, 46, 0.00)');

        const profitGradient = ctx.createLinearGradient(0, 0, 0, 240);
        profitGradient.addColorStop(0, 'rgba(58, 82, 156, 0.20)');
        profitGradient.addColorStop(1, 'rgba(58, 82, 156, 0.00)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($months),
                datasets: [
                    {
                        label: 'Money Earned (Inflow)',
                        data: @json($revenueData),
                        borderColor: '#D48A2E',
                        backgroundColor: revGradient,
                        borderWidth: 2.8,
                        tension: 0.38,
                        fill: true,
                        pointBackgroundColor: '#D48A2E',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4.5,
                        pointHoverRadius: 6.5,
                    },
                    {
                        label: 'Target Money',
                        data: @json($targetData),
                        borderColor: '#2E6E42',
                        borderWidth: 1.8,
                        borderDash: [4, 4],
                        tension: 0.3,
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#2E6E42',
                    },
                    {
                        label: 'Money Spent (Expenses)',
                        data: @json($expenseData),
                        borderColor: '#A2412C',
                        borderWidth: 1.8,
                        tension: 0.38,
                        fill: false,
                        pointRadius: 3.5,
                        pointHoverRadius: 5.5,
                        pointBackgroundColor: '#A2412C',
                    },
                    {
                        label: 'Tax Paid',
                        data: @json($taxData),
                        borderColor: '#8A5A10',
                        borderWidth: 1.8,
                        borderDash: [2, 2],
                        tension: 0.35,
                        fill: false,
                        pointRadius: 3.5,
                        pointHoverRadius: 5.5,
                        pointBackgroundColor: '#8A5A10',
                    },
                    {
                        label: 'Total Net Profit',
                        data: @json($profitData),
                        borderColor: '#3A529C',
                        backgroundColor: profitGradient,
                        borderWidth: 2.2,
                        tension: 0.38,
                        fill: true,
                        pointBackgroundColor: '#3A529C',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#17191C',
                        titleFont: { size: 11, family: "'Inter', sans-serif" },
                        bodyFont: { size: 12, weight: 'bold', family: "'Manrope', sans-serif" },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': PKR ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(225, 223, 215, 0.6)',
                            drawBorder: false,
                        },
                        ticks: {
                            font: { size: 10.5, weight: '600', family: "'Inter', sans-serif" },
                            color: '#68665D'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(225, 223, 215, 0.6)',
                            drawBorder: false,
                        },
                        ticks: {
                            font: { size: 10.5, family: "'Inter', sans-serif" },
                            color: '#68665D',
                            callback: function(value) {
                                if (value >= 1000000) return 'PKR ' + (value/1000000).toFixed(1) + 'M';
                                if (value >= 1000) return 'PKR ' + (value/1000).toFixed(0) + 'k';
                                return 'PKR ' + value;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Donut Chart: Fee Recovery Breakdown (Header Embedded)
    const donutCtx = document.getElementById('recoveryDonutChart');
    if (donutCtx) {
        new Chart(donutCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Collected (Paid)', 'Pending (In Grace)', 'Overdue Arrears'],
                datasets: [{
                    data: [
                        {{ $totalPaid > 0 ? $totalPaid : 345000 }},
                        {{ $totalPending > 0 ? $totalPending : 140000 }},
                        {{ $totalOverdue > 0 ? $totalOverdue : 48150 }}
                    ],
                    backgroundColor: ['#2E6E42', '#D48A2E', '#A2412C'],
                    borderWidth: 2,
                    borderColor: '#F9F8F5',
                    hoverOffset: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '74%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#17191C',
                        bodyFont: { size: 11, family: "'Inter', sans-serif" },
                        padding: 8,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': PKR ' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
