@extends(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app')

@php
    $routePrefix = 'principal.invoices.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.invoices.';
    }
@endphp

@section('title', 'Assign & Issue Fee Vouchers')
@section('breadcrumb', 'Assign Fee Vouchers')

@section('content')
<style>
    .form-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 20px;
        padding: 34px;
        max-width: 900px;
        margin: 0 auto;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
    }
    .form-section-title {
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 800;
        background: linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 20px;
    }
    @media (min-width: 768px) {
        .form-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #475569;
    }
    .form-control {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 11px;
        padding: 12px 16px;
        color: #0f172a;
        font-size: 14px;
        outline: none;
        transition: all 0.2s;
    }
    .form-control:focus {
        border-color: #e1306c;
        box-shadow: 0 0 0 3px rgba(225, 48, 108, 0.20);
    }
    .radio-option-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        padding: 18px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .radio-option-card:hover {
        border-color: rgba(225, 48, 108, 0.4);
        background: #fdf2f8;
    }
    .radio-option-card.selected {
        border-color: #e1306c;
        background: #fdf2f8;
        box-shadow: 0 4px 14px rgba(225, 48, 108, 0.15);
    }
</style>

<div class="header-actions" style="max-width:900px;margin:0 auto 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">
            ➕ Issue Fee Vouchers &amp; Assign Fees
        </h1>
        <div style="font-size:13px;color:#64748b;margin-top:2px;font-weight:500">
            Generate customized fee vouchers for the entire institute, a specific class, or individual students.
        </div>
    </div>
    <a href="{{ route($routePrefix . 'index') }}" class="btn btn-ghost">
        &larr; Back to Invoices
    </a>
</div>

