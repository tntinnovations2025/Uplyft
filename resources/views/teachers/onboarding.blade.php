@extends('layouts.app')

@section('title', 'Teacher Onboarding Portal')
@section('page-header', 'Teacher Onboarding & Document Verification')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- ONBOARDING FORM CARD -->
    <div class="glass-panel p-6">
        <div class="border-b border-white/10 pb-4 mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">Faculty Onboarding Registration</h2>
                <p class="text-xs text-slate-400">Register new teachers and upload academic transcripts under tenant storage isolation.</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-cyan-500/10 text-cyan-300 text-xs font-semibold border border-cyan-500/20">
                <i class="fa-solid fa-file-shield mr-1"></i> Max File 5MB
            </span>
        </div>

        <!-- ALERTS -->
        <div id="alertSuccess" class="hidden mb-4 p-5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs space-y-3">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-lg text-emerald-400 shrink-0"></i>
                <div>
                    <p class="font-bold text-emerald-200 text-sm">Onboarding Complete!</p>
                    <p class="text-[11px] text-slate-300" id="succText">Teacher record and transcript files saved.</p>
                </div>
            </div>
            <div class="bg-slate-900/60 rounded-xl p-3 border border-white/5 space-y-2 text-[11px]">
                <p class="uppercase text-slate-500 font-semibold tracking-wide text-[10px] mb-1">Generated Login Credentials</p>
                <div class="flex justify-between"><span class="text-slate-400">Employee ID:</span><span class="font-bold text-indigo-300" id="succEmpId">--</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Default Password:</span><span class="font-bold text-amber-300" id="succTeacherPwd">--</span></div>
                <div class="flex justify-between items-center"><span class="text-slate-400">Portal:</span>
                    <a href="/login" class="text-cyan-400 underline text-[10px]">Teacher Portal →</a>
                </div>
            </div>
        </div>

        <div id="alertError" class="hidden mb-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 text-xs"></div>

        <form id="teacherForm" enctype="multipart/form-data" class="space-y-5">
            <!-- Personal Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-xs font-medium text-slate-300 mb-1">First Name</label>
                    <input type="text" name="first_name" id="first_name" placeholder="Sarah" class="w-full glass-input p-3 rounded-xl text-sm" required>
                </div>

                <div>
                    <label for="last_name" class="block text-xs font-medium text-slate-300 mb-1">Last Name</label>
                    <input type="text" name="last_name" id="last_name" placeholder="Connor" class="w-full glass-input p-3 rounded-xl text-sm" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-1">Official Email Address</label>
                    <input type="email" name="email" id="email" placeholder="sarah.connor@uplyft.edu" class="w-full glass-input p-3 rounded-xl text-sm" required>
                </div>

                <div>
                    <label for="phone" class="block text-xs font-medium text-slate-300 mb-1">Phone Number (Optional)</label>
                    <input type="text" name="phone" id="phone" placeholder="+923009876543" class="w-full glass-input p-3 rounded-xl text-sm">
                </div>
            </div>

            <!-- Academic & Experience Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="qualification" class="block text-xs font-medium text-slate-300 mb-1">Highest Qualification</label>
                    <select name="qualification" id="qualification" class="w-full glass-input p-3 rounded-xl text-sm" required>
                        <option value="" disabled selected>Select Qualification</option>
                        <option value="Bachelors">Bachelors Degree</option>
                        <option value="Masters">Masters Degree</option>
                        <option value="PhD">Doctorate (PhD)</option>
                    </select>
                </div>
                <div>
                    <label for="years_of_experience" class="block text-xs font-medium text-slate-300 mb-1">Years of Experience</label>
                    <input type="number" name="years_of_experience" id="years_of_experience" class="w-full glass-input p-3 rounded-xl text-sm" placeholder="e.g. 5">
                </div>
            </div>

            <!-- Subject & Contact -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="specialization_subjects" class="block text-xs font-medium text-slate-300 mb-1">Specialization Subjects</label>
                    <input type="text" name="specialization_subjects" id="specialization_subjects" class="w-full glass-input p-3 rounded-xl text-sm" placeholder="Physics, Mathematics">
                </div>
                <div>
                    <label for="emergency_contact_phone" class="block text-xs font-medium text-slate-300 mb-1">Emergency Contact Phone</label>
                    <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" class="w-full glass-input p-3 rounded-xl text-sm" placeholder="+923001112233">
                </div>
            </div>

            <!-- Salary -->
            <div>
                <label for="basic_salary_pkr" class="block text-xs font-medium text-cyan-400 mb-1">Basic Salary (PKR)</label>
                <input type="number" name="basic_salary_pkr" id="basic_salary_pkr" step="500" class="w-full glass-input p-3 rounded-xl text-sm font-bold text-cyan-300" placeholder="75000">
            </div>

            <!-- Styled File Upload Boxes -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                
                <!-- Matriculation -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Matriculation (Required)</label>
                    <div class="relative border-2 border-dashed border-slate-700 hover:border-cyan-500/60 rounded-xl p-4 text-center transition bg-slate-900/40">
                        <input type="file" name="matriculation_cert" id="matriculation_cert" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this, 'matDisplay')" required>
                        <div class="pointer-events-none">
                            <i class="fa-solid fa-file-arrow-up text-cyan-400 mb-2"></i>
                            <p class="text-[10px] font-semibold text-white" id="matDisplay">Upload File</p>
                        </div>
                    </div>
                </div>

                <!-- Intermediate -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Intermediate (Required)</label>
                    <div class="relative border-2 border-dashed border-slate-700 hover:border-cyan-500/60 rounded-xl p-4 text-center transition bg-slate-900/40">
                        <input type="file" name="intermediate_cert" id="intermediate_cert" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this, 'intDisplay')" required>
                        <div class="pointer-events-none">
                            <i class="fa-solid fa-file-arrow-up text-cyan-400 mb-2"></i>
                            <p class="text-[10px] font-semibold text-white" id="intDisplay">Upload File</p>
                        </div>
                    </div>
                </div>

                <!-- Bachelors -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Bachelors (Required)</label>
                    <div class="relative border-2 border-dashed border-slate-700 hover:border-cyan-500/60 rounded-xl p-4 text-center transition bg-slate-900/40">
                        <input type="file" name="bachelors_cert" id="bachelors_cert" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this, 'bachDisplay')" required>
                        <div class="pointer-events-none">
                            <i class="fa-solid fa-file-arrow-up text-cyan-400 mb-2"></i>
                            <p class="text-[10px] font-semibold text-white" id="bachDisplay">Upload File</p>
                        </div>
                    </div>
                </div>

                <!-- Masters -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Masters (Optional)</label>
                    <div class="relative border-2 border-dashed border-slate-700 hover:border-cyan-500/60 rounded-xl p-4 text-center transition bg-slate-900/40">
                        <input type="file" name="masters_cert" id="masters_cert" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this, 'mastDisplay')">
                        <div class="pointer-events-none">
                            <i class="fa-solid fa-file-arrow-up text-cyan-400 mb-2"></i>
                            <p class="text-[10px] font-semibold text-white" id="mastDisplay">Upload File</p>
                        </div>
                    </div>
                </div>

                <!-- PhD -->
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">PhD (Optional)</label>
                    <div class="relative border-2 border-dashed border-slate-700 hover:border-cyan-500/60 rounded-xl p-4 text-center transition bg-slate-900/40">
                        <input type="file" name="phd_cert" id="phd_cert" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this, 'phdDisplay')">
                        <div class="pointer-events-none">
                            <i class="fa-solid fa-file-arrow-up text-cyan-400 mb-2"></i>
                            <p class="text-[10px] font-semibold text-white" id="phdDisplay">Upload File</p>
                        </div>
                    </div>
                </div>

            </div>

            <button type="button" onclick="submitTeacherOnboarding()" id="btnSubmitTeacher" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-cyan-600 to-cyan-700 hover:from-cyan-500 hover:to-cyan-600 text-white font-semibold text-sm shadow-lg shadow-cyan-600/30 flex items-center justify-center gap-2 transition">
                <i class="fa-solid fa-user-check"></i>
                <span>Complete Faculty Onboarding</span>
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function previewFileName(input, displayId) {
        const display = document.getElementById(displayId);
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            // Truncate name if too long
            const name = file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name;
            display.textContent = `${name} (${sizeMb} MB)`;
            display.classList.add('text-cyan-400');
        } else {
            display.textContent = 'Upload File';
            display.classList.remove('text-cyan-400');
        }
    }

    async function submitTeacherOnboarding() {
        const form = document.getElementById('teacherForm');
        const alertSucc = document.getElementById('alertSuccess');
        const alertErr = document.getElementById('alertError');
        const btn = document.getElementById('btnSubmitTeacher');

        alertSucc.classList.add('hidden');
        alertErr.classList.add('hidden');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const maxBytes = 5 * 1024 * 1024;
        const fileInputs = [
            document.getElementById('matriculation_cert'),
            document.getElementById('intermediate_cert'),
            document.getElementById('bachelors_cert'),
            document.getElementById('masters_cert'),
            document.getElementById('phd_cert')
        ];

        for (const fileInput of fileInputs) {
            if (fileInput.files.length > 0 && fileInput.files[0].size > maxBytes) {
                alertErr.textContent = 'One of the files exceeds 5MB. Please choose smaller documents.';
                alertErr.classList.remove('hidden');
                return;
            }
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner animate-spin"></i> Uploading Documents & Registering...`;

        const formData = new FormData();
        formData.append('first_name', document.getElementById('first_name').value);
        formData.append('last_name', document.getElementById('last_name').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('phone', document.getElementById('phone').value);
        formData.append('qualification', document.getElementById('qualification').value);
        
        if(fileInputs[0].files[0]) formData.append('matriculation_cert', fileInputs[0].files[0]);
        if(fileInputs[1].files[0]) formData.append('intermediate_cert', fileInputs[1].files[0]);
        if(fileInputs[2].files[0]) formData.append('bachelors_cert', fileInputs[2].files[0]);
        if(fileInputs[3].files[0]) formData.append('masters_cert', fileInputs[3].files[0]);
        if(fileInputs[4].files[0]) formData.append('phd_cert', fileInputs[4].files[0]);

        try {
            const response = await fetch('/api/teachers/onboarding', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const resData = await response.json();

            if (!response.ok) {
                let msg = resData.message || 'Onboarding failed.';
                if (resData.errors) {
                    msg = Object.values(resData.errors).flat().join('<br>');
                }
                alertErr.innerHTML = msg;
                alertErr.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-user-check"></i> Complete Faculty Onboarding`;
                return;
            }

            // Success feedback
            document.getElementById('succText').textContent = `${resData.data.first_name} ${resData.data.last_name} registered. Transcript files stored securely.`;
            document.getElementById('succEmpId').textContent     = resData.credentials.employee_id;
            document.getElementById('succTeacherPwd').textContent = resData.credentials.password;
            alertSucc.classList.remove('hidden');

            form.reset();
            const displays = ['matDisplay', 'intDisplay', 'bachDisplay', 'mastDisplay', 'phdDisplay'];
            displays.forEach(id => {
                const el = document.getElementById(id);
                el.textContent = 'Upload File';
                el.classList.remove('text-cyan-400');
            });

            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-user-check"></i> Complete Faculty Onboarding`;

        } catch (err) {
            alertErr.textContent = 'Server connection error: ' + err.message;
            alertErr.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-user-check"></i> Complete Faculty Onboarding`;
        }
    }
</script>
@endsection
