@extends('layouts.app')

@section('title', 'Student Admissions Portal')
@section('page-header', 'Student Admissions & Invoice Engine')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6" id="admissionWrapper">
    
    <!-- LEFT: FORM SECTION -->
    <div class="lg:col-span-7 liquid-glass-card p-6 md:p-8 border border-slate-700 shadow-2xs relative glass-specular-top bg-[#1E293B]">
        <div class="border-b border-slate-700 pb-4 mb-6">
            <h2 class="text-xl md:text-2xl font-extrabold text-[#F8FAFC] font-display">Student Registration</h2>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Fill in student details to compute fee tax ledger and issue PDF invoice.</p>
        </div>

        <div id="alertError" class="hidden mb-6 p-4 rounded-xl alert alert-danger text-xs font-semibold"></div>

        <form id="admissionForm" class="space-y-4" enctype="multipart/form-data">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Target Institute Selector -->
                <div>
                    <label for="institute_id" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Target Institute <span class="text-rose-500">*</span></label>
                    <select name="institute_id" id="institute_id" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500" required onchange="triggerReCalc()">
                        @php
                            try {
                                $institutes = \App\Models\Institute::all();
                            } catch (\Throwable $e) {
                                $institutes = collect();
                            }
                            try {
                                $allClasses = \App\Models\InstituteClass::with(['sections'])->get();
                            } catch (\Throwable $e) {
                                $allClasses = collect();
                            }
                        @endphp
                        @forelse($institutes as $inst)
                            <option value="{{ $inst->id }}" 
                                    data-filer-rate="{{ $inst->settings['filer_tax_rate'] ?? 0.05 }}"
                                    data-non-filer-rate="{{ $inst->settings['non_filer_tax_rate'] ?? 0.15 }}">
                                {{ $inst->name }}
                            </option>
                        @empty
                            <option value="" disabled selected>No institutes found.</option>
                        @endforelse
                    </select>
                </div>

                <!-- Passport Picture -->
                <div>
                    <label for="passport_picture" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Passport Picture</label>
                    <input type="file" name="passport_picture" id="passport_picture" accept="image/*" class="w-full p-2.5 rounded-xl text-xs bg-[#0F172A] border border-slate-700 shadow-2xs text-slate-300">
                </div>
            </div>

            <!-- Personal Details Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">First Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="first_name" id="first_name" placeholder="John" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500" required>
                </div>

                <div>
                    <label for="last_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Last Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="last_name" id="last_name" placeholder="Doe" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="email" placeholder="student@example.com" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500" required>
                </div>
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Phone Number</label>
                    <input type="text" name="phone" id="phone" placeholder="+923001234567" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500">
                </div>
            </div>

            <!-- IDs and Address -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="student_bform_cnic" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Student B-Form / CNIC</label>
                    <input type="text" name="student_bform_cnic" id="student_bform_cnic" placeholder="12345-1234567-1" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500">
                </div>
                <div>
                    <label for="father_guardian_cnic" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Father/Guardian CNIC</label>
                    <input type="text" name="father_guardian_cnic" id="father_guardian_cnic" placeholder="12345-1234567-1" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500">
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="father_guardian_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Father/Guardian Name</label>
                    <input type="text" name="father_guardian_name" id="father_guardian_name" placeholder="John Doe Sr." class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500">
                </div>
                <div>
                    <label for="address" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Address</label>
                    <textarea name="address" id="address" rows="1" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500" placeholder="123 Education St..."></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="date_of_birth" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Date of Birth <span class="text-rose-500">*</span></label>
                    <input type="date" name="date_of_birth" id="date_of_birth" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500" required>
                </div>
                <div>
                    <label for="blood_group" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Blood Group</label>
                    <select name="blood_group" id="blood_group" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500">
                        <option value="">Unknown</option>
                        <option value="A+">A+</option><option value="A-">A-</option>
                        <option value="B+">B+</option><option value="B-">B-</option>
                        <option value="O+">O+</option><option value="O-">O-</option>
                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                    </select>
                </div>
                <div>
                    <label for="previous_marks" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Previous Marks (%) <span class="text-rose-500">*</span></label>
                    <input type="number" name="previous_marks" id="previous_marks" step="0.01" min="0" max="100" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500" placeholder="85.50" required>
                </div>
            </div>

            <!-- Dynamic Class, Section & Academic Track Selection -->
            <div class="border-t border-slate-700 pt-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="admission_class_id" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Target Class / Grade <span class="text-rose-500">*</span>
                        </label>
                        <select id="admission_class_id" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500" onchange="onClassSelected(this.value)">
                            <option value="">-- Select Class / Level --</option>
                            @foreach($allClasses as $cls)
                                <option value="{{ $cls->id }}" data-name="{{ $cls->name }}" data-sections='@json($cls->sections)'>{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="class_section_id" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Target Section <span class="text-rose-500">*</span>
                        </label>
                        <select name="class_section_id" id="class_section_id" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500" required>
                            <option value="">-- Select Section --</option>
                        </select>
                    </div>
                </div>

                <!-- Academic Track / Bundle Selection -->
                <div id="trackWrapper" class="hidden">
                    <div class="p-4 rounded-xl border border-teal-500/30 bg-teal-950/20 space-y-3">
                        <div class="flex items-center justify-between">
                            <label for="academic_track_id" class="block text-xs font-bold text-teal-300 uppercase tracking-wider">
                                <i class="fa-solid fa-layer-group text-teal-400 mr-1.5"></i> Academic Curriculum Track (Subject Bundle)
                            </label>
                            <span id="customCombinationBadge" class="hidden text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-teal-500/20 text-teal-300 border border-teal-500/40">
                                <i class="fa-solid fa-sliders mr-1"></i> Custom Electives Allowed
                            </span>
                        </div>
                        <select name="academic_track_id" id="academic_track_id" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500" onchange="onTrackSelected(this.value)">
                            <option value="">-- Select Academic Track / Specialization --</option>
                        </select>
                        <p id="trackDescription" class="text-xs text-slate-400 italic hidden"></p>
                    </div>
                </div>

                <!-- Granular Subject Roster Preview & Elective Selector -->
                <div id="subjectRosterContainer" class="hidden p-4 rounded-xl border border-slate-700 bg-slate-900/60 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-book-open-reader text-teal-400"></i> Subject Enrollment Roster
                        </h4>
                        <span id="rosterSummaryBadge" class="text-[11px] font-semibold text-teal-400"></span>
                    </div>

                    <!-- Compulsory List -->
                    <div id="compulsorySubjectsBox" class="space-y-2">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1">
                            <i class="fa-solid fa-lock text-slate-500"></i> Compulsory Subjects (Required)
                        </div>
                        <div id="compulsoryList" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
                    </div>

                    <!-- Elective List -->
                    <div id="electiveSubjectsBox" class="space-y-2 hidden">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1">
                            <i class="fa-solid fa-check-to-slot text-teal-400"></i> Elective Subjects
                        </div>
                        <div id="electiveList" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="enrolled_program" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Program / Degree Header</label>
                        <input type="text" name="enrolled_program" id="enrolled_program" placeholder="e.g., FSc Pre-Medical / Class 11" class="w-full p-3 rounded-xl text-sm bg-[#0F172A] text-[#F8FAFC] border border-slate-700 shadow-2xs font-medium outline-none focus:border-teal-500">
                    </div>
                    <div>
                        <label for="base_fee" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Base Fee (PKR) <span class="text-rose-500">*</span></label>
                        <input type="number" name="base_fee" id="base_fee" value="50000" min="0" step="500" class="w-full p-3 rounded-xl text-sm font-bold text-teal-400 bg-[#0F172A] border border-slate-700 shadow-2xs outline-none focus:border-teal-500" required oninput="triggerReCalc()">
                    </div>
                </div>
            </div>

            <!-- TAX FILER TOGGLE -->
            <div class="mt-4 p-4 rounded-xl border border-slate-700 bg-slate-800/80">
                <label class="block text-xs font-bold text-slate-200 uppercase tracking-wider mb-2">Guardian Tax Filer Status (Affects Withholding Tax)</label>
                <div class="flex items-center gap-6">
                    <label class="cursor-pointer flex items-center gap-2 text-sm font-semibold text-slate-300">
                        <input type="radio" name="guardian_tax_status" value="filer" onchange="triggerReCalc()" class="accent-teal-500 w-4 h-4">
                        <span>Filer (5% Tax)</span>
                    </label>
                    <label class="cursor-pointer flex items-center gap-2 text-sm font-semibold text-slate-300">
                        <input type="radio" name="guardian_tax_status" value="non-filer" onchange="triggerReCalc()" class="accent-teal-500 w-4 h-4" checked>
                        <span>Non-Filer (15% Tax)</span>
                    </label>
                </div>
            </div>

            <button type="button" id="btnSubmitForm" onclick="submitAdmission()" class="w-full btn-primary py-4 text-sm font-extrabold shadow-md flex items-center justify-center gap-2 mt-4">
                <i class="fa-solid fa-file-invoice"></i> Generate Invoice & Enroll Student
            </button>
        </form>
    </div>

    <!-- RIGHT: LIVE LEDGER PREVIEW -->
    <div class="lg:col-span-5 space-y-6">
        <!-- Sticky Container -->
        <div class="sticky top-6">
            
            <div class="liquid-glass-card p-6 overflow-hidden relative border border-slate-700 shadow-2xs bg-[#1E293B]">
                <h3 class="font-extrabold text-[#F8FAFC] text-lg mb-1 font-display flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-teal-400"></i> Live Fee Ledger
                </h3>
                <p class="text-xs text-slate-400 font-medium mb-6 border-b border-slate-700 pb-3">Real-time tax computation based on FBR Filer status.</p>

                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Base Admission Fee</span>
                        <span class="text-[#F8FAFC] font-bold">PKR <span id="previewBaseFee">50,000.00</span></span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Tax Rate Applied</span>
                        <span class="badge badge-amber text-xs font-bold" id="previewTaxPill">Non-Filer 15%</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Tax Amount</span>
                        <span class="text-rose-400 font-bold">+ PKR <span id="previewTaxAmount">7,500.00</span></span>
                    </div>

                    <div class="pt-4 border-t border-slate-700 flex justify-between items-center">
                        <span class="text-slate-200 font-extrabold uppercase tracking-wider text-xs">Grand Total</span>
                        <span class="text-xl font-extrabold text-teal-400 font-display">PKR <span id="previewTotal">57,500.00</span></span>
                    </div>
                </div>
            </div>

            <!-- SUCCESS PANEL -->
            <div id="successPanel" class="hidden mt-6 liquid-glass-card p-6 border border-emerald-500/30 bg-emerald-950/40 shadow-2xs transition-all">
                <h3 class="text-emerald-400 font-extrabold mb-2 flex items-center gap-2 font-display text-base">
                    <i class="fa-solid fa-circle-check text-emerald-400"></i> Admission Complete
                </h3>
                <p class="text-xs text-emerald-300 font-medium mb-4" id="successMsg">Student enrolled successfully.</p>
                
                <div class="bg-slate-900/90 p-3.5 rounded-xl border border-slate-700 mb-4 space-y-1.5 shadow-2xs text-xs">
                    <div class="flex justify-between"><span class="text-slate-400 font-medium">Login Email:</span><span class="text-teal-400 font-bold font-mono" id="succLoginId">--</span></div>
                    <div class="flex justify-between"><span class="text-slate-400 font-medium">Password:</span><span class="text-amber-400 font-bold font-mono" id="succPassword">--</span></div>
                </div>

                <a id="btnDownloadInvoice" href="#" target="_blank" class="block w-full text-center py-3 rounded-xl btn-primary text-xs font-bold shadow-sm">
                    <i class="fa-solid fa-file-pdf mr-1"></i> Download PDF Invoice
                </a>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function triggerReCalc() {
        const select = document.getElementById('institute_id');
        const selectedOpt = select.options[select.selectedIndex];
        
        if(!selectedOpt || !selectedOpt.value) return;

        const baseFeeStr = document.getElementById('base_fee').value;
        const baseFee = parseFloat(baseFeeStr) || 50000;

        const filerRate = parseFloat(selectedOpt.getAttribute('data-filer-rate')) || 0.05;
        const nonFilerRate = parseFloat(selectedOpt.getAttribute('data-non-filer-rate')) || 0.15;

        // Filer status
        const isFiler = document.querySelector('input[name="guardian_tax_status"]:checked').value === 'filer';
        const currentRate = isFiler ? filerRate : nonFilerRate;
        const pillText = isFiler ? `Filer ${(currentRate * 100).toFixed(0)}%` : `Non-Filer ${(currentRate * 100).toFixed(0)}%`;

        const taxAmount = baseFee * currentRate;
        const total = baseFee + taxAmount;

        const formatCurrency = (val) => val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        document.getElementById('previewBaseFee').textContent = formatCurrency(baseFee);
        document.getElementById('previewTaxPill').textContent = pillText;
        
        const pill = document.getElementById('previewTaxPill');
        if (isFiler) {
            pill.className = 'badge badge-emerald text-xs font-bold';
        } else {
            pill.className = 'badge badge-amber text-xs font-bold';
        }

        document.getElementById('previewTaxAmount').textContent = formatCurrency(taxAmount);
        document.getElementById('previewTotal').textContent = formatCurrency(total);
    }

    // Call once on load
    document.addEventListener('DOMContentLoaded', triggerReCalc);

    let currentClassPool = null;

    async function onClassSelected(classId) {
        const classSelect = document.getElementById('admission_class_id');
        const selectedOpt = classSelect.options[classSelect.selectedIndex];
        const sectionSelect = document.getElementById('class_section_id');
        const trackWrapper = document.getElementById('trackWrapper');
        const trackSelect = document.getElementById('academic_track_id');
        const rosterContainer = document.getElementById('subjectRosterContainer');

        sectionSelect.innerHTML = '<option value="">-- Select Section --</option>';
        trackSelect.innerHTML = '<option value="">-- Select Academic Track / Specialization --</option>';
        trackWrapper.classList.add('hidden');
        rosterContainer.classList.add('hidden');

        if (!classId) return;

        // 1. Populate sections from data-sections attribute
        const sectionsData = selectedOpt.getAttribute('data-sections');
        if (sectionsData) {
            try {
                const sections = JSON.parse(sectionsData);
                sections.forEach(sec => {
                    const opt = document.createElement('option');
                    opt.value = sec.id;
                    opt.textContent = `${sec.section_name} (Capacity: ${sec.enrolled_students || 0}/${sec.capacity || 40})`;
                    sectionSelect.appendChild(opt);
                });
            } catch (e) {
                console.error("Failed to parse section JSON", e);
            }
        }

        const className = selectedOpt.getAttribute('data-name') || '';
        document.getElementById('enrolled_program').value = className;

        // 2. Fetch tracks and subjects via API
        try {
            const resp = await fetch(`/api/classes/${classId}/tracks-and-subjects`);
            if (resp.ok) {
                const json = await resp.json();
                currentClassPool = json.data || json;
                renderTracksAndSubjectPool(currentClassPool, className);
            }
        } catch (e) {
            console.error("Failed to load tracks and subjects", e);
        }
    }

    function renderTracksAndSubjectPool(pool, className) {
        const trackWrapper = document.getElementById('trackWrapper');
        const trackSelect = document.getElementById('academic_track_id');
        const trackDesc = document.getElementById('trackDescription');
        const customBadge = document.getElementById('customCombinationBadge');
        const rosterContainer = document.getElementById('subjectRosterContainer');
        const compulsoryList = document.getElementById('compulsoryList');
        const electiveList = document.getElementById('electiveList');
        const electiveBox = document.getElementById('electiveSubjectsBox');
        const summaryBadge = document.getElementById('rosterSummaryBadge');

        trackSelect.innerHTML = '<option value="">-- Select Academic Track / Specialization --</option>';
        compulsoryList.innerHTML = '';
        electiveList.innerHTML = '';

        const tracks = pool.tracks || [];
        const compulsory = pool.compulsory_subjects || [];
        const electives = pool.elective_subjects || [];

        // Render Compulsory subjects
        if (compulsory.length > 0) {
            compulsory.forEach(sub => {
                const el = document.createElement('div');
                el.className = 'p-2.5 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-between text-xs';
                el.innerHTML = `
                    <span class="font-bold text-slate-200">${sub.subject_name}</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400 font-semibold uppercase">Compulsory</span>
                    <input type="hidden" name="selected_subject_ids[]" value="${sub.id}">
                `;
                compulsoryList.appendChild(el);
            });
        } else {
            compulsoryList.innerHTML = '<div class="text-xs text-slate-500 italic col-span-2">No compulsory subjects configured.</div>';
        }

        // Render Elective subjects if any
        if (electives.length > 0) {
            electiveBox.classList.remove('hidden');
            electives.forEach(sub => {
                const el = document.createElement('label');
                el.className = 'p-2.5 rounded-lg bg-slate-800/80 border border-slate-700 flex items-center justify-between text-xs cursor-pointer hover:border-teal-500/50 transition-colors';
                el.innerHTML = `
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="selected_subject_ids[]" value="${sub.id}" class="elective-checkbox accent-teal-500 w-4 h-4 rounded">
                        <span class="font-bold text-slate-300">${sub.subject_name}</span>
                    </div>
                    <span class="text-[10px] text-teal-400 font-medium">${sub.credit_hours ? sub.credit_hours + ' Cr' : 'Elective'}</span>
                `;
                electiveList.appendChild(el);
            });
        } else {
            electiveBox.classList.add('hidden');
        }

        // Render Tracks dropdown
        if (tracks.length > 0) {
            trackWrapper.classList.remove('hidden');
            tracks.forEach(track => {
                const opt = document.createElement('option');
                opt.value = track.id;
                opt.textContent = `${track.track_name} ${track.track_code ? '(' + track.track_code + ')' : ''}`;
                trackSelect.appendChild(opt);
            });
            summaryBadge.textContent = `${compulsory.length} Compulsory + ${electives.length} Electives Available`;
        } else {
            trackWrapper.classList.add('hidden');
            summaryBadge.textContent = 'Standard Uniform Curriculum (All subjects compulsory)';
        }

        rosterContainer.classList.remove('hidden');
    }

    function onTrackSelected(trackId) {
        if (!currentClassPool) return;
        const tracks = currentClassPool.tracks || [];
        const track = tracks.find(t => String(t.id) === String(trackId));
        const trackDesc = document.getElementById('trackDescription');
        const customBadge = document.getElementById('customCombinationBadge');
        const programInput = document.getElementById('enrolled_program');

        // Uncheck all electives first
        document.querySelectorAll('.elective-checkbox').forEach(cb => {
            cb.checked = false;
            cb.disabled = false;
        });

        if (!track) {
            trackDesc.classList.add('hidden');
            customBadge.classList.add('hidden');
            return;
        }

        if (track.description) {
            trackDesc.textContent = track.description;
            trackDesc.classList.remove('hidden');
        } else {
            trackDesc.classList.add('hidden');
        }

        if (track.allow_custom_electives) {
            customBadge.classList.remove('hidden');
        } else {
            customBadge.classList.add('hidden');
        }

        // Check the track's bundled subjects
        const bundledIds = (track.subject_ids || []).map(String);
        document.querySelectorAll('.elective-checkbox').forEach(cb => {
            if (bundledIds.includes(String(cb.value))) {
                cb.checked = true;
            }
            if (!track.allow_custom_electives) {
                cb.disabled = true; // Locked to strict track bundle if custom combinations not permitted
            }
        });

        const currentProg = programInput.value.split(' (')[0];
        programInput.value = `${currentProg} (${track.track_name})`;
    }

    async function submitAdmission() {
        const form = document.getElementById('admissionForm');
        const errAlert = document.getElementById('alertError');
        const btn = document.getElementById('btnSubmitForm');
        
        errAlert.classList.add('hidden');
        document.getElementById('successPanel').classList.add('hidden');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner animate-spin"></i> Processing & Generating PDF...`;

        const formData = new FormData(form);

        // Ensure all checked subject checkboxes (even if disabled) are included
        document.querySelectorAll('input[name="selected_subject_ids[]"]:checked').forEach(cb => {
            if (!formData.getAll('selected_subject_ids[]').includes(cb.value)) {
                formData.append('selected_subject_ids[]', cb.value);
            }
        });

        try {
            const response = await fetch('/api/admissions', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const resData = await response.json();

            if (!response.ok) {
                let msg = resData.message || 'Submission failed.';
                if (resData.errors) {
                    msg = Object.values(resData.errors).flat().join('<br>');
                }
                errAlert.innerHTML = msg;
                errAlert.classList.remove('hidden');
                
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-file-invoice"></i> Generate Invoice & Enroll Student`;
                return;
            }

            // Success
            form.reset();
            triggerReCalc(); // Reset ledger

            const creds = resData.credentials || {};
            document.getElementById('succLoginId').textContent = creds.email || '--';
            document.getElementById('succPassword').textContent = creds.password || 'Already registered - password unchanged';
            
            document.getElementById('btnDownloadInvoice').href = resData.invoice.invoice_download_url;
            document.getElementById('successPanel').classList.remove('hidden');

            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-file-invoice"></i> Generate Invoice & Enroll Student`;

        } catch (err) {
            errAlert.textContent = 'Server connection error: ' + err.message;
            errAlert.classList.remove('hidden');
            
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-file-invoice"></i> Generate Invoice & Enroll Student`;
        }
    }
</script>
@endsection
