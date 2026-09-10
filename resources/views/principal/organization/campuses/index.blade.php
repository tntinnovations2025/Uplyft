@extends('principal.layouts.app')
@section('breadcrumb', 'Organization Campuses')
@section('title', 'Manage Organization Campuses — ' . $organization->name)

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:14px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">
            🏢 Organization Campuses &amp; Profiles
        </h1>
        <p style="color:#64748b;font-size:13.5px;margin-top:4px;font-weight:500">
            Manage all registered campus profiles for <strong>{{ $organization->name }}</strong> network.
        </p>
    </div>
    <div>
        @if($organization->canAddMoreCampuses())
            <a href="{{ route('principal.organization.campuses.create') }}" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;font-weight:700">
                <i class="fa-solid fa-plus-circle"></i> Register New Campus to Organization
            </a>
        @else
            <button disabled class="btn btn-secondary" style="opacity:0.75;cursor:not-allowed" title="Campus limit quota reached ({{ $organization->max_campuses }}/{{ $organization->max_campuses }})">
                🔒 Campus Quota Reached ({{ $organization->max_campuses }}/{{ $organization->max_campuses }})
            </button>
        @endif
    </div>
</div>

<!-- Organization Network Overview Banner -->
<div class="card" style="margin-bottom:20px;background:linear-gradient(135deg,#ecfdf5 0%,#eef2ff 100%);border:1.5px solid #a7f3d0;border-radius:18px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
        <div style="display:flex;align-items:center;gap:14px">
            <div style="width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#10b981,#059669);color:#ffffff;display:flex;align-items:center;justify-content:center;font-size:22px;box-shadow:0 4px 12px rgba(5,150,105,0.3)">
                🏢
            </div>
            <div>
                <div style="font-size:16px;font-weight:800;color:#0f172a">{{ $organization->name }}</div>
                <div style="font-size:12.5px;color:#047857;font-weight:600;margin-top:2px">
                    Multi-Campus Organization Network &bull; Owner Email: <strong>{{ $organization->owner_email ?? 'N/A' }}</strong>
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <div style="padding:8px 16px;background:#ffffff;border:1px solid #cbd5e1;border-radius:12px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.04)">
                <div style="font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.5px">Registered Profiles</div>
                <div style="font-size:18px;font-weight:800;color:#4f46e5;margin-top:2px">{{ $campuses->count() }} / {{ $organization->max_campuses }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Campuses Roster Table -->
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <h3 style="font-size:16px;font-weight:800;color:#0f172a">All Registered Campus Profiles</h3>
        <span style="font-size:12px;font-weight:700;color:#64748b">Total {{ $campuses->count() }} Campus Profile(s)</span>
    </div>

    <div style="overflow-x:auto">
        <table class="table" style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="border-bottom:1.5px solid #e2e8f0;text-align:left;font-size:11.5px;color:#64748b;text-transform:uppercase;letter-spacing:0.5px">
                    <th style="padding:12px 14px">Campus Profile</th>
                    <th style="padding:12px 14px">City / Location</th>
                    <th style="padding:12px 14px">Official Email &amp; Phone</th>
                    <th style="padding:12px 14px">Education Systems</th>
                    <th style="padding:12px 14px">Active Status</th>
                    <th style="padding:12px 14px;text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campuses as $campus)
                    @php $isActiveProfile = (int)$campus->id === (int)$activeCampusId; @endphp
                    <tr style="border-bottom:1px solid #f1f5f9;transition:all 0.15s;background:{{ $isActiveProfile ? '#f0fdf4' : 'transparent' }}">
                        
                        {{-- Campus Profile --}}
                        <td style="padding:14px">
                            <div style="display:flex;align-items:center;gap:12px">
                                <div style="width:36px;height:36px;border-radius:10px;background:{{ $isActiveProfile ? 'linear-gradient(135deg,#10b981,#059669)' : '#4f46e5' }};color:#ffffff;font-size:14px;font-weight:800;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.1)">
                                    {{ $campus->display_initial }}
                                </div>
                                <div>
                                    <div style="font-size:13.5px;font-weight:800;color:#0f172a">
                                        {{ $campus->name }}
                                    </div>
                                    <div style="font-size:11px;color:#64748b;font-weight:600">
                                        ID: #{{ $campus->id }} &bull; Tier: {{ ucfirst($campus->subscription_tier ?? 'Standard') }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- City --}}
                        <td style="padding:14px;font-size:13px;font-weight:700;color:#334155">
                            📍 {{ $campus->city ?? 'N/A' }}
                        </td>

                        {{-- Email & Phone --}}
                        <td style="padding:14px">
                            <div style="font-size:12.5px;font-weight:600;color:#0f172a">✉️ {{ $campus->contact_email ?? 'N/A' }}</div>
                            @if($campus->contact_phone)
                                <div style="font-size:11px;color:#64748b;margin-top:2px">📞 {{ $campus->contact_phone }}</div>
                            @endif
                        </td>

                        {{-- Education Systems --}}
                        <td style="padding:14px">
                            <div style="display:flex;flex-wrap:wrap;gap:4px">
                                @forelse($campus->education_systems ?? [] as $sys)
                                    <span style="font-size:10px;font-weight:800;background:#e0e7ff;color:#3730a3;padding:2px 6px;border-radius:6px;border:1px solid #c7d2fe">
                                        {{ strtoupper(str_replace('_', ' ', $sys)) }}
                                    </span>
                                @empty
                                    <span style="font-size:11px;color:#94a3b8;font-style:italic">Default</span>
                                @endforelse
                            </div>
                        </td>

                        {{-- Status --}}
                        <td style="padding:14px">
                            @if($isActiveProfile)
                                <span style="font-size:11px;font-weight:800;background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:12px;border:1px solid #bbf7d0;display:inline-flex;align-items:center;gap:4px">
                                    ✓ Currently Active
                                </span>
                            @else
                                <span style="font-size:11px;font-weight:700;background:#f1f5f9;color:#64748b;padding:4px 10px;border-radius:12px;border:1px solid #e2e8f0">
                                    Available Profile
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td style="padding:14px;text-align:right">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                                @if(!$isActiveProfile)
                                    <form method="POST" action="{{ route('tenant.switch-institute') }}" style="margin:0">
                                        @csrf
                                        <input type="hidden" name="institute_id" value="{{ $campus->id }}">
                                        <button type="submit" class="btn btn-secondary" style="padding:5px 12px;font-size:12px;font-weight:700">
                                            🔄 Switch Profile
                                        </button>
                                    </form>
                                @endif

                                @if($campuses->count() > 1)
                                    <form method="POST" action="{{ route('principal.organization.campuses.destroy', $campus->id) }}" style="margin:0" onsubmit="return confirm('Are you sure you want to remove the campus profile \'{{ addslashes($campus->name) }}\' from your organization network?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-secondary" style="padding:5px 12px;font-size:12px;font-weight:700;color:var(--danger);border-color:#fca5a5;background:#fff5f5">
                                            🗑️ Remove
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:24px;text-align:center;color:#64748b;font-weight:600">
                            No campus profiles registered in this organization network.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