<div class="form-card">
    <form action="{{ route($routePrefix . 'store') }}" method="POST">
        @csrf

        <!-- 1. TARGET SCOPE SELECTION -->
        <div class="form-section-title">
            <span>🎯</span> <span>1. Select Target Audience</span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:14px;margin-bottom:24px">
            <label class="radio-option-card" id="card_all">
                <input type="radio" name="target_scope" value="all_institute" {{ old('target_scope', 'all_institute') === 'all_institute' ? 'checked' : '' }} onchange="updateScopeUI()" style="accent-color:#e1306c">
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:14px">🏛️ Whole Institute</div>
                    <div style="font-size:12px;color:#64748b">Assign fee to ALL registered students at once</div>
                </div>
            </label>

            <label class="radio-option-card" id="card_class">
                <input type="radio" name="target_scope" value="class_section" {{ old('target_scope') === 'class_section' ? 'checked' : '' }} onchange="updateScopeUI()" style="accent-color:#e1306c">
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:14px">📚 Specific Class / Section</div>
                    <div style="font-size:12px;color:#64748b">Assign fee to students in a specific section</div>
                </div>
            </label>

            <label class="radio-option-card" id="card_single">
                <input type="radio" name="target_scope" value="single_student" {{ old('target_scope') === 'single_student' ? 'checked' : '' }} onchange="updateScopeUI()" style="accent-color:#e1306c">
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:14px">👤 Individual Student</div>
                    <div style="font-size:12px;color:#64748b">Assign custom voucher to 1 specific student</div>
                </div>
            </label>
        </div>

        <!-- Target Dropdowns (conditionally displayed) -->
        <div id="section_class_wrapper" class="form-group mb-4" style="display:none">
            <label class="form-label">Select Class &amp; Section <span style="color:#ef4444">*</span></label>
            <select name="class_section_id" class="form-control">
                <option value="">-- Choose Class &amp; Section --</option>
                @foreach($classes as $class)
                    @php $clsName = $class->name; @endphp
                    @foreach($class->sections as $sec)
                        <option value="{{ $sec->id }}" {{ old('class_section_id') == $sec->id ? 'selected' : '' }}>
                            📚 {{ $clsName }} — Section {{ $sec->section_name ?? $sec->name }}
                        </option>
                    @endforeach
                @endforeach
            </select>
        </div>

        <div id="section_student_wrapper" class="form-group mb-4" style="display:none">
            <label class="form-label">Select Student <span style="color:#ef4444">*</span></label>
            <select name="student_id" id="select_student_id" class="form-control" onchange="onStudentSelectChange(this)">
                <option value="" data-base-fee="50000">-- Choose Student --</option>
                @foreach($students as $st)
                    <option value="{{ $st->id }}" data-base-fee="{{ $st->base_fee ?? 50000 }}" {{ old('student_id') == $st->id ? 'selected' : '' }}>
                        {{ $st->full_name }} ({{ $st->roll_number }}) — 
                        @if($st->classSection && $st->classSection->instituteClass)
                            {{ $st->classSection->instituteClass->name }} (Sec {{ $st->classSection->section_name ?? $st->classSection->name }})
                        @else
                            {{ $st->enrolled_program ?? 'General' }}
                        @endif
                        [Default Fee: PKR {{ number_format($st->base_fee ?? 50000, 2) }}]
                    </option>
                @endforeach
            </select>
        </div>


        <!-- 2. MONTH DURATION & DETAILS -->
        <div class="form-section-title" style="margin-top:32px">
            <span>🗓️</span> <span>2. Month Duration &amp; Voucher Details</span>
        </div>

        <div class="form-grid mb-4">
            <div class="form-group">
                <label class="form-label">Fee Period Duration (From Month - To Month - Year) <span style="color:#ef4444">*</span></label>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:4px">
                    <div>
                        <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block">FROM MONTH</label>
                        <select name="from_month" class="form-control" required>
                            @foreach($monthsList as $m)
                                <option value="{{ $m }}" {{ old('from_month', 'June') === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block">TO MONTH</label>
                        <select name="to_month" class="form-control" required>
                            @foreach($monthsList as $m)
                                <option value="{{ $m }}" {{ old('to_month', 'September') === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px;display:block">SESSION YEAR</label>
                        <select name="fee_year" class="form-control" required>
                            @foreach($yearsList as $y)
                                <option value="{{ $y }}" {{ old('fee_year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top:16px">
                <label class="form-label">Voucher Title / Description <span style="color:#ef4444">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title', 'Monthly Tuition & Academic Fee') }}" required placeholder="e.g., Monthly Tuition Fee Voucher">
            </div>
        </div>


        <!-- 3. FINANCIAL OVERRIDE & DUE DATE -->
        <div class="form-section-title" style="margin-top:32px">
            <span>💰</span> <span>3. Financial Amount &amp; Payment Schedule</span>
        </div>

        <div class="form-grid form-grid-2 mb-4">
            <div class="form-group">
                <label class="form-label">
                    Base Fee Amount (PKR)
                    <span id="default_fee_badge" style="font-size:11px;color:#059669;font-weight:800;margin-left:8px;background:#ecfdf5;padding:2px 10px;border-radius:6px;border:1px solid #a7f3d0">
                        Default to Collect: PKR 50,000.00
                    </span>
                </label>
                <input type="number" step="0.01" name="base_fee" id="base_fee_input" class="form-control" value="{{ old('base_fee', 50000) }}" placeholder="e.g. 50000" required>
                <span style="font-size:11px;color:#64748b;margin-top:4px;display:block">Standard base fee to be collected. Tax breakdown (Filer: 0%, Non-Filer: 5%) will auto-calculate on voucher generation.</span>
            </div>

            <div class="form-group">
                <label class="form-label">Payment Due Date <span style="color:#ef4444">*</span></label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->addDays(10)->format('Y-m-d')) }}" required>
            </div>
        </div>

        <div style="margin-top:32px;display:flex;justify-content:flex-end;gap:12px">
            <a href="{{ route($routePrefix . 'index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary" style="padding:14px 28px;font-size:15px">
                ⚡ Issue &amp; Generate Fee Vouchers
            </button>
        </div>
    </form>
</div>

<script>
    function updateScopeUI() {
        const radios = document.getElementsByName('target_scope');
        let selectedValue = 'all_institute';
        for (const r of radios) {
            if (r.checked) selectedValue = r.value;
        }

        document.getElementById('card_all').classList.toggle('selected', selectedValue === 'all_institute');
        document.getElementById('card_class').classList.toggle('selected', selectedValue === 'class_section');
        document.getElementById('card_single').classList.toggle('selected', selectedValue === 'single_student');

        document.getElementById('section_class_wrapper').style.display = (selectedValue === 'class_section') ? 'block' : 'none';
        document.getElementById('section_student_wrapper').style.display = (selectedValue === 'single_student') ? 'block' : 'none';
    }

    function onStudentSelectChange(selectElem) {
        const opt = selectElem.options[selectElem.selectedIndex];
        const baseFee = opt ? opt.getAttribute('data-base-fee') || '50000' : '50000';
        
        const input = document.getElementById('base_fee_input');
        const badge = document.getElementById('default_fee_badge');
        
        input.value = parseFloat(baseFee).toFixed(2);
        badge.innerHTML = 'Default to Collect: PKR ' + parseFloat(baseFee).toLocaleString('en-US', {minimumFractionDigits: 2});
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateScopeUI();
    });
</script>
@endsection
