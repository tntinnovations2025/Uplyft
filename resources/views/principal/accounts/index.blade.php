@extends(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app')

@php
    $routePrefix = 'principal.accounts.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $prefixSlug = auth()->user()->getStaffUrlPrefix();
        $routePrefix = $prefixSlug . '.accounts.';
    }
    $canEditAccounts = auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin() || auth()->user()->hasPermission('accounts', 'edit');
    $canEditSalaries = auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin() || auth()->user()->hasPermission('staff_salaries', 'edit');
@endphp

@section('title', 'Finance & Accounts')
@section('breadcrumb', 'Finance & Accounts')

@section('content')
<div style="display:flex;flex-direction:column;gap:20px">


    {{-- ================= TAB: FACULTY & STAFF SALARIES ================= --}}
    @if($activeTab === 'salaries')
        <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:14px;box-shadow:0 2px 12px -2px rgba(0,0,0,0.03);overflow:hidden">
            {{-- Header & Search Section --}}
            <div style="padding:14px 20px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg, #4f46e5, #4338ca);display:flex;align-items:center;justify-content:center;font-size:15px;box-shadow:0 2px 8px rgba(79,70,229,0.2);color:#fff;flex-shrink:0">
                        💼
                    </div>
                    <div>
                        <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a;margin:0">
                            Faculty &amp; Staff Payroll &amp; Salaries
                        </h3>
                        <p style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500">
                            Manage monthly compensation, record salary payments, upload pay slips, and search members.
                        </p>
                    </div>
                </div>

                {{-- Search Bar by Name / Employee ID --}}
                <form method="GET" action="{{ route($routePrefix . 'index') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;flex:1;max-width:400px">
                    <input type="hidden" name="tab" value="salaries">
                    <input type="hidden" name="section" value="{{ $salarySection }}">
                    <div style="position:relative;flex:1">
                        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:#64748b">🔍</span>
                        <input type="text" name="search" value="{{ $salarySearch }}" placeholder="Search by name, email, or ID..." style="width:100%;padding:7px 12px 7px 34px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px;outline:none">
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding:7px 14px;font-size:12px">Search</button>
                    @if($salarySearch)
                        <a href="{{ route($routePrefix . 'index', ['tab' => 'salaries', 'section' => $salarySection]) }}" class="btn btn-danger" style="padding:7px 12px;font-size:12px">Reset</a>
                    @endif
                </form>
            </div>

            {{-- ⚡ AUTO SALARY DISBURSEMENT & INCOME DEDUCTION BANNER (COMPACT DESIGN) --}}
            <div style="margin:12px 20px 0;background:linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 45%, #FBF3E8 100%);border:1px solid #a7f3d0;border-radius:12px;padding:10px 16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:0 2px 10px -2px rgba(16,185,129,0.06)">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:36px;height:36px;border-radius:10px;background:#ffffff;border:1px solid #6ee7b7;color:#059669;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;box-shadow:0 2px 8px rgba(16,185,129,0.1)">
                        ⚡
                    </div>
                    <div>
                        <div style="font-family:'Outfit',sans-serif;font-size:14px;font-weight:800;color:#0f172a">
                            Automated Monthly Salary Disbursement &amp; Income Deduction
                        </div>
                        <p style="font-size:11.5px;color:#475569;margin-top:1px;font-weight:500">
                            Automatically deduct staff salaries from total accumulated institute income reserves.
                        </p>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                    <div style="display:flex;gap:10px">
                        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;padding:5px 12px;text-align:right;box-shadow:0 1px 4px rgba(0,0,0,0.02)">
                            <div style="font-size:9px;font-weight:800;letter-spacing:0.4px;text-transform:uppercase;color:#64748b">Total Income</div>
                            <div style="font-size:13px;font-weight:800;color:#059669;margin-top:1px">{{ $currencySymbol }} {{ number_format($totalIncome, 2) }}</div>
                        </div>
                        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;padding:5px 12px;text-align:right;box-shadow:0 1px 4px rgba(0,0,0,0.02)">
                            <div style="font-size:9px;font-weight:800;letter-spacing:0.4px;text-transform:uppercase;color:#64748b">Monthly Payroll</div>
                            <div style="font-size:13px;font-weight:800;color:#e11d48;margin-top:1px">{{ $currencySymbol }} {{ number_format($totalConfiguredPayroll, 2) }}</div>
                        </div>
                    </div>

                    @if($canEditSalaries)
                    <button type="button" onclick="openAutoDisburseModal()" style="background:linear-gradient(135deg, #10b981 0%, #059669 100%);color:#ffffff;border:none;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:800;cursor:pointer;box-shadow:0 3px 10px rgba(16,185,129,0.25);transition:all 0.2s">
                        ⚡ Auto-Disburse &amp; Deduct
                    </button>
                    @endif
                </div>
            </div>

            {{-- Segmented Section Toggle: Faculty vs Staff --}}
            <div style="display:flex;align-items:center;gap:10px;padding:10px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;margin-top:12px">
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'salaries', 'section' => 'faculty']) }}" class="btn {{ $salarySection === 'faculty' ? 'btn-primary' : 'btn-ghost' }}" style="font-size:12px;padding:6px 14px;border-radius:8px">
                    👨‍🏫 Faculty (Teaching Staff) — {{ $facultyList->count() }}
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'salaries', 'section' => 'staff']) }}" class="btn {{ $salarySection === 'staff' ? 'btn-primary' : 'btn-ghost' }}" style="font-size:12px;padding:6px 14px;border-radius:8px">
                    💼 Support &amp; Admin Staff — {{ $staffList->count() }}
                </a>
            </div>

            {{-- SECTION 1: FACULTY MEMBERS CARD GRID --}}
            @if($salarySection === 'faculty')
                @if($facultyList->isEmpty())
                    <div style="text-align:center;padding:40px 20px;background:#f8fafc;border-radius:14px;border:1px dashed #cbd5e1;margin:16px">
                        <div style="font-size:36px;margin-bottom:10px">👨‍🏫</div>
                        <h4 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:4px">No Faculty Members Found</h4>
                        <p style="font-size:12px;color:#64748b;max-width:380px;margin:0 auto">No teaching faculty members match your search criteria.</p>
                    </div>
                @else
                    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(290px, 1fr));gap:12px;padding:14px 20px">
                        @foreach($facultyList as $t)
                            <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:14px;padding:12px 14px;box-shadow:0 2px 10px -2px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;transition:transform 0.2s ease, border-color 0.2s ease">
                                <div>
                                    {{-- Card Top Header --}}
                                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px">
                                        <div style="display:flex;align-items:center;gap:10px">
                                            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);color:#ffffff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;box-shadow:0 3px 8px rgba(225,48,108,0.2);flex-shrink:0">
                                                {{ strtoupper(substr($t->first_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h4 style="font-family:'Outfit',sans-serif;font-size:14px;font-weight:800;color:#0f172a;margin:0;line-height:1.2">
                                                    {{ $t->full_name }}
                                                </h4>
                                                <div style="display:flex;align-items:center;gap:4px;margin-top:2px">
                                                    <span style="font-size:10px;font-weight:700;color:#059669;background:#ecfdf5;padding:1px 6px;border-radius:4px;border:1px solid #a7f3d0">
                                                        👨‍🏫 Faculty Teacher
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <span style="font-family:monospace;font-size:10px;font-weight:700;color:#D48A2E;background:#FBF3E8;padding:2px 6px;border-radius:6px;border:1px solid #E8CEAA;flex-shrink:0">
                                            {{ $t->employee_id ?: 'TCH-'.$t->id }}
                                        </span>
                                    </div>

                                    {{-- Key Info Box --}}
                                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;margin-bottom:10px;display:flex;flex-direction:column;gap:5px">
                                        <div style="display:flex;align-items:center;justify-content:space-between">
                                            <span style="font-size:11px;color:#64748b;font-weight:600">🎓 Qualification</span>
                                            <span style="font-size:11px;font-weight:700;color:#0f172a">{{ $t->qualification ?: 'Degree Verified' }}</span>
                                        </div>
                                        <div style="display:flex;align-items:center;justify-content:space-between">
                                            <span style="font-size:11px;color:#64748b;font-weight:600">📧 Email</span>
                                            <span style="font-size:11px;color:#334155;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $t->email }}">{{ $t->email }}</span>
                                        </div>
                                        <div style="display:flex;align-items:center;justify-content:space-between">
                                            <span style="font-size:11px;color:#64748b;font-weight:600">📞 Phone</span>
                                            <span style="font-size:11px;color:#334155">{{ $t->phone ?: 'Not provided' }}</span>
                                        </div>
                                    </div>

                                    {{-- Basic Salary & Slips --}}
                                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;margin-bottom:10px">
                                        <div>
                                            <div style="font-size:9.5px;font-weight:700;text-transform:uppercase;color:#059669;letter-spacing:0.4px">Monthly Basic Salary</div>
                                            <div style="font-family:'Outfit',sans-serif;font-size:14px;font-weight:800;color:#059669;margin-top:1px">
                                                Rs. {{ number_format($t->basic_salary_pkr ?: 0, 2) }}
                                            </div>
                                        </div>
                                        <span style="font-size:10px;font-weight:700;color:#D48A2E;background:#FBF3E8;padding:3px 8px;border-radius:6px;border:1px solid #E8CEAA">
                                            📄 {{ $t->salarySlips->count() }} Slip(s)
                                        </span>
                                    </div>
                                </div>

                                {{-- Card Footer Action Buttons --}}
                                <div style="display:grid;grid-template-columns:{{ $canEditSalaries ? '1fr 1fr' : '1fr' }};gap:8px;margin-top:2px">
                                    @if($canEditSalaries)
                                        <button type="button" class="btn btn-primary" onclick="openSalaryModal({{ $t->id }}, '{{ addslashes($t->full_name) }}')" style="padding:6px 10px;font-size:11px;display:flex;align-items:center;justify-content:center;gap:4px;border-radius:8px">
                                            <span>➕ Record Salary</span>
                                        </button>
                                    @endif
                                    <a href="{{ auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin() ? route('principal.directory.teacher', $t->id) : auth()->user()->staffUrl('directory/teacher/' . $t->id) }}" class="btn btn-ghost" style="padding:6px 10px;font-size:11px;display:flex;align-items:center;justify-content:center;gap:4px;border-radius:8px">
                                        <span>👁️ Slips &amp; Profile</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            {{-- SECTION 2: STAFF MEMBERS CARD GRID --}}
            @if($salarySection === 'staff')
                @if($staffList->isEmpty())
                    <div style="text-align:center;padding:40px 20px;background:#f8fafc;border-radius:14px;border:1px dashed #cbd5e1;margin:16px">
                        <div style="font-size:36px;margin-bottom:10px">💼</div>
                        <h4 style="font-size:16px;font-weight:700;color:#0f172a;margin-bottom:4px">No Administrative Staff Found</h4>
                        <p style="font-size:12px;color:#64748b;max-width:380px;margin:0 auto">No non-teaching support or admin staff match your search query.</p>
                    </div>
                @else
                    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(290px, 1fr));gap:12px;padding:14px 20px">
                        @foreach($staffList as $st)
                            <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:14px;padding:12px 14px;box-shadow:0 2px 10px -2px rgba(0,0,0,0.03);display:flex;flex-direction:column;justify-content:space-between;transition:transform 0.2s ease, border-color 0.2s ease">
                                <div>
                                    {{-- Card Top Header --}}
                                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px">
                                        <div style="display:flex;align-items:center;gap:10px">
                                            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);color:#ffffff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;box-shadow:0 3px 8px rgba(225,48,108,0.2);flex-shrink:0">
                                                {{ strtoupper(substr($st->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h4 style="font-family:'Outfit',sans-serif;font-size:14px;font-weight:800;color:#0f172a;margin:0;line-height:1.2">
                                                    {{ $st->name }}
                                                </h4>
                                                <div style="display:flex;align-items:center;gap:4px;margin-top:2px">
                                                    <span style="font-size:10px;font-weight:700;color:#9333ea;background:#fdf4ff;padding:1px 6px;border-radius:4px;border:1px solid #f5d0fe;text-transform:uppercase">
                                                        💼 {{ $st->role }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <span style="font-family:monospace;font-size:10px;font-weight:700;color:#64748b;background:#f1f5f9;padding:2px 6px;border-radius:6px;flex-shrink:0">
                                            {{ $st->identifier ?: 'STF-'.$st->id }}
                                        </span>
                                    </div>

                                    {{-- Key Info Box --}}
                                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;margin-bottom:10px;display:flex;flex-direction:column;gap:5px">
                                        <div style="display:flex;align-items:center;justify-content:space-between">
                                            <span style="font-size:11px;color:#64748b;font-weight:600">📧 Email</span>
                                            <span style="font-size:11px;color:#334155">{{ $st->email }}</span>
                                        </div>
                                        <div style="display:flex;align-items:center;justify-content:space-between">
                                            <span style="font-size:11px;color:#64748b;font-weight:600">📅 Registered</span>
                                            <span style="font-size:11px;color:#334155">{{ $st->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card Action Footer --}}
                                @if($canEditAccounts)
                                <button type="button" class="btn btn-ghost" onclick="openModal('addTransactionModal')" style="width:100%;padding:6px 10px;font-size:11px;display:flex;align-items:center;justify-content:center;gap:4px;border-radius:8px">
                                    <span>📋 Log Staff Compensation Expense</span>
                                </button>
                                @else
                                <div style="text-align:center;padding:6px;font-size:11px;color:#94a3b8;font-style:italic">
                                    👁️ View Only
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    @endif

    {{-- ================= TAB 1: FINANCIAL LEDGER & EXPENSES ================= --}}
    @if($activeTab === 'ledger' || $activeTab === 'transactions')
        <div style="display:flex;flex-direction:column;gap:18px">
            
            {{-- 1. Financial Ledger Header & Quick Action Hub --}}
            <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:16px;padding:18px 22px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;box-shadow:0 2px 12px -2px rgba(0,0,0,0.03)">
                <div style="display:flex;align-items:center;gap:12px">
                    <div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg, #059669, #10b981);display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 3px 10px rgba(16,185,129,0.25);color:#fff;flex-shrink:0">
                        💳
                    </div>
                    <div>
                        <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                            Expenses, Disbursements &amp; Financial Ledger
                        </h3>
                        <p style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500">
                            Record institutional expenses, utility bills, maintenance &amp; vendor disbursements for real-time AI Audit &amp; calculations.
                        </p>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <a href="{{ route($routePrefix . 'index', ['tab' => 'ai_report']) }}" class="btn btn-secondary btn-sm" style="padding:8px 16px;font-size:12.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px">
                        <span>🤖</span> View AI Audit &amp; P&amp;L
                    </a>
                    @if($canEditAccounts)
                        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addExpenseModal')" style="padding:8px 18px;font-size:12.5px;font-weight:800;background:linear-gradient(135deg, #059669, #10b981);border:none;box-shadow:0 4px 12px rgba(16,185,129,0.35)">
                            <span>➕</span> Record New Expense
                        </button>
                    @endif
                </div>
            </div>

            {{-- 2. Financial KPI Metric Highlights --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px">
                <!-- Total Income -->
                <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:14px;padding:16px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.02)">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <span style="font-size:11px;font-weight:800;text-transform:uppercase;color:#059669;letter-spacing:0.5px">Total Income Inflow</span>
                        <span style="font-size:16px">💰</span>
                    </div>
                    <div style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:#0f172a;margin-top:6px">
                        {{ $currencySymbol }} {{ number_format($totalIncome, 2) }}
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:4px">Cleared vouchers &amp; direct revenues</div>
                </div>

                <!-- Total Expenses -->
                <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:14px;padding:16px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.02)">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <span style="font-size:11px;font-weight:800;text-transform:uppercase;color:#e11d48;letter-spacing:0.5px">Total Operational Expenses</span>
                        <span style="font-size:16px">💸</span>
                    </div>
                    <div style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:#e11d48;margin-top:6px">
                        {{ $currencySymbol }} {{ number_format($totalExpense, 2) }}
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:4px">Bills, vendor payouts &amp; operational costs</div>
                </div>

                <!-- Net Cash Balance -->
                <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:14px;padding:16px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.02)">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <span style="font-size:11px;font-weight:800;text-transform:uppercase;color:{{ $netBalance >= 0 ? '#D48A2E' : '#e11d48' }};letter-spacing:0.5px">Net Operating Balance</span>
                        <span style="font-size:16px">⚖️</span>
                    </div>
                    <div style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:{{ $netBalance >= 0 ? '#D48A2E' : '#e11d48' }};margin-top:6px">
                        {{ $currencySymbol }} {{ number_format($netBalance, 2) }}
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:4px">{{ $netBalance >= 0 ? 'Surplus Operating Balance' : 'Operating Deficit' }}</div>
                </div>

                <!-- AI Audit Status -->
                <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:14px;padding:16px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.02)">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <span style="font-size:11px;font-weight:800;text-transform:uppercase;color:#6366f1;letter-spacing:0.5px">AI Financial Audit</span>
                        <span style="font-size:16px">🤖</span>
                    </div>
                    <div style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#4338ca;margin-top:6px">
                        Live Synchronized
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:4px">Ready for real-time calculation</div>
                </div>
            </div>

            {{-- 3. Filter Bar --}}
            <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:14px;padding:12px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                <form method="GET" action="{{ route($routePrefix . 'index') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;flex:1">
                    <input type="hidden" name="tab" value="ledger">
                    
                    <div style="display:flex;align-items:center;gap:6px">
                        <label style="font-size:11px;font-weight:800;color:#64748b">HEAD:</label>
                        <select name="head_id" onchange="this.form.submit()" style="padding:6px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;color:#0f172a;background:#ffffff">
                            <option value="">All Account Heads</option>
                            @foreach($heads as $h)
                                <option value="{{ $h->id }}" {{ $headFilter == $h->id ? 'selected' : '' }}>
                                    {{ $h->name }} ({{ strtoupper($h->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:flex;align-items:center;gap:6px">
                        <label style="font-size:11px;font-weight:800;color:#64748b">TYPE:</label>
                        <select name="type" onchange="this.form.submit()" style="padding:6px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:12px;color:#0f172a;background:#ffffff">
                            <option value="">All Types</option>
                            <option value="expense" {{ $typeFilter === 'expense' ? 'selected' : '' }}>Expenses Only</option>
                            <option value="income" {{ $typeFilter === 'income' ? 'selected' : '' }}>Income Only</option>
                        </select>
                    </div>

                    @if($headFilter || $typeFilter)
                        <a href="{{ route($routePrefix . 'index', ['tab' => 'ledger']) }}" class="btn btn-ghost btn-sm" style="font-size:11.5px;color:#e11d48;padding:5px 10px">
                            ✕ Reset Filter
                        </a>
                    @endif
                </form>

                @if($canEditAccounts)
                    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addExpenseModal')" style="background:linear-gradient(135deg, #059669, #10b981);border:none;font-weight:800">
                        ➕ Insert Expense
                    </button>
                @endif
            </div>

            {{-- 4. Transactions / Expenses Table --}}
            <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:16px;overflow:hidden;box-shadow:0 2px 12px -2px rgba(0,0,0,0.03)">
                @if($transactions->isEmpty())
                    <div style="text-align:center;padding:50px 20px;color:#64748b">
                        <div style="font-size:44px;margin-bottom:12px">🧾</div>
                        <h4 style="font-size:17px;color:#0f172a;margin-bottom:6px;font-family:'Outfit',sans-serif;font-weight:800">No Expenses or Transactions Recorded Yet</h4>
                        <p style="font-size:13px;margin-bottom:20px;color:#64748b;max-width:480px;margin-left:auto;margin-right:auto">
                            Record your campus electricity, stationery, internet, or maintenance expenses to synchronize real-time AI Audit &amp; calculations.
                        </p>
                        @if($canEditAccounts)
                            <button type="button" class="btn btn-primary" onclick="openModal('addExpenseModal')" style="background:linear-gradient(135deg, #059669, #10b981);border:none;padding:9px 22px;border-radius:10px;font-weight:800">
                                ➕ Insert First Expense for AI Audit
                            </button>
                        @endif
                    </div>
                @else
                    <div style="overflow-x:auto">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Title &amp; Reference</th>
                                    <th>Account Head</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Receipt / Bill</th>
                                    <th>Logged By</th>
                                    <th style="text-align:right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $tx)
                                    <tr>
                                        <td>
                                            <div style="font-weight:700;color:#0f172a;font-size:13px">
                                                {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}
                                            </div>
                                            <div style="font-size:11px;color:#94a3b8">{{ \Carbon\Carbon::parse($tx->transaction_date)->format('l') }}</div>
                                        </td>
                                        <td>
                                            <div style="font-weight:800;color:#0f172a;font-size:13.5px">{{ $tx->title }}</div>
                                            @if($tx->reference_number)
                                                <div style="font-size:11px;color:#64748b">Ref: <code>{{ $tx->reference_number }}</code></div>
                                            @endif
                                            @if($tx->notes)
                                                <div style="font-size:11px;color:#94a3b8;margin-top:2px;max-width:240px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $tx->notes }}">
                                                    {{ $tx->notes }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span style="font-size:12px;font-weight:700;color:#334155;background:#f8fafc;padding:4px 8px;border-radius:6px;border:1px solid #e2e8f0">
                                                {{ $tx->accountHead->name ?? 'General' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($tx->type === 'income')
                                                <span class="badge badge-green">INCOME</span>
                                            @else
                                                <span class="badge" style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;font-weight:800">EXPENSE</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="font-family:'Outfit',sans-serif;font-size:15px;font-weight:800;color:{{ $tx->type === 'income' ? '#059669' : '#dc2626' }}">
                                                {{ $tx->type === 'income' ? '+' : '-' }} {{ $currencySymbol }} {{ number_format($tx->amount, 2) }}
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size:12px;color:#475569;font-weight:600">{{ $tx->payment_method ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            @if($tx->receipt_image)
                                                @php
                                                    $isPdf = str_ends_with(strtolower($tx->receipt_image), '.pdf');
                                                    $fileUrl = asset('storage/' . $tx->receipt_image);
                                                @endphp
                                                @if($isPdf)
                                                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-ghost btn-sm" style="font-size:11px;padding:3px 8px;display:inline-flex;align-items:center;gap:4px">
                                                        📄 View PDF
                                                    </a>
                                                @else
                                                    <button type="button" onclick="viewReceiptImage('{{ $fileUrl }}', '{{ addslashes($tx->title) }}')" class="btn btn-ghost btn-sm" style="font-size:11px;padding:3px 8px;display:inline-flex;align-items:center;gap:4px">
                                                        🖼️ View Bill
                                                    </button>
                                                @endif
                                            @else
                                                <span style="font-size:11px;color:#94a3b8">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="font-size:12px;color:#0f172a;font-weight:700">{{ $tx->creator->name ?? 'System' }}</div>
                                        </td>
                                        <td style="text-align:right">
                                            @if($canEditAccounts)
                                                <form method="POST" action="{{ route($routePrefix . 'transactions.destroy', $tx->id) }}" onsubmit="return confirm('Are you sure you want to delete this transaction? It will be removed from AI calculations.')" style="display:inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 10px;font-size:11px">
                                                        🗑️ Delete
                                                    </button>
                                                </form>
                                            @else
                                                <span style="font-size:11px;color:#94a3b8">View Only</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($transactions->hasPages())
                        <div style="padding:14px 20px;border-top:1px solid #e2e8f0;background:#f8fafc">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    @endif

    {{-- ================= TAB 2: CUSTOMIZABLE ACCOUNT HEADS ================= --}}
    @if($activeTab === 'heads')
        <div class="card" style="margin-bottom:0">
            <div class="card-header">
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg, #4f46e5, #4338ca);display:flex;align-items:center;justify-content:center;font-size:15px;box-shadow:0 2px 8px rgba(79,70,229,0.2);color:#fff;flex-shrink:0">
                        💼
                    </div>
                    <div>
                        <h3 class="card-title">Customizable Institute Account Heads</h3>
                        <p style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500">
                            Create custom categories for utility bills, maintenance, salaries, or revenue streams. Configure receipt upload requirements per head.
                        </p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    @if($canEditAccounts)
                        @if(auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin())
                            <button type="button" class="btn btn-ghost btn-sm" onclick="openBulkFinanceImportModal()" style="border-color:#D48A2E;color:#D48A2E;background:#FBF3E8">
                                📥 Import Expenses
                            </button>
                        @endif
                        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addHeadModal')">
                            ➕ Create New Head
                        </button>
                    @endif
                </div>
            </div>

            @if($heads->isEmpty())
                <div style="text-align:center;padding:56px 20px;color:#64748b">
                    <div style="font-size:48px;margin-bottom:12px">⚙️</div>
                    <h4 style="font-size:18px;color:#0f172a;margin-bottom:6px;font-family:'Outfit',sans-serif;font-weight:700">No Account Heads Created Yet</h4>
                    <p style="font-size:13px;margin-bottom:20px;color:#64748b;max-width:480px;margin-left:auto;margin-right:auto">
                        No default options are created by system. Use the button below to create your custom income or expense account heads.
                    </p>
                    @if($canEditAccounts)
                    <button type="button" class="btn btn-primary" onclick="openModal('addHeadModal')">
                        ➕ Create First Account Head
                    </button>
                    @endif
                </div>
            @else
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Head Name</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Receipt Picture Policy</th>
                                <th>Status</th>
                                <th style="text-align:right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($heads as $hd)
                                <tr>
                                    <td>
                                        <div style="font-weight:800;color:#0f172a;font-size:14px">{{ $hd->name }}</div>
                                    </td>
                                    <td>
                                        @if($hd->type === 'income')
                                            <span class="badge badge-green">INCOME HEAD</span>
                                        @else
                                            <span class="badge" style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;font-weight:700">EXPENSE HEAD</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-size:12px;color:#64748b;font-weight:500">{{ $hd->description ?: 'No detailed description' }}</div>
                                    </td>
                                    <td>
                                        @if($hd->receipt_requirement === 'mandatory')
                                            <span class="badge badge-yellow">⚠️ MANDATORY PICTURE</span>
                                        @elseif($hd->receipt_requirement === 'optional')
                                            <span class="badge badge-blue">OPTIONAL ATTACHMENT</span>
                                        @else
                                            <span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1">NONE</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($hd->is_active)
                                            <span style="font-size:11px;color:#059669;font-weight:800">● Active</span>
                                        @else
                                            <span style="font-size:11px;color:#64748b;font-weight:700">○ Disabled</span>
                                        @endif
                                    </td>
                                    <td style="text-align:right">
                                        @if($canEditAccounts)
                                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                                            <button type="button" class="btn btn-ghost btn-sm" onclick="editAccountHead({{ $hd->id }}, '{{ addslashes($hd->name) }}', '{{ $hd->type }}', '{{ addslashes($hd->description ?? '') }}', '{{ $hd->receipt_requirement }}', {{ $hd->is_active ? 1 : 0 }})">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route($routePrefix . 'heads.destroy', $hd->id) }}" onsubmit="return classyConfirmForm(this, 'Delete Account Head', 'Are you sure you want to delete head \'{{ addslashes($hd->name) }}\'? This cannot be undone.', { danger: true, icon: '🗑️', confirmText: 'Yes, Delete Head' })">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                            </form>
                                        </div>
                                        @else
                                        <span style="font-size:11px;color:#94a3b8;font-style:italic">View Only</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    {{-- ================= TAB 3: AI PROFIT / LOSS ANALYSIS REPORT & GRAPHICAL ANALYTICS ================= --}}
    @if($activeTab === 'ai_report')
        @php
            $m = $aiReportData['metrics'];
            $isProfit = $m['is_profit'];
            $rawIncome = $m['raw_total_income'] ?? 0;
            $rawExpense = $m['raw_total_expense'] ?? 0;
            $rawNet = $m['raw_net_profit_loss'] ?? 0;
            $marginPct = max(0, min(100, (float)$m['profit_margin_pct']));
            
            $profitOffset = 283 - (283 * ($marginPct / 100));
            $expRatioPct = $rawIncome > 0 ? min(100, round(($rawExpense / $rawIncome) * 100, 1)) : ($rawExpense > 0 ? 100 : 0);
            $expRatioOffset = 283 - (283 * ($expRatioPct / 100));
        @endphp

        <div style="display:flex;flex-direction:column;gap:24px">
            
            {{-- 1. Audit Period Selector Bar --}}
            <div class="card" style="margin-bottom:0">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg, #4f46e5, #4338ca);display:flex;align-items:center;justify-content:center;font-size:15px;box-shadow:0 2px 8px rgba(79,70,229,0.2);color:#fff;flex-shrink:0">
                            💼
                        </div>
                        <div>
                            <h3 class="card-title" style="margin-bottom:2px">AI Financial &amp; Profit/Loss Audit Filter</h3>
                            <p style="font-size:12px;color:#64748b;font-weight:500">Select custom audit intervals to plot historical performance trajectory.</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route($routePrefix . 'index') }}" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <input type="hidden" name="tab" value="ai_report">
                        
                        <button type="submit" name="period" value="weekly" class="btn {{ $period === 'weekly' ? 'btn-primary' : 'btn-ghost' }} btn-sm">Weekly</button>
                        <button type="submit" name="period" value="monthly" class="btn {{ $period === 'monthly' ? 'btn-primary' : 'btn-ghost' }} btn-sm">Monthly</button>
                        <button type="submit" name="period" value="jan_jun" class="btn {{ $period === 'jan_jun' ? 'btn-primary' : 'btn-ghost' }} btn-sm">Jan - Jun</button>
                        <button type="submit" name="period" value="jul_dec" class="btn {{ $period === 'jul_dec' ? 'btn-primary' : 'btn-ghost' }} btn-sm">Jul - Dec</button>
                        <button type="submit" name="period" value="annual" class="btn {{ $period === 'annual' ? 'btn-primary' : 'btn-ghost' }} btn-sm">Annual</button>
                    </form>
                </div>

                {{-- Custom Date Interval --}}
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid #e2e8f0">
                    <form method="GET" action="{{ route($routePrefix . 'index') }}" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                        <input type="hidden" name="tab" value="ai_report">
                        <input type="hidden" name="period" value="custom">

                        <div style="display:flex;align-items:center;gap:6px">
                            <label style="font-size:11px;font-weight:700;color:#64748b">START DATE:</label>
                            <input type="date" name="start_date" value="{{ $customStart ?: date('Y-m-01') }}" style="padding:6px 10px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px">
                        </div>

                        <div style="display:flex;align-items:center;gap:6px">
                            <label style="font-size:11px;font-weight:700;color:#64748b">END DATE:</label>
                            <input type="date" name="end_date" value="{{ $customEnd ?: date('Y-m-t') }}" style="padding:6px 10px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;color:#0f172a;font-size:12px">
                        </div>

                        <button type="submit" class="btn btn-ghost btn-sm">
                            🔍 Run Custom Audit
                        </button>
                    </form>
                </div>
            </div>

            {{-- 2. Financial Metrics KPI Grid --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px">
                <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:18px;padding:22px;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#059669;letter-spacing:1px">Total Income</div>
                        <span style="font-size:18px">💰</span>
                    </div>
                    <div style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;margin-top:8px">
                        Rs. {{ $m['total_income'] }}
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:6px;font-weight:500">
                        Top Revenue Stream: <strong style="color:#059669">{{ $m['top_income_head'] }}</strong>
                    </div>
                </div>

                <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:18px;padding:22px;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#dc2626;letter-spacing:1px">Total Expenses</div>
                        <span style="font-size:18px">💸</span>
                    </div>
                    <div style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;margin-top:8px">
                        Rs. {{ $m['total_expense'] }}
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:6px;font-weight:500">
                        Primary Cost Driver: <strong style="color:#dc2626">{{ $m['top_expense_head'] }}</strong>
                    </div>
                </div>

                <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:18px;padding:22px;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:{{ $isProfit ? '#059669' : '#dc2626' }};letter-spacing:1px">Net Profit / (Loss)</div>
                        <span style="font-size:11px;padding:3px 8px;border-radius:100px;background:{{ $isProfit ? '#ecfdf5' : '#fee2e2' }};color:{{ $isProfit ? '#059669' : '#dc2626' }};font-weight:800">
                            {{ $isProfit ? '📈 GOING UP' : '📉 GOING DOWN' }}
                        </span>
                    </div>
                    <div style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:{{ $isProfit ? '#059669' : '#dc2626' }};margin-top:8px">
                        Rs. {{ $m['net_profit_loss'] }}
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:6px;font-weight:500">
                        Net Profit Margin: <strong style="color:#0f172a">{{ $m['profit_margin_pct'] }}%</strong>
                    </div>
                </div>

                <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:18px;padding:22px;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#d97706;letter-spacing:1px">Audit Context</div>
                        <span style="font-size:18px">📊</span>
                    </div>
                    <div style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin-top:8px">
                        {{ $m['period_title'] }}
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-top:6px;font-weight:500">
                        Processed <strong style="color:#d97706">{{ $m['transaction_count'] }} transaction(s)</strong>
                    </div>
                </div>
            </div>

            {{-- 3. GRAPHICAL ANALYTICS SUITE: Circular Progress Gauges + 6-Month Trajectory Plot --}}
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px">
                
                {{-- Left Column: Circular Progress Ring Analytics --}}
                <div class="card" style="margin-bottom:0;display:flex;flex-direction:column;gap:20px;justify-content:center">
                    <h4 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin:0">
                        Financial Circular Gauges
                    </h4>

                    {{-- Circular Ring 1: Net Profit Margin Rate --}}
                    <div style="display:flex;align-items:center;gap:16px;background:#f8fafc;padding:14px;border-radius:14px;border:1px solid #e2e8f0">
                        <div style="position:relative;width:80px;height:80px;flex-shrink:0">
                            <svg width="80" height="80" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#e2e8f0" stroke-width="8" />
                                <circle cx="50" cy="50" r="45" fill="none" stroke="{{ $isProfit ? '#059669' : '#dc2626' }}" stroke-width="8" stroke-dasharray="283" stroke-dashoffset="{{ $profitOffset }}" stroke-linecap="round" transform="rotate(-90 50 50)" />
                            </svg>
                            <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#0f172a">
                                {{ $marginPct }}%
                            </div>
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:800;color:#0f172a">Net Profit Margin</div>
                            <p style="font-size:11px;color:#64748b;margin-top:2px;font-weight:500">Percentage of total income retained as profit after expenses.</p>
                        </div>
                    </div>

                    {{-- Circular Ring 2: Expense to Income Ratio --}}
                    <div style="display:flex;align-items:center;gap:16px;background:#f8fafc;padding:14px;border-radius:14px;border:1px solid #e2e8f0">
                        <div style="position:relative;width:80px;height:80px;flex-shrink:0">
                            <svg width="80" height="80" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#e2e8f0" stroke-width="8" />
                                <circle cx="50" cy="50" r="45" fill="none" stroke="#dc2626" stroke-width="8" stroke-dasharray="283" stroke-dashoffset="{{ $expRatioOffset }}" stroke-linecap="round" transform="rotate(-90 50 50)" />
                            </svg>
                            <div style="position:absolute;top:0;left:0;right:0;bottom:0;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#dc2626">
                                {{ $expRatioPct }}%
                            </div>
                        </div>
                        <div>
                            <div style="font-size:13px;font-weight:800;color:#0f172a">Expense-to-Income Ratio</div>
                            <p style="font-size:11px;color:#64748b;margin-top:2px;font-weight:500">Ratio of institute expenses relative to incoming revenue.</p>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Graphical Bar Plot of Historical 6-Month Data --}}
                <div class="card" style="margin-bottom:0">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                        <div>
                            <h4 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin:0">
                                Historical 6-Month Cash Flow &amp; Trend Analytics
                            </h4>
                            <p style="font-size:11px;color:#64748b;margin-top:2px;font-weight:500">Plotting revenue, expenses, and net profit direction (Up 📈 / Down 📉) across recent months.</p>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;font-size:11px;font-weight:700">
                            <span style="display:flex;align-items:center;gap:4px;color:#059669"><span style="width:8px;height:8px;border-radius:50%;background:#059669"></span> Income</span>
                            <span style="display:flex;align-items:center;gap:4px;color:#dc2626"><span style="width:8px;height:8px;border-radius:50%;background:#dc2626"></span> Expense</span>
                        </div>
                    </div>

                    @php
                        $trends = $m['monthly_trends'] ?? [];
                        $maxVal = 1;
                        foreach ($trends as $tr) {
                            if ($tr['income'] > $maxVal) $maxVal = $tr['income'];
                            if ($tr['expense'] > $maxVal) $maxVal = $tr['expense'];
                        }
                    @endphp

                    <div style="display:grid;grid-template-columns:repeat(6, 1fr);gap:12px;align-items:end;height:180px;padding-top:20px;border-bottom:1px solid #e2e8f0;padding-bottom:8px">
                        @foreach($trends as $tr)
                            @php
                                $incHeight = max(12, round(($tr['income'] / $maxVal) * 140));
                                $expHeight = max(12, round(($tr['expense'] / $maxVal) * 140));
                            @endphp
                            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;height:100%;justify-content:flex-end">
                                {{-- Bar Pair --}}
                                <div style="display:flex;align-items:end;gap:4px;width:100%;justify-content:center">
                                    <div title="Income: Rs. {{ number_format($tr['income'], 2) }}" style="width:16px;height:{{ $incHeight }}px;background:linear-gradient(to top, #059669, #10b981);border-radius:4px 4px 0 0;transition:all 0.3s"></div>
                                    <div title="Expense: Rs. {{ number_format($tr['expense'], 2) }}" style="width:16px;height:{{ $expHeight }}px;background:linear-gradient(to top, #dc2626, #ef4444);border-radius:4px 4px 0 0;transition:all 0.3s"></div>
                                </div>
                                <span style="font-size:10px;font-weight:800;color:{{ $tr['is_up'] ? '#059669' : '#dc2626' }}">
                                    {{ $tr['is_up'] ? '📈 UP' : '📉 DOWN' }}
                                </span>
                                <span style="font-size:11px;color:#64748b;font-weight:700">{{ $tr['short_month'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- 4. AI Analysis Report Presentation Card --}}
            <div class="card" style="border:1px solid #e2e8f0;background:#ffffff;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
                <div class="card-header" style="border-bottom:1px solid #e2e8f0">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:36px;height:36px;border-radius:10px;background:#fdf4ff;display:flex;align-items:center;justify-content:center;font-size:20px;border:1px solid #f5d0fe">
                            🤖
                        </div>
                        <div>
                            <h3 class="card-title" style="font-size:18px">Executive AI Financial &amp; Profit/Loss Analysis Report</h3>
                            <div style="font-size:11px;color:#059669;font-weight:700">Audit Scope: {{ $m['period_title'] }}</div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-ghost btn-sm" onclick="window.print()">
                        🖨️ Print Report
                    </button>
                </div>

                {{-- Report Body --}}
                <div class="ai-report-presentation" style="line-height:1.7;color:#334155;font-size:14px;padding:24px">
                    @if(str_contains($aiReportData['ai_analysis'], '<div'))
                        {!! \App\Support\HtmlSanitizer::clean($aiReportData['ai_analysis']) !!}
                    @else
                        {!! \App\Support\HtmlSanitizer::clean(\Illuminate\Support\Str::markdown(e($aiReportData['ai_analysis']))) !!}
                    @endif
                </div>
            </div>

        </div>
    @endif

</div>

{{-- MODAL 1: ADD TRANSACTION --}}
<div id="addTransactionModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.5);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:16px">
    <div style="max-width:560px;width:100%;max-height:88vh;background:#ffffff;border:1px solid #e2e8f0;border-radius:22px;box-shadow:0 25px 60px rgba(0,0,0,0.15);display:flex;flex-direction:column;overflow:hidden">
        {{-- Modal Header --}}
        <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:#ffffff">
            <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                Log New Financial Transaction
            </h3>
            <button type="button" onclick="closeModal('addTransactionModal')" style="background:#f1f5f9;border:1px solid #cbd5e1;color:#64748b;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer;transition:all 0.2s">✕</button>
        </div>

        <form method="POST" action="{{ route($routePrefix . 'transactions.store') }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;flex:1;overflow:hidden;margin:0">
            @csrf

            {{-- Scrollable Form Body --}}
            <div style="padding:20px 24px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:16px">
                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Select Account Head *</label>
                    <select name="account_head_id" id="tx_head_select" onchange="checkHeadRequirement()" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                        <option value="">-- Select Account Head --</option>
                        @foreach($heads as $hd)
                            <option value="{{ $hd->id }}" data-requirement="{{ $hd->receipt_requirement }}" data-type="{{ $hd->type }}">
                                {{ $hd->name }} ({{ strtoupper($hd->type) }}) — {{ strtoupper($hd->receipt_requirement) }} RECEIPT
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="head_requirement_warning" style="display:none;padding:10px 14px;border-radius:8px;font-size:12px;font-weight:700"></div>

                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Title / Description *</label>
                    <input type="text" name="title" placeholder="e.g. Electric Bill for August / Stationary Purchase" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group" style="margin:0">
                        <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Amount (Rs.) *</label>
                        <input type="number" step="0.01" min="0.01" name="amount" placeholder="0.00" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Transaction Date *</label>
                        <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group" style="margin:0">
                        <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Payment Method *</label>
                        <select name="payment_method" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Online / Mobile Wallet">Online / Mobile Wallet</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Bill / Ref Number</label>
                        <input type="text" name="reference_number" placeholder="e.g. INV-90412" style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                    </div>
                </div>

                <div class="form-group" style="margin:0">
                    <label id="receipt_file_label" style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Receipt / Bill Attachment (Image or PDF)</label>
                    <input type="file" name="receipt_image" accept="image/*,application/pdf" style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                </div>

                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Notes / Additional Remarks</label>
                    <textarea name="notes" rows="2" placeholder="Optional notes for audit reference..." style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px"></textarea>
                </div>
            </div>

            {{-- Fixed Sticky Footer --}}
            <div style="padding:16px 24px;border-top:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-shrink:0">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addTransactionModal')" style="padding:9px 18px">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:9px 20px">Save Transaction Entry</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: ADD / EDIT ACCOUNT HEAD --}}
<div id="addHeadModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.5);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:16px">
    <div style="max-width:520px;width:100%;max-height:88vh;background:#ffffff;border:1px solid #e2e8f0;border-radius:22px;box-shadow:0 25px 60px rgba(0,0,0,0.15);display:flex;flex-direction:column;overflow:hidden">
        {{-- Modal Header --}}
        <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:#ffffff">
            <h3 id="headModalTitle" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                Create Account Head
            </h3>
            <button type="button" onclick="closeModal('addHeadModal')" style="background:#f1f5f9;border:1px solid #cbd5e1;color:#64748b;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer">✕</button>
        </div>

        <form id="headModalForm" method="POST" action="{{ route($routePrefix . 'heads.store') }}" style="display:flex;flex-direction:column;flex:1;overflow:hidden;margin:0">
            @csrf
            <input type="hidden" name="_method" id="headMethod" value="POST">

            {{-- Scrollable Form Body --}}
            <div style="padding:20px 24px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:16px">
                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Account Head Name *</label>
                    <input type="text" name="name" id="head_name" placeholder="e.g. Utility Bills - Electricity" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                </div>

                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Account Type *</label>
                    <select name="type" id="head_type" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                        <option value="expense">Expense Head (Utility, Repair, Salary, etc.)</option>
                        <option value="income">Income Head (Tuition, Lease, Grants, etc.)</option>
                    </select>
                </div>

                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Receipt Picture Requirement *</label>
                    <select name="receipt_requirement" id="head_receipt_req" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                        <option value="mandatory">⚠️ Mandatory (Picture required for every transaction under this head)</option>
                        <option value="optional">Optional (Staff can attach if available)</option>
                        <option value="none">None (No picture required)</option>
                    </select>
                </div>

                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Description / Notes</label>
                    <textarea name="description" id="head_description" rows="2" placeholder="Brief description for accountants..." style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px"></textarea>
                </div>

                <div id="head_active_container" class="form-group" style="display:none;margin:0">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;color:#0f172a;font-size:13px;font-weight:700">
                        <input type="checkbox" name="is_active" id="head_is_active" value="1" checked style="width:auto">
                        <span>Head is Active for Logging</span>
                    </label>
                </div>
            </div>

            {{-- Fixed Sticky Footer --}}
            <div style="padding:16px 24px;border-top:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-shrink:0">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addHeadModal')" style="padding:9px 18px">Cancel</button>
                <button type="submit" class="btn btn-primary" id="headModalSubmitBtn" style="padding:9px 20px">Save Account Head</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: RECEIPT VIEWER --}}
<div id="receiptViewerModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(8px);z-index:99999;align-items:center;justify-content:center;padding:16px">
    <div style="max-width:700px;width:100%;max-height:88vh;background:#ffffff;border:1px solid #e2e8f0;border-radius:22px;box-shadow:0 25px 60px rgba(0,0,0,0.2);display:flex;flex-direction:column;overflow:hidden">
        <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:#ffffff">
            <h3 id="receiptTitle" style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin:0">
                Receipt Attachment
            </h3>
            <button type="button" onclick="closeModal('receiptViewerModal')" style="background:#f1f5f9;border:1px solid #cbd5e1;color:#64748b;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer">✕</button>
        </div>
        <div style="padding:20px 24px;overflow-y:auto;flex:1;background:#f8fafc;display:flex;align-items:center;justify-content:center">
            <img id="receiptImageSrc" src="" alt="Receipt Image" style="max-width:100%;height:auto;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.1)">
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e2e8f0;background:#ffffff;display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-shrink:0">
            <button type="button" class="btn btn-ghost" onclick="closeModal('receiptViewerModal')" style="padding:9px 18px">Close</button>
            <a id="receiptDownloadLink" href="" target="_blank" class="btn btn-primary" style="padding:9px 20px">
                ↗ Open Original / Download
            </a>
        </div>
    </div>
</div>

{{-- MODAL 4: RECORD SALARY PAYMENT --}}
<div id="recordSalaryModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.5);backdrop-filter:blur(8px);z-index:9999;align-items:center;justify-content:center;padding:16px">
    <div style="max-width:520px;width:100%;max-height:88vh;background:#ffffff;border:1px solid #e2e8f0;border-radius:22px;box-shadow:0 25px 60px rgba(0,0,0,0.15);display:flex;flex-direction:column;overflow:hidden">
        {{-- Modal Header --}}
        <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:#ffffff">
            <div>
                <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                    Record Salary Payment
                </h3>
                <div id="salaryMemberName" style="font-size:12px;color:#059669;font-weight:700;margin-top:2px"></div>
            </div>
            <button type="button" onclick="closeModal('recordSalaryModal')" style="background:#f1f5f9;border:1px solid #cbd5e1;color:#64748b;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer">✕</button>
        </div>

        <form id="salaryPaymentForm" method="POST" action="" enctype="multipart/form-data" style="display:flex;flex-direction:column;flex:1;overflow:hidden;margin:0">
            @csrf

            {{-- Scrollable Form Body --}}
            <div style="padding:20px 24px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:16px">
                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Salary Voucher Title *</label>
                    <input type="text" name="title" id="sal_title" placeholder="e.g. Monthly Salary - August 2026" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group" style="margin:0">
                        <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Month &amp; Year *</label>
                        <input type="text" name="month_year" value="{{ date('F Y') }}" placeholder="e.g. August 2026" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                    </div>
                    <div class="form-group" style="margin:0">
                        <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Paid Amount (Rs.) *</label>
                        <input type="number" step="0.01" min="0.01" name="amount" placeholder="0.00" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px">
                    </div>
                </div>

                <div class="form-group" style="margin:0">
                    <label style="color:#ef4444;font-weight:700;font-size:11px;text-transform:uppercase">⚠️ MANDATORY: Salary Slip / Payment Proof Attachment (Required) *</label>
                    <input type="file" name="slip_file" accept="image/*,application/pdf" required style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #fca5a5;border-radius:10px;color:#0f172a;font-size:14px">
                    <div style="font-size:11px;color:#64748b;margin-top:4px">Accountant / Principal must upload a receipt picture or bank slip screenshot to maintain official audit records.</div>
                </div>

                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Notes / Payment Reference</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Paid via direct bank transfer / cheque #10923..." style="width:100%;padding:11px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:14px"></textarea>
                </div>
            </div>

            {{-- Fixed Sticky Footer --}}
            <div style="padding:16px 24px;border-top:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-shrink:0">
                <button type="button" class="btn btn-ghost" onclick="closeModal('recordSalaryModal')" style="padding:9px 18px">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:9px 20px">Save Salary Payment</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 5: RECORD NEW EXPENSE FOR AI AUDIT & CALCULATION --}}
<div id="addExpenseModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);z-index:99999;align-items:center;justify-content:center;padding:16px">
    <div style="max-width:540px;width:100%;max-height:90vh;background:#ffffff;border:1px solid #e2e8f0;border-radius:24px;box-shadow:0 25px 60px rgba(0,0,0,0.22);display:flex;flex-direction:column;overflow:hidden">
        {{-- Modal Header --}}
        <div style="padding:18px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%)">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg, #059669, #10b981);color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 3px 10px rgba(16,185,129,0.3);flex-shrink:0">
                    💸
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:17.5px;font-weight:800;color:#0f172a;margin:0">
                        Record Expense for AI Audit
                    </h3>
                    <p style="font-size:11.5px;color:#64748b;margin-top:2px;font-weight:500">
                        Inserts into ledger &amp; triggers instant AI financial recalculation.
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeModal('addExpenseModal')" style="background:#f1f5f9;border:1px solid #cbd5e1;color:#64748b;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer">✕</button>
        </div>

        <form method="POST" action="{{ route($routePrefix . 'transactions.store') }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;flex:1;overflow:hidden;margin:0">
            @csrf

            {{-- Scrollable Form Body --}}
            <div style="padding:20px 24px;overflow-y:auto;flex:1;display:flex;flex-direction:column;gap:14px">
                
                {{-- Dynamic Head Requirement Notice --}}
                <div id="head_requirement_warning" style="display:none;font-size:11.5px;font-weight:700;padding:8px 12px;border-radius:8px"></div>

                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Expense Title *</label>
                    <input type="text" name="title" placeholder="e.g. Monthly Electricity Bill, Lab Chemicals, Internet Bill" required style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13.5px">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group" style="margin:0">
                        <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Account Head Category *</label>
                        <select name="account_head_id" id="tx_head_select" onchange="checkHeadRequirement()" required style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13px">
                            <option value="">Select Account Head</option>
                            @foreach($heads as $hd)
                                <option value="{{ $hd->id }}" data-requirement="{{ $hd->receipt_requirement }}" {{ $hd->type === 'expense' ? '' : 'style=color:#059669' }}>
                                    {{ $hd->name }} ({{ strtoupper($hd->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="margin:0">
                        <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Amount (PKR) *</label>
                        <input type="number" step="0.01" min="0.01" name="amount" placeholder="0.00" required style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13.5px">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group" style="margin:0">
                        <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Expense Date *</label>
                        <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13px">
                    </div>

                    <div class="form-group" style="margin:0">
                        <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Payment Method *</label>
                        <select name="payment_method" required style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13px">
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Online / Mobile Banking">Online / Mobile Banking</option>
                            <option value="Card">Debit / Credit Card</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Reference / Cheque / Voucher Number</label>
                    <input type="text" name="reference_number" placeholder="e.g. CHQ-99021, INV-8812, TR-1092" style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13.5px">
                </div>

                <div class="form-group" style="margin:0">
                    <label id="receipt_file_label" style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Receipt / Bill Attachment (Optional)</label>
                    <input type="file" name="receipt_image" accept="image/*,application/pdf" style="width:100%;padding:9px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:12px">
                    <div style="font-size:10.5px;color:#64748b;margin-top:3px">Attach photo, scanned invoice, or PDF receipt for AI audit validation.</div>
                </div>

                <div class="form-group" style="margin:0">
                    <label style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase">Audit Memo &amp; Notes</label>
                    <textarea name="notes" rows="2" placeholder="Describe expenditure purpose for AI Audit &amp; calculations..." style="width:100%;padding:10px 14px;background:#ffffff;border:1px solid #cbd5e1;border-radius:10px;color:#0f172a;font-size:13px"></textarea>
                </div>
            </div>

            {{-- Sticky Modal Footer --}}
            <div style="padding:16px 24px;border-top:1px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:flex-end;gap:12px;flex-shrink:0">
                <button type="button" class="btn btn-ghost" onclick="closeModal('addExpenseModal')" style="padding:9px 18px">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:9px 22px;background:linear-gradient(135deg, #059669, #10b981);border:none;font-weight:800;box-shadow:0 4px 12px rgba(16,185,129,0.3)">
                    💾 Save Expense &amp; Trigger AI Calculation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('action') === 'new_expense') {
            openModal('addExpenseModal');
        }
    });

    function openSalaryModal(teacherId, teacherName) {
        document.getElementById('salaryMemberName').innerText = 'Faculty Member: ' + teacherName;
        document.getElementById('sal_title').value = 'Monthly Salary — ' + new Date().toLocaleString('default', { month: 'long', year: 'numeric' });
        let storeRoute = "{{ route($routePrefix . 'salaries.store', 9999) }}".replace('9999', teacherId);
        document.getElementById('salaryPaymentForm').action = storeRoute;
        openModal('recordSalaryModal');
    }

    function checkHeadRequirement() {
        const select = document.getElementById('tx_head_select');
        const selectedOpt = select.options[select.selectedIndex];
        const req = selectedOpt ? selectedOpt.getAttribute('data-requirement') : null;
        const warningBox = document.getElementById('head_requirement_warning');
        const fileLabel = document.getElementById('receipt_file_label');

        if (req === 'mandatory') {
            warningBox.style.display = 'block';
            warningBox.style.background = '#fee2e2';
            warningBox.style.border = '1px solid #fca5a5';
            warningBox.style.color = '#dc2626';
            warningBox.innerHTML = '⚠️ MANDATORY: You MUST upload a receipt / bill picture for this head.';
            fileLabel.innerHTML = 'Receipt / Bill Attachment (MANDATORY) *';
        } else if (req === 'optional') {
            warningBox.style.display = 'block';
            warningBox.style.background = '#FBF3E8';
            warningBox.style.border = '1px solid #E8CEAA';
            warningBox.style.color = '#D48A2E';
            warningBox.innerHTML = '💡 OPTIONAL: You can attach a receipt / bill picture if available.';
            fileLabel.innerHTML = 'Receipt / Bill Attachment (Optional)';
        } else {
            warningBox.style.display = 'none';
            fileLabel.innerHTML = 'Receipt / Bill Attachment (Optional)';
        }
    }

    function editAccountHead(id, name, type, description, receiptReq, isActive) {
        document.getElementById('headModalTitle').innerText = 'Edit Account Head';
        document.getElementById('headMethod').value = 'PUT';
        document.getElementById('head_name').value = name;
        document.getElementById('head_type').value = type;
        document.getElementById('head_description').value = description;
        document.getElementById('head_receipt_req').value = receiptReq;
        document.getElementById('head_is_active').checked = (isActive == 1);
        document.getElementById('head_active_container').style.display = 'block';
        
        let updateRoute = "{{ route($routePrefix . 'heads.update', 9999) }}".replace('9999', id);
        document.getElementById('headModalForm').action = updateRoute;
        
        openModal('addHeadModal');
    }

    function viewReceiptImage(url, title) {
        document.getElementById('receiptTitle').innerText = 'Receipt: ' + title;
        document.getElementById('receiptImageSrc').src = url;
        document.getElementById('receiptDownloadLink').href = url;
        openModal('receiptViewerModal');
    }

    function openAutoDisburseModal() {
        openModal('autoDisburseModal');
    }

    function closeAutoDisburseModal() {
        closeModal('autoDisburseModal');
    }
</script>

<!-- AUTO DISBURSEMENT CONFIRMATION MODAL -->
<div id="autoDisburseModal" class="apple-liquid-glass-overlay" style="display:none;z-index:99999;padding:20px;box-sizing:border-box">
    <div class="apple-liquid-glass-card" style="width:100%;max-width:540px;max-height:90vh;overflow-y:auto;padding:26px 28px;border-radius:24px;box-sizing:border-box;box-shadow:0 25px 60px -12px rgba(15,23,42,0.35)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px;border-bottom:1px solid #e2e8f0;padding-bottom:14px">
            <div>
                <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                    Confirm Salary Disbursement &amp; Income Deduction
                </h3>
                <p style="font-size:12px;color:#64748b;margin-top:4px">
                    Disburse salaries for {{ now()->format('F Y') }} and deduct total payroll directly from collected income.
                </p>
            </div>
            <button type="button" onclick="closeAutoDisburseModal()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;padding:4px">✕</button>
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:16px;margin-bottom:20px;display:flex;flex-direction:column;gap:12px">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:13px;color:#64748b;font-weight:600">Total Income Collected So Far</span>
                <span style="font-size:14px;font-weight:800;color:#059669">{{ $currencySymbol }} {{ number_format($totalIncome, 2) }}</span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:13px;color:#64748b;font-weight:600">Total Monthly Staff Payroll</span>
                <span style="font-size:14px;font-weight:800;color:#e11d48">- {{ $currencySymbol }} {{ number_format($totalConfiguredPayroll, 2) }}</span>
            </div>
            <div style="border-top:1px dashed #cbd5e1;padding-top:10px;display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:13px;color:#0f172a;font-weight:800">Estimated Net Income Remaining</span>
                <span style="font-size:16px;font-weight:800;color:{{ ($totalIncome - $totalConfiguredPayroll) >= 0 ? '#D48A2E' : '#e11d48' }}">
                    {{ $currencySymbol }} {{ number_format($totalIncome - $totalConfiguredPayroll, 2) }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route($routePrefix . 'salaries.auto-disburse') }}">
            @csrf
            <div style="font-size:12.5px;color:#475569;background:#FBF3E8;border:1px solid #E8CEAA;border-radius:12px;padding:12px 16px;margin-bottom:20px;line-height:1.5;font-weight:600">
                ℹ️ Executing this action will auto-generate expense ledger entries for all active faculty &amp; staff members, generate monthly salary slips for {{ now()->format('F Y') }}, and deduct the total payroll amount from total institute income reserves.
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;border-top:1px solid #e2e8f0;padding-top:16px">
                <button type="button" onclick="closeAutoDisburseModal()" class="btn btn-secondary" style="padding:10px 20px;border-radius:12px;font-size:13px">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #10b981, #059669);border:none;padding:10px 24px;border-radius:12px;font-weight:800;color:#fff;font-size:13px">
                    ⚡ Confirm &amp; Disburse All Salaries
                </button>
            </div>
        </form>
    </div>
</div>

{{-- BULK IMPORT FINANCE MODAL --}}
<div id="bulkFinanceImportModal" class="modal-backdrop">
    <div class="modal-box" style="max-width:520px;border-radius:18px;padding:24px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="width:36px;height:36px;border-radius:10px;background:#FBF3E8;color:#D48A2E;display:flex;align-items:center;justify-content:center;font-size:18px">
                    📥
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a;margin:0">Bulk Import Financial Transactions</h3>
                    <p style="font-size:11.5px;color:#64748b;margin-top:2px">Upload CSV or Excel file to import legacy income &amp; expense transactions.</p>
                </div>
            </div>
            <button type="button" onclick="closeBulkFinanceImportModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b">&times;</button>
        </div>

        <form method="POST" action="{{ route('principal.bulk-import.finance') }}" enctype="multipart/form-data">
            @csrf

            <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:14px;margin-bottom:16px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <span style="font-size:12px;font-weight:700;color:#0f172a">📁 Sample CSV Template</span>
                    <a href="{{ route('principal.bulk-import.sample', 'finance') }}" class="btn btn-ghost btn-sm" style="font-size:11px;color:#D48A2E;padding:4px 10px">
                        ⬇️ Download Sample CSV
                    </a>
                </div>
                <p style="font-size:11px;color:#64748b;margin:0">
                    Expected columns: <code>Category</code> (or <code>Head</code>), <code>Type</code> (Income/Expense), <code>Amount</code>, <code>Date</code>, <code>Notes</code>. Unrecognized categories will be created automatically.
                </p>
            </div>

            <div style="margin-bottom:20px">
                <label style="display:block;font-size:12px;font-weight:700;color:#0f172a;margin-bottom:6px">Select CSV / Excel File</label>
                <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required style="width:100%;padding:8px;border:1px dashed #D48A2E;background:#FBF3E8;border-radius:10px;font-size:12px">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" class="btn btn-ghost" onclick="closeBulkFinanceImportModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">⚡ Start Bulk Import</button>
            </div>
        </form>
    </div>
</div>

<style>
    .modal-backdrop {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }
    .modal-backdrop.active {
        display: flex;
    }
    .modal-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        padding: 24px;
        position: relative;
        color: #0f172a;
    }
</style>

<script>
    function openBulkFinanceImportModal() {
        const modal = document.getElementById('bulkFinanceImportModal');
        if (modal) {
            modal.classList.add('active');
            modal.style.display = 'flex';
        }
    }
    function closeBulkFinanceImportModal() {
        const modal = document.getElementById('bulkFinanceImportModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
        }
    }
    window.openBulkFinanceImportModal = openBulkFinanceImportModal;
    window.closeBulkFinanceImportModal = closeBulkFinanceImportModal;
    document.getElementById('bulkFinanceImportModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeBulkFinanceImportModal();
    });
</script>
@endsection

