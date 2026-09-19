@extends(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app')

@php
    $routePrefix = 'principal.directory.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.directory.';
    }
@endphp

@section('title', 'Faculty Profile: ' . $teacher->full_name)
@section('breadcrumb', 'Faculty Profile & Salary Ledger')

@section('content')
<style>
    .profile-header-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 18px;
        flex-wrap: wrap;
    }
    .hero-avatar {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        background: #FBF3E8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        border: 2px solid #E8CEAA;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.1);
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 20px;
    }
    @media(min-width: 768px) {
        .detail-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .detail-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    .info-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
    }
    .info-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 4px;
    }
    .info-val {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
    }
    .slip-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
        transition: all 0.2s;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .slip-card:hover {
        border-color: #a7f3d0;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);
    }
</style>

<div class="profile-header-card">
    <div style="display:flex;align-items:center;gap:20px">
        @if($teacher->profile_picture_path)
            <img src="{{ Storage::url($teacher->profile_picture_path) }}" class="hero-avatar" alt="{{ $teacher->full_name }}" style="object-fit:cover">
        @else
            <div class="hero-avatar">👨‍🏫</div>
        @endif
        <div>
            <div style="font-family:'Outfit',sans-serif;font-size:24px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">
                {{ $teacher->full_name }}
            </div>
            <div style="font-size:13px;color:#D48A2E;font-weight:700;margin-top:4px;display:flex;gap:12px;align-items:center">
                <span class="badge badge-blue">Employee ID: {{ $teacher->employee_id ?? 'EMP-' . $teacher->id }}</span>
                <span>🎓 {{ $teacher->qualification ?? 'Faculty Member' }}</span>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px">
        @if(auth()->check() && auth()->user()->hasPermission('profile_edit', 'edit'))
            <a href="{{ route($routePrefix . 'teacher.edit', $teacher) }}" class="btn btn-primary">
                <span><x-icon name="pencil" class="w-3.5 h-3.5" /></span> Edit Profile
            </a>
        @endif
        <a href="{{ route($routePrefix . 'index') }}" class="btn btn-ghost">
            &larr; Back to Directory
        </a>
    </div>
</div>

