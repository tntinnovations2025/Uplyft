@extends('layouts.app')

@section('title', 'Teacher Onboarding Portal')
@section('page-header', 'Teacher Onboarding & Document Verification')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- ONBOARDING FORM CARD -->
    <div class="liquid-glass-card p-6 md:p-8 border border-slate-200/90 shadow-sm relative overflow-hidden glass-specular-top">
        <div class="border-b border-slate-200 pb-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 font-display">Faculty Onboarding Registration</h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Register new teachers and upload academic transcripts under tenant storage isolation.</p>
            </div>
            <span class="badge badge-pink text-xs font-bold">
                <i class="fa-solid fa-file-shield"></i> Max File 5MB
            </span>
        </div>

        <!-- ALERTS -->
        <div id="alertSuccess" class="hidden mb-6 p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs space-y-3 shadow-2xs">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl text-emerald-600 shrink-0"></i>
                <div>
                    <p class="font-extrabold text-emerald-900 text-sm font-display">Onboarding Complete!</p>
                    <p class="text-xs text-emerald-700 font-medium" id="succText">Teacher record and transcript files saved.</p>
                </div>
            </div>
            <div class="bg-white rounded-xl p-3.5 border border-emerald-200/80 space-y-2 text-xs">
                <p class="uppercase text-slate-500 font-extrabold tracking-wider text-[10px] mb-1">Generated Login Credentials</p>
                <div class="flex justify-between"><span class="text-slate-600 font-medium">Employee ID:</span><span class="font-bold text-indigo-700 font-mono" id="succEmpId">--</span></div>
                <div class="flex justify-between"><span class="text-slate-600 font-medium">Default Password:</span><span class="font-bold text-amber-700 font-mono" id="succTeacherPwd">--</span></div>
                <div class="flex justify-between items-center"><span class="text-slate-600 font-medium">Portal:</span>
                    <a href="/login" class="text-pink-600 hover:text-pink-700 font-bold underline text-xs">Teacher Portal →</a>
                </div>
            </div>
        </div>

        <div id="alertError" class="hidden mb-6 p-4 rounded-xl alert alert-danger text-xs font-semibold"></div>

        <form id="teacherForm" enctype="multipart/form-data" class="space-y-5">
            <!-- Personal Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">First Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="first_name" id="first_name" placeholder="Sarah" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" required>
                </div>

                <div>
                    <label for="last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Last Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="last_name" id="last_name" placeholder="Connor" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Official Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="email" placeholder="sarah.connor@uplyft.edu" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" required>
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number (Optional)</label>
                    <input type="text" name="phone" id="phone" placeholder="+923009876543" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500">
                </div>
            </div>

            <!-- Academic & Experience Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="qualification" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Highest Qualification <span class="text-rose-500">*</span></label>
                    <select name="qualification" id="qualification" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" required>
                        <option value="" disabled selected>Select Qualification</option>
                        <option value="Bachelors">Bachelors Degree</option>
                        <option value="Masters">Masters Degree</option>
                        <option value="PhD">Doctorate (PhD)</option>
                    </select>
                </div>
                <div>
                    <label for="years_of_experience" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Years of Experience</label>
                    <input type="number" name="years_of_experience" id="years_of_experience" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" placeholder="e.g. 5">
                </div>
            </div>

            <!-- Subject & Contact -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="specialization_subjects" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Specialization Subjects</label>
                    <input type="text" name="specialization_subjects" id="specialization_subjects" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" placeholder="Physics, Mathematics">
                </div>
                <div>
                    <label for="emergency_contact_phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Emergency Contact Phone</label>
                    <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" class="w-full p-3 rounded-xl text-sm bg-white text-slate-900 border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500" placeholder="+923001112233">
                </div>
            </div>

            <!-- Salary -->
            <div>
                <label for="basic_salary_pkr" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Basic Salary (PKR)</label>
                <input type="number" name="basic_salary_pkr" id="basic_salary_pkr" step="500" class="w-full p-3 rounded-xl text-sm font-bold text-pink-700 bg-white border border-slate-300 shadow-2xs outline-none focus:border-pink-500" placeholder="75000">
            </div>

            <!-- Styled File Upload Boxes -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Academic Transcripts &amp; Certificates</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <!-- Matriculation -->
                    <div>
                        <span class="block text-[11px] font-bold text-slate-600 mb-1">Matriculation <span class="text-rose-500">*</span></span>
                        <div class="relative border-2 border-dashed border-slate-300 hover:border-pink-500 rounded-xl p-4 text-center transition bg-slate-50/70 hover:bg-pink-50/30">
                            <input type="file" name="matriculation_cert" id="matriculation_cert" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this, 'matDisplay')" required>
                            <div class="pointer-events-none">
                                <i class="fa-solid fa-file-arrow-up text-pink-500 mb-1.5 text-base"></i>
                                <p class="text-xs font-bold text-slate-700" id="matDisplay">Upload File</p>
                            </div>
                        </div>
                    </div>

                    <!-- Intermediate -->
                    <div>
                        <span class="block text-[11px] font-bold text-slate-600 mb-1">Intermediate <span class="text-rose-500">*</span></span>
                        <div class="relative border-2 border-dashed border-slate-300 hover:border-pink-500 rounded-xl p-4 text-center transition bg-slate-50/70 hover:bg-pink-50/30">
                            <input type="file" name="intermediate_cert" id="intermediate_cert" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this, 'intDisplay')" required>
                            <div class="pointer-events-none">
                                <i class="fa-solid fa-file-arrow-up text-pink-500 mb-1.5 text-base"></i>
                                <p class="text-xs font-bold text-slate-700" id="intDisplay">Upload File</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bachelors -->
                    <div>
                        <span class="block text-[11px] font-bold text-slate-600 mb-1">Bachelors <span class="text-rose-500">*</span></span>
                        <div class="relative border-2 border-dashed border-slate-300 hover:border-pink-500 rounded-xl p-4 text-center transition bg-slate-50/70 hover:bg-pink-50/30">
                            <input type="file" name="bachelors_cert" id="bachelors_cert" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this, 'bachDisplay')" required>
                            <div class="pointer-events-none">
                                <i class="fa-solid fa-file-arrow-up text-pink-500 mb-1.5 text-base"></i>
                                <p class="text-xs font-bold text-slate-700" id="bachDisplay">Upload File</p>
                            </div>
                        </div>
                    </div>

                    <!-- Masters -->
                    <div>
                        <span class="block text-[11px] font-bold text-slate-600 mb-1">Masters (Optional)</span>
                        <div class="relative border-2 border-dashed border-slate-300 hover:border-pink-500 rounded-xl p-4 text-center transition bg-slate-50/70 hover:bg-pink-50/30">
                            <input type="file" name="masters_cert" id="masters_cert" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this, 'mastDisplay')">
                            <div class="pointer-events-none">
                                <i class="fa-solid fa-file-arrow-up text-pink-500 mb-1.5 text-base"></i>
                                <p class="text-xs font-bold text-slate-700" id="mastDisplay">Upload File</p>
                            </div>
                        </div>
                    </div>

                    <!-- PhD -->
                    <div>
                        <span class="block text-[11px] font-bold text-slate-600 mb-1">PhD (Optional)</span>
                        <div class="relative border-2 border-dashed border-slate-300 hover:border-pink-500 rounded-xl p-4 text-center transition bg-slate-50/70 hover:bg-pink-50/30">
                            <input type="file" name="phd_cert" id="phd_cert" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFileName(this, 'phdDisplay')">
                            <div class="pointer-events-none">
                                <i class="fa-solid fa-file-arrow-up text-pink-500 mb-1.5 text-base"></i>
                                <p class="text-xs font-bold text-slate-700" id="phdDisplay">Upload File</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <button type="button" onclick="submitTeacherOnboarding()" id="btnSubmitTeacher" class="w-full btn-primary py-3.5 px-4 text-sm font-bold flex items-center justify-center gap-2 shadow-md">
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
            const name = file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name;
            display.textContent = `${name} (${sizeMb} MB)`;
            display.classList.add('text-pink-600');
        } else {
            display.textContent = 'Upload File';
            display.classList.remove('text-pink-600');
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
        if (document.getElementById('years_of_experience').value) {
            formData.append('years_of_experience', document.getElementById('years_of_experience').value);
        }
        if (document.getElementById('specialization_subjects').value) {
            formData.append('specialization_subjects', document.getElementById('specialization_subjects').value);
        }
        if (document.getElementById('emergency_contact_phone').value) {
            formData.append('emergency_contact_phone', document.getElementById('emergency_contact_phone').value);
        }
        if (document.getElementById('basic_salary_pkr').value) {
            formData.append('basic_salary_pkr', document.getElementById('basic_salary_pkr').value);
        }
        
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
                el.classList.remove('text-pink-600');
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
