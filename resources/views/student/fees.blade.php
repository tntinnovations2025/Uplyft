@extends('layouts.app')

@section('title', 'My Fee Ledger')
@section('page-header', 'Fee Ledger')

@section('content')
<div class="space-y-6">

    <div class="glass-panel p-6 bg-gradient-to-r from-indigo-900/40 to-slate-900/60">
        <h2 class="text-xl font-bold text-white">Fee Statement</h2>
        <p class="text-xs text-slate-400">View your current outstanding balance and tax details.</p>
    </div>

    <div class="glass-panel p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/10 text-xs text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">Amount</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-300">
                    <tr class="border-b border-white/5">
                        <td class="py-3 px-4">Base Tuition Fee</td>
                        <td class="py-3 px-4">PKR {{ number_format($baseFee, 2) }}</td>
                    </tr>
                    <tr class="border-b border-white/5">
                        <td class="py-3 px-4">
                            Tax ({{ $isFiler ? 'Filer - 0%' : 'Non-Filer - 5%' }})
                            <span class="block text-[10px] text-slate-500">Based on guardian's tax status</span>
                        </td>
                        <td class="py-3 px-4">PKR {{ number_format($taxAmount, 2) }}</td>
                    </tr>
                    <tr class="border-b border-white/10 font-bold text-white bg-white/5">
                        <td class="py-3 px-4">Total Amount Payable</td>
                        <td class="py-3 px-4 text-indigo-400">PKR {{ number_format($totalFee, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end">
            <a href="#" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition">
                <i class="fa-solid fa-download"></i>
                <span>Download Latest PDF Invoice</span>
            </a>
        </div>
    </div>
</div>
@endsection
