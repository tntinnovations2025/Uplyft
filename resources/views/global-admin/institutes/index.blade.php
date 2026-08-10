@extends('global-admin.layouts.app')
@section('breadcrumb', 'Institutes')
@section('title', 'All Institutes')

@section('content')

{{-- ── Custom Delete Confirmation Modal ───────────────────────────── --}}
<div id="deleteModal" style="
    display:none;
    position:fixed;inset:0;z-index:9999;
    background:rgba(0,0,0,0.7);
    backdrop-filter:blur(4px);
    align-items:center;justify-content:center;
">
    <div style="
        background:#1c1f38;
        border:1px solid #2a2d4a;
        border-radius:16px;
        padding:36px 40px;
        max-width:420px;width:90%;
        box-shadow:0 24px 60px rgba(0,0,0,0.6);
        animation:modalPop .18s ease;
        text-align:center;
    ">
        <div style="font-size:48px;margin-bottom:16px">🗑️</div>
        <h2 style="font-size:20px;font-weight:800;margin-bottom:8px;color:#e2e8f0">Deactivate Institute?</h2>
        <p style="color:#94a3b8;font-size:14px;margin-bottom:8px">You are about to deactivate:</p>
        <p id="modalInstituteName" style="color:#a78bfa;font-size:16px;font-weight:700;margin-bottom:24px"></p>
        <p style="color:#94a3b8;font-size:13px;margin-bottom:28px">
            The institute will be <strong style="color:#f87171">deactivated</strong> and hidden from active views.
            You can <strong style="color:#22d3a0">restore</strong> it anytime.
        </p>
        <div style="display:flex;gap:12px;justify-content:center">
            <button id="modalCancelBtn" onclick="closeDeleteModal()" style="
                padding:10px 28px;border-radius:8px;border:1px solid #2a2d4a;
                background:#141628;color:#94a3b8;font-size:14px;font-weight:600;
                cursor:pointer;transition:all .15s;
            " onmouseover="this.style.borderColor='#6c63ff';this.style.color='#a78bfa'"
               onmouseout="this.style.borderColor='#2a2d4a';this.style.color='#94a3b8'">
                Cancel
            </button>
            <button id="modalConfirmBtn" onclick="submitDeleteForm()" style="
                padding:10px 28px;border-radius:8px;border:none;
                background:linear-gradient(135deg,#ef4444,#dc2626);
                color:#fff;font-size:14px;font-weight:700;
                cursor:pointer;transition:all .15s;
            " onmouseover="this.style.opacity='.85'"
               onmouseout="this.style.opacity='1'">
                ✅ Yes, Deactivate
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modalPop {
    from { transform:scale(.92);opacity:0; }
    to   { transform:scale(1);opacity:1; }
}
</style>

{{-- ── Hidden delete form (single, reused for all rows) ──────────── --}}
<form id="deleteForm" method="POST" action="" style="display:none">
    @csrf
    @method('DELETE')
</form>

<div class="page-header">
    <h1 class="page-title">All Institutes</h1>
    <a href="{{ route('global-admin.institutes.create') }}" class="btn btn-primary">➕ Register New</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Institute</th>
                <th>Tier</th>
                <th>Status</th>
                <th>Modules ON</th>
                <th>Expires</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($institutes as $inst)
            <tr style="{{ $inst->trashed() ? 'opacity:.5' : '' }}">
                <td style="color:var(--text-muted)">{{ $inst->id }}</td>
                <td>
                    <div style="font-weight:600">{{ $inst->name }}</div>
                    <div style="font-size:12px;color:var(--text-muted)">{{ $inst->city }}</div>
                </td>
                <td>
                    <span class="badge {{ $inst->subscription_tier === 'premium' ? 'badge-purple' : ($inst->subscription_tier === 'standard' ? 'badge-yellow' : 'badge-blue') }}">
                        {{ ucfirst($inst->subscription_tier) }}
                    </span>
                </td>
                <td>
                    @if($inst->trashed())
                        <span class="badge badge-red">Deleted</span>
                    @elseif($inst->is_active)
                        <span class="badge badge-green">Active</span>
                    @else
                        <span class="badge badge-red">Inactive</span>
                    @endif
                </td>
                <td>
                    @if($inst->featureToggles)
                        <span style="font-weight:600">
                            {{ collect(\App\Models\InstituteFeatureToggle::$featureKeys)->filter(fn($k) => $inst->featureToggles->$k)->count() }}
                        </span>
                        <span style="color:var(--text-muted)">/{{ count(\App\Models\InstituteFeatureToggle::$featureKeys) }}</span>
                    @else —
                    @endif
                </td>
                <td style="font-size:13px;color:var(--text-muted)">
                    {{ $inst->subscription_expires_at?->format('d M Y') ?? 'Perpetual' }}
                </td>
                <td style="display:flex;gap:6px;align-items:center">
                    @if($inst->trashed())
                        <form method="POST" action="{{ route('global-admin.institutes.restore', $inst->id) }}">
                            @csrf
                            <button class="btn btn-success btn-sm" type="submit">Restore</button>
                        </form>
                    @else
                        <a href="{{ route('global-admin.institutes.show', $inst) }}" class="btn btn-ghost btn-sm">View</a>
                        <a href="{{ route('global-admin.institutes.edit', $inst) }}" class="btn btn-ghost btn-sm">Edit</a>
                        <a href="{{ route('global-admin.institutes.toggles.edit', $inst) }}" class="btn btn-ghost btn-sm">🔧 Toggles</a>

                        {{-- Del button — triggers custom modal, NO native confirm --}}
                        <button
                            type="button"
                            class="btn btn-danger btn-sm"
                            onclick="openDeleteModal(
                                '{{ route('global-admin.institutes.destroy', $inst) }}',
                                '{{ addslashes($inst->name) }}'
                            )">
                            🗑 Del
                        </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px">
                    No institutes yet. <a href="{{ route('global-admin.institutes.create') }}" style="color:var(--accent2)">Register one →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:20px">{{ $institutes->links() }}</div>
</div>

<script>
    const modal    = document.getElementById('deleteModal');
    const nameEl   = document.getElementById('modalInstituteName');
    const delForm  = document.getElementById('deleteForm');

    function openDeleteModal(actionUrl, instituteName) {
        delForm.action = actionUrl;
        nameEl.textContent = instituteName;
        modal.style.display = 'flex';
        // Close on backdrop click
        modal.onclick = function(e) {
            if (e.target === modal) closeDeleteModal();
        };
    }

    function closeDeleteModal() {
        modal.style.display = 'none';
    }

    function submitDeleteForm() {
        document.getElementById('modalConfirmBtn').textContent = 'Deleting…';
        document.getElementById('modalConfirmBtn').disabled = true;
        delForm.submit();
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeDeleteModal();
    });
</script>

@endsection
