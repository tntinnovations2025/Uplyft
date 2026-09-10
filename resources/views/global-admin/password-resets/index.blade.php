@extends('global-admin.layouts.app')

@section('title', 'Emergency Reset Queue')
@section('page-title', 'Emergency Password Reset Queue')
@section('page-subtitle', 'Authorize and process pending password reset requests from Institute Principals')

@section('content')

@if (session('success'))
    <div class="alert alert-success">
        <span>✅ {{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-error">
        <span>⚠️ {{ session('error') }}</span>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">🔑 Pending Principal Password Reset Requests</div>
            <div class="card-subtitle">Review, approve, or deny reset requests transmitted by Institute Principals</div>
        </div>
        <span class="badge badge-purple" style="font-size:11px;font-weight:700">{{ $resetRequests->total() }} TOTAL REQUESTS</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>USER / IDENTIFIER</th>
                <th>ROLE</th>
                <th>INSTITUTE</th>
                <th>STATUS</th>
                <th>REQUESTED TIME</th>
                <th style="text-align: right;">ACTION</th>
            </tr>
        </thead>
        <tbody>
            @forelse($resetRequests as $request)
                <tr style="{{ $request->isPending() ? 'background: #fffbeb;' : '' }}">
                    <td>
                        <div style="font-weight: 800; color: #0f172a; font-size: 14.5px;">{{ $request->user->name }}</div>
                        <div style="font-size: 12px; color: #64748b; font-weight: 600; margin-top: 2px;">{{ $request->user->identifier ?? $request->user->email }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $request->user->role === 'principal' ? 'badge-purple' : 'badge-cyan' }}">
                            {{ strtoupper($request->user->role) }}
                        </span>
                    </td>
                    <td>
                        <div style="color: #334155; font-size: 13.5px; font-weight: 600;">{{ $request->institute->name ?? '—' }}</div>
                    </td>
                    <td>
                        @if($request->isPending())
                            <span class="badge badge-amber">⚡ PENDING APPROVAL</span>
                        @elseif($request->status === 'completed')
                            <span class="badge badge-green">✅ COMPLETED</span>
                        @else
                            <span class="badge badge-rose">❌ DENIED</span>
                        @endif
                    </td>
                    <td style="color: #64748b; font-size: 13px; font-weight: 600;">
                        {{ $request->created_at->diffForHumans() }}
                    </td>
                    <td style="text-align: right;">
                        @if($request->isPending())
                            <a href="{{ route('global-admin.password-resets.show', $request) }}" class="btn btn-primary btn-sm">
                                <span>Process Request</span>
                                <span>&rarr;</span>
                            </a>
                        @else
                            <a href="{{ route('global-admin.password-resets.show', $request) }}" class="btn btn-secondary btn-sm">
                                <span>View Log</span>
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 36px; font-weight: 500;">
                        🔑 No emergency password reset requests currently in queue.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $resetRequests->links() }}
    </div>
</div>
@endsection
