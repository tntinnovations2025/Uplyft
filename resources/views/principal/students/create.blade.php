@extends(auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin() ? 'layouts.app' : 'principal.layouts.app')

@php
    $routePrefix = 'principal.students.';
    if (auth()->check() && !auth()->user()->isPrincipal() && !auth()->user()->isGlobalAdmin()) {
        $routePrefix = auth()->user()->getStaffUrlPrefix() . '.students.';
    }
@endphp

@section('title', 'Register New Student')
@section('breadcrumb', 'Student Admissions')

@section('content')
<style>
    .glass-card {
        background: #ffffff;
        border: 1px solid rgba(226, 232, 240, 0.85);
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }
    .form-header {
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 16px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .form-title {
        font-family: 'Outfit', sans-serif;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.5px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 20px;
    }
    @media (min-width: 768px) {
        .form-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .form-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    .field-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 8px;
    }
    .form-control {
        width: 100%;
        padding: 11px 14px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        color: #0f172a;
        font-size: 14px;
        outline: none;
        transition: all 0.2s;
    }
    .form-control:focus {
        border-color: #e1306c;
        box-shadow: 0 0 0 3px rgba(225, 48, 108, 0.2);
    }

    /* ===== ACCORDION SECTION ===== */
    .form-section {
        margin-bottom: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        transition: box-shadow 0.2s;
    }
    .form-section.open {
        box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    }
    .section-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 12px 16px;
        border: none;
        cursor: pointer;
        font-family: 'Outfit', sans-serif;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.5px;
        border-radius: 0;
        transition: background 0.2s, opacity 0.2s;
        text-align: left;
        gap: 10px;
    }
    .section-toggle:hover { filter: brightness(0.97); }
    .section-toggle-left { display: flex; align-items: center; gap: 8px; }
    .section-caret {
        font-size: 11px;
        transition: transform 0.3s ease;
        opacity: 0.6;
        flex-shrink: 0;
    }
    .form-section.open .section-caret { transform: rotate(180deg); opacity: 1; }
    .section-badge-inline {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 10px;
        background: rgba(255,255,255,0.4);
        border: 1px solid rgba(255,255,255,0.6);
    }
    .section-body {
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.4s ease, padding 0.3s ease;
        padding: 0 20px;
    }
    .form-section.open .section-body {
        max-height: 2000px;
        padding: 20px 20px 24px;
    }

    /* Section colour themes */
    .toggle-personal  { background: #ecfdf5; color: #059669; }
    .toggle-guardian  { background: #fef3c7; color: #d97706; }
    .toggle-academic  { background: #eff6ff; color: #0284c7; }
    .toggle-financial { background: #fdf4ff; color: #9333ea; }

    .btn-submit {
        background: linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);
        color: #ffffff;
        padding: 14px 28px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.25s;
        box-shadow: 0 6px 20px rgba(225, 48, 108, 0.35);
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(225, 48, 108, 0.5);
    }
    .optional-tag {
        font-size: 10px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: none;
        letter-spacing: 0;
        margin-left: 4px;
    }
</style>

<div class="glass-card">
    <div class="form-header">
        <div>
            <div class="form-title">🎓 Student Registration &amp; Admission Form</div>
            <div style="font-size:13px;color:#64748b;margin-top:4px;font-weight:500">
                Register a new student under {{ $institute->name }}. Click any section heading to expand it. All fields are optional — fill in whatever data is available.
            </div>
        </div>
        <a href="{{ route($routePrefix . 'index') }}" class="btn btn-ghost">
            &larr; Back to Roster
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-error mb-6">
            <div>
                <strong>Please fix the following validation errors:</strong>
                <ul style="margin-top:6px;padding-left:20px;font-size:13px">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route($routePrefix . 'store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="institute_id" value="{{ auth()->user()->institute_id }}">

        {{-- Auto Roll Number Banner --}}
        <div style="display:flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #a7f3d0;border-radius:10px;padding:8px 14px;margin-bottom:18px;font-size:12px;color:#059669;font-weight:700">
            <span>⚡ Auto-Generated Student Roll Number / ID:</span>
            <code style="background:#ffffff;border:1px solid #a7f3d0;padding:2px 10px;border-radius:6px;font-weight:800;font-size:13px;color:#059669;font-family:monospace">
                {{ $autoStudentId ?? 'STD-'.now()->year.'-0001' }}
            </code>
        </div>

        {{-- ============================================================= --}}
        {{-- SECTION 1: PERSONAL INFORMATION (open by default)             --}}
        {{-- ============================================================================================================= --}}
        <div class="form-section open" id="section-personal">
            <button type="button" class="section-toggle toggle-personal" onclick="toggleSection('section-personal')">
                <span class="section-toggle-left">
                    <span>👤</span>
                    <span>1. PERSONAL INFORMATION</span>
                    <span class="section-badge-inline">optional</span>
                </span>
                <span class="section-caret">▼</span>
            </button>
            <div class="section-body">
                <div class="form-grid form-grid-2 mb-6">
                    <div>
                        <label class="field-label">First Name <span class="optional-tag">(optional)</span></label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" placeholder="e.g. Muhammad">
                    </div>
                    <div>
                        <label class="field-label">Last Name <span class="optional-tag">(optional)</span></label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" placeholder="e.g. Ali">
                    </div>
                </div>

                <div class="form-grid form-grid-3 mb-6">
                    <div>
                        <label class="field-label">Email Address <span class="optional-tag">(optional)</span></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="student@institute.edu">
                    </div>
                    <div>
                        <label class="field-label">Student Phone Number <span class="optional-tag">(optional)</span></label>
                        <div style="display:flex;gap:6px">
                            <select id="phone_country_code" name="phone_country_code" class="form-control" style="width:115px;flex-shrink:0" onchange="updatePhoneValidation('student_phone', 'phone_country_code', 'phone_digit_hint')">
                                <option value="+92">PK (+92)</option>
                                <option value="+1">US (+1)</option>
                                <option value="+44">GB (+44)</option>
                                <option value="+971">AE (+971)</option>
                                <option value="+966">SA (+966)</option>
                                <option value="+91">IN (+91)</option>
                                <option value="+974">QA (+974)</option>
                                <option value="+965">KW (+965)</option>
                                <option value="+968">OM (+968)</option>
                                <option value="+61">AU (+61)</option>
                            </select>
                            <input type="text" id="student_phone" name="phone_number" class="form-control" value="{{ old('phone_number') }}" placeholder="3001234567" maxlength="10" oninput="formatPhoneInput(this, 'phone_country_code', 'phone_digit_hint')">
                        </div>
                        <span id="phone_digit_hint" style="font-size:11px;color:#64748b;margin-top:3px;display:block">Standard: 10 digits for Pakistan (+92)</span>
                    </div>
                    <div>
                        <label class="field-label">Date of Birth <span class="optional-tag">(optional)</span></label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                    </div>
                </div>

                <div class="form-grid form-grid-3">
                    <div>
                        <label class="field-label">Blood Group <span class="optional-tag">(optional)</span></label>
                        <select name="blood_group" class="form-control">
                            <option value="">Select Blood Group</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group') == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Student B-Form / CNIC <span class="optional-tag">(optional)</span></label>
                        <input type="text" name="student_bform_cnic" class="form-control" value="{{ old('student_bform_cnic') }}" placeholder="00000-0000000-0" maxlength="15" oninput="formatCNIC(this)">
                        <span style="font-size:11px;color:#64748b">Fixed Format: 00000-0000000-0</span>
                    </div>
                    <div>
                        <label class="field-label">Student Picture <span class="optional-tag">(optional)</span></label>
                        <input type="file" name="passport_picture" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- SECTION 2: GUARDIAN DETAILS (collapsed by default)            --}}
        {{-- ============================================================= --}}
        <div class="form-section" id="section-guardian">
            <button type="button" class="section-toggle toggle-guardian" onclick="toggleSection('section-guardian')">
                <span class="section-toggle-left">
                    <span>👨‍👧‍👦</span>
                    <span>2. FATHER &amp; GUARDIAN INFORMATION</span>
                    <span class="section-badge-inline">optional</span>
                </span>
                <span class="section-caret">▼</span>
            </button>
            <div class="section-body">
                <div class="form-grid form-grid-3 mb-6">
                    <div>
                        <label class="field-label">Father / Guardian Name <span class="optional-tag">(optional)</span></label>
                        <input type="text" name="father_guardian_name" class="form-control" value="{{ old('father_guardian_name') }}" placeholder="e.g. Tariq Mehmood">
                    </div>
                    <div>
                        <label class="field-label">Guardian Phone Number <span class="optional-tag">(optional)</span></label>
                        <div style="display:flex;gap:6px">
                            <select id="guardian_country_code" name="guardian_country_code" class="form-control" style="width:115px;flex-shrink:0" onchange="updatePhoneValidation('guardian_phone', 'guardian_country_code', 'guardian_digit_hint')">
                                <option value="+92">PK (+92)</option>
                                <option value="+1">US (+1)</option>
                                <option value="+44">GB (+44)</option>
                                <option value="+971">AE (+971)</option>
                                <option value="+966">SA (+966)</option>
                                <option value="+91">IN (+91)</option>
                                <option value="+974">QA (+974)</option>
                                <option value="+965">KW (+965)</option>
                                <option value="+968">OM (+968)</option>
                                <option value="+61">AU (+61)</option>
                            </select>
                            <input type="text" id="guardian_phone" name="guardian_phone_number" class="form-control" value="{{ old('guardian_phone_number') }}" placeholder="3009876543" maxlength="10" oninput="formatPhoneInput(this, 'guardian_country_code', 'guardian_digit_hint')">
                        </div>
                        <span id="guardian_digit_hint" style="font-size:11px;color:#64748b;margin-top:3px;display:block">Standard: 10 digits for Pakistan (+92)</span>
                    </div>
                    <div>
                        <label class="field-label">Father / Guardian CNIC <span class="optional-tag">(optional)</span></label>
                        <input type="text" name="father_guardian_cnic" class="form-control" value="{{ old('father_guardian_cnic') }}" placeholder="00000-0000000-0" maxlength="15" oninput="formatCNIC(this)">
                        <span style="font-size:11px;color:#64748b">Fixed Format: 00000-0000000-0</span>
                    </div>
                </div>

                <div>
                    <label class="field-label">Residential Address <span class="optional-tag">(optional)</span></label>
                    <textarea name="address" class="form-control" rows="2" placeholder="House #, Street, Sector, City">{{ old('address') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- SECTION 3: ACADEMIC CLASS & ENROLLMENT (collapsed)            --}}
        {{-- ============================================================= --}}
        <div class="form-section" id="section-academic">
            <button type="button" class="section-toggle toggle-academic" onclick="toggleSection('section-academic')">
                <span class="section-toggle-left">
                    <span>📚</span>
                    <span>3. ACADEMIC CLASS &amp; ENROLLMENT</span>
                    <span class="section-badge-inline">optional</span>
                </span>
                <span class="section-caret">▼</span>
            </button>
            <div class="section-body">
                <div class="form-grid form-grid-3">
                    <div>
                        <label class="field-label">1. Select Academic Class <span class="optional-tag">(optional)</span></label>
                        <select id="class_selector" class="form-control" onchange="onClassSelected(this.value)">
                            <option value="">-- Choose Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    📚 {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">2. Select Section <span class="optional-tag">(optional)</span></label>
                        <select id="section_selector" name="class_section_id" class="form-control" disabled>
                            <option value="">-- Select Class First --</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Previous Class Marks (%) <span class="optional-tag">(optional)</span></label>
                        <input type="number" name="previous_marks" step="0.01" min="0" max="100" class="form-control" value="{{ old('previous_marks') }}" placeholder="e.g. 85.50">
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- SECTION 4: FINANCIAL & TAX LEDGER (collapsed)                 --}}
        {{-- ============================================================= --}}
        <div class="form-section" id="section-financial">
            <button type="button" class="section-toggle toggle-financial" onclick="toggleSection('section-financial')">
                <span class="section-toggle-left">
                    <span>💳</span>
                    <span>4. FINANCIAL &amp; TAX LEDGER</span>
                    <span class="section-badge-inline">optional</span>
                </span>
                <span class="section-caret">▼</span>
            </button>
            <div class="section-body">
                <div class="form-grid form-grid-3 mb-6">
                    <div>
                        <label class="field-label">Base Monthly Tuition Fee ({{ $currencySymbol ?? 'PKR' }}) <span class="optional-tag">(optional)</span></label>
                        <input type="number" id="base_fee" name="base_fee" min="0" step="500" class="form-control" value="{{ old('base_fee', '10000') }}" oninput="recalculateFeeVoucher()">
                    </div>
                    <div>
                        <label class="field-label">Guardian Tax Filer Status (FBR) <span class="optional-tag">(optional)</span></label>
                        <select id="guardian_tax_status" name="guardian_tax_status" class="form-control" onchange="onTaxStatusChanged()">
                            <option value="non-filer" {{ old('guardian_tax_status') == 'non-filer' ? 'selected' : '' }}>Non-Filer (Standard 15% Tax)</option>
                            <option value="filer" {{ old('guardian_tax_status') == 'filer' ? 'selected' : '' }}>Filer (Discounted 5% Tax)</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Tax Rate Percentage (%) <span class="optional-tag">(optional)</span></label>
                        <div style="position:relative">
                            <input type="number" id="tax_percentage" name="tax_percentage" step="0.5" min="0" max="100" class="form-control" value="{{ old('tax_percentage', '15') }}" oninput="recalculateFeeVoucher()">
                            <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#64748b;font-weight:800">%</span>
                        </div>
                        <span style="font-size:11px;color:#64748b">Filer defaults to 5%, Non-Filer to 15%</span>
                    </div>
                </div>

                <div class="form-grid form-grid-2 mb-6" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:14px;padding:18px">
                    <div>
                        <label class="field-label" style="color:#059669">
                            Admission Fee ({{ $currencySymbol ?? 'PKR' }}) — <span style="color:#ef4444;font-weight:bold">Non-Refundable</span>
                        </label>
                        <input type="number" id="admission_fee" name="admission_fee" min="0" step="500" class="form-control" value="{{ old('admission_fee', '5000') }}" oninput="recalculateFeeVoucher()">
                        <span style="font-size:11px;color:#64748b">Charged strictly on 1st time registration (Non-Refundable).</span>
                    </div>
                    <div>
                        <label class="field-label" style="color:#0284c7">
                            Security Deposit Fee ({{ $currencySymbol ?? 'PKR' }}) — <span style="color:#059669;font-weight:bold">Refundable on Graduation/Departure</span>
                        </label>
                        <input type="number" id="security_fee" name="security_fee" min="0" step="500" class="form-control" value="{{ old('security_fee', '5000') }}" oninput="recalculateFeeVoucher()">
                        <span style="font-size:11px;color:#64748b">Refundable deposit returned when student leaves/graduates.</span>
                    </div>
                </div>

                {{-- Scholarship Section --}}
                <div style="background:#fdf4ff;border:1px solid #f5d0fe;border-radius:14px;padding:20px;margin-bottom:16px">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:8px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-size:18px">🎓</span>
                            <div>
                                <h4 style="margin:0;font-size:15px;font-weight:800;color:#0f172a">Scholarship &amp; Fee Concession Policy</h4>
                                <p style="margin:2px 0 0;font-size:11px;color:#64748b;font-weight:500">Select an institutional scholarship policy or specify a custom discount percentage</p>
                            </div>
                        </div>
                        @if(auth()->user()->role === 'principal' || auth()->user()->role === 'admin')
                            <span class="badge badge-purple" style="font-size:11px;font-weight:700">⭐ Principal Override Mode Active</span>
                        @else
                            <span class="badge badge-blue" style="font-size:11px;font-weight:700">🔒 Policy-Locked Mode</span>
                        @endif
                    </div>

                    <div class="form-grid form-grid-2 mb-4">
                        <div>
                            <label class="field-label">Scholarship Category / Policy <span class="optional-tag">(optional)</span></label>
                            <select id="scholarship_category_id" name="scholarship_category_id" class="form-control" onchange="onScholarshipCategoryChanged()">
                                <option value="" data-pct="0">-- No Scholarship (0% Discount) --</option>
                                @foreach($scholarships as $sch)
                                    <option value="{{ $sch->id }}" data-pct="{{ $sch->discount_percentage }}" data-questions='@json($sch->questions)' {{ old('scholarship_category_id') == $sch->id ? 'selected' : '' }}>
                                        🎓 {{ $sch->title }} ({{ number_format($sch->discount_percentage, 0) }}% Off)
                                    </option>
                                @endforeach
                                @if(auth()->user()->role === 'principal' || auth()->user()->role === 'admin')
                                    <option value="custom" data-pct="0">✨ Custom Percentage (Principal Direct Discount)</option>
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Scholarship Discount Percentage (%) <span class="optional-tag">(optional)</span></label>
                            <div style="position:relative">
                                <input type="number" id="scholarship_percentage" name="scholarship_percentage" step="0.5" min="0" max="100" class="form-control" value="{{ old('scholarship_percentage', '0') }}" oninput="recalculateFeeVoucher()" readonly>
                                <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#059669;font-weight:800">%</span>
                            </div>
                            <span id="scholarship_hint" style="font-size:11px;color:#64748b">
                                🔒 Predefined percentages are locked. Select "Custom Percentage" option to specify a custom discount.
                            </span>
                        </div>
                    </div>

                    <div id="verificationQuestionsPanel" style="display:none;background:#ffffff;border:1px dashed #cbd5e1;border-radius:12px;padding:16px;margin-top:14px">
                        <div style="font-size:12px;font-weight:800;color:#9333ea;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px">
                            📋 Scholarship Verification Questions &amp; Proof Criteria
                        </div>
                        <div id="verificationQuestionsFields" class="form-grid form-grid-2"></div>
                    </div>
                </div>

                {{-- Fee Voucher Preview --}}
                <div style="background:#ffffff;border:1px solid rgba(226,232,240,0.85);border-radius:16px;padding:20px;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid #e2e8f0">
                        <span style="font-size:13px;font-weight:800;text-transform:uppercase;color:#059669;letter-spacing:1px">
                            🧾 Real-Time First Admission Voucher Breakdown
                        </span>
                        <span id="voucher_status_badge" class="badge badge-green">Calculated</span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));gap:12px;text-align:center">
                        <div style="background:#f8fafc;padding:12px;border-radius:10px;border:1px solid #e2e8f0">
                            <div style="font-size:10px;text-transform:uppercase;color:#64748b;font-weight:700">Tuition Fee</div>
                            <div id="preview_base_fee" style="font-size:15px;font-weight:800;color:#0f172a;margin-top:4px">{{ $currencySymbol ?? 'PKR' }} 10,000</div>
                        </div>
                        <div style="background:#ecfdf5;padding:12px;border-radius:10px;border:1px solid #a7f3d0">
                            <div style="font-size:10px;text-transform:uppercase;color:#059669;font-weight:700">Admission Fee</div>
                            <div id="preview_admission_fee" style="font-size:15px;font-weight:800;color:#059669;margin-top:4px">{{ $currencySymbol ?? 'PKR' }} 5,000</div>
                        </div>
                        <div style="background:#eff6ff;padding:12px;border-radius:10px;border:1px solid #bfdbfe">
                            <div style="font-size:10px;text-transform:uppercase;color:#0284c7;font-weight:700">Security Fee</div>
                            <div id="preview_security_fee" style="font-size:15px;font-weight:800;color:#0284c7;margin-top:4px">{{ $currencySymbol ?? 'PKR' }} 5,000</div>
                        </div>
                        <div style="background:#ecfdf5;padding:12px;border-radius:10px;border:1px solid #a7f3d0">
                            <div style="font-size:10px;text-transform:uppercase;color:#059669;font-weight:700">Scholarship</div>
                            <div id="preview_discount" style="font-size:15px;font-weight:800;color:#059669;margin-top:4px">- {{ $currencySymbol ?? 'PKR' }} 0</div>
                        </div>
                        <div style="background:#eff6ff;padding:12px;border-radius:10px;border:1px solid #bfdbfe">
                            <div style="font-size:10px;text-transform:uppercase;color:#0284c7;font-weight:700">Tax (<span id="preview_tax_rate">15%</span>)</div>
                            <div id="preview_tax_amount" style="font-size:15px;font-weight:800;color:#0284c7;margin-top:4px">+ {{ $currencySymbol ?? 'PKR' }} 2,250</div>
                        </div>
                        <div style="background:#fdf2f8;padding:12px;border-radius:10px;border:1px solid #fbcfe8">
                            <div style="font-size:10px;text-transform:uppercase;color:#be185d;font-weight:800">Grand Total</div>
                            <div id="preview_grand_total" style="font-size:17px;font-weight:900;color:#be185d;margin-top:4px">{{ $currencySymbol ?? 'PKR' }} 22,250</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SUBMIT --}}
        <div style="margin-top:24px;display:flex;justify-content:flex-end;gap:16px;border-top:1px solid #e2e8f0;padding-top:20px">
            <button type="submit" class="btn-submit">
                <span>✨ Submit Registration &amp; Issue Student Credentials</span>
                <span>&rarr;</span>
            </button>
        </div>
    </form>
