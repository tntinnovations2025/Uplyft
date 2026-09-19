@extends('layouts.app')

@section('title', 'Fee Invoices & Billing Statement')
@section('page-header', 'Fee Invoices & Billing')

@section('content')
<style>
    .billing-page-wrapper {
        display: flex;
        flex-direction: column;
        gap: 20px;
        font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: #0f172a;
    }

    /* 1. Header Identity & Quick Action Strip */
    .billing-header-strip {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03), 0 6px 18px -3px rgba(15, 23, 42, 0.03);
    }

    .billing-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .billing-header-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 20px;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
        flex-shrink: 0;
    }

    .billing-header-title {
        font-family: 'Outfit', sans-serif;
        font-size: 19px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        line-height: 1.2;
    }

    .billing-header-sub {
        font-size: 12px;
        color: #64748b;
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .billing-meta-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
        color: #334155;
    }

    .billing-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-action-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: 11px;
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        border: none;
        color: #ffffff;
        font-size: 12.5px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-action-primary:hover {
        background: linear-gradient(135deg, #4338ca, #4f46e5);
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(79, 70, 229, 0.35);
        color: #ffffff;
    }

    .btn-action-secondary {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 15px;
        border-radius: 11px;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        color: #334155;
        font-size: 12.5px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-action-secondary:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
    }

    /* 2. Top 3-Financial KPI Grid */
    .financial-kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    @media (max-width: 1024px) {
        .financial-kpi-grid { grid-template-columns: 1fr; }
    }

    .kpi-card {
        border-radius: 18px;
        padding: 20px 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 135px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03), 0 6px 18px -3px rgba(15, 23, 42, 0.03);
        transition: transform 0.2s ease;
    }
    .kpi-card:hover { transform: translateY(-2px); }

    .kpi-card-unpaid {
        background: linear-gradient(145deg, #fff5f5, #ffe4e6);
        border: 1px solid #fecdd3;
    }

    .kpi-card-paid {
        background: linear-gradient(145deg, #f0fdf4, #dcfce7);
        border: 1px solid #bbf7d0;
    }

    .kpi-card-neutral {
        background: #ffffff;
        border: 1px solid #e2e8f0;
    }

    .kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .kpi-tag {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
    }

    .kpi-value {
        font-family: 'Outfit', sans-serif;
        font-size: 25px;
        font-weight: 800;
        color: #0f172a;
        margin: 6px 0 2px;
        line-height: 1.1;
    }

    .kpi-sub {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* 3. Professional 2-Column Split Workspace */
    .split-workspace-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        align-items: stretch;
    }

    @media (max-width: 1024px) {
        .split-workspace-grid { grid-template-columns: 1fr; }
    }

    .workspace-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 22px 24px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03), 0 6px 18px -3px rgba(15, 23, 42, 0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 480px;
    }

    .workspace-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 16px;
    }

    .workspace-panel-title {
        font-family: 'Outfit', sans-serif;
        font-size: 16.5px;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    /* Bank Info Box */
    .bank-info-box {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 14px;
    }

    .bank-info-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 0;
        border-bottom: 1px solid #e2e8f0;
        font-size: 12.5px;
    }
    .bank-info-row:last-child { border-bottom: none; }

    .bank-info-label {
        color: #64748b;
        font-weight: 600;
    }

    .bank-info-val {
        color: #0f172a;
        font-weight: 800;
        font-family: monospace;
        letter-spacing: 0.3px;
    }

    /* 3-Column Vouchers Table */
    .vouchers-table-wrapper {
        overflow-x: auto;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
    }

    .vouchers-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .vouchers-table th {
        background: #f8fafc;
        padding: 12px 16px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
    }

    .vouchers-table td {
        padding: 14px 16px;
        font-size: 12.5px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .vouchers-table tr:last-child td {
        border-bottom: none;
    }

    .vouchers-table tr:hover td {
        background: #f8fafc;
    }
</style>

<div class="billing-page-wrapper">

    <!-- 1. HEADER HERO STRIP -->
    <div class="billing-header-strip">
        <div class="billing-header-left">
            <div class="billing-header-icon">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h1 class="billing-header-title">Fee Invoices &amp; Billing Statement</h1>
                <div class="billing-header-sub">
                    <span>Official tuition billing ledger &amp; FBR tax compliance</span>
                    <span class="billing-meta-tag">
                        <i class="fa-solid fa-id-card text-indigo-500"></i> Roll: <strong>{{ $student->roll_number ?? 'STD-2026' }}</strong>
                    </span>
                    <span class="billing-meta-tag">
                        <i class="fa-solid fa-school text-indigo-500"></i> Class: <strong>{{ $student->classSection?->instituteClass?->class_name ?? 'Class 10' }} ({{ $student->classSection?->section_name ?? 'Sec A' }})</strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="billing-actions">
            <button type="button" onclick="window.print()" class="btn-action-secondary">
                <i class="fa-solid fa-print"></i>
                <span>Print Statement</span>
            </button>

            @if($latestPendingInvoice && $latestPendingInvoice->pdf_path)
                <a href="{{ Storage::url($latestPendingInvoice->pdf_path) }}" target="_blank" class="btn-action-primary">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Download Latest PDF Voucher</span>
                </a>
            @else
                <button type="button" onclick="window.print()" class="btn-action-primary">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>Print Official Challan</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 2. TOP 3-KPI FINANCIAL HIGHLIGHT STRIP -->
    <div class="financial-kpi-grid">

        <!-- KPI 1: OUTSTANDING BALANCE & STATUS -->
        @if($hasPendingFee)
            <div class="kpi-card kpi-card-unpaid">
                <div class="kpi-header">
                    <span class="kpi-tag" style="color:#be123c">💳 Current Outstanding Due</span>
                    <span style="font-size:11px;font-weight:800;background:#ffe4e6;color:#e11d48;border:1px solid #fecdd3;padding:2px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px">
                        <span style="width:6px;height:6px;border-radius:50%;background:#e11d48"></span> ⚠️ Payment Pending
                    </span>
                </div>
                <div class="kpi-value" style="color:#be123c">
                    {{ $currencySymbol ?? 'PKR' }} {{ number_format($totalFee, 2) }}
                </div>
                <div class="kpi-sub" style="color:#9f1239">
                    <span>📅 Due Date: <strong>{{ $dueDate->format('M d, Y') }}</strong></span>
                    <span>•</span>
                    <span style="font-weight:800;color:{{ $daysRemaining < 3 ? '#e11d48' : '#d97706' }}">
                        {{ $daysRemaining >= 0 ? "⏳ {$daysRemaining} Days Left" : "⚠️ Overdue" }}
                    </span>
                </div>
            </div>
        @else
            <div class="kpi-card kpi-card-paid">
                <div class="kpi-header">
                    <span class="kpi-tag" style="color:#047857">💳 Current Outstanding Due</span>
                    <span style="font-size:11px;font-weight:800;background:#dcfce7;color:#059669;border:1px solid #bbf7d0;padding:2px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px">
                        <span style="width:6px;height:6px;border-radius:50%;background:#059669"></span> ✓ All Dues Cleared
                    </span>
                </div>
                <div class="kpi-value" style="color:#047857">
                    {{ $currencySymbol ?? 'PKR' }} 0.00
                </div>
                <div class="kpi-sub" style="color:#065f46">
                    <span>✓ All tuition and institutional vouchers are fully settled.</span>
                </div>
            </div>
        @endif

        <!-- KPI 2: FBR TAX STANDING -->
        <div class="kpi-card kpi-card-neutral">
            <div class="kpi-header">
                <span class="kpi-tag">🏛️ Guardian FBR Tax Standing</span>
                <span style="font-size:11px;font-weight:800;padding:2px 10px;border-radius:20px;border:1px solid {{ $isFiler ? '#bbf7d0' : '#fde68a' }};background:{{ $isFiler ? '#f0fdf4' : '#fffbeb' }};color:{{ $isFiler ? '#059669' : '#d97706' }}">
                    {{ $isFiler ? '🛡️ Filer (0% Tax)' : '⚖️ Non-Filer (5% Advance Tax)' }}
                </span>
            </div>
            <div class="kpi-value" style="color:{{ $taxAmount > 0 ? '#b45309' : '#0f172a' }}">
                {{ $currencySymbol ?? 'PKR' }} {{ number_format($taxAmount, 2) }}
            </div>
            <div class="kpi-sub">
                <span>{{ $isFiler ? 'FBR tax exemption applied under Active Taxpayer List.' : 'Adjustable withholding tax under Section 236I.' }}</span>
            </div>
        </div>

        <!-- KPI 3: SCHOLARSHIP & AID -->
        <div class="kpi-card kpi-card-neutral">
            <div class="kpi-header">
                <span class="kpi-tag">🎓 Scholarship &amp; Concessions</span>
                @if($scholarshipPercentage > 0)
                    <span style="font-size:11px;font-weight:800;padding:2px 10px;border-radius:20px;background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe">
                        ⭐ {{ $scholarshipPercentage }}% Concession
                    </span>
                @else
                    <span style="font-size:11px;font-weight:800;padding:2px 10px;border-radius:20px;background:#f8fafc;color:#64748b;border:1px solid #e2e8f0">
                        Standard Tuition
                    </span>
                @endif
            </div>
            <div class="kpi-value" style="color:{{ $scholarshipDiscount > 0 ? '#059669' : '#0f172a' }}">
                @if($scholarshipDiscount > 0)
                    - {{ $currencySymbol ?? 'PKR' }} {{ number_format($scholarshipDiscount, 2) }}
                @else
                    {{ $currencySymbol ?? 'PKR' }} 0.00
                @endif
            </div>
            <div class="kpi-sub">
                <span>{{ !empty($scholarshipName) ? $scholarshipName : ($scholarshipPercentage > 0 ? 'Institutional merit discount active' : 'Full academic program fee schedule') }}</span>
            </div>
        </div>

    </div>

    <!-- 3. SPLIT 2-COLUMN WORKSPACE: LEFT PAYMENT CHANNELS & RIGHT 3-COLUMN VOUCHERS TABLE -->
    <div class="split-workspace-grid" style="{{ !$showPaymentDetails ? 'grid-template-columns: 1fr;' : '' }}">

        <!-- LEFT PANEL: PAYMENT CHANNELS & OFFICIAL BANK DEPOSIT DETAILS (IF ENABLED BY PRINCIPAL) -->
        @if($showPaymentDetails)
            <div class="workspace-panel">
                <div>
                    <div class="workspace-panel-header">
                        <div class="workspace-panel-title">
                            <span>🏦 Payment Channels &amp; Deposit Details</span>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:#D48A2E;background:#FBF3E8;padding:3px 10px;border-radius:8px;border:1px solid #E8CEAA">
                            1Link / 1Bill Enabled
                        </span>
                    </div>

                    <p style="font-size:12px;color:#64748b;line-height:1.5;margin-bottom:12px">
                        {{ $paymentInstructions }}
                    </p>

                    <!-- Bank Info Box -->
                    <div class="bank-info-box">
                        <div class="bank-info-row">
                            <span class="bank-info-label">🏛️ Collection Bank:</span>
                            <span class="bank-info-val">{{ $bankName }}</span>
                        </div>
                        <div class="bank-info-row">
                            <span class="bank-info-label">👤 Account Title:</span>
                            <span class="bank-info-val">{{ $bankAccountTitle }}</span>
                        </div>
                        <div class="bank-info-row">
                            <span class="bank-info-label">💳 Account / IBAN:</span>
                            <span class="bank-info-val" style="color:#4338ca">{{ $bankIban }}</span>
                        </div>
                        <div class="bank-info-row">
                            <span class="bank-info-label">⚡ 1Bill Voucher ID:</span>
                            <span class="bank-info-val" style="color:#7c3aed">{{ $onebillPrefix }}{{ str_pad($student->id ?? 1, 6, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="bank-info-row">
                            <span class="bank-info-label">📅 Due Date:</span>
                            <span class="bank-info-val" style="color:#e11d48">{{ $dueDate->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <!-- Instant Online Payment Quick Steps -->
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;font-size:11.5px;color:#475569">
                        <div style="font-weight:800;color:#0f172a;margin-bottom:5px;display:flex;align-items:center;gap:6px">
                            <span>📱</span> <span>Online Payment Instructions (1Bill)</span>
                        </div>
                        <ol style="margin:0;padding-left:18px;line-height:1.5;font-size:11px">
                            <li>Open Mobile Banking App, Easypaisa, or JazzCash.</li>
                            <li>Select <strong>Bill Payment ➔ 1Bill / Invoices</strong>.</li>
                            <li>Enter prefix <strong>{{ $onebillPrefix }}{{ str_pad($student->id ?? 1, 6, '0', STR_PAD_LEFT) }}</strong> to fetch voucher.</li>
                            <li>Verify student name and pay securely.</li>
                        </ol>
                    </div>
                </div>

                <!-- Quick Action Button -->
                <div style="margin-top:16px">
                    <button type="button" onclick="window.print()" class="btn-action-primary" style="width:100%;justify-content:center;padding:11px">
                        <i class="fa-solid fa-file-arrow-down"></i>
                        <span>Generate &amp; Print Bank Deposit Challan</span>
                    </button>
                </div>
            </div>
        @endif

        <!-- RIGHT PANEL: ISSUED FEE VOUCHERS & DOWNLOAD TABLE (3 THINGS ONLY) -->
        <div class="workspace-panel">
            <div>
                <div class="workspace-panel-header">
                    <div class="workspace-panel-title">
                        <span>📚 Issued Fee Vouchers</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px">
                        <span style="font-size:11px;font-weight:800;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;padding:3px 10px;border-radius:20px">
                            Total: {{ $totalInvoicesCount }}
                        </span>
                        <span style="font-size:11px;font-weight:800;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;padding:3px 10px;border-radius:20px">
                            Paid: {{ $paidCount }}
                        </span>
                    </div>
                </div>

                <p style="font-size:12px;color:#64748b;margin-bottom:12px">
                    Official billing cycles, current payment status, and PDF voucher downloads.
                </p>

                <!-- 3-COLUMNS TABLE: Duration of Fee | Status | Download Option -->
                <div class="vouchers-table-wrapper">
                    <table class="vouchers-table">
                        <thead>
                            <tr>
                                <th>Duration of Fee</th>
                                <th>Status</th>
                                <th style="text-align:right">Download Option</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <!-- 1. Duration of Fee -->
                                    <td>
                                        <div style="display:flex;flex-direction:column;gap:3px">
                                            <span style="font-size:12px;font-weight:800;color:#4338ca;background:#eef2ff;border:1px solid #c7d2fe;padding:2px 8px;border-radius:8px;display:inline-flex;align-items:center;gap:4px;width:fit-content">
                                                🗓️ {{ $invoice->fee_month ?? $invoice->created_at->format('F Y') }}
                                            </span>
                                            <span style="font-size:11px;color:#64748b;font-weight:500">
                                                Due: {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : $dueDate->format('M d, Y') }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- 2. Status if Paid/Unpaid -->
                                    <td>
                                        @if($invoice->status === 'paid')
                                            <span style="font-size:11px;font-weight:800;padding:3px 10px;border-radius:20px;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0;display:inline-flex;align-items:center;gap:4px">
                                                <span style="width:6px;height:6px;border-radius:50%;background:#059669"></span> ✓ Paid
                                            </span>
                                        @else
                                            <span style="font-size:11px;font-weight:800;padding:3px 10px;border-radius:20px;background:#fff1f2;color:#e11d48;border:1px solid #fecdd3;display:inline-flex;align-items:center;gap:4px">
                                                <span style="width:6px;height:6px;border-radius:50%;background:#e11d48"></span> ⚠️ Unpaid
                                            </span>
                                        @endif
                                    </td>

                                    <!-- 3. Download Option -->
                                    <td style="text-align:right">
                                        @if($invoice->pdf_path)
                                            <a href="{{ Storage::url($invoice->pdf_path) }}" target="_blank" class="btn-action-primary" style="padding:6px 12px;font-size:11.5px">
                                                <i class="fa-solid fa-file-arrow-down"></i>
                                                <span>Download PDF</span>
                                            </a>
                                        @else
                                            <button type="button" onclick="window.print()" class="btn-action-secondary" style="padding:6px 12px;font-size:11.5px">
                                                <i class="fa-solid fa-print"></i>
                                                <span>Print Voucher</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center;padding:36px 16px;color:#64748b">
                                        <div style="font-size:28px;margin-bottom:6px">💳</div>
                                        <div style="font-size:14px;font-weight:800;color:#0f172a">No Fee Invoices Found</div>
                                        <div style="font-size:11.5px;color:#64748b;margin-top:2px">Your issued vouchers will appear here once generated.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Total Settlement Summary Footer -->
            <div style="margin-top:16px;padding:12px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;display:flex;align-items:center;justify-content:space-between;font-size:11.5px">
                <span style="color:#64748b;font-weight:600">Total Settlement Record</span>
                <span style="font-weight:800;color:#0f172a">
                    {{ $paidCount }} of {{ $totalInvoicesCount }} Vouchers Cleared
                </span>
            </div>
        </div>

    </div>

</div>
@endsection


