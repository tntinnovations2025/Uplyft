@extends(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app')

@php
    $routePrefix = 'principal.students.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.students.';
    }
@endphp

@section('title', $student->full_name . ' — Student Profile')
@section('breadcrumb', 'Student Profile')

@section('content')
<style>
    .profile-header {
        background: #ffffff;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.85);
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        border-radius: 16px;
        padding: 28px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 24px;
    }
    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 800;
        box-shadow: 0 4px 16px rgba(225, 48, 108, 0.25);
    }
    .grid-card {
        background: #ffffff;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(226, 232, 240, 0.85);
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 16px;
    }
    @media (min-width: 768px) {
        .detail-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .detail-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    .detail-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
    }
    .detail-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #64748b;
        margin-bottom: 4px;
    }
    .detail-value {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }
</style>

<div class="profile-header">
    @if($student->passport_picture_path)
        <img src="{{ Storage::url($student->passport_picture_path) }}" class="profile-avatar" style="object-fit:cover" alt="{{ $student->full_name }}">
    @else
        <div class="profile-avatar">{{ strtoupper(substr($student->first_name, 0, 1)) }}</div>
    @endif

    <div style="flex:1">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:4px">
            <h1 style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.5px">
                {{ $student->full_name }}
            </h1>
            <span class="badge badge-purple" style="font-family:monospace">{{ $student->roll_number }}</span>
        </div>
        <div style="font-size:13px;color:#64748b;font-weight:500">
            Enrolled in {{ $student->institute->name }} &bull; {{ $student->email }}
        </div>
    </div>

    <div>
        @if(auth()->check() && auth()->user()->hasPermission('profile_edit', 'edit'))
            <a href="{{ route($routePrefix . 'edit', $student) }}" class="btn btn-primary mb-2">
                <span><x-icon name="pencil" class="w-3.5 h-3.5" /></span> Edit Profile
            </a>
            <br>
        @endif
        <a href="{{ route($routePrefix . 'index') }}" class="btn btn-ghost">
            &larr; Return to Roster
        </a>
    </div>
</div>