<!-- TEACHER PERSONAL & ACADEMIC PROFILE -->
<div class="card mb-6">
    <div class="card-header">
        <div class="card-title" style="color:#D48A2E">👤 Faculty &amp; Contact Details</div>
    </div>

    <div class="detail-grid detail-grid-3 mb-4">
        <div class="info-box">
            <div class="info-label">Full Name</div>
            <div class="info-val">{{ $teacher->full_name }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Email Address</div>
            <div class="info-val">{{ $teacher->email }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Phone Number</div>
            <div class="info-val">{{ $teacher->phone ?? 'N/A' }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Highest Qualification</div>
            <div class="info-val">{{ $teacher->qualification ?? 'N/A' }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Teaching Experience</div>
            <div class="info-val">{{ $teacher->years_of_experience ? $teacher->years_of_experience . ' Years' : 'N/A' }}</div>
        </div>
        <div class="info-box">
            <div class="info-label">Monthly Basic Salary</div>
            <div class="info-val" style="color:#059669">PKR {{ number_format($teacher->basic_salary_pkr ?? 0) }}</div>
        </div>
    </div>

    @if($teacher->emergency_contact_phone)
        <div class="info-box">
            <div class="info-label">Emergency Contact Phone</div>
            <div class="info-val" style="color:#d97706">{{ $teacher->emergency_contact_phone }}</div>
        </div>
    @endif
</div>

<!-- VERIFIED EDUCATIONAL QUALIFICATION RESULTS SECTION -->
<div class="card mb-6" style="border-color:rgba(56,189,248,0.3)">
    <div class="card-header">
        <div class="card-title" style="color:#D48A2E">📜 Verified Educational Qualification Results</div>
    </div>

    <div class="detail-grid detail-grid-3 mb-2">
        <div class="info-box">
            <div class="info-label">Matric / O-Level Result</div>
            <div style="margin-top:6px">
                @if($teacher->matriculation_cert)
                    <a href="{{ Storage::url($teacher->matriculation_cert) }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#D48A2E;border-color:#E8CEAA;background:#FBF3E8">
                        📄 View Matric Result
                    </a>
                @else
                    <span style="font-size:12px;color:#64748b">Not Uploaded</span>
                @endif
            </div>
        </div>

        <div class="info-box">
            <div class="info-label">Inter / A-Level Result</div>
            <div style="margin-top:6px">
                @if($teacher->intermediate_cert)
                    <a href="{{ Storage::url($teacher->intermediate_cert) }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#D48A2E;border-color:#E8CEAA;background:#FBF3E8">
                        📄 View Inter Result
                    </a>
                @else
                    <span style="font-size:12px;color:#64748b">Not Uploaded</span>
                @endif
            </div>
        </div>

        <div class="info-box">
            <div class="info-label">Bachelors Result / Degree</div>
            <div style="margin-top:6px">
                @if($teacher->bachelors_cert)
                    <a href="{{ Storage::url($teacher->bachelors_cert) }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#D48A2E;border-color:#E8CEAA;background:#FBF3E8">
                        📄 View Bachelors Result
                    </a>
                @else
                    <span style="font-size:12px;color:#64748b">Not Uploaded</span>
                @endif
            </div>
        </div>
    </div>

    @if($teacher->masters_cert || $teacher->phd_cert)
        <div class="detail-grid detail-grid-2" style="margin-top:12px">
            @if($teacher->masters_cert)
                <div class="info-box">
                    <div class="info-label">MS / M.Phil Result Certificate</div>
                    <div style="margin-top:6px">
                        <a href="{{ Storage::url($teacher->masters_cert) }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#D48A2E;border-color:#E8CEAA;background:#FBF3E8">
                            🔬 View MS Result
                        </a>
                    </div>
                </div>
            @endif

            @if($teacher->phd_cert)
                <div class="info-box">
                    <div class="info-label">PhD Degree / Certificate</div>
                    <div style="margin-top:6px">
                        <a href="{{ Storage::url($teacher->phd_cert) }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#D48A2E;border-color:#E8CEAA;background:#FBF3E8">
                            🎓 View PhD Degree
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

<!-- UPLOAD SALARY SLIP / PAYMENT SCREENSHOT FORM -->
<div class="card mb-6" style="border-color:rgba(16,185,129,0.3)">
    <div class="card-header">
        <div class="card-title" style="color:#059669">📤 Upload Salary Slip / Payment Screenshot</div>
    </div>

    <form action="{{ route($routePrefix . 'teacher.salary-slips.store', $teacher->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="detail-grid detail-grid-3 mb-4">
            <div>
                <label class="info-label">Slip / Payment Title <span style="color:#ef4444">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g. August 2026 Monthly Salary" required>
            </div>
            <div>
                <label class="info-label">Month &amp; Year <span style="color:#ef4444">*</span></label>
                <input type="text" name="month_year" class="form-control" placeholder="e.g. August 2026" required>
            </div>
            <div>
                <label class="info-label">Amount Paid (PKR)</label>
                <input type="number" name="amount" step="500" class="form-control" value="{{ $teacher->basic_salary_pkr }}" placeholder="Amount in PKR">
            </div>
        </div>

        <div class="detail-grid detail-grid-2 mb-4">
            <div>
                <label class="info-label">Salary Slip / Screenshot File (Image / PDF) <span style="color:#ef4444">*</span></label>
                <input type="file" name="slip_file" class="form-control" accept="image/*,.pdf" required>
                <span style="font-size:11px;color:#64748b">Accepted formats: JPG, PNG, PDF (Max 5MB)</span>
            </div>
            <div>
                <label class="info-label">Optional Notes / Reference No.</label>
                <input type="text" name="notes" class="form-control" placeholder="e.g. Bank Transfer Ref #129481">
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary">
                <span>✨ Upload Pay Slip / Receipt Screenshot</span>
            </button>
        </div>
    </form>
</div>

<!-- SALARY SLIPS & PAYMENT SCREENSHOTS LEDGER -->
<div class="card">
    <div class="card-header">
        <div class="card-title">📄 Salary Slips &amp; Payment Receipts Ledger</div>
        <span class="badge badge-green">{{ $teacher->salarySlips->count() }} Records Uploaded</span>
    </div>

    @if($teacher->salarySlips->isEmpty())
        <div style="padding:24px;text-align:center;color:#64748b;background:#f8fafc;border-radius:12px;border:1px dashed #cbd5e1">
            No salary slips or payment screenshots uploaded yet for {{ $teacher->full_name }}.
        </div>
    @else
        <div>
            @foreach($teacher->salarySlips as $slip)
                <div class="slip-card">
                    <div style="display:flex;align-items:center;gap:16px">
                        <div style="width:48px;height:48px;border-radius:10px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;font-size:24px;color:#059669;border:1px solid #a7f3d0">
                            📄
                        </div>
                        <div>
                            <div style="font-family:'Outfit',sans-serif;font-weight:800;font-size:16px;color:#0f172a">
                                {{ $slip->title }}
                            </div>
                            <div style="font-size:12px;color:#64748b;margin-top:2px;display:flex;gap:12px;font-weight:500">
                                <span>📅 {{ $slip->month_year }}</span>
                                @if($slip->amount)
                                    <span style="color:#059669;font-weight:700">💵 PKR {{ number_format($slip->amount) }}</span>
                                @endif
                                <span>🕒 Uploaded {{ $slip->created_at->format('d M, Y (h:i A)') }}</span>
                            </div>
                            @if($slip->notes)
                                <div style="font-size:11px;color:#334155;margin-top:4px">
                                    📌 {{ $slip->notes }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:12px">
                        <a href="{{ Storage::url($slip->file_path) }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#D48A2E;border-color:#E8CEAA;background:#FBF3E8">
                            👁️ View / Download File
                        </a>

                        <form action="{{ route($routePrefix . 'salary-slips.destroy', $slip->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this salary slip?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                🗑️ Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
