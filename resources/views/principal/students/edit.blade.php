@extends(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app')

@php
    $routePrefix = 'principal.students.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.students.';
    }
@endphp

@section('title', 'Edit Student Profile: ' . $student->full_name)
@section('breadcrumb', 'Edit Student Sensitive Profile')

@section('content')
<style>
    .edit-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 18px;
    }
    .edit-section-title {
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 16px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 16px;
    }
    @media (min-width: 768px) {
        .form-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    .form-group label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 6px;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        color: #0f172a;
        background: #f8fafc;
        transition: border 0.2s, box-shadow 0.2s;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #D48A2E;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
    }
    .form-group .help-text {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 4px;
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.5px">
            ✏️ Edit Student Profile
        </h1>
        <div style="font-size:13px;color:#64748b;margin-top:2px">Update sensitive identification records. Changes sync to the login account.</div>
    </div>
    <div style="display:flex;gap:10px">
        <a href="{{ route($routePrefix . 'show', $student) }}" class="btn btn-ghost">
            <span><x-icon name="arrow-left" class="w-3.5 h-3.5" /></span> Back to Profile
        </a>
    </div>
</div>

<form method="POST" action="{{ route($routePrefix . 'update-profile', $student) }}">
    @csrf
    @method('PUT')

    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:16px">
            <strong style="font-size:13px;color:#b91c1c">Please fix the following errors:</strong>
            <ul style="margin:6px 0 0 0;padding-left:18px;font-size:12.5px;color:#b91c1c">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="edit-card">
        <h2 class="edit-section-title">👤 Student Identification</h2>
        <div class="form-grid form-grid-2 mb-4">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required>
            </div>
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required>
            </div>
            <div class="form-group">
                <label>Student ID / Roll Number *</label>
                <input type="text" name="roll_number" value="{{ old('roll_number', $student->roll_number) }}" required>
                <div class="help-text">Sensitive student ID — also used as the student login ID.</div>
            </div>
            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" value="{{ old('email', $student->email ?? $student->user?->email) }}" required>
                <div class="help-text">Used as the login email for the student account.</div>
            </div>
            <div class="form-group">
                <label>Student Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone', $student->phone) }}">
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '') }}">
            </div>
            <div class="form-group">
                <label>Student B-Form / CNIC</label>
                <input type="text" name="student_bform_cnic" value="{{ old('student_bform_cnic', $student->student_bform_cnic) }}">
            </div>
            <div class="form-group">
                <label>Father / Guardian CNIC</label>
                <input type="text" name="father_guardian_cnic" value="{{ old('father_guardian_cnic', $student->father_guardian_cnic) }}">
            </div>
            <div class="form-group">
                <label>Father / Guardian Name</label>
                <input type="text" name="father_guardian_name" value="{{ old('father_guardian_name', $student->father_guardian_name) }}">
            </div>
            <div class="form-group">
                <label>Guardian Phone Number</label>
                <input type="text" name="guardian_phone" value="{{ old('guardian_phone', $student->guardian_phone) }}">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label>Residential Address</label>
                <textarea name="address" rows="2">{{ old('address', $student->address) }}</textarea>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:12px;align-items:center">
        <button type="submit" class="btn btn-primary" style="padding:10px 20px">
            <span><x-icon name="save" class="w-4 h-4" /></span> Save Sensitive Changes
        </button>
        <a href="{{ route($routePrefix . 'show', $student) }}" class="btn btn-ghost">Cancel</a>
    </div>
</form>
@endsection