<div class="grid-card">
    <div style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:8px">
        <span>👤</span> <span>Personal &amp; Guardian Details</span>
    </div>

    <div class="detail-grid detail-grid-3 mb-4">
        <div class="detail-item">
            <div class="detail-label">Father / Guardian Name</div>
            <div class="detail-value" style="color:#059669">{{ $student->father_guardian_name ?? 'N/A' }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Guardian Phone Number</div>
            <div class="detail-value" style="color:#0284c7">{{ $student->guardian_phone ?? 'N/A' }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Student Phone Number</div>
            <div class="detail-value">{{ $student->phone ?? 'N/A' }}</div>
        </div>
    </div>

    <div class="detail-grid detail-grid-3 mb-4">
        <div class="detail-item">
            <div class="detail-label">Blood Group</div>
            <div class="detail-value">
                @if($student->blood_group)
                    <span class="badge badge-yellow">{{ $student->blood_group }}</span>
                @else
                    N/A
                @endif
            </div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Assigned Class &amp; Section</div>
            <div class="detail-value">
                @if($student->classSection && $student->classSection->instituteClass)
                    {{ $student->classSection->instituteClass->name }} {{ $student->classSection->section_name }}
                @else
                    {{ $student->enrolled_program ?? 'General' }}
                @endif
            </div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Date of Birth</div>
            <div class="detail-value">{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : 'N/A' }}</div>
        </div>
    </div>

    <div class="detail-grid detail-grid-3">
        <div class="detail-item">
            <div class="detail-label">Father / Guardian CNIC</div>
            <div class="detail-value" style="font-family:monospace;color:#9333ea">{{ $student->father_guardian_cnic ?? ($student->guardian_cnic ?? 'N/A') }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Student B-Form / CNIC Number</div>
            <div class="detail-value" style="font-family:monospace;color:#0284c7">{{ $student->b_form_or_father_cnic ?? 'N/A' }}</div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Residential Address</div>
            <div class="detail-value" style="font-size:13px;color:#334155">{{ $student->address ?? 'N/A' }}</div>
        </div>
    </div>
</div>

<div class="grid-card">
    <div style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:8px">
        <span>📑</span> <span>Uploaded Registration &amp; Clearance Documents</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:14px">
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:20px">🖼️</span>
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:13.5px">Passport Size Photograph</div>
                    <div style="font-size:11px;color:#64748b">Official Student Photo ID</div>
                </div>
            </div>
            @if($student->passport_picture_path)
                <a href="{{ Storage::url($student->passport_picture_path) }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#059669;border-color:#a7f3d0;background:#ecfdf5">
                    👁️ View Photo
                </a>
            @else
                <span style="font-size:11px;color:#64748b;background:#f1f5f9;padding:3px 8px;border-radius:6px">Not Uploaded</span>
            @endif
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:20px">🪪</span>
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:13.5px">B-Form / CNIC Document</div>
                    <div style="font-size:11px;color:#64748b">Student / Father CNIC Proof</div>
                </div>
            </div>
            @if($student->b_form_or_father_cnic && (str_contains($student->b_form_or_father_cnic, '/') || str_contains($student->b_form_or_father_cnic, '.')))
                <a href="{{ Storage::url($student->b_form_or_father_cnic) }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                    📄 View File
                </a>
            @elseif($student->b_form_or_father_cnic)
                <span style="font-size:11px;color:#0284c7;font-family:monospace;font-weight:600">{{ $student->b_form_or_father_cnic }}</span>
            @else
                <span style="font-size:11px;color:#64748b;background:#f1f5f9;padding:3px 8px;border-radius:6px">Not Uploaded</span>
            @endif
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:20px">🛡️</span>
                <div>
                    <div style="font-weight:700;color:#0f172a;font-size:13.5px">Guardian CNIC Document</div>
                    <div style="font-size:11px;color:#64748b">Guardian Legal Proof</div>
                </div>
            </div>
            @if($student->guardian_cnic && (str_contains($student->guardian_cnic, '/') || str_contains($student->guardian_cnic, '.')))
                <a href="{{ Storage::url($student->guardian_cnic) }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#9333ea;border-color:#f5d0fe;background:#fdf4ff">
                    📄 View File
                </a>
            @elseif($student->guardian_cnic)
                <span style="font-size:11px;color:#9333ea;font-family:monospace;font-weight:600">{{ $student->guardian_cnic }}</span>
            @else
                <span style="font-size:11px;color:#64748b;background:#f1f5f9;padding:3px 8px;border-radius:6px">Not Uploaded</span>
            @endif
        </div>
    </div>
</div>

<div class="grid-card">
    <div style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:8px">
            <span>📊</span> <span>Attendance Performance &amp; Analytics</span>
        </div>
        @php
            $pct = $student->attendance_percentage ?? 100;
            $pctColor = $pct >= 75 ? '#059669' : ($pct >= 50 ? '#d97706' : '#dc2626');
            $pctBg = $pct >= 75 ? '#ecfdf5' : ($pct >= 50 ? '#fffbeb' : '#fef2f2');
            $pctBorder = $pct >= 75 ? '#a7f3d0' : ($pct >= 50 ? '#fde68a' : '#fecaca');
        @endphp
        <span style="padding:4px 14px;border-radius:20px;font-size:13px;font-weight:800;background:{{ $pctBg }};color:{{ $pctColor }};border:1px solid {{ $pctBorder }}">
            Overall: {{ $pct }}%
        </span>
    </div>

    <div class="detail-grid detail-grid-4" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
        <div class="detail-item" style="text-align:center">
            <div class="detail-label">Total Sessions</div>
            <div class="detail-value" style="font-size:20px">{{ $student->attendance_total_sessions ?? 0 }}</div>
        </div>

        <div class="detail-item" style="text-align:center">
            <div class="detail-label">Present Count</div>
            <div class="detail-value" style="font-size:20px;color:#059669">{{ $student->attendance_present_sessions ?? 0 }}</div>
        </div>

        <div class="detail-item" style="text-align:center">
            <div class="detail-label">Absent Count</div>
            <div class="detail-value" style="font-size:20px;color:#dc2626">{{ $student->attendance_absent_sessions ?? 0 }}</div>
        </div>

        <div class="detail-item" style="text-align:center">
            <div class="detail-label">Leave Count</div>
            <div class="detail-value" style="font-size:20px;color:#d97706">{{ $student->attendance_leave_sessions ?? 0 }}</div>
        </div>
    </div>
</div>

<div class="grid-card">
    <div style="font-size:16px;font-weight:800;color:#0f172a;margin-bottom:16px;display:flex;align-items:center;gap:8px">
        <span>📄</span> <span>Fee Invoices &amp; Ledger</span>
    </div>

    @forelse($student->invoices as $invoice)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:8px">
            <div>
                <div style="font-weight:700;color:#0f172a;font-size:14px">Invoice #{{ $invoice->id }} &bull; PKR {{ number_format($invoice->amount_pkr, 2) }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px">Status: {{ strtoupper($invoice->status) }} &bull; Due: {{ $invoice->due_date }}</div>
            </div>
            @if($invoice->pdf_path)
                <a href="{{ Storage::url($invoice->pdf_path) }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#0284c7;border-color:#bae6fd;background:#f0f9ff">
                    📄 Download PDF
                </a>
            @endif
        </div>
    @empty
        <div style="font-size:13px;color:#64748b;padding:12px 0">No invoices generated yet for this student.</div>
    @endforelse
</div>
@endsection
