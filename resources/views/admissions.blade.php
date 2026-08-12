@extends('layouts.app')

@section('title', 'Student Admissions Portal')
@section('page-header', 'Student Admissions & Invoice Engine')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6" id="admissionWrapper">
    
    <!-- LEFT: FORM SECTION -->
    <div class="lg:col-span-7 glass-panel p-6">
        <div class="border-b border-white/10 pb-4 mb-6">
            <h2 class="text-xl font-bold text-white">Student Registration</h2>
            <p class="text-xs text-slate-400">Fill in student details to compute fee tax ledger and issue PDF invoice.</p>
        </div>

        <div id="alertError" class="hidden mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 text-xs"></div>

        <form id="admissionForm" class="space-y-4" enctype="multipart/form-data">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Target Institute Selector -->
                <div>
                    <label for="institute_id" class="block text-xs font-medium text-slate-300 mb-1">Target Institute</label>
                    <select name="institute_id" id="institute_id" class="w-full glass-input p-3 rounded-xl text-sm" required onchange="triggerReCalc()">
                        @php
                            try {
                                $institutes = \App\Models\Institute::all();
                            } catch (\Throwable $e) {
                                $institutes = collect();
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
                    <label for="passport_picture" class="block text-xs font-medium text-slate-300 mb-1">Passport Picture</label>
                    <input type="file" name="passport_picture" id="passport_picture" accept="image/*" class="w-full glass-input p-2 rounded-xl text-sm bg-slate-900/40">
                </div>
            </div>

            <!-- Personal Details Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-xs font-medium text-slate-300 mb-1">First Name</label>
                    <input type="text" name="first_name" id="first_name" placeholder="John" class="w-full glass-input p-3 rounded-xl text-sm" required>
                </div>

                <div>
                    <label for="last_name" class="block text-xs font-medium text-slate-300 mb-1">Last Name</label>
                    <input type="text" name="last_name" id="last_name" placeholder="Doe" class="w-full glass-input p-3 rounded-xl text-sm" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-1">Email Address</label>
                    <input type="email" name="email" id="email" placeholder="student@example.com" class="w-full glass-input p-3 rounded-xl text-sm" required>
                </div>
                <div>
                    <label for="phone" class="block text-xs font-medium text-slate-300 mb-1">Phone Number</label>
                    <input type="text" name="phone" id="phone" placeholder="+923001234567" class="w-full glass-input p-3 rounded-xl text-sm">
                </div>
            </div>

            <!-- IDs and Address -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="student_bform_cnic" class="block text-xs font-medium text-slate-300 mb-1">Student B-Form/CNIC</label>
                    <input type="text" name="student_bform_cnic" id="student_bform_cnic" placeholder="12345-1234567-1" class="w-full glass-input p-3 rounded-xl text-sm">
                </div>
                <div>
                    <label for="father_guardian_cnic" class="block text-xs font-medium text-slate-300 mb-1">Father/Guardian CNIC</label>
                    <input type="text" name="father_guardian_cnic" id="father_guardian_cnic" placeholder="12345-1234567-1" class="w-full glass-input p-3 rounded-xl text-sm">
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="father_guardian_name" class="block text-xs font-medium text-slate-300 mb-1">Father/Guardian Name</label>
                    <input type="text" name="father_guardian_name" id="father_guardian_name" placeholder="John Doe Sr." class="w-full glass-input p-3 rounded-xl text-sm">
                </div>
                <div>
                    <label for="address" class="block text-xs font-medium text-slate-300 mb-1">Address</label>
                    <textarea name="address" id="address" rows="1" class="w-full glass-input p-3 rounded-xl text-sm" placeholder="123 Education St..."></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="date_of_birth" class="block text-xs font-medium text-slate-300 mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" class="w-full glass-input p-3 rounded-xl text-sm" required>
                </div>
                <div>
                    <label for="blood_group" class="block text-xs font-medium text-slate-300 mb-1">Blood Group</label>
                    <select name="blood_group" id="blood_group" class="w-full glass-input p-3 rounded-xl text-sm">
                        <option value="">Unknown</option>
                        <option value="A+">A+</option><option value="A-">A-</option>
                        <option value="B+">B+</option><option value="B-">B-</option>
                        <option value="O+">O+</option><option value="O-">O-</option>
                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                    </select>
                </div>
                <div>
                    <label for="previous_marks" class="block text-xs font-medium text-slate-300 mb-1">Previous Marks (%)</label>
                    <input type="number" name="previous_marks" id="previous_marks" step="0.01" min="0" max="100" class="w-full glass-input p-3 rounded-xl text-sm" placeholder="85.50" required>
                </div>
            </div>

            <!-- Academic & Fees -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-white/10 pt-4">
                <div>
                    <label for="enrolled_program" class="block text-xs font-medium text-slate-300 mb-1">Enrolled Program (Grade/Class)</label>
                    <input type="text" name="enrolled_program" id="enrolled_program" placeholder="Grade 10 / BSCS Sem 3" class="w-full glass-input p-3 rounded-xl text-sm">
                </div>
                <div>
                    <label for="base_fee" class="block text-xs font-medium text-emerald-400 mb-1">Base Fee (PKR)</label>
                    <input type="number" name="base_fee" id="base_fee" value="50000" min="0" step="500" class="w-full glass-input p-3 rounded-xl text-sm font-bold text-emerald-300" required oninput="triggerReCalc()">
                </div>
            </div>

            <!-- TAX FILER TOGGLE -->
            <div class="mt-4 p-4 rounded-xl border border-cyan-500/30 bg-cyan-500/5">
                <label class="block text-xs font-medium text-cyan-300 mb-2">Guardian Tax Filer Status (Affects Withholding Tax)</label>
                <div class="flex items-center gap-4">
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="radio" name="guardian_tax_status" value="filer" onchange="triggerReCalc()" class="accent-cyan-500 w-4 h-4">
                        <span class="text-sm text-slate-300">Filer (5% Tax)</span>
                    </label>
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="radio" name="guardian_tax_status" value="non-filer" onchange="triggerReCalc()" class="accent-cyan-500 w-4 h-4" checked>
                        <span class="text-sm text-slate-300">Non-Filer (15% Tax)</span>
                    </label>
                </div>
            </div>

            <button type="button" id="btnSubmitForm" onclick="submitAdmission()" class="w-full py-4 rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-bold shadow-lg shadow-emerald-500/30 transition flex items-center justify-center gap-2 mt-4">
                <i class="fa-solid fa-file-invoice"></i> Generate Invoice & Enroll Student
            </button>
        </form>
    </div>

    <!-- RIGHT: LIVE LEDGER PREVIEW -->
    <div class="lg:col-span-5 space-y-6">
        <!-- Sticky Container -->
        <div class="sticky top-6">
            
            <div class="glass-panel p-6 overflow-hidden relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-bl-full -mr-10 -mt-10 pointer-events-none"></div>
                
                <h3 class="font-bold text-white mb-1"><i class="fa-solid fa-calculator text-emerald-400 mr-2"></i> Live Fee Ledger</h3>
                <p class="text-[11px] text-slate-400 mb-6 border-b border-white/10 pb-3">Real-time tax computation based on FBR Filer status.</p>

                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Base Admission Fee</span>
                        <span class="text-white font-semibold">PKR <span id="previewBaseFee">50,000.00</span></span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Tax Rate Applied</span>
                        <span class="text-amber-400 text-xs font-bold px-2 py-1 rounded bg-amber-500/10 border border-amber-500/20" id="previewTaxPill">Non-Filer 15%</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Tax Amount</span>
                        <span class="text-red-300">+ PKR <span id="previewTaxAmount">7,500.00</span></span>
                    </div>

                    <div class="pt-4 border-t border-white/10 flex justify-between items-center">
                        <span class="text-slate-300 font-bold uppercase tracking-wider text-xs">Grand Total</span>
                        <span class="text-xl font-bold text-emerald-400">PKR <span id="previewTotal">57,500.00</span></span>
                    </div>
                </div>
            </div>

            <!-- SUCCESS PANEL -->
            <div id="successPanel" class="hidden mt-6 glass-panel p-6 border border-emerald-500/30 bg-emerald-500/5 transition-all">
                <h3 class="text-emerald-400 font-bold mb-2 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i> Admission Complete
                </h3>
                <p class="text-xs text-slate-300 mb-4" id="successMsg">Student enrolled successfully.</p>
                
                <div class="bg-slate-900/60 p-3 rounded-lg border border-white/5 mb-4 space-y-1">
                    <div class="flex justify-between text-[11px]"><span class="text-slate-400">Login ID:</span><span class="text-emerald-300 font-bold" id="succLoginId">--</span></div>
                    <div class="flex justify-between text-[11px]"><span class="text-slate-400">Password:</span><span class="text-amber-300 font-bold" id="succPassword">--</span></div>
                </div>

                <a id="btnDownloadInvoice" href="#" target="_blank" class="block w-full text-center py-3 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-lg shadow-emerald-500/20">
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
        
        if (isFiler) {
            document.getElementById('previewTaxPill').classList.replace('text-amber-400', 'text-cyan-400');
            document.getElementById('previewTaxPill').classList.replace('bg-amber-500/10', 'bg-cyan-500/10');
            document.getElementById('previewTaxPill').classList.replace('border-amber-500/20', 'border-cyan-500/20');
        } else {
            document.getElementById('previewTaxPill').classList.replace('text-cyan-400', 'text-amber-400');
            document.getElementById('previewTaxPill').classList.replace('bg-cyan-500/10', 'bg-amber-500/10');
            document.getElementById('previewTaxPill').classList.replace('border-cyan-500/20', 'border-amber-500/20');
        }

        document.getElementById('previewTaxAmount').textContent = formatCurrency(taxAmount);
        document.getElementById('previewTotal').textContent = formatCurrency(total);
    }

    // Call once on load
    document.addEventListener('DOMContentLoaded', triggerReCalc);

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

            document.getElementById('succLoginId').textContent = resData.credentials.login_id;
            document.getElementById('succPassword').textContent = resData.credentials.password;
            
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
