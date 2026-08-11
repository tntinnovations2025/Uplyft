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

        <form id="admissionForm" class="space-y-4">
            <!-- Target Institute Selector -->
            <div>
                <label for="institute_id" class="block text-xs font-medium text-slate-300 mb-1">Target Institute</label>
                <select name="institute_id" id="institute_id" class="w-full glass-input p-3 rounded-xl text-sm" required>
                    @php
                        try {
                            $institutes = \App\Models\Institute::all();
                        } catch (\Throwable $e) {
                            $institutes = collect();
                        }
                    @endphp
                    @forelse($institutes as $inst)
                        <option value="{{ $inst->id }}" 
                                data-base-fee="{{ $inst->settings['base_admission_fee'] ?? 15000 }}"
                                data-filer-rate="{{ $inst->settings['filer_tax_rate'] ?? 0.04 }}"
                                data-non-filer-rate="{{ $inst->settings['non_filer_tax_rate'] ?? 0.12 }}">
                            {{ $inst->name }}
                        </option>
                    @empty
                        <option value="" disabled selected>No institutes found. Please seed default tenant!</option>
                    @endforelse
                </select>
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
                    <input type="email" name="email" id="email" placeholder="john.doe@example.com" class="w-full glass-input p-3 rounded-xl text-sm" required>
                </div>

                <div>
                    <label for="phone" class="block text-xs font-medium text-slate-300 mb-1">Phone Number (Optional)</label>
                    <input type="text" name="phone" id="phone" placeholder="+923001234567" class="w-full glass-input p-3 rounded-xl text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="date_of_birth" class="block text-xs font-medium text-slate-300 mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" class="w-full glass-input p-3 rounded-xl text-sm" required>
                </div>

                <div>
                    <label for="previous_marks" class="block text-xs font-medium text-slate-300 mb-1">Previous Marks (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="previous_marks" id="previous_marks" placeholder="85.50" class="w-full glass-input p-3 rounded-xl text-sm" required>
                </div>

                <div>
                    <label for="blood_group" class="block text-xs font-medium text-slate-300 mb-1">Blood Group (Optional)</label>
                    <select name="blood_group" id="blood_group" class="w-full glass-input p-3 rounded-xl text-sm">
                        <option value="" selected>Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
            </div>

            <!-- Guardian Tax Status Toggle -->
            <div class="pt-2">
                <label class="block text-xs font-medium text-slate-300 mb-2">Guardian Tax Filing Profile</label>
                <div class="grid grid-cols-2 gap-4">
                    <label id="cardFiler" class="cursor-pointer glass-panel p-4 flex items-center gap-3 border-2 border-indigo-500 bg-indigo-500/10">
                        <input type="radio" name="guardian_tax_status" id="tax_filer" value="filer" class="hidden" checked onchange="updateLedger()">
                        <div class="w-4 h-4 rounded-full border-2 border-indigo-400 flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Active Filer</p>
                            <p class="text-[10px] text-slate-400">Reduced Tax Rate (e.g. 4-5%)</p>
                        </div>
                    </label>

                    <label id="cardNonFiler" class="cursor-pointer glass-panel p-4 flex items-center gap-3 border-2 border-transparent hover:border-slate-600">
                        <input type="radio" name="guardian_tax_status" id="tax_nonfiler" value="non-filer" class="hidden" onchange="updateLedger()">
                        <div class="w-4 h-4 rounded-full border-2 border-slate-500 flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-transparent"></div>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white">Non-Filer</p>
                            <p class="text-[10px] text-slate-400">Standard Surcharge (e.g. 12-15%)</p>
                        </div>
                    </label>
                </div>
            </div>
        </form>
    </div>

    <!-- RIGHT: LIVE LEDGER & SUCCESS PANEL -->
    <div class="lg:col-span-5 flex flex-col gap-6">
        
        <!-- LIVE LEDGER PANEL -->
        <div class="glass-panel p-6 flex-1 flex flex-col justify-between" id="ledgerBox">
            <div>
                <div class="border-b border-white/10 pb-4 mb-4 flex items-center justify-between">
                    <h3 class="font-bold text-white text-base">Admission Fee Ledger</h3>
                    <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 text-[10px] font-semibold">Realtime Calculation</span>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-1 text-slate-300">
                        <span>Target Institute:</span>
                        <span class="font-semibold text-white" id="ledInst">--</span>
                    </div>
                    <div class="flex justify-between py-1 text-slate-300">
                        <span>Base Processing Fee:</span>
                        <span class="font-semibold text-white" id="ledBase">$0.00</span>
                    </div>
                    <div class="flex justify-between py-1 text-slate-300">
                        <span>Tax Status Adjustment:</span>
                        <span class="font-semibold text-indigo-300" id="ledStatus">Active Filer</span>
                    </div>
                    <div class="flex justify-between py-1 text-slate-300">
                        <span>Applied Tax Rate:</span>
                        <span class="font-semibold text-white" id="ledRate">0%</span>
                    </div>
                    <div class="flex justify-between py-1 text-slate-300">
                        <span>Tax Surcharge:</span>
                        <span class="font-semibold text-amber-400" id="ledTax">$0.00</span>
                    </div>

                    <div class="border-t border-white/10 pt-3 mt-3 flex justify-between items-center text-sm font-bold text-white">
                        <span>Grand Total Payable:</span>
                        <span class="text-xl text-indigo-400 font-heading" id="ledTotal">$0.00</span>
                    </div>
                </div>
            </div>

            <button type="button" onclick="submitAdmission()" id="btnSubmit" class="w-full mt-6 py-3.5 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition">
                <i class="fa-solid fa-file-invoice"></i>
                <span>Confirm Admission & Generate Invoice</span>
            </button>
        </div>

        <!-- SUCCESS PANEL (HIDDEN INITIALLY) -->
        <div id="successPanel" class="hidden glass-panel p-6 text-center space-y-4 border-emerald-500/40 bg-emerald-950/20">
            <div class="w-14 h-14 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-2xl mx-auto shadow-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-white">Registration Successful!</h3>
                <p class="text-xs text-slate-300 mt-1" id="succMessage">Student record persisted and invoice calculated.</p>
            </div>

            <div class="p-4 rounded-xl bg-slate-900/60 text-left text-xs space-y-2.5 border border-white/5">
                <div class="flex justify-between"><span class="text-slate-400">Student Name:</span><span class="font-bold text-white" id="succRef">--</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Total Invoice:</span><span class="font-bold text-emerald-400" id="succTotal">--</span></div>
                <div class="border-t border-white/10 pt-2 mt-1 space-y-2">
                    <p class="text-[10px] uppercase text-slate-500 font-semibold tracking-wide">Generated Login Credentials</p>
                    <div class="flex justify-between"><span class="text-slate-400">Roll Number:</span><span class="font-bold text-indigo-300" id="succRollNo">--</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Default Password:</span><span class="font-bold text-amber-300" id="succPassword">--</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Login Portal:</span><a href="/login" id="succPortal" class="text-cyan-400 underline">Student Portal</a></div>
                </div>
            </div>

            <a href="#" id="pdfDownloadBtn" target="_blank" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center justify-center gap-2 transition shadow-lg shadow-emerald-600/30">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Download PDF Invoice</span>
            </a>

            <button type="button" onclick="resetForm()" class="text-xs text-slate-400 hover:text-white underline pt-2">Register Another Student</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const instSelect = document.getElementById('institute_id');
    const filerCard = document.getElementById('cardFiler');
    const nonFilerCard = document.getElementById('cardNonFiler');

    document.addEventListener('DOMContentLoaded', () => {
        updateLedger();
        instSelect.addEventListener('change', updateLedger);
    });

    function updateLedger() {
        const opt = instSelect.options[instSelect.selectedIndex];
        if (!opt) return;

        const baseFee = parseFloat(opt.dataset.baseFee || 15000);
        const isFiler = document.getElementById('tax_filer').checked;
        
        // Highlight radio card
        if (isFiler) {
            filerCard.classList.add('border-indigo-500', 'bg-indigo-500/10');
            filerCard.classList.remove('border-transparent');
            nonFilerCard.classList.remove('border-indigo-500', 'bg-indigo-500/10');
            nonFilerCard.classList.add('border-transparent');
        } else {
            nonFilerCard.classList.add('border-indigo-500', 'bg-indigo-500/10');
            nonFilerCard.classList.remove('border-transparent');
            filerCard.classList.remove('border-indigo-500', 'bg-indigo-500/10');
            filerCard.classList.add('border-transparent');
        }

        const rate = isFiler ? parseFloat(opt.dataset.filerRate || 0.04) : parseFloat(opt.dataset.nonFilerRate || 0.12);
        const taxAmount = baseFee * rate;
        const total = baseFee + taxAmount;

        document.getElementById('ledInst').textContent = opt.text.trim();
        document.getElementById('ledBase').textContent = '$' + baseFee.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('ledStatus').textContent = isFiler ? 'Active Filer' : 'Non-Filer';
        document.getElementById('ledRate').textContent = (rate * 100).toFixed(1) + '%';
        document.getElementById('ledTax').textContent = '$' + taxAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('ledTotal').textContent = '$' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
    }

    async function submitAdmission() {
        const form = document.getElementById('admissionForm');
        const alertBox = document.getElementById('alertError');
        const btn = document.getElementById('btnSubmit');

        alertBox.classList.add('hidden');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner animate-spin"></i> Processing Registration...`;

        const payload = {
            institute_id: document.getElementById('institute_id').value,
            first_name: document.getElementById('first_name').value,
            last_name: document.getElementById('last_name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            date_of_birth: document.getElementById('date_of_birth').value,
            previous_marks: document.getElementById('previous_marks').value,
            blood_group: document.getElementById('blood_group').value,
            guardian_tax_status: document.querySelector('input[name="guardian_tax_status"]:checked').value
        };

        try {
            const response = await fetch('/api/admissions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            const resData = await response.json();

            if (!response.ok) {
                let msg = resData.message || 'Validation error.';
                if (resData.errors) {
                    msg = Object.values(resData.errors).flat().join('<br>');
                }
                alertBox.innerHTML = msg;
                alertBox.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-file-invoice"></i> Confirm Admission & Generate Invoice`;
                return;
            }

            // Show success panel
            document.getElementById('succRef').textContent = `${resData.student.first_name} ${resData.student.last_name}`;
            document.getElementById('succTotal').textContent = 'PKR ' + parseFloat(resData.invoice.grand_total).toLocaleString('en-US', {minimumFractionDigits: 2});
            document.getElementById('succRollNo').textContent  = resData.credentials.login_id;
            document.getElementById('succPassword').textContent = resData.credentials.password;
            document.getElementById('pdfDownloadBtn').href = resData.invoice.invoice_download_url;

            document.getElementById('ledgerBox').classList.add('hidden');
            document.getElementById('successPanel').classList.remove('hidden');

        } catch (err) {
            alertBox.textContent = 'Server error occurred: ' + err.message;
            alertBox.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-file-invoice"></i> Confirm Admission & Generate Invoice`;
        }
    }

    function resetForm() {
        document.getElementById('admissionForm').reset();
        document.getElementById('successPanel').classList.add('hidden');
        document.getElementById('ledgerBox').classList.remove('hidden');
        document.getElementById('btnSubmit').disabled = false;
        document.getElementById('btnSubmit').innerHTML = `<i class="fa-solid fa-file-invoice"></i> Confirm Admission & Generate Invoice`;
        updateLedger();
    }
</script>
@endsection
