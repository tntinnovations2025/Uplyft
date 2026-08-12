@extends('layouts.app')

@section('title', 'Fee Management')
@section('page-header', 'Invoice Management')

@section('content')
<div class="space-y-6">

    <div class="glass-panel p-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white">Student Invoices</h2>
            <p class="text-xs text-slate-400">Track and manage fee payments.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
            <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="glass-panel p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-white/10 text-xs text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6">Invoice ID</th>
                        <th class="py-4 px-6">Student Name</th>
                        <th class="py-4 px-6">Amount (PKR)</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-300">
                    @forelse($invoices as $invoice)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-3 px-6 font-semibold text-white">#{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-3 px-6 text-slate-300">
                                {{ $invoice->student->first_name ?? 'Unknown' }} {{ $invoice->student->last_name ?? '' }}
                                <div class="text-[10px] text-slate-500">{{ $invoice->student->roll_number ?? '' }}</div>
                            </td>
                            <td class="py-3 px-6 text-amber-400 font-semibold">{{ number_format($invoice->amount_pkr, 2) }}</td>
                            <td class="py-3 px-6">
                                @if($invoice->status === 'paid')
                                    <span class="px-2 py-1 rounded text-xs border border-emerald-500/50 bg-emerald-500/20 text-emerald-400">Paid</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs border border-red-500/50 bg-red-500/20 text-red-400">Unpaid</span>
                                @endif
                            </td>
                            <td class="py-3 px-6 text-center">
                                @if($invoice->status === 'unpaid')
                                    <form method="POST" action="{{ route('admin.invoices.mark-paid', $invoice->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-lg shadow-emerald-500/20">
                                            Mark Paid
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-500"><i class="fa-solid fa-check"></i> Cleared</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500 text-sm">No invoices generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
