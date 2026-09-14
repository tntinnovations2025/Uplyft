@extends('principal.layouts.app')

@section('title', 'Institute Settings & Configuration')
@section('breadcrumb', 'Institute Settings')

@section('content')
<style>
    .settings-container {
        max-width: 1140px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .settings-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 24px 28px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
    }

    .settings-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .settings-grid { grid-template-columns: 1fr; }
    }

    .settings-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 26px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
        display: flex;
        flex-direction: column;
        gap: 20px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .settings-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
    }

    .settings-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .settings-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-label {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #334155;
    }

    .form-hint {
        font-size: 11.5px;
        color: #64748b;
        margin-top: 2px;
    }

    .form-input {
        width: 100%;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 13.5px;
        font-weight: 600;
        color: #0f172a;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-input:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }

    /* Mode Selection Tiles */
    .mode-tile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .mode-tile {
        background: #ffffff;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        gap: 8px;
        position: relative;
    }

    .mode-tile:hover {
        border-color: #4f46e5;
        background: #f8fafc;
    }

    .mode-tile.selected {
        background: #eef2ff;
        border-color: #4f46e5;
        box-shadow: 0 4px 16px rgba(79, 70, 229, 0.12);
    }

    .mode-tile .tile-title {
        font-size: 14px;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mode-tile.selected .tile-title {
        color: #4338ca;
    }

    .mode-tile .tile-desc {
        font-size: 11.5px;
        color: #64748b;
        line-height: 1.4;
    }

    /* Toggle Switch */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        flex-shrink: 0;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background-color: #cbd5e1;
        transition: .3s;
        border-radius: 24px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .toggle-switch input:checked + .toggle-slider {
        background-color: #4f46e5;
    }
    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(20px);
    }

    .toggle-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 14px 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }
</style>

