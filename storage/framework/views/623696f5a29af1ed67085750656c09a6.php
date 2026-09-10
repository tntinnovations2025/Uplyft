<?php $__env->startSection('title', 'Attendance Management'); ?>
<?php $__env->startSection('page-header', 'Class Roster & Attendance Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">

    <!-- FILTER CONTROL BAR -->
    <div class="liquid-glass-card p-5 flex flex-col md:flex-row items-center justify-between gap-4 border border-slate-200/90 shadow-2xs glass-specular-top">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 border border-pink-200 flex items-center justify-center text-lg shadow-2xs">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 text-base font-display">Academic Term Roster</h3>
                <p class="text-xs text-slate-500 font-medium">Select term and date to mark or update student attendance.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Academic Term Selector -->
            <div>
                <select id="academic_term_id" class="p-2.5 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-800 shadow-2xs outline-none focus:border-pink-500" onchange="loadRoster()">
                    <option value="101" selected>Term 101 - Fall 2026 (Active)</option>
                    <option value="102">Term 102 - Spring 2027</option>
                </select>
            </div>

            <!-- Date Picker -->
            <div>
                <input type="date" id="attendance_date" class="p-2.5 rounded-xl text-xs font-semibold bg-white border border-slate-300 text-slate-800 shadow-2xs outline-none focus:border-pink-500" value="<?php echo e(date('Y-m-d')); ?>" onchange="loadRoster()">
            </div>

            <button type="button" onclick="loadRoster()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition border border-slate-300 flex items-center gap-2 shadow-2xs">
                <i class="fa-solid fa-arrows-rotate text-pink-600"></i>
                <span>Refresh Roster</span>
            </button>
        </div>
    </div>

    <!-- ALERTS -->
    <div id="alertSuccess" class="hidden p-4 rounded-xl alert-success text-xs flex items-center gap-3 shadow-2xs">
        <i class="fa-solid fa-circle-check text-lg text-emerald-600"></i>
        <span id="succMessage" class="font-bold">Attendance records updated successfully.</span>
    </div>

    <div id="alertError" class="hidden p-4 rounded-xl alert-error text-xs font-bold shadow-2xs"></div>

    <!-- ROSTER TABLE PANEL -->
    <div class="liquid-glass-card p-6 space-y-4 border border-slate-200/90 shadow-2xs">
        <div class="flex items-center justify-between border-b border-slate-200 pb-4">
            <div>
                <h4 class="font-extrabold text-slate-900 text-sm font-display">Class Roster List</h4>
                <p class="text-xs text-slate-500 font-medium" id="rosterStats">Loading roster data...</p>
            </div>

            <button type="button" onclick="submitAttendance()" id="btnSubmitAttendance" class="btn-primary px-5 py-2.5 text-xs font-extrabold shadow-md flex items-center gap-2">
                <i class="fa-solid fa-check-double"></i>
                <span>Save Attendance Roster</span>
            </button>
        </div>

        <!-- TABLE CONTAINER -->
        <div class="overflow-x-auto rounded-xl border border-slate-200/80">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="text-[11px] uppercase bg-slate-50 text-slate-700 font-extrabold border-b border-slate-200 tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Student Name</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4">Phone</th>
                        <th class="py-3.5 px-4 text-center">Attendance Status</th>
                    </tr>
                </thead>
                <tbody id="rosterTableBody" class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-400 font-medium">
                            <i class="fa-solid fa-spinner animate-spin mr-2"></i> Fetching roster...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    let currentRoster = [];

    document.addEventListener('DOMContentLoaded', () => {
        loadRoster();
    });

    async function loadRoster() {
        const termId = document.getElementById('academic_term_id').value;
        const date = document.getElementById('attendance_date').value;
        const tbody = document.getElementById('rosterTableBody');
        const stats = document.getElementById('rosterStats');

        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="py-8 text-center text-slate-400 font-medium">
                    <i class="fa-solid fa-spinner animate-spin mr-2"></i> Loading student roster for ${date}...
                </td>
            </tr>`;

        try {
            const response = await fetch(`/api/attendance/roster?academic_term_id=${termId}&date=${date}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            currentRoster = data.roster || [];
            stats.textContent = `Term ID: ${termId} | Date: ${date} | Total Students: ${currentRoster.length}`;

            if (currentRoster.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-500 font-medium">
                            No students registered for this tenant. <a href="/admissions" class="text-pink-600 font-bold underline ml-1">Register a student first</a>.
                        </td>
                    </tr>`;
                return;
            }

            renderRosterRows();

        } catch (err) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="py-8 text-center text-rose-600 font-bold">
                        Failed to load roster: ${err.message}
                    </td>
                </tr>`;
        }
    }

    function renderRosterRows() {
        const tbody = document.getElementById('rosterTableBody');
        tbody.innerHTML = '';

        currentRoster.forEach(student => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition';

            const status = student.status || 'present';

            tr.innerHTML = `
                <td class="py-3.5 px-4 font-bold text-slate-900">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-pink-100 text-pink-700 border border-pink-200 flex items-center justify-center font-bold text-xs">
                            ${student.full_name.charAt(0)}
                        </div>
                        <span>${student.full_name}</span>
                    </div>
                </td>
                <td class="py-3.5 px-4 text-slate-600 font-medium">${student.email}</td>
                <td class="py-3.5 px-4 text-slate-500 font-medium">${student.phone || 'N/A'}</td>
                <td class="py-3.5 px-4">
                    <div class="flex items-center justify-center gap-1.5" data-student-id="${student.student_id}">
                        <button type="button" onclick="setStatus(${student.student_id}, 'present')" 
                                class="btn-status px-3 py-1 rounded-lg text-xs font-bold border transition ${status === 'present' ? 'bg-emerald-600 text-white border-emerald-600 shadow-2xs' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200'}">
                            Present
                        </button>
                        <button type="button" onclick="setStatus(${student.student_id}, 'absent')" 
                                class="btn-status px-3 py-1 rounded-lg text-xs font-bold border transition ${status === 'absent' ? 'bg-rose-600 text-white border-rose-600 shadow-2xs' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200'}">
                            Absent
                        </button>
                        <button type="button" onclick="setStatus(${student.student_id}, 'late')" 
                                class="btn-status px-3 py-1 rounded-lg text-xs font-bold border transition ${status === 'late' ? 'bg-amber-500 text-white border-amber-500 shadow-2xs' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200'}">
                            Late
                        </button>
                        <button type="button" onclick="setStatus(${student.student_id}, 'leave')" 
                                class="btn-status px-3 py-1 rounded-lg text-xs font-bold border transition ${status === 'leave' ? 'bg-cyan-600 text-white border-cyan-600 shadow-2xs' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200'}">
                            Leave
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function setStatus(studentId, newStatus) {
        const student = currentRoster.find(s => s.student_id === studentId);
        if (student) {
            student.status = newStatus;
            renderRosterRows();
        }
    }

    async function submitAttendance() {
        const termId = document.getElementById('academic_term_id').value;
        const date = document.getElementById('attendance_date').value;
        const alertSucc = document.getElementById('alertSuccess');
        const alertErr = document.getElementById('alertError');
        const btn = document.getElementById('btnSubmitAttendance');

        alertSucc.classList.add('hidden');
        alertErr.classList.add('hidden');

        if (currentRoster.length === 0) {
            alertErr.textContent = 'No students available in roster to mark.';
            alertErr.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner animate-spin"></i> Saving Roster...`;

        const payload = {
            academic_term_id: parseInt(termId),
            date: date,
            attendances: currentRoster.map(s => ({
                student_id: s.student_id,
                status: s.status || 'present'
            }))
        };

        try {
            const response = await fetch('/api/attendance', {
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
                alertErr.textContent = resData.message || 'Failed to submit attendance.';
                alertErr.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-check-double"></i> Save Attendance Roster`;
                return;
            }

            document.getElementById('succMessage').textContent = `Attendance updated for ${resData.processed} student(s) on ${date}.`;
            alertSucc.classList.remove('hidden');

            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-check-double"></i> Save Attendance Roster`;

        } catch (err) {
            alertErr.textContent = 'Server error: ' + err.message;
            alertErr.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = `<i class="fa-solid fa-check-double"></i> Save Attendance Roster`;
        }
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\UPLYFT\uplifyt\resources\views\attendance.blade.php ENDPATH**/ ?>