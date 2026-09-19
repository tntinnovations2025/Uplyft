@extends(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app')

@php
    $routePrefix = 'principal.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.';
    }
@endphp

@section('title', 'Password Reset Requests')
@section('page-title', 'Password Reset Requests')
@section('page-subtitle', 'Review and resolve password reset requests from campus students and faculty.')

@section('content')

@if (session('success'))
    <div class="alert alert-success" style="background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.4);color:#10b981;padding:12px 16px;border-radius:10px;margin-bottom:20px">
        <span>✅ {{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-error" style="background:rgba(239,68,68,0.2);border:1px solid rgba(239,68,68,0.4);color:#ef4444;padding:12px 16px;border-radius:10px;margin-bottom:20px">
        <span>⚠️ {{ session('error') }}</span>
    </div>
@endif

<div class="card" style="padding:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;border-bottom:1px solid #e2e8f0;padding-bottom:14px">
        <div>
            <h2 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:700;color:#0f172a;margin:0">
                🔑 Password Reset Requests
            </h2>
            <span style="font-size:12px;color:#64748b">
                Review, approve, or reject account reset requests submitted by students and faculty.
            </span>
        </div>
        <span class="badge" style="background:rgba(212,138,46,0.15);border:1px solid rgba(212,138,46,0.3);color:#D48A2E;padding:6px 12px;border-radius:20px;font-weight:700;font-size:11px">
            {{ $resetRequests->total() }} TOTAL REQUESTS
        </span>
    </div>

    <div style="overflow-x:auto;border-radius:12px;border:1px solid var(--border)">
        <table class="horiz-matrix-table" style="width:100%">
            <thead>
                <tr>
                    <th style="text-align:left;padding-left:14px">USER / IDENTIFIER</th>
                    <th style="text-align:left">ROLE</th>
                    <th style="text-align:left">STATUS</th>
                    <th style="text-align:left">REQUESTED TIME</th>
                    <th style="text-align:right;padding-right:14px">ACTION</th>
                </tr>
            </thead>
            <tbody>
                @forelse($resetRequests as $request)
                    <tr style="{{ $request->isPending() ? 'background: rgba(245, 158, 11, 0.08);' : '' }}">
                        <td style="padding:14px">
                            <div style="font-weight: 700; color: #fff;">{{ $request->user->name }}</div>
                            <div style="font-size: 11px; color: #E8CEAA;">{{ $request->user->identifier ?? $request->user->email }}</div>
                        </td>
                        <td style="padding:14px">
                            <span class="badge" style="background:{{ $request->user->role === 'teacher' ? 'rgba(56,189,248,0.15)' : 'rgba(16,185,129,0.15)' }};border:1px solid {{ $request->user->role === 'teacher' ? 'rgba(56,189,248,0.3)' : 'rgba(16,185,129,0.3)' }};color:{{ $request->user->role === 'teacher' ? '#E8CEAA' : '#10b981' }};padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700">
                                {{ strtoupper($request->user->role) }}
                            </span>
                        </td>
                        <td style="padding:14px">
                            @if($request->isPending())
                                <span class="badge" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#f59e0b;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700">
                                    ⚡ PENDING RESET
                                </span>
                            @elseif($request->status === 'completed')
                                <span class="badge" style="background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.3);color:#10b981;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700">
                                    ✅ COMPLETED
                                </span>
                            @else
                                <span class="badge" style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#ef4444;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700">
                                    ❌ DENIED
                                </span>
                            @endif
                        </td>
                        <td style="color: var(--text-muted); font-size: 12px; padding:14px">
                            {{ $request->created_at->diffForHumans() }}
                        </td>
                        <td style="text-align: right; padding-right:14px">
                            @if($request->isPending())
                                <a href="{{ route($routePrefix . 'password-resets.show', $request) }}" class="btn-primary" style="padding:6px 14px;font-size:12px;border-radius:8px">
                                    Process Request &rarr;
                                </a>
                            @else
                                <a href="{{ route($routePrefix . 'password-resets.show', $request) }}" style="color:#94a3b8;font-size:12px">
                                    View Details
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 36px;">
                            🔑 No pending password reset requests in queue.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $resetRequests->links() }}
    </div>
</div>
@endsection
