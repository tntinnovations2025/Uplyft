@extends('layouts.app')

@section('title', 'Fee Management')
@section('page-header', 'Invoice Management')

@section('content')
<div class="space-y-6">

    <div class="liquid-glass-card p-6 flex flex-col md:flex-row items-center justify-between gap-4 border border-slate-200/90 shadow-2xs glass-specular-top">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 font-display">Student Invoices</h2>
            <p class="text-xs text-slate-500 font-medium">Track and manage fee payments across campus.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl alert-success text-xs font-bold flex items-center gap-2 shadow-2xs">
            <i class="fa-solid fa-circle-check text-emerald-600"></i> {{ session('success') }}
        </div>
    @endif

    <div class="liquid-glass-card p-0 overflow-hidden border border-slate-200/90 shadow-2xs">
        <div class="overflow-x-auto rounded-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-700 font-extrabold uppercase tracking-wider">
                        <th class="py-3.5 px-6">Invoice ID</th>
                        <th class="py-3.5 px-6">Student Name</th>
                        <th class="py-3.5 px-6 text-right">Amount (PKR)</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-slate-100 bg-white">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-6 font-bold font-mono text-pink-700">#{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-3.5 px-6">
                                <div class="font-extrabold text-slate-900">{{ $invoice->student->first_name ?? 'Unknown' }} {{ $invoice->student->last_name ?? '' }}</div>
                                <div class="text-[11px] font-mono font-bold text-slate-500">{{ $invoice->student->roll_number ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-6 text-right font-extrabold text-slate-900 font-mono">{{ number_format($invoice->amount_pkr, 2) }}</td>
                            <td class="py-3.5 px-6 text-center">
                                @if($invoice->status === 'paid')
                                    <span class="badge badge-emerald text-xs font-bold">Paid</span>
                                @else
                                    <span class="badge badge-rose text-xs font-bold">Unpaid</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6 text-center">
                                @if($invoice->status === 'unpaid')
                                    <form method="POST" action="{{ route('admin.invoices.mark-paid', $invoice->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-2xs">
                                            Mark Paid
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-emerald-700 font-bold"><i class="fa-solid fa-check"></i> Cleared</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-sm font-medium">No invoices generated yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
