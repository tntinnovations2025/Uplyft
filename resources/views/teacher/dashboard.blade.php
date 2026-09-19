@extends('layouts.app')

@section('title', ($user->staff_role ? $user->staff_role . ' Dashboard' : 'Staff Dashboard'))
@section('page-header', ($user->staff_role ? $user->staff_role . ' Portal' : 'Faculty & Staff Portal'))

@section('content')
<div class="space-y-6">

    <!-- WELCOME BANNER (Ergonomic Daylight Card) -->
    <div class="liquid-glass-card p-6 bg-gradient-to-r from-indigo-500/[0.04] via-blue-500/[0.02] to-violet-500/[0.04] border border-slate-200/90 relative overflow-hidden glass-specular-top">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-700 via-indigo-600 to-violet-600 text-white flex items-center justify-center text-xl font-extrabold shadow-md shadow-indigo-500/25 border border-white/60 font-display">
                    {{ strtoupper(substr($user->name ?? 'T', 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight font-display">
                        Welcome back, {{ $user->first_name }}
                    </h1>
                    <p class="text-xs text-slate-500 font-semibold mt-1 flex items-center gap-2 flex-wrap">
                        <span>Employee ID:</span>
                        <span class="text-indigo-700 font-bold bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-200/70">{{ $user->identifier ?? '—' }}</span>
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <span class="badge badge-indigo text-xs font-bold">
                    <i class="fa-solid fa-id-badge"></i> {{ strtoupper($user->staff_role ?? 'Teacher') }}{{ $user->employment_type ? ' (' . ucfirst($user->employment_type) . ')' : '' }}
                </span>
                @if($user->is_delegated_admin)
                    <span class="badge badge-amber text-xs font-bold">
                        <i class="fa-solid fa-crown"></i> Master Delegated Admin
                    </span>
                @endif
                <span class="badge badge-indigo text-xs font-bold">
                    <i class="fa-solid fa-building"></i> {{ $user->institute->name ?? 'Uplyft Academy' }}
                </span>
            </div>
        </div>
    </div>

    <!-- FINANCIAL KPIS & GRAPH-BASED ANALYTICS (For Accountants & Financial Staff) -->
    @if($isAccountant || auth()->user()->hasPermission('accounts') || auth()->user()->hasPermission('invoices'))
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <!-- QUICK ACTION BAR: EXPENSE INSERTION FOR AI AUDIT & CALCULATION -->
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-white/90 border border-slate-200/90 rounded-2xl shadow-sm backdrop-blur-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-lg font-extrabold flex-shrink-0">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-slate-900 font-display">AI Financial Audit &amp; Expense Calculation</h4>
                    <p class="text-xs text-slate-500 font-medium">Record campus operational expenses, utility bills &amp; vendor disbursements for real-time AI auditing.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ auth()->user()->staffUrl('accounts?tab=ledger&action=new_expense') }}" class="btn btn-primary text-xs font-bold px-3.5 py-2 inline-flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-plus"></i> Record Expense for AI Audit
                </a>
                <a href="{{ auth()->user()->staffUrl('accounts?tab=ai_report') }}" class="btn btn-secondary text-xs font-bold px-3.5 py-2 inline-flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie"></i> View AI Audit &amp; P&amp;L
                </a>
            </div>
        </div>

        <!-- FINANCIAL KPI SUMMARY CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Card 1: Fee Collected -->
            <div class="liquid-glass-card p-5 border border-emerald-200/80 bg-gradient-to-br from-emerald-50/60 via-white to-emerald-50/30 relative overflow-hidden group shadow-2xs hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-800">Fee Collected</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-slate-900 font-display tracking-tight">
                    PKR {{ number_format($totalFeeCollected ?? 0) }}
                </div>
                <div class="mt-2 flex items-center gap-1.5 text-xs text-emerald-700 font-semibold">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i>
                    <span>Real-time cleared payments</span>
                </div>
            </div>

            <!-- Card 2: Remaining Fee -->
            <div class="liquid-glass-card p-5 border border-rose-200/80 bg-gradient-to-br from-rose-50/60 via-white to-rose-50/30 relative overflow-hidden group shadow-2xs hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-rose-800">Remaining Unpaid Fee</span>
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-slate-900 font-display tracking-tight">
                    PKR {{ number_format($totalRemainingFee ?? 0) }}
                </div>
                <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700 font-semibold">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                    <span>{{ $unpaidCount ?? 0 }} Pending vouchers</span>
                </div>
            </div>

            <!-- Card 3: Fines Collected -->
            <div class="liquid-glass-card p-5 border border-amber-200/80 bg-gradient-to-br from-amber-50/60 via-white to-amber-50/30 relative overflow-hidden group shadow-2xs hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-amber-800">Fines &amp; Penalties</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-gavel"></i>
                    </div>
                </div>
                <div class="text-2xl font-black text-slate-900 font-display tracking-tight">
                    PKR {{ number_format($totalFinesCollected ?? 0) }}
                </div>
                <div class="mt-2 flex items-center gap-1.5 text-xs text-amber-700 font-semibold">
                    <i class="fa-solid fa-bolt text-amber-600"></i>
                    <span>Late fees &amp; disciplinary charges</span>
                </div>
            </div>

            <!-- Card 4: Total Vouchers Issued -->
            <div class="liquid-glass-card p-5 border border-indigo-200/80 bg-gradient-to-br from-indigo-50/60 via-white to-indigo-50/30 relative overflow-hidden group shadow-2xs hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-indigo-800">Voucher Recovery</span>
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                </div>
                @php
                    $totalInvoicesCount = ($paidCount ?? 0) + ($unpaidCount ?? 0);
                    $recoveryRate = $totalInvoicesCount > 0 ? round((($paidCount ?? 0) / $totalInvoicesCount) * 100, 1) : 0;
                @endphp
                <div class="text-2xl font-black text-slate-900 font-display tracking-tight">
                    {{ $recoveryRate }}%
                </div>
                <div class="mt-2 flex items-center gap-1.5 text-xs text-indigo-700 font-semibold">
                    <i class="fa-solid fa-receipt text-indigo-600"></i>
                    <span>{{ $paidCount ?? 0 }} Paid / {{ $totalInvoicesCount }} Total Issued</span>
                </div>
            </div>
        </div>

        <!-- GRAPH-BASED ANALYTICS SECTION -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Graph: 6-Month Fee Collection vs Unpaid Trajectory -->
            <div class="lg:col-span-2 liquid-glass-card p-6 border border-slate-200/90 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-200/80">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 font-display flex items-center gap-2">
                            <i class="fa-solid fa-chart-column text-emerald-600"></i>
                            <span>Fee Collection &amp; Unpaid Revenue Trajectory</span>
                        </h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Historical breakdown of monthly fee recoveries vs pending arrears over the last 6 months.</p>
                    </div>
                    <span class="badge badge-emerald text-xs font-bold">Monthly Trend</span>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="feeCollectionChart"></canvas>
                </div>
            </div>

            <!-- Side Graph: Fee Voucher Status Distribution Donut Chart -->
            <div class="liquid-glass-card p-6 border border-slate-200/90 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-200/80">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 font-display flex items-center gap-2">
                            <i class="fa-solid fa-chart-pie text-indigo-600"></i>
                            <span>Voucher Status Breakdown</span>
                        </h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Paid vs Unpaid ratio</p>
                    </div>
                </div>
                <div class="relative h-48 w-full flex items-center justify-center">
                    <canvas id="feeStatusDonutChart"></canvas>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-200/80 flex items-center justify-around text-xs font-bold">
                    <div class="flex items-center gap-2 text-emerald-700">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span>Paid: {{ $paidCount ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-rose-700">
                        <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        <span>Unpaid: {{ $unpaidCount ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // 1. Fee Collection Bar & Line Chart
                const ctxBar = document.getElementById('feeCollectionChart');
                if (ctxBar) {
                    new Chart(ctxBar.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: @json($monthsList ?? ['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6']),
                            datasets: [
                                {
                                    label: 'Fee Collected (PKR)',
                                    data: @json($monthlyCollectedData ?? [0,0,0,0,0,0]),
                                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                                    borderColor: '#059669',
                                    borderWidth: 1.5,
                                    borderRadius: 6
                                },
                                {
                                    label: 'Unpaid Arrears (PKR)',
                                    data: @json($monthlyUnpaidData ?? [0,0,0,0,0,0]),
                                    backgroundColor: 'rgba(244, 63, 94, 0.75)',
                                    borderColor: '#e11d48',
                                    borderWidth: 1.5,
                                    borderRadius: 6
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top', labels: { font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 11 } } },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': PKR ' + context.raw.toLocaleString();
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9' },
                                    ticks: {
                                        font: { family: 'Plus Jakarta Sans', size: 10 },
                                        callback: function(value) { return 'PKR ' + value.toLocaleString(); }
                                    }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { family: 'Plus Jakarta Sans', weight: 'bold', size: 10 } }
                                }
                            }
                        }
                    });
                }

                // 2. Fee Status Donut Chart
                const ctxDonut = document.getElementById('feeStatusDonutChart');
                if (ctxDonut) {
                    new Chart(ctxDonut.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Paid Invoices', 'Unpaid Invoices'],
                            datasets: [{
                                data: [{{ $paidCount ?? 0 }}, {{ $unpaidCount ?? 0 }}],
                                backgroundColor: ['#10b981', '#f43f5e'],
                                borderWidth: 3,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });
                }
            });
        </script>
    @endif

    @php
        $isAccountant = strtolower(auth()->user()->staff_role ?? '') === 'accountant' || (auth()->user()->hasPermission('accounts') && !auth()->user()->hasPermission('attendance'));
    @endphp

    <!-- METRICS GRID / WIDGETS -->
    @if(!$isAccountant)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Widget 1: Today's Classes for Faculty -->
        <div class="liquid-glass-card p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-indigo-300 hover:shadow-md transition duration-200">
            <div class="flex justify-between items-start">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200/70 flex items-center justify-center text-lg font-bold shadow-2xs">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <span class="badge badge-indigo text-xs font-bold">
                    {{ $todaySlots->count() }} {{ Str::plural('Class', $todaySlots->count()) }}
                </span>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 text-base mb-1 group-hover:text-indigo-600 transition font-display">Today's Classes</h3>
                <p class="text-xs text-slate-500 font-medium leading-relaxed">View your schedule for today and manage lecture materials.</p>
            </div>

            <div class="mt-auto pt-4 border-t border-slate-200/80 flex flex-col gap-2.5">
                @forelse($todaySlots as $todaySlot)
                    <div class="flex items-center justify-between text-xs text-slate-800 bg-slate-50/90 hover:bg-indigo-50/50 p-3 rounded-xl border border-slate-200/85 hover:border-indigo-200/80 transition">
                        <span class="flex items-center gap-1.5 text-indigo-600 font-bold">
                            <i class="fa-regular fa-clock text-indigo-500"></i> {{ $todaySlot['start_time'] }}
                        </span>
                        <div class="text-right">
                            <span class="font-extrabold text-slate-900 block">{{ $todaySlot['subject_name'] }}</span>
                            <span class="text-[11px] text-slate-500 font-bold flex items-center justify-end gap-1">
                                <i class="fa-solid fa-chalkboard text-slate-400 text-[10px]"></i> {{ $todaySlot['section_name'] }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-500 italic py-3 text-center bg-slate-50 rounded-xl border border-slate-200/60">
                        No assigned classes scheduled for today.
                    </div>
                @endforelse

                <!-- Button to Open Complete Week Schedule Modal & Link to Full Page -->
                <div class="pt-2 flex items-center gap-2">
                    <button type="button" onclick="openTimetableModal()" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl btn-secondary text-xs font-bold">
                        <i class="fa-solid fa-calendar-week text-[11px]"></i>
                        <span>View Complete Schedule</span>
                    </button>
                    <a href="{{ auth()->user()->staffUrl('schedule') }}" title="Open Full Screen Schedule" class="px-3.5 py-2.5 rounded-xl btn-secondary text-xs font-bold">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Widget 2: Pending Assignments -->
        @if(auth()->user()->hasPermission('assessment_engine') || auth()->user()->hasPermission('lms_content'))
            <div class="liquid-glass-card p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-amber-500/40 hover:shadow-md transition duration-200">
                <div class="flex justify-between items-start">
                    <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/70 flex items-center justify-center text-lg font-bold shadow-2xs">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <span class="badge badge-amber text-xs font-bold">{{ $pendingSubmissionsCount ?? 0 }} Pending</span>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base mb-1 font-display">Assignments to Grade</h3>
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">Review submissions and provide feedback for your students.</p>
                </div>
                <div class="mt-auto pt-4 border-t border-slate-200/80">
                    <a href="{{ route('lms.assessments.index') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl btn-secondary text-xs font-bold">
                        <i class="fa-solid fa-check-double text-[11px]"></i>
                        <span>Grade Submissions</span>
                    </a>
                </div>
            </div>
        @elseif(auth()->user()->hasPermission('invoices') || auth()->user()->hasPermission('accounts'))
            <div class="liquid-glass-card p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-emerald-500/40 hover:shadow-md transition duration-300">
                <div class="flex justify-between items-start">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/70 flex items-center justify-center text-lg font-bold shadow-2xs">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <span class="badge badge-emerald text-xs font-bold">Accounts Hub</span>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base mb-1 font-display">Fee Invoices &amp; Ledger</h3>
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">Manage student fee vouchers, discounts, and payments.</p>
                </div>
                <div class="mt-auto pt-4 border-t border-slate-200/80 flex gap-2">
                    <a href="{{ auth()->user()->staffUrl('invoices') }}" class="flex-1 inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl btn-success text-xs font-bold">
                        <i class="fa-solid fa-receipt text-[11px]"></i>
                        <span>Fee Invoices</span>
                    </a>
                    <a href="{{ auth()->user()->staffUrl('accounts') }}" class="inline-flex items-center justify-center px-3.5 py-2.5 rounded-xl btn-secondary text-xs font-bold">
                        <i class="fa-solid fa-calculator"></i>
                    </a>
                </div>
            </div>
        @endif

        <!-- Widget 3: Quick Attendance / Master Directory -->
        @if(auth()->user()->hasPermission('attendance'))
            <div class="liquid-glass-card p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-pink-500/40 hover:shadow-md transition duration-300">
                <div class="flex justify-between items-start">
                    <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 border border-purple-200/70 flex items-center justify-center text-lg font-bold shadow-2xs">
                        <i class="fa-solid fa-clipboard-user"></i>
                    </div>
                    <span class="badge badge-purple text-xs font-bold">Class Roster</span>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base mb-1 font-display">Quick Attendance</h3>
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">Launch the interactive roster to mark attendance for your current class.</p>
                </div>
                <div class="mt-auto pt-4 border-t border-slate-200/80">
                    <a href="{{ auth()->user()->staffUrl('attendance') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl btn-primary text-xs font-bold">
                        <i class="fa-solid fa-play text-[11px]"></i>
                        <span>Start Class Roster{{ $todaySlots->isNotEmpty() ? ' for ' . $todaySlots->first()['subject_code'] : '' }}</span>
                    </a>
                </div>
            </div>
        @elseif(auth()->user()->hasPermission('directory'))
            <div class="liquid-glass-card p-6 flex flex-col gap-4 relative overflow-hidden group hover:border-pink-500/40 hover:shadow-md transition duration-300">
                <div class="flex justify-between items-start">
                    <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 border border-sky-200/70 flex items-center justify-center text-lg font-bold shadow-2xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <span class="badge badge-cyan text-xs font-bold">Directory</span>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base mb-1 font-display">Master Directory</h3>
                    <p class="text-xs text-slate-500 font-medium leading-relaxed">Search student dossiers, teacher qualifications, and salary slips.</p>
                </div>
                <div class="mt-auto pt-4 border-t border-slate-200/80">
                    <a href="{{ auth()->user()->staffUrl('directory') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl btn-primary text-xs font-bold">
                        <i class="fa-solid fa-search text-[11px]"></i>
                        <span>Open Master Directory</span>
                    </a>
                </div>
            </div>
        @endif
    </div>
    @endif

    <!-- DELEGATED ADMINISTRATIVE RIGHTS PANEL FOR COORDINATORS / STAFF -->
    @if(auth()->user()->hasAnyDelegatedPermission())
    <div class="liquid-glass-card p-6 border border-slate-200/90 shadow-sm relative overflow-hidden">
        <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate-200/80">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-200/70 flex items-center justify-center text-base font-bold shadow-2xs">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2 font-display">
                        <span>Assigned Administrative Rights &amp; Portal Modules</span>
                        <span class="badge badge-pink text-[10px]">Active Rights</span>
                    </h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Granted by Executive Principal Office. Modules update dynamically when rights are toggled.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            @if(auth()->user()->hasPermission('student_registration', 'view'))
                <a href="{{ auth()->user()->staffUrl('students/create') }}" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/90 hover:border-emerald-300 transition group shadow-2xs hover:shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/70 flex items-center justify-center text-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 group-hover:text-emerald-700 transition">Register Student</div>
                        <div class="text-[10.5px] text-slate-500 font-medium">Admissions &amp; Onboarding</div>
                    </div>
                    @if(!auth()->user()->hasPermission('student_registration', 'edit'))
                        <span class="ml-auto text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><i class="fa-solid fa-eye text-slate-400 mr-1"></i> View Only</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->hasPermission('students', 'view'))
                <a href="{{ auth()->user()->staffUrl('students') }}" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/90 hover:border-sky-300 transition group shadow-2xs hover:shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 border border-sky-200/70 flex items-center justify-center text-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 group-hover:text-sky-700 transition">Student Roster</div>
                        <div class="text-[10.5px] text-slate-500 font-medium">View Enrolled Directory</div>
                    </div>
                    @if(!auth()->user()->hasPermission('students', 'edit'))
                        <span class="ml-auto text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><i class="fa-solid fa-eye text-slate-400 mr-1"></i> View Only</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->hasPermission('timetables', 'view'))
                <a href="{{ auth()->user()->staffUrl('timetables') }}" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/90 hover:border-indigo-300 transition group shadow-2xs hover:shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200/70 flex items-center justify-center text-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-calendar-week"></i>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 group-hover:text-indigo-700 transition">Timetable</div>
                        <div class="text-[10.5px] text-slate-500 font-medium">Matrix Scheduling</div>
                    </div>
                    @if(!auth()->user()->hasPermission('timetables', 'edit'))
                        <span class="ml-auto text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><i class="fa-solid fa-eye text-slate-400 mr-1"></i> View Only</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->hasPermission('directory', 'view'))
                <a href="{{ auth()->user()->staffUrl('directory') }}" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/90 hover:border-purple-300 transition group shadow-2xs hover:shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-200/70 flex items-center justify-center text-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 group-hover:text-purple-700 transition">Master Directory</div>
                        <div class="text-[10.5px] text-slate-500 font-medium">Search Staff &amp; Students</div>
                    </div>
                    @if(!auth()->user()->hasPermission('directory', 'edit'))
                        <span class="ml-auto text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><i class="fa-solid fa-eye text-slate-400 mr-1"></i> View Only</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->hasPermission('invoices', 'view'))
                <a href="{{ auth()->user()->staffUrl('invoices') }}" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/90 hover:border-amber-300 transition group shadow-2xs hover:shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/70 flex items-center justify-center text-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 group-hover:text-amber-800 transition">Fee Invoices</div>
                        <div class="text-[10.5px] text-slate-500 font-medium">Vouchers &amp; Payments</div>
                    </div>
                    @if(!auth()->user()->hasPermission('invoices', 'edit'))
                        <span class="ml-auto text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><i class="fa-solid fa-eye text-slate-400 mr-1"></i> View Only</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->hasPermission('scholarships', 'view'))
                <a href="{{ auth()->user()->staffUrl('scholarships') }}" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/90 hover:border-pink-300 transition group shadow-2xs hover:shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 border border-pink-200/70 flex items-center justify-center text-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 group-hover:text-pink-700 transition">Scholarship Policy</div>
                        <div class="text-[10.5px] text-slate-500 font-medium">Discount Percentages</div>
                    </div>
                    @if(!auth()->user()->hasPermission('scholarships', 'edit'))
                        <span class="ml-auto text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><i class="fa-solid fa-eye text-slate-400 mr-1"></i> View Only</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->hasPermission('rooms', 'view'))
                <a href="{{ auth()->user()->staffUrl('rooms') }}" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/90 hover:border-teal-300 transition group shadow-2xs hover:shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 border border-teal-200/70 flex items-center justify-center text-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 group-hover:text-teal-700 transition">Campus Rooms</div>
                        <div class="text-[10.5px] text-slate-500 font-medium">Facilities &amp; Labs</div>
                    </div>
                    @if(!auth()->user()->hasPermission('rooms', 'edit'))
                        <span class="ml-auto text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><i class="fa-solid fa-eye text-slate-400 mr-1"></i> View Only</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->hasPermission('classes', 'view') || auth()->user()->hasPermission('subjects', 'view'))
                <a href="{{ auth()->user()->staffUrl('classes-subjects') }}" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/90 hover:border-violet-300 transition group shadow-2xs hover:shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 border border-violet-200/70 flex items-center justify-center text-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 group-hover:text-violet-700 transition">Classes &amp; Subjects</div>
                        <div class="text-[10.5px] text-slate-500 font-medium">Academic Structure</div>
                    </div>
                    @if(!auth()->user()->hasPermission('classes', 'edit') && !auth()->user()->hasPermission('subjects', 'edit'))
                        <span class="ml-auto text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><i class="fa-solid fa-eye text-slate-400 mr-1"></i> View Only</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->hasPermission('accounts', 'view') || auth()->user()->hasPermission('staff_salaries', 'view'))
                <a href="{{ auth()->user()->staffUrl('accounts') }}" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/90 hover:border-emerald-300 transition group shadow-2xs hover:shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/70 flex items-center justify-center text-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 group-hover:text-emerald-700 transition">Financial Accounts</div>
                        <div class="text-[10.5px] text-slate-500 font-medium">Ledger &amp; Salary Records</div>
                    </div>
                    @if(!auth()->user()->hasPermission('accounts', 'edit') && !auth()->user()->hasPermission('staff_salaries', 'edit'))
                        <span class="ml-auto text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><i class="fa-solid fa-eye text-slate-400 mr-1"></i> View Only</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->hasPermission('staff', 'view'))
                <a href="{{ auth()->user()->staffUrl('staff') }}" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/90 hover:border-cyan-300 transition group shadow-2xs hover:shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-700 border border-cyan-200/70 flex items-center justify-center text-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 group-hover:text-cyan-800 transition">Staff Roster</div>
                        <div class="text-[10.5px] text-slate-500 font-medium">Faculty &amp; Rights</div>
                    </div>
                    @if(!auth()->user()->hasPermission('staff', 'edit'))
                        <span class="ml-auto text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><i class="fa-solid fa-eye text-slate-400 mr-1"></i> View Only</span>
                    @endif
                </a>
            @endif

            @if(auth()->user()->hasPermission('security', 'view'))
                <a href="{{ auth()->user()->staffUrl('password-resets') }}" class="flex items-center gap-3.5 p-3.5 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/90 hover:border-rose-300 transition group shadow-2xs hover:shadow-xs">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 border border-rose-200/70 flex items-center justify-center text-sm group-hover:scale-105 transition">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 group-hover:text-rose-700 transition">Password Resets</div>
                        <div class="text-[10.5px] text-slate-500 font-medium">Security &amp; Credentials</div>
                    </div>
                    @if(!auth()->user()->hasPermission('security', 'edit'))
                        <span class="ml-auto text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"><i class="fa-solid fa-eye text-slate-400 mr-1"></i> View Only</span>
                    @endif
                </a>
            @endif
        </div>
    </div>
    @endif

    <!-- LOGOUT LINK -->
    <div class="text-right pt-2">
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-xs font-bold text-slate-500 hover:text-rose-600 transition flex items-center gap-1.5 ml-auto">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sign Out</span>
            </button>
        </form>
    </div>
</div>

<!-- COMPLETE WEEKLY SCHEDULE MODAL -->
<div id="timetableModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 md:p-8 hidden">
    <div class="liquid-glass-card max-w-6xl w-full max-h-[92vh] overflow-y-auto p-6 md:p-8 relative bg-white border border-slate-200 shadow-2xl rounded-2xl">
        <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-200 sticky top-0 bg-white/95 z-20 backdrop-blur-md py-1">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-pink-50 text-pink-600 border border-pink-200/70 flex items-center justify-center text-xl shadow-2xs font-bold">
                    <i class="fa-solid fa-calendar-week"></i>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold text-slate-900 tracking-tight font-display">Timetable</h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">All assigned classes, sections, and laboratories for the active session.</p>
                </div>
            </div>
            <button type="button" onclick="closeTimetableModal()" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-900 flex items-center justify-center text-base transition border border-slate-200 shadow-2xs">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Weekly Timetable Grid -->
        <div class="overflow-x-auto">
            <div class="min-w-[850px]">
                <div class="grid grid-cols-6 gap-3 text-center mb-3">
                    <div class="text-xs font-extrabold text-pink-700 uppercase tracking-wider py-2.5 bg-pink-50 rounded-xl border border-pink-200/70">TIME</div>
                    @foreach($daysOfWeek as $day)
                        <div class="text-xs font-extrabold text-slate-700 uppercase tracking-wider py-2.5 bg-slate-100/90 rounded-xl border border-slate-200/90 shadow-2xs">
                            {{ ucfirst($day) }}
                        </div>
                    @endforeach
                </div>

                @foreach($timeSlotsList as $tSlot)
                    @php
                        $rowTimeRange = $tSlot;
                        foreach($daysOfWeek as $d) {
                            if (!empty($weeklyGrid[$tSlot][$d]['time_range'])) {
                                $rowTimeRange = $weeklyGrid[$tSlot][$d]['time_range'];
                                break;
                            }
                        }
                    @endphp
                    <div class="grid grid-cols-6 gap-3 mb-3">
                        <div class="text-xs text-pink-700 font-mono font-bold text-center flex flex-col items-center justify-center bg-pink-50/80 rounded-xl border border-pink-200/70 p-3 shadow-2xs">
                            <i class="fa-regular fa-clock text-pink-600 mb-1 text-sm"></i>
                            <span class="text-[11px] font-extrabold text-slate-900 leading-tight">{{ $rowTimeRange }}</span>
                        </div>
                        @foreach($daysOfWeek as $day)
                            @php $cell = $weeklyGrid[$tSlot][$day] ?? null; @endphp
                            @if($cell)
                                <div class="bg-white border border-slate-200/90 hover:border-pink-300 rounded-xl p-3.5 text-left shadow-2xs hover:shadow-xs transition flex flex-col justify-between space-y-2">
                                    <div class="text-xs font-extrabold text-slate-900 truncate">
                                        {{ $cell['subject_name'] }}
                                        <span class="text-pink-600 text-[10.5px] font-bold">({{ $cell['subject_code'] }})</span>
                                    </div>
                                    
                                    <!-- CLASS & SECTION -->
                                    <div class="text-[11px] font-extrabold text-indigo-700 flex items-center gap-1.5 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-200/60">
                                        <i class="fa-solid fa-chalkboard text-indigo-500 text-[10px]"></i>
                                        <span class="truncate">{{ $cell['section_name'] }}</span>
                                    </div>

                                    <!-- ROOM NUMBER -->
                                    <div class="text-[11px] font-bold text-slate-600 flex items-center gap-1.5 bg-slate-100/80 px-2.5 py-1 rounded-lg border border-slate-200/70">
                                        <i class="fa-solid fa-location-dot text-slate-400 text-[10px]"></i>
                                        <span class="truncate">{{ $cell['room_name'] }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="bg-slate-50/50 border border-dashed border-slate-200 rounded-xl p-3 flex items-center justify-center">
                                    <span class="text-[11px] text-slate-400 font-semibold">Free Slot</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500 font-semibold flex items-center gap-1.5">
                <i class="fa-solid fa-circle-info text-pink-500"></i> Timetable synced with Principal Matrix Engine.
            </span>
            <div class="flex items-center gap-3">
                <a href="{{ route('teacher.schedule') }}" class="btn-primary px-4 py-2 text-xs font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-up-right-and-down-left-from-center"></i> Full Page View
                </a>
                <button type="button" onclick="closeTimetableModal()" class="btn-secondary px-4 py-2 text-xs font-bold">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openTimetableModal() {
        document.getElementById('timetableModal').classList.remove('hidden');
    }
    function closeTimetableModal() {
        document.getElementById('timetableModal').classList.add('hidden');
    }
</script>
@endsection
