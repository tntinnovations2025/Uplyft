@extends('layouts.app')

@section('title', 'My Fees & Invoices')
@section('page-header', 'Fee Ledger')

@section('content')
<div class="space-y-6">

    <div class="glass-panel p-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white">Fee Invoices</h2>
            <p class="text-xs text-slate-400">Download and track your fee payments.</p>
        </div>
    </div>

    <div class="glass-panel p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-white/10 text-xs text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6">Invoice ID</th>
                        <th class="py-4 px-6">Date Issued</th>
                        <th class="py-4 px-6">Due Date</th>
                        <th class="py-4 px-6">Amount (PKR)</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-300">
                    @forelse($invoices as $invoice)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-3 px-6 font-semibold text-white">#{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-3 px-6">{{ $invoice->created_at->format('M d, Y') }}</td>
                            <td class="py-3 px-6">{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}</td>
                            <td class="py-3 px-6 text-amber-400 font-semibold">{{ number_format($invoice->amount_pkr, 2) }}</td>
                            <td class="py-3 px-6">
                                @if($invoice->status === 'paid')
                                    <span class="px-2 py-1 rounded text-xs border border-emerald-500/50 bg-emerald-500/20 text-emerald-400">Paid</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs border border-red-500/50 bg-red-500/20 text-red-400">Unpaid</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                @if($invoice->pdf_path)
                                    <a href="{{ Storage::url($invoice->pdf_path) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-lg shadow-emerald-500/20 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-download"></i> Download
                                    </a>
                                @else
                                    <span class="text-xs text-slate-500">Not Available</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-500 text-sm">No invoices generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
