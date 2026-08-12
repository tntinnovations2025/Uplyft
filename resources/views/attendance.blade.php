@extends('layouts.app')

@section('title', 'Attendance Management')
@section('page-header', 'Class Roster & Attendance Management')

@section('content')
<div class="space-y-6">

    <!-- FILTER CONTROL BAR -->
    <div class="glass-panel p-5 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-base">Academic Term Roster</h3>
                <p class="text-xs text-slate-400">Select term and date to mark or update student attendance.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Academic Term Selector (Developer A Domain Reference) -->
            <div>
                <select id="academic_term_id" class="glass-input p-2.5 rounded-xl text-xs" onchange="loadRoster()">
                    <option value="101" selected>Term 101 - Fall 2026 (Active)</option>
                    <option value="102">Term 102 - Spring 2027</option>
                </select>
            </div>

            <!-- Date Picker -->
            <div>
                <input type="date" id="attendance_date" class="glass-input p-2 rounded-xl text-xs" value="{{ date('Y-m-d') }}" onchange="loadRoster()">
            </div>

            <button type="button" onclick="loadRoster()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-medium transition border border-white/10 flex items-center gap-2">
                <i class="fa-solid fa-arrows-rotate"></i>
                <span>Refresh Roster</span>
            </button>
        </div>
    </div>

    <!-- ALERTS -->
    <div id="alertSuccess" class="hidden p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs flex items-center gap-3">
        <i class="fa-solid fa-circle-check text-lg text-emerald-400"></i>
        <span id="succMessage">Attendance records updated successfully.</span>
    </div>

    <div id="alertError" class="hidden p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 text-xs"></div>

    <!-- ROSTER TABLE PANEL -->
    <div class="glass-panel p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div>
                <h4 class="font-bold text-white text-sm">Class Roster List</h4>
                <p class="text-xs text-slate-400" id="rosterStats">Loading roster data...</p>
            </div>

            <button type="button" onclick="submitAttendance()" id="btnSubmitAttendance" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition">
                <i class="fa-solid fa-check-double"></i>
                <span>Save Attendance Roster</span>
            </button>
        </div>

        <!-- TABLE CONTAINER -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[11px] uppercase bg-slate-900/60 text-slate-400 border-b border-white/5">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Student Name</th>
                        <th class="py-3.5 px-4 font-semibold">Email</th>
                        <th class="py-3.5 px-4 font-semibold">Phone</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Attendance Status</th>
                    </tr>
                </thead>
                <tbody id="rosterTableBody" class="divide-y divide-white/5">
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-400">
                            <i class="fa-solid fa-spinner animate-spin mr-2"></i> Fetching roster...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
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
                <td colspan="4" class="py-8 text-center text-slate-400">
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
                        <td colspan="4" class="py-8 text-center text-slate-400">
                            No students registered for this tenant. <a href="/admissions" class="text-indigo-400 underline ml-1">Register a student first</a>.
                        </td>
                    </tr>`;
                return;
            }

            renderRosterRows();

        } catch (err) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="py-8 text-center text-red-400">
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
            tr.className = 'hover:bg-white/[0.02] transition';

            const status = student.status || 'present';

            tr.innerHTML = `
                <td class="py-3.5 px-4 font-semibold text-white">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-indigo-500/20 text-indigo-300 flex items-center justify-center font-bold text-xs">
                            ${student.full_name.charAt(0)}
                        </div>
                        <span>${student.full_name}</span>
                    </div>
                </td>
                <td class="py-3.5 px-4 text-slate-300">${student.email}</td>
                <td class="py-3.5 px-4 text-slate-400">${student.phone || 'N/A'}</td>
                <td class="py-3.5 px-4">
                    <div class="flex items-center justify-center gap-1.5" data-student-id="${student.student_id}">
                        <button type="button" onclick="setStatus(${student.student_id}, 'present')" 
                                class="btn-status px-3 py-1 rounded-lg text-xs font-semibold border transition ${status === 'present' ? 'bg-emerald-500 text-white border-emerald-400 shadow-md shadow-emerald-500/20' : 'bg-slate-900/60 text-slate-400 border-white/5 hover:text-white'}">
                            Present
                        </button>
                        <button type="button" onclick="setStatus(${student.student_id}, 'absent')" 
                                class="btn-status px-3 py-1 rounded-lg text-xs font-semibold border transition ${status === 'absent' ? 'bg-rose-500 text-white border-rose-400 shadow-md shadow-rose-500/20' : 'bg-slate-900/60 text-slate-400 border-white/5 hover:text-white'}">
                            Absent
                        </button>
                        <button type="button" onclick="setStatus(${student.student_id}, 'late')" 
                                class="btn-status px-3 py-1 rounded-lg text-xs font-semibold border transition ${status === 'late' ? 'bg-amber-500 text-white border-amber-400 shadow-md shadow-amber-500/20' : 'bg-slate-900/60 text-slate-400 border-white/5 hover:text-white'}">
                            Late
                        </button>
                        <button type="button" onclick="setStatus(${student.student_id}, 'leave')" 
                                class="btn-status px-3 py-1 rounded-lg text-xs font-semibold border transition ${status === 'leave' ? 'bg-cyan-500 text-white border-cyan-400 shadow-md shadow-cyan-500/20' : 'bg-slate-900/60 text-slate-400 border-white/5 hover:text-white'}">
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
@endsection