<div class="settings-container">
    @php
        $activeTab = request('tab', 'financial');
    @endphp

    <!-- Quick Links: Related Administration Settings -->
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:14px 18px;box-shadow:0 4px 20px rgba(15,23,42,0.03);">
        <span style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.6px;">Quick Links</span>
        <a href="{{ route('principal.settings.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;font-size:12.5px;font-weight:700;color:#0f172a;background:#f1f5f9;border:1px solid #e2e8f0;text-decoration:none;">🏠 Institute Settings</a>
        @if(Route::has('principal.security.edit'))
            <a href="{{ route('principal.security.edit') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;font-size:12.5px;font-weight:700;color:#0f172a;background:#f1f5f9;border:1px solid #e2e8f0;text-decoration:none;">🔐 Security &amp; 2FA</a>
        @endif
        <a href="{{ route('principal.attendance-settings.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;font-size:12.5px;font-weight:700;color:#0f172a;background:#f1f5f9;border:1px solid #e2e8f0;text-decoration:none;">⏰ Attendance Lock Rules</a>
        @if(isset($topOrg) && $topOrg && auth()->user()->isPrincipal())
            <a href="{{ route('principal.organization.campuses.index') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;font-size:12.5px;font-weight:700;color:#0f172a;background:#f1f5f9;border:1px solid #e2e8f0;text-decoration:none;">🏢 Manage Campuses</a>
        @endif
        <a href="{{ route('profile.edit') }}" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;font-size:12.5px;font-weight:700;color:#0f172a;background:#f1f5f9;border:1px solid #e2e8f0;text-decoration:none;">👤 Personal Settings</a>
    </div>

    <!-- Sub-Menu: Settings Sections -->
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:12px 14px;box-shadow:0 4px 20px rgba(15,23,42,0.03);">
        @php
            $tabs = [
                'financial'     => ['💰', 'Financial & Bank'],
                'payroll'       => ['💵', 'Staff Payroll'],
                'breaks'        => ['☕', 'Class & Faculty Breaks'],
                'academic'      => ['🏛️', 'Academic & Profile'],
                'branding'      => ['🎨', 'Branding'],
                'notifications' => ['📧', 'Notifications & Contact'],
            ];
        @endphp
        @foreach($tabs as $key => [$icon, $label])
            <a href="?tab={{ $key }}"
               style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:999px;font-size:12.5px;font-weight:800;text-decoration:none;transition:all 0.2s ease;
                      {{ $activeTab === $key ? 'background:linear-gradient(135deg,#4f46e5,#6366f1);color:#ffffff;box-shadow:0 3px 10px rgba(79,70,229,0.3);' : 'background:#f8fafc;color:#475569;border:1px solid #e2e8f0;' }}">
                <span style="font-size:14px">{{ $icon }}</span>{{ $label }}
            </a>
        @endforeach
        @if($activeTab !== 'all')
            <a href="?tab=all" style="display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:999px;font-size:12.5px;font-weight:800;text-decoration:none;transition:all 0.2s ease;background:#ffffff;color:#4f46e5;border:1px dashed #a5b4fc;">
                🗂️ View All &amp; Save Everything
            </a>
        @endif
    </div>
    
    <!-- Top Header & Save Bar -->
    <form method="POST" action="{{ route('principal.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="active_tab" value="{{ $activeTab }}">



        <div class="settings-grid" style="{{ $activeTab !== 'all' ? 'display:flex;flex-direction:column;gap:24px' : '' }}">
            
            <!-- CARD 1: 💰 Financial Targets & Budgets -->
            @if($activeTab === 'financial' || $activeTab === 'all')
                <div class="settings-card" id="card-financial">
                    <div class="settings-card-header">
                        <div class="settings-card-icon" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0">
                            💰
                        </div>
                        <div>
                            <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a">
                                Financial Targets &amp; Operations
                            </h3>
                            <p style="font-size:12px;color:#64748b;margin-top:2px">
                                Set monthly target revenue and budget lines shown on the analytics charts.
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="monthly_income_target">Monthly Income Target ({{ $currencySymbol }}) *</label>
                        <input type="number" step="1000" id="monthly_income_target" name="monthly_income_target" value="{{ old('monthly_income_target', $setting->monthly_income_target) }}" required class="form-input" placeholder="e.g. 500000">
                        <span class="form-hint">Used as the primary collection benchmark line on your executive financial graph.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="monthly_expense_budget">Monthly Expense Budget ({{ $currencySymbol }}) *</label>
                        <input type="number" step="1000" id="monthly_expense_budget" name="monthly_expense_budget" value="{{ old('monthly_expense_budget', $setting->monthly_expense_budget) }}" required class="form-input" placeholder="e.g. 350000">
                        <span class="form-hint">Operational spending cap benchmark for monthly expense analytics.</span>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                        <div class="form-group">
                            <label class="form-label" for="fee_due_day_of_month">Fee Due Day *</label>
                            <input type="number" min="1" max="31" id="fee_due_day_of_month" name="fee_due_day_of_month" value="{{ old('fee_due_day_of_month', $setting->fee_due_day_of_month) }}" required class="form-input" placeholder="e.g. 10">
                            <span class="form-hint">Day of month (1-31)</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="late_fee_fine_amount">Late Fine ({{ $currencySymbol }}) *</label>
                            <input type="number" step="50" min="0" id="late_fee_fine_amount" name="late_fee_fine_amount" value="{{ old('late_fee_fine_amount', $setting->late_fee_fine_amount) }}" required class="form-input" placeholder="e.g. 500">
                            <span class="form-hint">Fine applied after due date</span>
                        </div>
                    </div>

                    <div class="form-group" style="position:relative">
                        <label class="form-label" for="currency_symbol">Currency Code / Symbol *</label>
                        <input type="hidden" id="currency_symbol" name="currency_symbol" value="{{ old('currency_symbol', $setting->currency_symbol ?? 'PKR') }}">

                        <div id="currency_dropdown_wrapper" style="position:relative">
                            <!-- Trigger Button -->
                            <button type="button" id="currency_trigger_btn" onclick="toggleCurrencyDropdown()" 
                                    style="width:100%;background:#ffffff;border:1.5px solid #cbd5e1;border-radius:10px;padding:10px 14px;font-size:13.5px;font-weight:700;color:#0f172a;display:flex;align-items:center;justify-content:space-between;cursor:pointer;transition:all 0.2s ease;outline:none">
                                <div style="display:flex;align-items:center;gap:8px;min-width:0">
                                    <span id="currency_selected_text" style="font-size:13.5px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">PKR - Pakistan Rupee</span>
                                </div>
                                <span id="currency_chevron" style="font-size:11px;color:#64748b;transition:transform 0.2s ease;flex-shrink:0">▼</span>
                            </button>

                            <!-- Searchable Dropdown Menu -->
                            <div id="currency_dropdown_menu" 
                                 style="display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:9999;background:#ffffff;border:1.5px solid #c7d2fe;border-radius:14px;box-shadow:0 12px 36px rgba(15,23,42,0.16);padding:12px;overflow:hidden">
                                
                                <!-- Search Input Bar -->
                                <div style="position:relative;margin-bottom:8px">
                                    <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:#94a3b8">🔍</span>
                                    <input type="text" id="currency_search_input" placeholder="Search currency by name, code or symbol..." onkeyup="filterCurrencyOptions(this.value)"
                                           style="width:100%;padding:9px 12px 9px 34px;background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:9px;font-size:12.5px;font-weight:600;color:#0f172a;outline:none" autocomplete="off" />
                                </div>

                                <!-- Currency Options List -->
                                <div id="currency_options_list" style="max-height:220px;overflow-y:auto;display:flex;flex-direction:column;gap:2px;padding-right:2px">
                                    <!-- Populated via JS -->
                                </div>
                                <div id="currency_no_results" style="display:none;padding:12px;text-align:center;font-size:12px;color:#64748b;font-weight:600">
                                    No matching currency found.
                                </div>
                            </div>
                        </div>
                        <span class="form-hint">Selected currency code/symbol will format all financial records and student fee invoices across campus.</span>
                    </div>
                </div>
            @endif
            <!-- CARD 1.5: 💵 Staff Salaries & Leave Policy -->
            @if($activeTab === 'payroll' || $activeTab === 'all')
                <div class="settings-card" id="card-payroll">
                    <div class="settings-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
                        <div style="display:flex;align-items:center;gap:14px">
                            <div class="settings-card-icon" style="background:#ecfdf5;color:#059669;border:1px solid #a7f3d0">
                                💵
                            </div>
                            <div>
                                <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a">
                                    Staff Salaries &amp; Leave Allowance Policy
                                </h3>
                                <p style="font-size:12px;color:#64748b;margin-top:2px">
                                    Configure individual employee monthly basic salaries, allowed absent leaves per month (excluding official faculty working off-days), and leave deduction rules.
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('principal.accounts.salaries.auto-disburse') }}" style="margin:0" onsubmit="return confirm('⚡ Confirm auto-disbursing all staff salaries for {{ now()->format('F Y') }} and deducting from total income?');">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #10b981, #059669);border:none;padding:9px 18px;border-radius:10px;font-weight:800;color:#fff;font-size:12.5px;box-shadow:0 3px 12px rgba(16,185,129,0.3)">
                                ⚡ Auto-Disburse &amp; Deduct Salaries
                            </button>
                        </form>
                    </div>

                    <!-- STAFF ROSTER TABLE -->
                    <div style="margin-top:16px;overflow-x:auto;border:1px solid #e2e8f0;border-radius:14px">
                        <table style="width:100%;border-collapse:collapse;text-align:left">
                            <thead>
                                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                                    <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:800">Employee Name</th>
                                    <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:800">Role / Title</th>
                                    <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:800">Monthly Basic Salary</th>
                                    <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:800">Allowed Absences</th>
                                    <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:800">Payout Day</th>
                                    <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:800">Deduction Policy</th>
                                    <th style="padding:12px 16px;font-size:11px;text-transform:uppercase;color:#64748b;font-weight:800;text-align:right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staffMembers as $staff)
                                    @php
                                        $sal = $staff->basic_salary_pkr ?? ($staff->teacherProfile->basic_salary_pkr ?? null);
                                        $abs = $staff->allowed_absent_days_per_month ?? ($staff->teacherProfile->allowed_absent_days_per_month ?? 2);
                                        $pday = $staff->salary_disbursement_day ?? ($staff->teacherProfile->salary_disbursement_day ?? 1);
                                        $dtype = $staff->salary_deduction_type ?? ($staff->teacherProfile->salary_deduction_type ?? 'pro_rata');
                                        $ffine = $staff->fixed_absent_deduction_amount ?? ($staff->teacherProfile->fixed_absent_deduction_amount ?? 0);
                                        $roleTitle = $staff->staff_role ?? ucfirst($staff->role);
                                    @endphp
                                    <tr style="border-bottom:1px solid #f1f5f9;cursor:pointer"
                                        data-staff-id="{{ $staff->id }}"
                                        data-staff-name="{{ e($staff->name) }}"
                                        data-role-title="{{ e($roleTitle) }}"
                                        data-salary="{{ $sal ?? '' }}"
                                        data-absent-days="{{ $abs }}"
                                        data-payout-day="{{ $pday }}"
                                        data-deduction-type="{{ $dtype }}"
                                        data-fixed-fine="{{ $ffine }}"
                                        onclick="openStaffPayrollFromElem(this)">
                                        <td style="padding:14px 16px">
                                            <div style="font-weight:800;color:#0f172a;font-size:14px">{{ $staff->name }}</div>
                                            <div style="font-size:11.5px;color:#64748b;margin-top:1px">✉️ {{ $staff->email }}</div>
                                        </td>
                                        <td style="padding:14px 16px">
                                            <span class="badge badge-purple" style="font-size:11px;font-weight:700">
                                                {{ $roleTitle }}
                                            </span>
                                        </td>
                                        <td style="padding:14px 16px">
                                            @if($sal)
                                                <span style="font-weight:800;color:#059669;font-size:14px">
                                                    {{ $currencySymbol }} {{ number_format($sal, 2) }}
                                                </span>
                                            @else
                                                <span style="font-size:12px;color:#94a3b8;font-style:italic">Not Configured</span>
                                            @endif
                                        </td>
                                        <td style="padding:14px 16px">
                                            <span class="badge badge-blue" style="font-size:11px;font-weight:700">
                                                {{ $abs }} Days / Month
                                            </span>
                                        </td>
                                        <td style="padding:14px 16px">
                                            <span style="font-size:13px;font-weight:700;color:#334155">
                                                🗓️ Day {{ $pday }}
                                            </span>
                                        </td>
                                        <td style="padding:14px 16px">
                                            @if($dtype === 'pro_rata')
                                                <span style="font-size:12px;font-weight:700;color:#0284c7">Pro-Rata Daily Rate</span>
                                            @elseif($dtype === 'fixed')
                                                <span style="font-size:12px;font-weight:700;color:#d97706">Fixed Fine ({{ $currencySymbol }} {{ number_format($ffine, 0) }})</span>
                                            @else
                                                <span style="font-size:12px;font-weight:700;color:#059669">No Deduction</span>
                                            @endif
                                        </td>
                                        <td style="padding:14px 16px;text-align:right" onclick="event.stopPropagation()">
                                            <button type="button"
                                                    data-staff-id="{{ $staff->id }}"
                                                    data-staff-name="{{ e($staff->name) }}"
                                                    data-role-title="{{ e($roleTitle) }}"
                                                    data-salary="{{ $sal ?? '' }}"
                                                    data-absent-days="{{ $abs }}"
                                                    data-payout-day="{{ $pday }}"
                                                    data-deduction-type="{{ $dtype }}"
                                                    data-fixed-fine="{{ $ffine }}"
                                                    onclick="openStaffPayrollFromElem(this)"
                                                    style="background:linear-gradient(135deg, #4f46e5, #6366f1);color:#fff;border:none;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:800;cursor:pointer;box-shadow:0 2px 8px rgba(79,70,229,0.25)">
                                                ⚙️ Configure Salary &amp; Leaves
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="padding:24px;text-align:center;color:#64748b;font-size:13px">
                                            No active staff members found for this institute.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- CARD 2: 🏦 Bank Accounts & Student Payment Instructions -->
            @if($activeTab === 'financial' || $activeTab === 'bank' || $activeTab === 'all')
                <div class="settings-card" id="card-bank">
                    <div class="settings-card-header">
                        <div class="settings-card-icon" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe">
                            🏦
                        </div>
                        <div>
                            <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a">
                                Bank Accounts &amp; Fee Payments
                            </h3>
                            <p style="font-size:12px;color:#64748b;margin-top:2px">
                                Direct deposit information and 1Bill prefixes shown to students on invoices.
                            </p>
                        </div>
                    </div>

                    <div class="toggle-box">
                        <div>
                            <div style="font-size:13.5px;font-weight:700;color:#0f172a">Display Payment Details on Invoices</div>
                            <div style="font-size:12px;color:#64748b;margin-top:2px">Show bank account info and instructions on student invoice views.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="show_payment_details" value="1" {{ old('show_payment_details', $setting->show_payment_details) ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                        <div class="form-group">
                            <label class="form-label" for="bank_name">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', $setting->bank_name) }}" class="form-input" placeholder="e.g. Meezan Bank Ltd.">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="bank_account_title">Account Title</label>
                            <input type="text" id="bank_account_title" name="bank_account_title" value="{{ old('bank_account_title', $setting->bank_account_title) }}" class="form-input" placeholder="e.g. Uplyft Education Trust">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                        <div class="form-group">
                            <label class="form-label" for="bank_account_number">Account Number</label>
                            <input type="text" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $setting->bank_account_number) }}" class="form-input" placeholder="e.g. 01020304050607">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="onebill_voucher_prefix">1Bill Prefix / Biller ID</label>
                            <input type="text" id="onebill_voucher_prefix" name="onebill_voucher_prefix" value="{{ old('onebill_voucher_prefix', $setting->onebill_voucher_prefix) }}" class="form-input" placeholder="e.g. 100456">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="bank_iban">IBAN (International Bank Account Number)</label>
                        <input type="text" id="bank_iban" name="bank_iban" value="{{ old('bank_iban', $setting->bank_iban) }}" class="form-input" placeholder="PK00MEZN0000000102030405">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="payment_instructions">Payment Instructions (Shown to Students)</label>
                        <textarea id="payment_instructions" name="payment_instructions" rows="2" class="form-input" placeholder="Please upload a photo of your deposit slip or ATM receipt after transfer.">{{ old('payment_instructions', $setting->payment_instructions) }}</textarea>
                    </div>
                </div>
            @endif

            <!-- CARD: ☕ Class Breaks & Faculty Break Timings -->
            @if($activeTab === 'breaks' || $activeTab === 'all')
                <div class="settings-card" id="card-breaks" style="{{ $activeTab === 'breaks' ? 'grid-column: 1 / -1;' : '' }}">
                    <div class="settings-card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                        <div style="display:flex;align-items:center;gap:14px">
                            <div class="settings-card-icon" style="background:#fff1f2;color:#e11d48;border:1px solid #fecdd3">
                                ☕
                            </div>
                            <div>
                                <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a">
                                    Class Breaks &amp; Faculty Break Timings
                                </h3>
                                <p style="font-size:12px;color:#64748b;margin-top:2px">
                                    Configure day-specific recess / break hours for classes and view faculty break rules. The AI Timetable Generator will never schedule lectures during these periods.
                                </p>
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;gap:10px">
                            <button type="button" onclick="openAddClassBreakModal()" class="btn btn-primary" style="font-size:12.5px;padding:8px 16px;border-radius:10px">
                                ➕ Add Class Break
                            </button>
                            <a href="{{ route('principal.teachers.availability.index') }}" class="btn btn-secondary" style="font-size:12.5px;padding:8px 16px;border-radius:10px">
                                👨‍🏫 Faculty Work Hours &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Active Class Breaks Table -->
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                            <h4 style="font-size:13.5px;font-weight:800;color:#0f172a;margin:0">
                                🏫 Configured Class Breaks ({{ $classBreaks->count() }})
                            </h4>
                            <span style="font-size:11.5px;color:#64748b">Applied automatically during AI timetable generation</span>
                        </div>

                        @if($classBreaks->isEmpty())
                            <div style="background:#f8fafc;border:1.5px dashed #cbd5e1;border-radius:12px;padding:24px;text-align:center">
                                <span style="font-size:24px">☕</span>
                                <p style="font-size:13px;color:#64748b;margin:6px 0 12px">No class breaks configured yet. Set break timings per class and day.</p>
                                <button type="button" onclick="openAddClassBreakModal()" class="btn btn-secondary btn-sm" style="font-size:12px">
                                    ➕ Set First Class Break
                                </button>
                            </div>
                        @else
                            <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:12px">
                                <table style="width:100%;border-collapse:collapse;font-size:13px">
                                    <thead>
                                        <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;text-align:left">
                                            <th style="padding:10px 14px;font-weight:800;color:#0f172a;font-size:11px;text-transform:uppercase">Class &amp; Section</th>
                                            <th style="padding:10px 14px;font-weight:800;color:#0f172a;font-size:11px;text-transform:uppercase">Day of Week</th>
                                            <th style="padding:10px 14px;font-weight:800;color:#0f172a;font-size:11px;text-transform:uppercase">Break Window</th>
                                            <th style="padding:10px 14px;font-weight:800;color:#0f172a;font-size:11px;text-transform:uppercase;text-align:right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classBreaks as $cBreak)
                                            <tr style="border-bottom:1px solid #f1f5f9;background:#ffffff">
                                                <td style="padding:10px 14px;font-weight:700;color:#0f172a">
                                                    {{ $cBreak->section?->instituteClass?->custom_name ?? 'Class' }} — {{ $cBreak->section?->section_name ?? 'Section' }}
                                                </td>
                                                <td style="padding:10px 14px;color:#475569;text-transform:capitalize;font-weight:600">
                                                    📅 {{ $cBreak->day_of_week }}
                                                </td>
                                                <td style="padding:10px 14px">
                                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#fff1f2;color:#e11d48;border-radius:20px;font-size:12px;font-weight:700;border:1px solid #fecdd3">
                                                        ⏰ {{ date('g:i A', strtotime($cBreak->break_start_time)) }} – {{ date('g:i A', strtotime($cBreak->break_end_time)) }}
                                                    </span>
                                                </td>
                                                <td style="padding:10px 14px;text-align:right">
                                                    <button type="button" onclick="deleteClassBreak({{ $cBreak->id }})" class="btn btn-ghost btn-sm" style="color:#ef4444;font-size:12px;padding:4px 8px" title="Delete Break">
                                                        🗑️ Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <!-- Quick Faculty Break Overview -->
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-top:8px">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:8px">
                            <div style="display:flex;align-items:center;gap:8px">
                                <span style="font-size:16px">👨‍🏫</span>
                                <h4 style="font-size:13.5px;font-weight:800;color:#0f172a;margin:0">Faculty Break &amp; Shift Schedule</h4>
                            </div>
                            <a href="{{ route('principal.teachers.availability.index') }}" style="font-size:12px;font-weight:700;color:#4f46e5;text-decoration:none">
                                Edit All Faculty Breaks &rarr;
                            </a>
                        </div>
                        <p style="font-size:12px;color:#64748b;margin:0">
                            Faculty-specific breaks and working windows are configured per teacher in <strong>Faculty Work Hours</strong>. The Timetable Generator strictly enforces these breaks during AI slot allocation.
                        </p>
                    </div>
                </div>
            @endif

            <!-- CARD 3: 📧 Notifications & Contact Channels -->
            @if($activeTab === 'notifications' || $activeTab === 'financial' || $activeTab === 'bank' || $activeTab === 'all')
                <div class="settings-card" id="card-notifications">
                    <div class="settings-card-header">
                        <div class="settings-card-icon" style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0">
                            📧
                        </div>
                        <div>
                            <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a">
                                Notifications &amp; Contact Channels
                            </h3>
                            <p style="font-size:12px;color:#64748b;margin-top:2px">
                                Official channels used by the platform to reach your institute.
                            </p>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                        <div class="form-group">
                            <label class="form-label" for="notification_email">Notification Email (OTP Sender)</label>
                            <input type="email" id="notification_email" name="notification_email" value="{{ old('notification_email', $setting->notification_email) }}" class="form-input" placeholder="e.g. noreply@yourinstitute.edu">
                            <span class="form-hint">Security e-mails — such as password-change OTPs — are dispatched from this address so recipients can verify their origin.</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="notification_phone">Notification Phone / SMS Sender</label>
                            <input type="text" id="notification_phone" name="notification_phone" value="{{ old('notification_phone', $setting->notification_phone) }}" class="form-input" placeholder="e.g. +92 300 1234567">
                            <span class="form-hint">Reserved for future automated SMS / WhatsApp bot messages to students and parents.</span>
                        </div>
                    </div>

                    <div style="display:flex;align-items:flex-start;gap:10px;background:#f8fafc;border:1px solid #e2e8f0;padding:12px 14px;border-radius:10px;">
                        <span style="font-size:15px">💡</span>
                        <p style="margin:0;font-size:12px;color:#475569;line-height:1.55;">
                            The <strong>Notification Email</strong> is currently used by the system to send One-Time Passcodes when staff change their passwords from personal settings. A verified institutional address is strongly recommended.
                        </p>
                    </div>
                </div>
            @endif

            <!-- CARD 4: 🎓 Academic Standards & Campus Info -->
            @if($activeTab === 'academic' || $activeTab === 'all')
                <div class="settings-card" id="card-academic">
                    <div class="settings-card-header">
                        <div class="settings-card-icon" style="background:#fdf4ff;color:#c026d3;border:1px solid #f5d0fe">
                            🏛️
                        </div>
                        <div>
                            <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a">
                                Academic Standards &amp; Campus Profile
                            </h3>
                            <p style="font-size:12px;color:#64748b;margin-top:2px">
                                Passing score thresholds and institutional contact information.
                            </p>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                        <div class="form-group">
                            <label class="form-label" id="passing_score_label" for="passing_percentage">Passing Score / Marks *</label>
                            <input type="number" step="0.1" min="0" max="1000" id="passing_percentage" name="passing_percentage" value="{{ old('passing_percentage', $setting->passing_percentage) }}" required class="form-input" placeholder="e.g. 40">
                            <span class="form-hint" id="passing_score_hint">Minimum passing threshold</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="grade_scale_type">Evaluation Scale *</label>
                            <select name="grade_scale_type" id="grade_scale_type" class="form-input" onchange="updatePassingScoreLabel(this.value)">
                                <option value="percentage" {{ old('grade_scale_type', $setting->grade_scale_type) === 'percentage' ? 'selected' : '' }}>Percentage (0-100%)</option>
                                <option value="marks" {{ old('grade_scale_type', $setting->grade_scale_type) === 'marks' ? 'selected' : '' }}>Absolute Marks (Direct Marks Scale)</option>
                                <option value="gpa" {{ old('grade_scale_type', $setting->grade_scale_type) === 'gpa' ? 'selected' : '' }}>GPA 4.0 Standard Scale</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="institute_name">Campus / Institute Name</label>
                        <input type="text" id="institute_name" name="institute_name" value="{{ old('institute_name', $institute?->name ?? 'Uplyft Academy') }}" class="form-input">
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                        <div class="form-group">
                            <label class="form-label" for="contact_email">Official Email</label>
                            <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $institute?->contact_email ?? '') }}" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact_phone">Contact Phone</label>
                            <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $institute?->contact_phone ?? '') }}" class="form-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="city">City / Campus Location</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $institute?->city ?? '') }}" class="form-input" placeholder="e.g. Lahore, Karachi, Islamabad">
                    </div>
                </div>
            @endif

            <!-- CARD 5: 🎨 Institute Brand Logo & Identity Studio -->
            @if($activeTab === 'branding' || $activeTab === 'all')
                <div class="settings-card" id="card-branding" style="grid-column: 1 / -1;">
                    <div class="settings-card-header">
                        <div class="settings-card-icon" style="background:#fffbeb;color:#d97706;border:1px solid #fde68a">
                            🎨
                        </div>
                    <div>
                        <h3 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a">
                            Institute Logo &amp; Dynamic Portal Branding
                        </h3>
                        <p style="font-size:12px;color:#64748b;margin-top:2px">
                            Upload your institute logo. It will power the 3D spinning coin loader and platform header for all teachers and students in your campus.
                        </p>
                    </div>
                </div>

                {{-- Current Active Logo Status --}}
                @if($institute && $institute->logo_path)
                    <div style="display:flex;align-items:center;justify-content:space-between;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 18px">
                        <div style="display:flex;align-items:center;gap:14px">
                            <div style="width:54px;height:54px;border-radius:50%;background:#ffffff;border:2.5px solid #eab308;box-shadow:0 0 14px rgba(234,179,8,0.35);padding:2px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                                <img src="{{ $institute->logo_url }}" alt="{{ $institute->name }} Logo" style="width:100%;height:100%;object-fit:contain;border-radius:50%" />
                            </div>
                            <div>
                                <div style="font-size:14px;font-weight:800;color:#0f172a">Active Institute Logo</div>
                                <div style="font-size:12px;color:#64748b">Active across Principal, Teacher, and Student portals</div>
                            </div>
                        </div>
                        <label style="display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:800;color:#ef4444;cursor:pointer;margin:0;background:#fee2e2;padding:8px 14px;border-radius:10px;border:1.5px solid #fca5a5;transition:all 0.2s ease;user-select:none">
                            <input type="checkbox" name="remove_logo" value="1" style="width:16px!important;height:16px!important;min-width:16px;margin:0!important;padding:0!important;accent-color:#ef4444;flex-shrink:0;cursor:pointer;background:transparent;border:none;outline:none" />
                            <span style="display:inline-flex;align-items:center;gap:4px">🗑️ Remove Custom Logo</span>
                        </label>
                    </div>
                @endif

                {{-- Interactive Dropzone --}}
                <div id="principal-logo-dropzone" 
                     style="border:2px dashed #6366f1;border-radius:14px;padding:26px 20px;text-align:center;background:#f5f3ff;cursor:pointer;transition:all .2s;position:relative"
                     onclick="document.getElementById('principal_logo_file_input').click()"
                     ondragover="event.preventDefault(); this.style.borderColor='#4f46e5'; this.style.background='#ede9fe';"
                     ondragleave="this.style.borderColor='#6366f1'; this.style.background='#f5f3ff';"
                     ondrop="handlePrincipalLogoDrop(event)">
                    
                    <input id="principal_logo_file_input" type="file" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" style="display:none" onchange="handlePrincipalFileSelect(this)" />
                    <input type="hidden" name="cropped_logo" id="principal_cropped_logo_input" value="" />

                    <div id="principal-dropzone-idle">
                        <div style="width:50px;height:50px;border-radius:50%;background:#ffffff;border:1px solid #e2e8f0;display:inline-flex;align-items:center;justify-content:center;font-size:22px;color:#6366f1;margin-bottom:8px;box-shadow:0 4px 10px rgba(0,0,0,0.05)">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <div style="font-size:14px;font-weight:800;color:#1e1b4b">{{ ($institute && $institute->logo_path) ? 'Click or Drop Image to Replace Institute Logo' : 'Click or Drop Image to Upload Institute Logo' }}</div>
                        <div style="font-size:12px;color:#6b7280;margin-top:3px">Supports SVG, PNG, JPG, WEBP &bull; Full original image saved as-is without forced cropping</div>
                    </div>

                    {{-- Applied Image Preview --}}
                    <div id="principal-dropzone-applied" style="display:none;align-items:center;justify-content:center;gap:16px">
                        <div style="width:64px;height:64px;border-radius:12px;background:#ffffff;border:2px solid #6366f1;padding:3px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(99,102,241,0.25);overflow:hidden;flex-shrink:0">
                            <img id="principal-applied-crop-preview" src="#" alt="Selected Logo Preview" style="width:100%;height:100%;object-fit:contain;border-radius:8px" />
                        </div>
                        <div style="text-align:left">
                            <div style="display:flex;align-items:center;gap:6px">
                                <span id="principal-applied-status-title" style="font-size:13.5px;font-weight:800;color:#059669">✓ Full Original Image Selected</span>
                                <span style="font-size:11px;background:#d1fae5;color:#059669;padding:1px 6px;border-radius:4px;font-weight:700">Ready to Save</span>
                            </div>
                            <div id="principal-applied-crop-filename" style="font-size:11.5px;color:#64748b;margin-top:2px"></div>
                            <div style="display:flex;align-items:center;gap:12px;margin-top:6px">
                                <button type="button" onclick="event.stopPropagation(); openPrincipalCropModal();" style="font-size:11.5px;font-weight:700;color:#4f46e5;background:transparent;border:none;cursor:pointer;padding:0;text-decoration:underline">
                                    ✂️ Optional: Crop Tool
                                </button>
                                <span style="color:#cbd5e1">&bull;</span>
                                <button type="button" onclick="event.stopPropagation(); keepFullPrincipalLogo();" style="font-size:11.5px;font-weight:700;color:#059669;background:transparent;border:none;cursor:pointer;padding:0;text-decoration:underline">
                                    🖼️ Keep Full Image
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        <!-- Sticky Save Floating Bar at bottom -->
        <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:12px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:18px 24px;box-shadow:0 4px 20px rgba(15,23,42,0.04)">
            <a href="{{ route('principal.dashboard') }}" class="btn btn-secondary" style="padding:10px 22px;border-radius:10px">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #4f46e5, #6366f1);border:none;padding:10px 28px;border-radius:10px;font-weight:800;box-shadow:0 4px 14px rgba(79,70,229,0.25);color:#fff">
                💾 Save Settings
            </button>
        </div>
    </form>
