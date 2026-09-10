<?php $__env->startSection('title', 'Student Admissions Portal'); ?>
<?php $__env->startSection('page-header', 'Student Admissions & Invoice Engine'); ?>

<?php $__env->startSection('content'); ?>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6" id="admissionWrapper">
    
    <!-- LEFT: FORM SECTION -->
    <div class="lg:col-span-7 liquid-glass-card p-6 md:p-8 border border-slate-200/90 shadow-2xs relative glass-specular-top">
        <div class="border-b border-slate-200 pb-4 mb-6">
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 font-display">Student Registration</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Fill in student details to compute fee tax ledger and issue PDF invoice.</p>
        </div>

        <div id="alertError" class="hidden mb-6 p-4 rounded-xl alert alert-danger text-xs font-semibold"></div>

        <form id="admissionForm" class="space-y-4" enctype="multipart/form-data">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Target Institute Selector -->
                <div>
                    <label for="institute_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Target Institute <span class="text-rose-500">*</span></label>
                    <select name="institute_id" id="institute_id" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" required onchange="triggerReCalc()">
                        <?php
                            try {
                                $institutes = \App\Models\Institute::all();
                            } catch (\Throwable $e) {
                                $institutes = collect();
                            }
                        ?>
                        <?php $__empty_1 = true; $__currentLoopData = $institutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <option value="<?php echo e($inst->id); ?>" 
                                    data-filer-rate="<?php echo e($inst->settings['filer_tax_rate'] ?? 0.05); ?>"
                                    data-non-filer-rate="<?php echo e($inst->settings['non_filer_tax_rate'] ?? 0.15); ?>">
                                <?php echo e($inst->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <option value="" disabled selected>No institutes found.</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Passport Picture -->
                <div>
                    <label for="passport_picture" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Passport Picture</label>
                    <input type="file" name="passport_picture" id="passport_picture" accept="image/*" class="w-full p-2.5 rounded-xl text-xs bg-slate-50 border border-slate-300 shadow-2xs text-slate-700">
                </div>
            </div>

            <!-- Personal Details Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">First Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="first_name" id="first_name" placeholder="John" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" required>
                </div>

                <div>
                    <label for="last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Last Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="last_name" id="last_name" placeholder="Doe" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="email" placeholder="student@example.com" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" required>
                </div>
                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number</label>
                    <input type="text" name="phone" id="phone" placeholder="+923001234567" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500">
                </div>
            </div>

            <!-- IDs and Address -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="student_bform_cnic" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Student B-Form / CNIC</label>
                    <input type="text" name="student_bform_cnic" id="student_bform_cnic" placeholder="12345-1234567-1" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500">
                </div>
                <div>
                    <label for="father_guardian_cnic" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Father/Guardian CNIC</label>
                    <input type="text" name="father_guardian_cnic" id="father_guardian_cnic" placeholder="12345-1234567-1" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500">
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="father_guardian_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Father/Guardian Name</label>
                    <input type="text" name="father_guardian_name" id="father_guardian_name" placeholder="John Doe Sr." class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500">
                </div>
                <div>
                    <label for="address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Address</label>
                    <textarea name="address" id="address" rows="1" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" placeholder="123 Education St..."></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="date_of_birth" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Date of Birth <span class="text-rose-500">*</span></label>
                    <input type="date" name="date_of_birth" id="date_of_birth" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" required>
                </div>
                <div>
                    <label for="blood_group" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Blood Group</label>
                    <select name="blood_group" id="blood_group" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500">
                        <option value="">Unknown</option>
                        <option value="A+">A+</option><option value="A-">A-</option>
                        <option value="B+">B+</option><option value="B-">B-</option>
                        <option value="O+">O+</option><option value="O-">O-</option>
                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                    </select>
                </div>
                <div>
                    <label for="previous_marks" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Previous Marks (%) <span class="text-rose-500">*</span></label>
                    <input type="number" name="previous_marks" id="previous_marks" step="0.01" min="0" max="100" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" placeholder="85.50" required>
                </div>
            </div>

            <!-- Academic & Fees -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-slate-200 pt-4">
                <div>
                    <label for="enrolled_program" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Enrolled Program (Grade/Class)</label>
                    <input type="text" name="enrolled_program" id="enrolled_program" placeholder="Grade 10 / BSCS Sem 3" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500">
                </div>
                <div>
                    <label for="base_fee" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Base Fee (PKR) <span class="text-rose-500">*</span></label>
                    <input type="number" name="base_fee" id="base_fee" value="50000" min="0" step="500" class="w-full p-3 rounded-xl text-sm font-bold text-pink-700 bg-white border border-slate-300 shadow-2xs outline-none focus:border-pink-500" required oninput="triggerReCalc()">
                </div>
            </div>

            <!-- TAX FILER TOGGLE -->
            <div class="mt-4 p-4 rounded-xl border border-pink-200 bg-pink-50/40">
                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Guardian Tax Filer Status (Affects Withholding Tax)</label>
                <div class="flex items-center gap-6">
                    <label class="cursor-pointer flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="radio" name="guardian_tax_status" value="filer" onchange="triggerReCalc()" class="accent-pink-600 w-4 h-4">
                        <span>Filer (5% Tax)</span>
                    </label>
                    <label class="cursor-pointer flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="radio" name="guardian_tax_status" value="non-filer" onchange="triggerReCalc()" class="accent-pink-600 w-4 h-4" checked>
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
            
            <div class="liquid-glass-card p-6 overflow-hidden relative border border-slate-200/90 shadow-2xs">
                <h3 class="font-extrabold text-slate-900 text-lg mb-1 font-display flex items-center gap-2">
                    <i class="fa-solid fa-calculator text-pink-600"></i> Live Fee Ledger
                </h3>
                <p class="text-xs text-slate-500 font-medium mb-6 border-b border-slate-200 pb-3">Real-time tax computation based on FBR Filer status.</p>

                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Base Admission Fee</span>
                        <span class="text-slate-900 font-bold">PKR <span id="previewBaseFee">50,000.00</span></span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Tax Rate Applied</span>
                        <span class="badge badge-amber text-xs font-bold" id="previewTaxPill">Non-Filer 15%</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Tax Amount</span>
                        <span class="text-rose-700 font-bold">+ PKR <span id="previewTaxAmount">7,500.00</span></span>
                    </div>

                    <div class="pt-4 border-t-2 border-slate-200 flex justify-between items-center">
                        <span class="text-slate-900 font-extrabold uppercase tracking-wider text-xs">Grand Total</span>
                        <span class="text-xl font-extrabold text-pink-700 font-display">PKR <span id="previewTotal">57,500.00</span></span>
                    </div>
                </div>
            </div>

            <!-- SUCCESS PANEL -->
            <div id="successPanel" class="hidden mt-6 liquid-glass-card p-6 border border-emerald-300 bg-emerald-50/70 shadow-2xs transition-all">
                <h3 class="text-emerald-900 font-extrabold mb-2 flex items-center gap-2 font-display text-base">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i> Admission Complete
                </h3>
                <p class="text-xs text-emerald-800 font-medium mb-4" id="successMsg">Student enrolled successfully.</p>
                
                <div class="bg-white p-3.5 rounded-xl border border-emerald-200 mb-4 space-y-1.5 shadow-2xs text-xs">
                    <div class="flex justify-between"><span class="text-slate-600 font-medium">Login Email:</span><span class="text-pink-700 font-bold font-mono" id="succLoginId">--</span></div>
                    <div class="flex justify-between"><span class="text-slate-600 font-medium">Password:</span><span class="text-amber-700 font-bold font-mono" id="succPassword">--</span></div>
                </div>

                <a id="btnDownloadInvoice" href="#" target="_blank" class="block w-full text-center py-3 rounded-xl btn-primary text-xs font-bold shadow-sm">
                    <i class="fa-solid fa-file-pdf mr-1"></i> Download PDF Invoice
                </a>
            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\admissions.blade.php ENDPATH**/ ?>