</div>

<script>
    // ===== ACCORDION TOGGLE =====
    function toggleSection(id) {
        const section = document.getElementById(id);
        section.classList.toggle('open');
    }

    // ===== CLASS → SECTION LOADER =====
    const classSectionsData = {
        @foreach($classes as $class)
            "{{ $class->id }}": [
                @foreach($class->sections as $sec)
                    { id: "{{ $sec->id }}", name: "{{ $sec->section_name ?? $sec->name }}" },
                @endforeach
            ],
        @endforeach
    };

    function onClassSelected(classId) {
        const sectionSelect = document.getElementById('section_selector');
        sectionSelect.innerHTML = '<option value="">-- Choose Section --</option>';

        if (!classId || !classSectionsData[classId] || classSectionsData[classId].length === 0) {
            sectionSelect.disabled = true;
            sectionSelect.innerHTML = '<option value="">-- No Sections Available --</option>';
            return;
        }

        classSectionsData[classId].forEach(sec => {
            const opt = document.createElement('option');
            opt.value = sec.id;
            opt.textContent = `Section ${sec.name}`;
            sectionSelect.appendChild(opt);
        });
        sectionSelect.disabled = false;
    }

    // ===== PHONE VALIDATION =====
    const countryPhoneSpecs = {
        '+92': { name: 'Pakistan', digits: 10, placeholder: '3001234567' },
        '+1': { name: 'USA/Canada', digits: 10, placeholder: '2025550143' },
        '+44': { name: 'UK', digits: 10, placeholder: '7911123456' },
        '+971': { name: 'UAE', digits: 9, placeholder: '501234567' },
        '+966': { name: 'Saudi Arabia', digits: 9, placeholder: '501234567' },
        '+91': { name: 'India', digits: 10, placeholder: '9876543210' },
        '+974': { name: 'Qatar', digits: 8, placeholder: '33123456' },
        '+965': { name: 'Kuwait', digits: 8, placeholder: '91234567' },
        '+968': { name: 'Oman', digits: 8, placeholder: '91234567' },
        '+61': { name: 'Australia', digits: 9, placeholder: '412345678' }
    };

    function updatePhoneValidation(inputId, selectId, hintId) {
        const select = document.getElementById(selectId);
        const input = document.getElementById(inputId);
        const spec = countryPhoneSpecs[select.value] || { name: 'International', digits: 10, placeholder: '1234567890' };
        input.placeholder = spec.placeholder;
        input.maxLength = spec.digits;
        formatPhoneInput(input, selectId, hintId);
    }

    function formatPhoneInput(input, selectId, hintId) {
        const select = document.getElementById(selectId);
        const hint = document.getElementById(hintId);
        const spec = countryPhoneSpecs[select.value] || { name: 'International', digits: 10, placeholder: '1234567890' };
        let digits = input.value.replace(/\D/g, '');
        if (digits.length > spec.digits) digits = digits.substring(0, spec.digits);
        input.value = digits;
        if (!hint) return;
        if (digits.length === 0) {
            hint.innerHTML = `Standard: Requires ${spec.digits} digits for ${spec.name} (${select.value})`;
            hint.style.color = '#64748b';
        } else if (digits.length === spec.digits) {
            hint.innerHTML = `✓ Exact valid length for ${spec.name} (${digits.length}/${spec.digits} digits)`;
            hint.style.color = '#059669';
        } else {
            hint.innerHTML = `⚠️ Entering ${digits.length}/${spec.digits} required digits for ${spec.name}`;
            hint.style.color = '#d97706';
        }
    }

    function formatCNIC(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 13) value = value.substring(0, 13);
        if (value.length > 12) {
            input.value = value.substring(0, 5) + '-' + value.substring(5, 12) + '-' + value.substring(12, 13);
        } else if (value.length > 5) {
            input.value = value.substring(0, 5) + '-' + value.substring(5);
        } else {
            input.value = value;
        }
    }

    // ===== TAX & SCHOLARSHIP ENGINE =====
    function onTaxStatusChanged() {
        const status = document.getElementById('guardian_tax_status').value;
        const taxInput = document.getElementById('tax_percentage');
        taxInput.value = (status === 'filer') ? 5 : 15;
        recalculateFeeVoucher();
    }

    function onScholarshipCategoryChanged() {
        const select = document.getElementById('scholarship_category_id');
        const selectedOpt = select.options[select.selectedIndex];
        const discountInput = document.getElementById('scholarship_percentage');
        const hintSpan = document.getElementById('scholarship_hint');
        const questionsPanel = document.getElementById('verificationQuestionsPanel');
        const questionsFields = document.getElementById('verificationQuestionsFields');

        if (select.value === 'custom') {
            discountInput.removeAttribute('readonly');
            discountInput.value = discountInput.value || 0;
            discountInput.focus();
            if (hintSpan) { hintSpan.innerHTML = '✨ Custom percentage mode active — enter custom discount percentage (0 - 100%)'; hintSpan.style.color = '#d97706'; }
            questionsPanel.style.display = 'none';
            questionsFields.innerHTML = '';
        } else if (select.value === '') {
            discountInput.value = 0;
            discountInput.setAttribute('readonly', 'readonly');
            if (hintSpan) { hintSpan.innerHTML = 'No scholarship discount applied (0%)'; hintSpan.style.color = '#64748b'; }
            questionsPanel.style.display = 'none';
            questionsFields.innerHTML = '';
        } else {
            const pct = selectedOpt.getAttribute('data-pct') || 0;
            discountInput.value = pct;
            discountInput.setAttribute('readonly', 'readonly');
            if (hintSpan) { hintSpan.innerHTML = `🔒 Predefined percentage locked to policy default (${pct}%)`; hintSpan.style.color = '#059669'; }

            const questionsJson = selectedOpt.getAttribute('data-questions');
            let questions = [];
            try { questions = JSON.parse(questionsJson); } catch (e) {}

            if (questions && questions.length > 0) {
                questionsFields.innerHTML = '';
                questions.forEach((q, idx) => {
                    const fieldDiv = document.createElement('div');
                    const qId = q.id || `q_${idx}`;
                    let inputHtml = '';
                    if (q.type === 'number') {
                        inputHtml = `<input type="number" name="scholarship_verification_answers[${qId}]" class="form-control" placeholder="Enter number...">`;
                    } else if (q.type === 'date') {
                        inputHtml = `<input type="date" name="scholarship_verification_answers[${qId}]" class="form-control">`;
                    } else {
                        inputHtml = `<input type="text" name="scholarship_verification_answers[${qId}]" class="form-control" placeholder="Enter answer...">`;
                    }
                    fieldDiv.innerHTML = `<label class="field-label">${escapeHtml(q.label)}</label>${inputHtml}`;
                    questionsFields.appendChild(fieldDiv);
                });
                questionsPanel.style.display = 'block';
            } else {
                questionsPanel.style.display = 'none';
                questionsFields.innerHTML = '';
            }
        }
        recalculateFeeVoucher();
    }

    function recalculateFeeVoucher() {
        const baseFee = parseFloat(document.getElementById('base_fee')?.value) || 0;
        const admFee = parseFloat(document.getElementById('admission_fee')?.value) || 0;
        const secFee = parseFloat(document.getElementById('security_fee')?.value) || 0;
        const schPct = parseFloat(document.getElementById('scholarship_percentage')?.value) || 0;
        const taxRate = parseFloat(document.getElementById('tax_percentage')?.value) || 0;

        const discountAmt = Math.round(baseFee * (schPct / 100));
        const subtotalTuition = Math.max(0, baseFee - discountAmt);
        const taxableAmount = subtotalTuition + admFee;
        const taxAmt = Math.round(taxableAmount * (taxRate / 100));
        const grandTotal = Math.round(taxableAmount + taxAmt + secFee);

        const currSym = @json($currencySymbol ?? 'PKR');
        const set = (id, val) => { const el = document.getElementById(id); if (el) el.innerText = val; };
        set('preview_base_fee', currSym + ' ' + baseFee.toLocaleString());
        set('preview_admission_fee', currSym + ' ' + admFee.toLocaleString());
        set('preview_security_fee', currSym + ' ' + secFee.toLocaleString());
        set('preview_discount', '- ' + currSym + ' ' + discountAmt.toLocaleString());
        set('preview_tax_rate', taxRate + '%');
        set('preview_tax_amount', '+ ' + currSym + ' ' + taxAmt.toLocaleString());
        set('preview_grand_total', currSym + ' ' + grandTotal.toLocaleString());
    }

    function escapeHtml(text) {
        return text ? String(text).replace(/"/g, '&quot;') : '';
    }

    document.addEventListener('DOMContentLoaded', () => {
        onTaxStatusChanged();
        onScholarshipCategoryChanged();
    });
</script>
@endsection