</div>

{{-- ── Interactive Circular Cropper Modal for Principal (Apple Liquid Glass) ── --}}
<div id="principalCropModal" class="apple-liquid-glass-overlay" style="display:none;">
    <div class="apple-liquid-glass-card" style="max-width:540px;width:92%;padding:26px 30px;">
        
        {{-- Modal Top Specular Highlight --}}
        <div style="position:absolute;top:0;left:10%;right:10%;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.9),transparent)"></div>

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(226,232,240,0.7);padding-bottom:14px;margin-bottom:16px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#eef2ff 0%,#e0e7ff 100%);color:#4f46e5;border:1px solid rgba(199,210,254,0.6);display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 2px 8px rgba(79,70,229,0.12)">
                    🎨
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.3px">Crop / Adjust Campus Logo (Optional)</h3>
                    <p style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500">Drag to position &bull; Use slider or buttons to Zoom In / Out</p>
                </div>
            </div>
            <button type="button" onclick="closePrincipalCropModal()" class="apple-liquid-close-btn">✕</button>
        </div>

        {{-- Canvas --}}
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;background:#0f172a;border-radius:16px;padding:12px;position:relative;user-select:none;overflow:hidden;box-shadow:inset 0 2px 6px rgba(0,0,0,0.3)">
            <canvas id="principalCropperCanvas" width="340" height="340" style="border-radius:12px;cursor:grab;touch-action:none;background:#1e293b"></canvas>
            <div style="position:absolute;bottom:18px;left:50%;transform:translateX(-50%);background:rgba(15,23,42,0.85);backdrop-filter:blur(6px);color:#e2e8f0;font-size:10.5px;font-weight:700;padding:4px 12px;border-radius:9999px;pointer-events:none;border:1px solid rgba(255,255,255,0.1)">
                🖱️ Drag to pan &bull; 📜 Scroll to zoom
            </div>
        </div>

        {{-- Zoom Controls --}}
        <div style="margin-top:16px;padding:12px 16px;background:rgba(248,250,252,0.7);backdrop-filter:blur(8px);border-radius:14px;border:1px solid rgba(226,232,240,0.8)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:12px;font-weight:800;color:#334155;letter-spacing:0.3px">ZOOM CONTROLS</span>
                <span id="principalZoomLabel" style="font-size:12px;font-weight:800;color:#4f46e5">100%</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
                <button type="button" onclick="adjustPrincipalZoom(-0.15)" style="padding:6px 12px;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;font-weight:800;cursor:pointer">-</button>
                <input id="principalZoomSlider" type="range" min="0.2" max="3.5" step="0.02" value="1.0" oninput="setPrincipalZoom(this.value)" style="flex:1;accent-color:#4f46e5;cursor:pointer" />
                <button type="button" onclick="adjustPrincipalZoom(0.15)" style="padding:6px 12px;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;font-weight:800;cursor:pointer">+</button>
                <button type="button" onclick="resetPrincipalCropTransform()" style="padding:6px 10px;border-radius:8px;border:1px solid #cbd5e1;background:#ffffff;color:#334155;font-size:12px;font-weight:700;cursor:pointer">🎯 Fit</button>
            </div>
        </div>

        {{-- Actions --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:20px;border-top:1px solid rgba(226,232,240,0.7);padding-top:16px">
            <button type="button" onclick="keepFullPrincipalLogo()" class="apple-liquid-btn-cancel" style="font-size:13px">
                🖼️ Keep Full Image (No Crop)
            </button>
            <button type="button" onclick="applyPrincipalCircularCrop()" class="apple-liquid-btn-primary" style="font-size:13px">
                <span>✨</span> Apply Circular Crop
            </button>
        </div>
    </div>
</div>

<script>
    function selectAttMode(mode) {
        document.querySelectorAll('.mode-tile').forEach(tile => tile.classList.remove('selected'));
        const radio = document.getElementById('mode_' + mode);
        if (radio) {
            radio.checked = true;
            radio.closest('.mode-tile').classList.add('selected');
        }
    }

    let prCropImg = null;
    let prRawDataUrl = '';
    let prCropScale = 1.0;
    let prCropPosX = 0;
    let prCropPosY = 0;
    let prIsDragging = false;
    let prStartDragX = 0;
    let prStartDragY = 0;
    let prOriginalFilename = '';

    const prCanvas = document.getElementById('principalCropperCanvas');
    const prCtx = prCanvas.getContext('2d');
    const prCircleRadius = 135;

    function handlePrincipalFileSelect(input) {
        if (input.files && input.files[0]) {
            processUploadedPrincipalFile(input.files[0]);
        }
    }

    function handlePrincipalLogoDrop(e) {
        e.preventDefault();
        e.currentTarget.style.borderColor = '#6366f1';
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            const file = e.dataTransfer.files[0];
            document.getElementById('principal_logo_file_input').files = e.dataTransfer.files;
            processUploadedPrincipalFile(file);
        }
    }

    function processUploadedPrincipalFile(file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('File size exceeds 2MB limit.');
            return;
        }
        prOriginalFilename = file.name;
        const reader = new FileReader();
        reader.onload = function(e) {
            prRawDataUrl = e.target.result;
            prCropImg = new Image();
            prCropImg.onload = function() {
                keepFullPrincipalLogo();
            };
            prCropImg.src = prRawDataUrl;
        };
        reader.readAsDataURL(file);
    }

    function keepFullPrincipalLogo() {
        document.getElementById('principal_cropped_logo_input').value = '';
        
        document.getElementById('principal-applied-crop-preview').src = prRawDataUrl || (prCropImg ? prCropImg.src : '');
        document.getElementById('principal-applied-crop-preview').style.borderRadius = '8px';
        document.getElementById('principal-applied-crop-filename').textContent = prOriginalFilename || 'full_institute_logo';
        document.getElementById('principal-applied-status-title').textContent = '✓ Full Original Image Selected';
        
        document.getElementById('principal-dropzone-idle').style.display = 'none';
        document.getElementById('principal-dropzone-applied').style.display = 'flex';

        closePrincipalCropModal();
    }

    function openPrincipalCropModal() {
        if (prCropImg) {
            document.getElementById('principalCropModal').style.display = 'flex';
            resetPrincipalCropTransform();
        } else {
            document.getElementById('principal_logo_file_input').click();
        }
    }

    function closePrincipalCropModal() {
        document.getElementById('principalCropModal').style.display = 'none';
    }

    function resetPrincipalCropTransform() {
        if (!prCropImg || !prCanvas) return;
        prCropPosX = prCanvas.width / 2;
        prCropPosY = prCanvas.height / 2;
        const maxDim = Math.max(prCropImg.width, prCropImg.height);
        prCropScale = (prCircleRadius * 2 * 0.92) / maxDim;
        document.getElementById('principalZoomSlider').value = 1.0;
        document.getElementById('principalZoomLabel').textContent = '100%';
        renderPrincipalCropper();
    }

    function setPrincipalZoom(val) {
        prCropScale = parseFloat(val);
        document.getElementById('principalZoomLabel').textContent = Math.round(prCropScale * 100) + '%';
        renderPrincipalCropper();
    }

    function adjustPrincipalZoom(delta) {
        const slider = document.getElementById('principalZoomSlider');
        let newVal = Math.min(3.5, Math.max(0.2, parseFloat(slider.value) + delta));
        slider.value = newVal;
        setPrincipalZoom(newVal);
    }

    function renderPrincipalCropper() {
        if (!prCropImg || !prCtx || !prCanvas) return;
        prCtx.clearRect(0, 0, prCanvas.width, prCanvas.height);

        prCtx.save();
        prCtx.translate(prCropPosX, prCropPosY);
        prCtx.scale(prCropScale, prCropScale);
        prCtx.drawImage(prCropImg, -prCropImg.width / 2, -prCropImg.height / 2);
        prCtx.restore();

        const cx = prCanvas.width / 2;
        const cy = prCanvas.height / 2;

        prCtx.save();
        prCtx.fillStyle = 'rgba(15, 23, 42, 0.72)';
        prCtx.beginPath();
        prCtx.rect(0, 0, prCanvas.width, prCanvas.height);
        prCtx.arc(cx, cy, prCircleRadius, 0, Math.PI * 2, true);
        prCtx.fill();

        prCtx.lineWidth = 3;
        prCtx.strokeStyle = '#eab308';
        prCtx.shadowColor = 'rgba(234, 179, 8, 0.6)';
        prCtx.shadowBlur = 10;
        prCtx.beginPath();
        prCtx.arc(cx, cy, prCircleRadius, 0, Math.PI * 2, false);
        prCtx.stroke();
        prCtx.restore();
    }

    if (prCanvas) {
        prCanvas.addEventListener('mousedown', function(e) {
            prIsDragging = true;
            prStartDragX = e.clientX - prCropPosX;
            prStartDragY = e.clientY - prCropPosY;
            prCanvas.style.cursor = 'grabbing';
        });

        window.addEventListener('mousemove', function(e) {
            if (prIsDragging) {
                prCropPosX = e.clientX - prStartDragX;
                prCropPosY = e.clientY - prStartDragY;
                renderPrincipalCropper();
            }
        });

        window.addEventListener('mouseup', function() {
            if (prIsDragging) {
                prIsDragging = false;
                prCanvas.style.cursor = 'grab';
            }
        });

        prCanvas.addEventListener('wheel', function(e) {
            e.preventDefault();
            const delta = e.deltaY < 0 ? 0.08 : -0.08;
            adjustPrincipalZoom(delta);
        }, { passive: false });
    }

    function applyPrincipalCircularCrop() {
        if (!prCropImg || !prCanvas) return;

        const exportSize = 512;
        const exportCanvas = document.createElement('canvas');
        exportCanvas.width = exportSize;
        exportCanvas.height = exportSize;
        const eCtx = exportCanvas.getContext('2d');

        const scaleFactor = exportSize / (prCircleRadius * 2);

        eCtx.beginPath();
        eCtx.arc(exportSize / 2, exportSize / 2, exportSize / 2, 0, Math.PI * 2, true);
        eCtx.closePath();
        eCtx.clip();

        eCtx.fillStyle = '#ffffff';
        eCtx.fill();

        const relX = (prCropPosX - prCanvas.width / 2) * scaleFactor;
        const relY = (prCropPosY - prCanvas.height / 2) * scaleFactor;
        const relScale = prCropScale * scaleFactor;

        eCtx.save();
        eCtx.translate(exportSize / 2 + relX, exportSize / 2 + relY);
        eCtx.scale(relScale, relScale);
        eCtx.drawImage(prCropImg, -prCropImg.width / 2, -prCropImg.height / 2);
        eCtx.restore();

        const croppedDataUrl = exportCanvas.toDataURL('image/png', 0.95);

        document.getElementById('principal_cropped_logo_input').value = croppedDataUrl;
        document.getElementById('principal-applied-crop-preview').src = croppedDataUrl;
        document.getElementById('principal-applied-crop-filename').textContent = prOriginalFilename || 'institute_logo.png';
        document.getElementById('principal-dropzone-idle').style.display = 'none';
        document.getElementById('principal-dropzone-applied').style.display = 'flex';

        closePrincipalCropModal();
    }

    // ── Searchable Currency Dropdown Engine ──
    const WORLD_CURRENCIES = [
        { code: 'PKR', name: 'Pakistan Rupee' },
        { code: 'USD', name: 'US Dollar' },
        { code: 'EUR', name: 'Euro' },
        { code: 'GBP', name: 'British Pound' },
        { code: 'AED', name: 'UAE Dirham' },
        { code: 'SAR', name: 'Saudi Riyal' },
        { code: 'INR', name: 'Indian Rupee' },
        { code: 'CAD', name: 'Canadian Dollar' },
        { code: 'AUD', name: 'Australian Dollar' },
        { code: 'SGD', name: 'Singapore Dollar' },
        { code: 'CNY', name: 'Chinese Yuan' },
        { code: 'JPY', name: 'Japanese Yen' },
        { code: 'CHF', name: 'Swiss Franc' },
        { code: 'MYR', name: 'Malaysian Ringgit' },
        { code: 'TRY', name: 'Turkish Lira' },
        { code: 'BDT', name: 'Bangladeshi Taka' },
        { code: 'EGP', name: 'Egyptian Pound' },
        { code: 'QAR', name: 'Qatari Riyal' },
        { code: 'KWD', name: 'Kuwaiti Dinar' },
        { code: 'OMR', name: 'Omani Rial' },
        { code: 'BHD', name: 'Bahraini Dinar' },
        { code: 'NZD', name: 'New Zealand Dollar' },
        { code: 'ZAR', name: 'South African Rand' },
        { code: 'NGN', name: 'Nigerian Naira' },
        { code: 'KES', name: 'Kenyan Shilling' },
        { code: 'RUB', name: 'Russian Ruble' },
        { code: 'BRL', name: 'Brazilian Real' },
        { code: 'HKD', name: 'Hong Kong Dollar' },
        { code: 'SEK', name: 'Swedish Krona' },
        { code: 'NOK', name: 'Norwegian Krone' },
        { code: 'DKK', name: 'Danish Krone' },
        { code: 'PLN', name: 'Polish Zloty' },
        { code: 'THB', name: 'Thai Baht' },
        { code: 'IDR', name: 'Indonesian Rupiah' },
        { code: 'PHP', name: 'Philippine Peso' },
        { code: 'VND', name: 'Vietnamese Dong' }
    ];

    function initCurrencyDropdown() {
        const listContainer = document.getElementById('currency_options_list');
        const hiddenInput = document.getElementById('currency_symbol');
        if (!listContainer || !hiddenInput) return;

        const currentVal = hiddenInput.value || 'PKR';
        listContainer.innerHTML = '';

        WORLD_CURRENCIES.forEach(c => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'currency-option-item';
            btn.setAttribute('data-code', c.code);
            btn.setAttribute('data-search', `${c.code} ${c.name}`.toLowerCase());
            btn.style.cssText = 'width:100%;text-align:left;padding:8px 12px;border-radius:8px;border:none;background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:12.5px;font-weight:600;color:#0f172a;transition:all 0.15s ease';
            
            const isSelected = (c.code.toUpperCase() === currentVal.toUpperCase());
            if (isSelected) {
                btn.style.background = '#eef2ff';
                btn.style.color = '#4338ca';
                btn.style.fontWeight = '800';
            }

            btn.onmouseover = function() {
                if (hiddenInput.value.toUpperCase() !== c.code.toUpperCase()) {
                    this.style.background = '#f8fafc';
                }
            };
            btn.onmouseout = function() {
                if (hiddenInput.value.toUpperCase() !== c.code.toUpperCase()) {
                    this.style.background = 'transparent';
                }
            };

            btn.onclick = function() {
                selectCurrency(c.code, c.name);
            };

            btn.innerHTML = `
                <div style="display:flex;align-items:center;gap:8px;min-width:0">
                    <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><strong>${c.code}</strong> - ${c.name}</span>
                </div>
                <span class="curr-check" style="font-size:12px;font-weight:800;color:#4f46e5;display:${isSelected ? 'inline' : 'none'}">✓</span>
            `;

            listContainer.appendChild(btn);
        });

        // Set initial trigger label
        const match = WORLD_CURRENCIES.find(c => c.code.toUpperCase() === currentVal.toUpperCase());
        if (match) {
            document.getElementById('currency_selected_text').textContent = `${match.code} - ${match.name}`;
        } else {
            document.getElementById('currency_selected_text').textContent = currentVal;
        }
    }

    function toggleCurrencyDropdown() {
        const menu = document.getElementById('currency_dropdown_menu');
        const chevron = document.getElementById('currency_chevron');
        if (!menu) return;

        const isVisible = menu.style.display === 'block';
        menu.style.display = isVisible ? 'none' : 'block';
        if (chevron) chevron.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';

        if (!isVisible) {
            const searchInput = document.getElementById('currency_search_input');
            if (searchInput) {
                searchInput.value = '';
                filterCurrencyOptions('');
                setTimeout(() => searchInput.focus(), 50);
            }
        }
    }

    function selectCurrency(code, name) {
        document.getElementById('currency_symbol').value = code;
        document.getElementById('currency_selected_text').textContent = `${code} - ${name}`;

        document.querySelectorAll('.currency-option-item').forEach(item => {
            const isMatch = item.getAttribute('data-code').toUpperCase() === code.toUpperCase();
            item.style.background = isMatch ? '#eef2ff' : 'transparent';
            item.style.color = isMatch ? '#4338ca' : '#0f172a';
            item.style.fontWeight = isMatch ? '800' : '600';
            const check = item.querySelector('.curr-check');
            if (check) check.style.display = isMatch ? 'inline' : 'none';
        });

        toggleCurrencyDropdown();
    }

    function filterCurrencyOptions(query) {
        const q = (query || '').toLowerCase().trim();
        const items = document.querySelectorAll('.currency-option-item');
        let visibleCount = 0;

        items.forEach(item => {
            const searchData = item.getAttribute('data-search') || '';
            if (searchData.includes(q)) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        const noRes = document.getElementById('currency_no_results');
        if (noRes) {
            noRes.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('currency_dropdown_wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            const menu = document.getElementById('currency_dropdown_menu');
            if (menu && menu.style.display === 'block') {
                menu.style.display = 'none';
                const chevron = document.getElementById('currency_chevron');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        }
    });

    function updatePassingScoreLabel(scaleType) {
        const label = document.getElementById('passing_score_label');
        const hint = document.getElementById('passing_score_hint');
        const input = document.getElementById('passing_percentage');
        if (!label || !input) return;

        if (scaleType === 'marks') {
            label.textContent = 'Passing Marks (Minimum Required) *';
            input.placeholder = 'e.g. 33';
            if (hint) hint.textContent = 'Minimum passing marks threshold for exams and assessments';
        } else if (scaleType === 'gpa') {
            label.textContent = 'Passing GPA Threshold *';
            input.placeholder = 'e.g. 2.0';
            if (hint) hint.textContent = 'Minimum passing GPA scale threshold (e.g. 2.0 / 4.0)';
        } else {
            label.textContent = 'Passing Score (%) *';
            input.placeholder = 'e.g. 40';
            if (hint) hint.textContent = 'Minimum passing percentage threshold (0-100%)';
        }
    }

    function toggleFixedDeductionField(deductionType) {
        const group = document.getElementById('fixed_deduction_group');
        if (group) {
            group.style.display = deductionType === 'fixed' ? 'block' : 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        initCurrencyDropdown();
        const scaleSelect = document.getElementById('grade_scale_type');
        if (scaleSelect) {
            updatePassingScoreLabel(scaleSelect.value);
        }
    });
</script>

<!-- INDIVIDUAL STAFF PAYROLL MODAL -->
<div id="staffPayrollModal" class="apple-liquid-glass-overlay" style="display:none;z-index:99999;padding:20px;box-sizing:border-box">
    <div class="apple-liquid-glass-card" style="width:100%;max-width:540px;max-height:90vh;overflow-y:auto;padding:26px 28px;border-radius:24px;box-sizing:border-box;box-shadow:0 25px 60px -12px rgba(15,23,42,0.35)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px;border-bottom:1px solid #e2e8f0;padding-bottom:14px">
            <div>
                <h3 id="modalStaffNameTitle" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0">
                    Configure Salary &amp; Leave Policy
                </h3>
                <p id="modalStaffRoleSub" style="font-size:12px;color:#64748b;margin-top:4px;line-height:1.4">
                    Set individual monthly basic salary and allowed absent leaves per month.
                </p>
            </div>
            <button type="button" onclick="closeStaffPayrollModal()" style="background:none;border:none;font-size:20px;color:#94a3b8;cursor:pointer;padding:4px;line-height:1">✕</button>
        </div>

        <form id="staffPayrollForm" method="POST" action="">
            @csrf
            @method('PUT')

            <div class="form-group" style="margin-bottom:16px">
                <label class="form-label" for="modal_basic_salary_pkr" style="margin-bottom:6px">Individual Monthly Basic Salary ({{ $currencySymbol }}) *</label>
                <input type="number" step="1000" min="0" id="modal_basic_salary_pkr" name="basic_salary_pkr" class="form-input" placeholder="e.g. 50000" required style="padding:10px 14px">
                <span class="form-hint" style="font-size:11px;margin-top:4px">Monthly base compensation for this employee.</span>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start">
                <div class="form-group" style="margin-bottom:16px">
                    <label class="form-label" for="modal_allowed_absent_days" style="min-height:22px;display:flex;align-items:flex-end;margin-bottom:6px">Allowed Absences / Month *</label>
                    <input type="number" min="0" max="31" id="modal_allowed_absent_days" name="allowed_absent_days_per_month" class="form-input" placeholder="e.g. 2" required style="padding:10px 14px">
                    <span class="form-hint" style="font-size:11px;margin-top:4px">Excused absent days/month</span>
                </div>

                <div class="form-group" style="margin-bottom:16px">
                    <label class="form-label" for="modal_salary_disbursement_day" style="min-height:22px;display:flex;align-items:flex-end;margin-bottom:6px">Salary Payout Day *</label>
                    <input type="number" min="1" max="31" id="modal_salary_disbursement_day" name="salary_disbursement_day" class="form-input" placeholder="e.g. 1" required style="padding:10px 14px">
                    <span class="form-hint" style="font-size:11px;margin-top:4px">Day of month (1-31)</span>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:16px">
                <label class="form-label" for="modal_salary_deduction_type" style="margin-bottom:6px">Salary Deduction Policy (Extra Absences) *</label>
                <select name="salary_deduction_type" id="modal_salary_deduction_type" class="form-input" onchange="toggleModalFixedDeduction(this.value)" style="padding:10px 14px">
                    <option value="pro_rata">Pro-Rata Daily Rate Deduction (Daily Rate = Salary ÷ Working Days)</option>
                    <option value="fixed">Fixed Amount Fine per Extra Absent Day</option>
                    <option value="none">No Salary Deduction (Full Excused Absences)</option>
                </select>
                <span class="form-hint" style="font-size:11px;margin-top:4px">Deduction calculation applied when staff exceed allowed limit</span>
            </div>

            <div class="form-group" id="modal_fixed_deduction_group" style="display:none;margin-bottom:16px">
                <label class="form-label" for="modal_fixed_absent_deduction_amount" style="margin-bottom:6px">Fixed Fine Per Extra Absent Day ({{ $currencySymbol }}) *</label>
                <input type="number" step="50" min="0" id="modal_fixed_absent_deduction_amount" name="fixed_absent_deduction_amount" class="form-input" placeholder="e.g. 500" style="padding:10px 14px">
                <span class="form-hint" style="font-size:11px;margin-top:4px">Flat deduction amount per unexcused absent day over limit</span>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:20px;border-top:1px solid #e2e8f0;padding-top:16px">
                <button type="button" onclick="closeStaffPayrollModal()" class="btn btn-secondary" style="padding:10px 20px;border-radius:12px;font-size:13px;font-weight:700">Cancel</button>
                <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg, #059669, #10b981);border:none;padding:10px 24px;border-radius:12px;font-weight:800;color:#fff;font-size:13px">
                    💾 Save Staff Settings
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add / Configure Class Break -->
<div id="classBreakModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.5);backdrop-filter:blur(4px);z-index:9999;align-items:center;justify-content:center;padding:16px">
    <div style="background:#ffffff;border-radius:20px;max-width:480px;width:100%;box-shadow:0 20px 40px rgba(15,23,42,0.2);overflow:hidden;border:1px solid #cbd5e1">
        <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between">
            <h3 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin:0">☕ Schedule Class Break</h3>
            <button type="button" onclick="closeAddClassBreakModal()" style="border:none;background:transparent;font-size:18px;cursor:pointer;color:#64748b">&times;</button>
        </div>
        <form method="POST" action="{{ route('principal.settings.class-breaks.store') }}" style="padding:24px">
            @csrf
            <div style="display:flex;flex-direction:column;gap:16px">
                <div class="form-group">
                    <label class="form-label">Select Class &amp; Section *</label>
                    <select name="class_section_id" required class="form-input">
                        @if(isset($classSections))
                            @foreach($classSections as $sec)
                                <option value="{{ $sec->id }}">
                                    {{ $sec->instituteClass->custom_name ?? 'Class' }} — {{ $sec->section_name ?? 'Section' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Day of Week *</label>
                    <select name="day_of_week" required class="form-input">
                        @foreach(['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'] as $dKey => $dLabel)
                            <option value="{{ $dKey }}">{{ $dLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group">
                        <label class="form-label">Break Start Time *</label>
                        <input type="time" name="break_start_time" value="12:30" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Break End Time *</label>
                        <input type="time" name="break_end_time" value="13:00" required class="form-input">
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:12px">
                    <button type="button" onclick="closeAddClassBreakModal()" class="btn btn-secondary" style="padding:8px 16px;font-size:12.5px">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding:8px 18px;font-size:12.5px">💾 Save Class Break</button>
                </div>
            </div>
        </form>
    </div>
</div>

<form id="deleteClassBreakForm" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

<script>
    function openAddClassBreakModal() {
        const modal = document.getElementById('classBreakModal');
        if (modal) modal.style.display = 'flex';
    }

    function closeAddClassBreakModal() {
        const modal = document.getElementById('classBreakModal');
        if (modal) modal.style.display = 'none';
    }

    function deleteClassBreak(breakId) {
        if (!confirm('Are you sure you want to remove this class break timing?')) {
            return;
        }
        const form = document.getElementById('deleteClassBreakForm');
        form.action = "{{ url('/principal/settings/class-breaks') }}/" + breakId;
        form.submit();
    }

    function openStaffPayrollFromElem(elem) {
        if (!elem) return;
        const staffId = elem.getAttribute('data-staff-id');
        const staffName = elem.getAttribute('data-staff-name');
        const roleTitle = elem.getAttribute('data-role-title');
        const sal = elem.getAttribute('data-salary');
        const abs = elem.getAttribute('data-absent-days');
        const pday = elem.getAttribute('data-payout-day');
        const dtype = elem.getAttribute('data-deduction-type');
        const ffine = elem.getAttribute('data-fixed-fine');

        openStaffPayrollModal(staffId, staffName, roleTitle, sal, abs, pday, dtype, ffine);
    }

    function openStaffPayrollModal(staffId, staffName, roleTitle, sal, abs, pday, dtype, ffine) {
        const modal = document.getElementById('staffPayrollModal');
        const form = document.getElementById('staffPayrollForm');
        if (!modal || !form) return;

        form.action = "{{ url('/principal/settings/staff') }}/" + staffId + "/payroll";
        document.getElementById('modalStaffNameTitle').innerText = "Configure Settings: " + staffName;
        document.getElementById('modalStaffRoleSub').innerText = "Role: " + roleTitle + " • Manage individual basic salary and leave rules.";
        
        document.getElementById('modal_basic_salary_pkr').value = sal !== null ? sal : '';
        document.getElementById('modal_allowed_absent_days').value = abs !== null ? abs : 2;
        document.getElementById('modal_salary_disbursement_day').value = pday !== null ? pday : 1;
        document.getElementById('modal_salary_deduction_type').value = dtype || 'pro_rata';
        document.getElementById('modal_fixed_absent_deduction_amount').value = ffine !== null ? ffine : 0;

        toggleModalFixedDeduction(dtype || 'pro_rata');
        modal.style.display = 'flex';
    }

    function closeStaffPayrollModal() {
        const modal = document.getElementById('staffPayrollModal');
        if (modal) modal.style.display = 'none';
    }

    function toggleModalFixedDeduction(typeVal) {
        const group = document.getElementById('modal_fixed_deduction_group');
        if (group) {
            group.style.display = typeVal === 'fixed' ? 'block' : 'none';
        }
    }
</script>
@endsection
