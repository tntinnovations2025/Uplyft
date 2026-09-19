@extends(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app')

@php
    $routePrefix = 'principal.invoices.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.invoices.';
    }
    $canEditInvoices = auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin() || auth()->user()->hasPermission('invoices', 'edit');
@endphp

@section('title', 'Fee Invoices & Billing Engine')
@section('breadcrumb', 'Fee Invoices')

@section('content')
<div class="space-y-6 max-w-full overflow-hidden">

    <!-- HEADER BAR -->
    <div class="liquid-glass-card p-6 bg-gradient-to-r from-emerald-500/[0.04] via-indigo-500/[0.02] to-teal-500/[0.04] border border-slate-200/90 relative overflow-hidden glass-specular-top">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-600 to-emerald-700 text-white flex items-center justify-center text-xl font-extrabold shadow-md shadow-emerald-500/20 border border-white/60 font-display">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight font-display flex items-center gap-2">
                        <span>Fee Invoices &amp; Billing Engine</span>
                        <span class="badge badge-emerald text-xs font-bold">Active Module</span>
                    </h1>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Generate tuition vouchers, track payment recoveries, and issue receipts seamlessly.</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @if($canEditInvoices)
                    <a href="{{ route($routePrefix . 'create') }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold rounded-xl" style="background: linear-gradient(135deg, #059669, #10b981); border:none;">
                        <i class="fa-solid fa-plus text-[11px]"></i>
                        <span>Generate Fee Vouchers</span>
                    </a>
                @else
                    <div class="text-xs font-bold text-sky-700 bg-sky-50 px-3.5 py-2 rounded-xl border border-sky-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-eye text-sky-500"></i>
                        <span>View Only Access Mode</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- STATS OVERVIEW CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="liquid-glass-card p-4 border border-slate-200/90 flex flex-col justify-between">
            <div class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Total Vouchers</div>
            <div class="text-2xl font-black text-slate-900 font-display mt-2">{{ number_format($totalIssued) }}</div>
            <div class="text-[11px] text-slate-500 font-semibold mt-1 flex items-center gap-1">
                <i class="fa-solid fa-folder-open text-slate-400"></i> Issued Vouchers
            </div>
        </div>

        <div class="liquid-glass-card p-4 border border-sky-200/80 bg-sky-50/30 flex flex-col justify-between">
            <div class="text-[11px] font-extrabold text-sky-700 uppercase tracking-wider">Total Invoiced</div>
            <div class="text-2xl font-black text-sky-900 font-display mt-2">{{ $currencySymbol ?? 'PKR' }} {{ number_format($totalAmount) }}</div>
            <div class="text-[11px] text-sky-600 font-semibold mt-1 flex items-center gap-1">
                <i class="fa-solid fa-calculator"></i> Total Billed Value
            </div>
        </div>

        <div class="liquid-glass-card p-4 border border-emerald-200/80 bg-emerald-50/30 flex flex-col justify-between">
            <div class="text-[11px] font-extrabold text-emerald-800 uppercase tracking-wider">Collected Fee</div>
            <div class="text-2xl font-black text-emerald-700 font-display mt-2">{{ $currencySymbol ?? 'PKR' }} {{ number_format($totalPaid) }}</div>
            <div class="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                <i class="fa-solid fa-circle-check text-emerald-500"></i> Cleared Payments
            </div>
        </div>

        <div class="liquid-glass-card p-4 border border-rose-200/80 bg-rose-50/30 flex flex-col justify-between">
            <div class="text-[11px] font-extrabold text-rose-800 uppercase tracking-wider">Remaining Unpaid</div>
            <div class="text-2xl font-black text-rose-700 font-display mt-2">{{ $currencySymbol ?? 'PKR' }} {{ number_format($totalUnpaid) }}</div>
            <div class="text-[11px] text-rose-600 font-semibold mt-1 flex items-center gap-1">
                <i class="fa-solid fa-clock-rotate-left text-rose-500"></i> Outstanding Balance
            </div>
        </div>
    </div>

    <!-- FILTER BAR (COMPACT & RESPONSIVE - NO OVERFLOW) -->
    <div class="liquid-glass-card p-4 border border-slate-200/90 shadow-2xs">
        <form method="GET" action="{{ route($routePrefix . 'index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Search Input -->
            <div class="lg:col-span-4 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" class="w-full pl-9 pr-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                    value="{{ request('search') }}" placeholder="Search student name, roll #, or month...">
            </div>

            <!-- Status Filter -->
            <div class="lg:col-span-3">
                <select name="status" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    <option value="">All Statuses (Paid &amp; Unpaid)</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>⚠️ Unpaid Only</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>✓ Paid Only</option>
                </select>
            </div>

            <!-- Class & Section Filter -->
            <div class="lg:col-span-3">
                <select name="class_section_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    <option value="">All Classes &amp; Sections</option>
                    @foreach($classes as $class)
                        @foreach($class->sections as $sec)
                            <option value="{{ $sec->id }}" {{ request('class_section_id') == $sec->id ? 'selected' : '' }}>
                                {{ $class->custom_name ?? $class->name }} — Sec {{ $sec->section_name ?? $sec->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-filter text-[10px]"></i> Filter
                </button>
                @if(request('search') || request('status') || request('class_section_id'))
                    <a href="{{ route($routePrefix . 'index') }}" class="py-2 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- INVOICES GRID / ROSTER CARDS (ZERO HORIZONTAL SCROLL - CLEAN RESPONSIVE DESIGN) -->
    <div class="space-y-3">
        @forelse($invoices as $invoice)
            <div class="liquid-glass-card p-4 border border-slate-200/90 hover:border-emerald-300/80 transition duration-200 bg-white rounded-2xl shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                <!-- Left: Voucher Ref & Student Info -->
                <div class="flex items-start gap-3.5 min-w-0 flex-1">
                    <div class="w-11 h-11 rounded-xl flex flex-col items-center justify-center font-mono text-[11px] font-black border flex-shrink-0 {{ $invoice->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                        <i class="fa-solid {{ $invoice->status === 'paid' ? 'fa-circle-check text-emerald-600' : 'fa-receipt text-rose-500' }} text-sm mb-0.5"></i>
                        <span>#{{ str_pad($invoice->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-sm font-extrabold text-slate-900 tracking-tight truncate font-display">
                                {{ $invoice->student ? $invoice->student->full_name : 'Student Removed' }}
                            </h4>
                            @if($invoice->student && $invoice->student->roll_number)
                                <span class="text-[10px] font-mono font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                    {{ $invoice->student->roll_number }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-500 font-semibold flex-wrap">
                            <span class="inline-flex items-center gap-1 text-slate-700">
                                <i class="fa-solid fa-chalkboard text-indigo-500 text-[10px]"></i>
                                @if($invoice->student && $invoice->student->classSection && $invoice->student->classSection->instituteClass)
                                    {{ $invoice->student->classSection->instituteClass->custom_name ?? $invoice->student->classSection->instituteClass->name }} — Sec {{ $invoice->student->classSection->section_name ?? $invoice->student->classSection->name }}
                                @else
                                    {{ $invoice->student->enrolled_program ?? 'General Class' }}
                                @endif
                            </span>
                            <span class="text-slate-300">•</span>
                            <span class="inline-flex items-center gap-1 text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-200/60 text-[11px]">
                                <i class="fa-regular fa-calendar text-amber-600 text-[10px]"></i>
                                {{ $invoice->fee_month ?? 'Monthly Fee' }}
                            </span>
                            <span class="text-slate-300">•</span>
                            <span class="text-slate-500 text-[11px] font-medium truncate">
                                {{ $invoice->title ?? 'Tuition Fee' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Middle: Amount & Status Pill -->
                <div class="flex items-center justify-between md:justify-end gap-6 flex-shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                    <div class="text-left md:text-right">
                        <div class="text-xs text-slate-400 font-extrabold uppercase tracking-wider">Amount Due</div>
                        <div class="text-base font-black text-emerald-700 font-display">
                            {{ $currencySymbol ?? 'PKR' }} {{ number_format($invoice->amount_pkr) }}
                        </div>
                        <div class="text-[10.5px] text-slate-400 font-medium">
                            Due: {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                        @if($invoice->status === 'paid')
                            <span class="badge badge-emerald text-xs font-extrabold px-3 py-1 inline-flex items-center gap-1.5 shadow-2xs whitespace-nowrap flex-shrink-0">
                                <i class="fa-solid fa-check"></i> <span>Paid</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black text-rose-700 bg-rose-50 border border-rose-200 shadow-2xs whitespace-nowrap flex-shrink-0">
                                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-[11px]"></i> <span>Unpaid</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Right: Quick Actions -->
                <div class="flex items-center justify-end gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100 flex-shrink-0">
                    @if($invoice->pdf_path)
                        <a href="{{ Storage::url($invoice->pdf_path) }}" target="_blank" title="View Fee Voucher PDF" class="px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-xs font-bold transition flex items-center gap-1">
                            <i class="fa-solid fa-file-pdf text-sky-600"></i> PDF
                        </a>
                    @endif

                    @if($invoice->status === 'unpaid' && $canEditInvoices)
                        <button type="button" onclick="openMarkPaidModal('{{ route($routePrefix . 'mark-paid', $invoice) }}', '{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}', '{{ addslashes($invoice->student ? $invoice->student->full_name : '') }}', '{{ number_format($invoice->amount_pkr, 2) }}')" 
                            class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-[11px]"></i>
                            <span>Mark Paid</span>
                        </button>
                    @endif

                    @if($invoice->status === 'paid' && $invoice->paid_slip_path)
                        <a href="{{ Storage::url($invoice->paid_slip_path) }}" target="_blank" title="View Uploaded Payment Slip" class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold transition flex items-center gap-1">
                            <i class="fa-solid fa-receipt text-emerald-600"></i> Slip
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="liquid-glass-card p-12 text-center border border-slate-200/90 bg-white rounded-2xl">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-2xl font-bold mx-auto mb-3 shadow-2xs">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900 font-display">No Fee Invoices Found</h3>
                <p class="text-xs text-slate-500 font-medium mt-1 max-w-md mx-auto">No vouchers matched your current search filters or no fee invoices have been generated yet.</p>
                @if($canEditInvoices)
                    <div class="mt-4">
                        <a href="{{ route($routePrefix . 'create') }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-xl" style="background: linear-gradient(135deg, #059669, #10b981); border:none;">
                            <i class="fa-solid fa-plus"></i> Issue First Fee Voucher
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    <!-- PAGINATION -->
    <div class="pt-2">
        {{ $invoices->links() }}
    </div>
</div>

<!-- MARK PAID FEE SLIP UPLOAD MODAL -->
<div id="markPaidFeeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[99999] flex items-center justify-center p-4 hidden">
    <div class="liquid-glass-card max-w-md w-full bg-white border border-slate-200 p-6 rounded-2xl shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200">
            <h3 class="text-base font-extrabold text-slate-900 font-display flex items-center gap-2">
                <i class="fa-solid fa-credit-card text-emerald-600"></i>
                <span>Confirm Fee Collection</span>
            </h3>
            <button type="button" onclick="closeMarkPaidModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-sm transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="markPaidForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="bg-emerald-50/80 border border-emerald-200/80 rounded-xl p-3.5 text-xs">
                <div class="text-slate-500 font-medium">Voucher Reference: <strong id="modalVoucherNum" class="text-slate-900 font-bold">#INV-00000</strong></div>
                <div class="text-sm font-extrabold text-slate-900 mt-1 font-display" id="modalStudentName">Student Name</div>
                <div class="text-base font-black text-emerald-700 mt-1 font-display">Amount: PKR <span id="modalInvoiceAmount">0.00</span></div>
            </div>

            <div>
                <label class="block text-xs font-extrabold uppercase text-slate-600 mb-1.5">Payment Mode *</label>
                <select name="payment_method" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-emerald-500" required>
                    <option value="Cash">Cash Payment</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cheque">Cheque</option>
                    <option value="Online / Mobile Wallet">Online / Mobile Wallet</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-extrabold uppercase text-slate-600 mb-1.5">Paid Fee Slip Picture (Optional)</label>
                <input type="file" name="paid_slip" accept="image/*,application/pdf" class="w-full p-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-700">
                <p class="text-[11px] text-slate-500 mt-1">Attach photo or receipt. System will automatically log income to Financial Ledger.</p>
            </div>

            <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" class="btn-secondary px-4 py-2 text-xs font-bold rounded-xl" onclick="closeMarkPaidModal()">Cancel</button>
                <button type="submit" class="btn-primary px-4 py-2 text-xs font-bold rounded-xl" style="background: linear-gradient(135deg, #059669, #10b981); border:none;">
                    Confirm &amp; Log Income
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openMarkPaidModal(url, voucherNum, studentName, amount) {
        document.getElementById('markPaidForm').action = url;
        document.getElementById('modalVoucherNum').innerText = '#INV-' + voucherNum;
        document.getElementById('modalStudentName').innerText = studentName;
        document.getElementById('modalInvoiceAmount').innerText = amount;
        document.getElementById('markPaidFeeModal').classList.remove('hidden');
    }

    function closeMarkPaidModal() {
        document.getElementById('markPaidFeeModal').classList.add('hidden');
    }
</script>
@endsection
