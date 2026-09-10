@extends(auth()->check() && (auth()->user()->isPrincipal() || auth()->user()->isGlobalAdmin()) ? 'principal.layouts.app' : 'layouts.app')

@section('title', 'Mark Attendance')
@section('page-header', 'Interactive Roster')

@section('content')
<div class="space-y-6">

    <!-- Top Selection & Filtering Bar -->
    <div class="liquid-glass-card p-6 bg-gradient-to-r from-emerald-500/[0.06] via-teal-500/[0.04] to-indigo-500/[0.06] border border-slate-200/90 flex flex-col md:flex-row items-center justify-between gap-4 glass-specular-top">
        <div>
            <h2 class="text-xl md:text-2xl font-extrabold text-slate-900 flex items-center gap-2.5 flex-wrap font-display">
                <span>📋 Attendance Roster</span>
                @if($attendanceMode === 'daily')
                    <span class="badge badge-cyan text-xs font-bold">
                        🏷️ Daily Attendance Mode
                    </span>
                @else
                    <span class="badge badge-purple text-xs font-bold">
                        📖 Subject Wise Mode
                    </span>
                @endif

                @if($lockStatus['is_locked'])
                    <span class="badge badge-amber text-xs font-bold">
                        🔒 Locked
                    </span>
                @else
                    <span class="badge badge-emerald text-xs font-bold">
                        🟢 Active Window
                    </span>
                @endif
            </h2>
            <p class="text-xs text-slate-500 font-medium mt-1">
                @if($attendanceMode === 'daily')
                    Daily class attendance mode is active. Only designated Class Incharge teachers can log daily attendance.
                @else
                    Subject-wise attendance mode is active. Teachers mark attendance for their assigned subject sections.
                @endif
            </p>
        </div>

        <form method="GET" action="{{ route('teacher.attendance') }}" class="flex items-center gap-3 flex-wrap">
            <div>
                <label class="text-[10px] text-slate-600 font-bold uppercase block mb-1">Assigned Class Section</label>
                <select name="section_id" class="text-xs px-3 py-2.5 rounded-xl bg-white text-slate-900 border border-slate-300 shadow-2xs outline-none focus:border-pink-500 font-semibold" onchange="this.form.submit()">
                    @forelse($sections as $sec)
                        @php
                            $cName = $sec->instituteClass->custom_name ?? 'Class';
                            $sName = $sec->section_name ?? 'Section';
                            $incName = $sec->classIncharge ? $sec->classIncharge->name : 'No Incharge';
                        @endphp
                        <option value="{{ $sec->id }}" {{ (int)$selectedSectionId === (int)$sec->id ? 'selected' : '' }}>
                            {{ $cName }} — Section {{ $sName }} (Incharge: {{ $incName }})
                        </option>
                    @empty
                        <option value="">-- No Accessible Sections --</option>
                    @endforelse
                </select>
            </div>

            <div>
                <label class="text-[10px] text-slate-600 font-bold uppercase block mb-1">Date</label>
                <input type="date" name="date" value="{{ $selectedDate }}" class="text-xs px-3 py-2 rounded-xl bg-white text-slate-900 border border-slate-300 shadow-2xs outline-none focus:border-pink-500 font-semibold" onchange="this.form.submit()">
            </div>

            <div class="self-end">
                <button type="submit" class="btn-success px-4 py-2.5 text-xs font-bold shadow-xs">
                    Load Roster
                </button>
            </div>
        </form>
    </div>

    <!-- Alert Notices -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation text-base"></i>
            <span class="font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    @if(!empty($inchargeNotice))
        <div class="p-5 rounded-2xl bg-sky-50 border border-sky-200 flex items-start gap-4 shadow-2xs">
            <div class="w-10 h-10 rounded-xl bg-sky-100 border border-sky-300 text-sky-700 flex items-center justify-center text-xl shrink-0 font-bold">
                🔒
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-sky-900 font-display">Daily Attendance Mode Active</h3>
                <p class="text-xs text-sky-800 mt-1 leading-relaxed font-medium">
                    {{ $inchargeNotice }}
                </p>
            </div>
        </div>
    @endif

    @php
        $isLockedForUser = $lockStatus['is_locked'] && !$user->isPrincipal() && !$user->is_delegated_admin;
    @endphp

    <!-- Lock Warning Banner / Principal Oversight Banner -->
    @if($user->isPrincipal() || $user->is_delegated_admin)
        <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-200 flex items-center justify-between gap-4 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0">
                    👑
                </div>
                <div class="text-xs text-indigo-950 font-semibold">
                    <strong class="font-extrabold text-indigo-900">Principal Administrative Control:</strong> Full authority to view and update student attendance for any class on any date at any time.
                </div>
            </div>
            <div class="text-[11px] text-indigo-700 font-bold shrink-0">
                Date: {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}
            </div>
        </div>
    @elseif($isLockedForUser)
        <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 flex items-start gap-4 shadow-2xs">
            <div class="w-10 h-10 rounded-xl bg-amber-100 border border-amber-300 text-amber-800 flex items-center justify-center text-xl shrink-0 font-bold">
                🔒
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-amber-900 font-display">Attendance Locking Active</h3>
                <p class="text-xs text-amber-800 mt-1 leading-relaxed font-medium">
                    {{ $lockStatus['reason'] }}
                </p>
                <div class="mt-2 text-[11px] text-amber-700 font-semibold">
                    💡 <em>Contact your Institute Principal if you require a past date or after-hours attendance edit unlock.</em>
                </div>
            </div>
        </div>
    @else
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between gap-4 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                <div class="text-xs text-emerald-900 font-medium">
                    Attendance Window Open (Daily Hours: <strong class="font-bold">{{ \Carbon\Carbon::createFromFormat('H:i', $setting->start_time)->format('g:i A') }} — {{ \Carbon\Carbon::createFromFormat('H:i', $setting->end_time)->format('g:i A') }}</strong>)
                </div>
            </div>
            <div class="text-[11px] text-emerald-800 font-bold">
                Date: {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}
            </div>
        </div>
    @endif

    <!-- Empty Sections Warning -->
    @if($sections->isEmpty())
        <div class="liquid-glass-card p-12 text-center text-slate-500">
            <div class="text-4xl mb-3">⚠️</div>
            <h3 class="text-lg font-extrabold text-slate-900 mb-1 font-display">No Accessible Class Sections</h3>
            <p class="text-xs max-w-md mx-auto text-slate-600 font-medium">
                @if($attendanceMode === 'daily')
                    Your institute operates in <strong>Daily Class Attendance Mode</strong>. Only assigned <strong>Class Incharge</strong> teachers can mark daily attendance. Contact your Principal to be assigned as Class Incharge.
                @else
                    You are currently not assigned to teach any class section in Subject-Teacher Allocations.
                @endif
            </p>
        </div>
    @else

        <form method="POST" action="{{ route('teacher.attendance.store') }}">
            @csrf
            <input type="hidden" name="section_id" value="{{ $selectedSectionId }}">
            <input type="hidden" name="date" value="{{ $selectedDate }}">

            <div class="liquid-glass-card p-0 overflow-hidden border border-slate-200/90 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-100/90 border-b border-slate-200 text-xs text-slate-700 uppercase tracking-wider font-extrabold">
                                <th class="py-4 px-6">Roll No</th>
                                <th class="py-4 px-6">Student Name</th>
                                <th class="py-4 px-6 text-center">Attendance Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-slate-700 divide-y divide-slate-100">
                            @forelse($students as $student)
                                @php
                                    $existingLog = $attendances->get($student->id);
                                    $currentStatus = $existingLog ? strtolower($existingLog->status) : 'present';
                                    $pct = $student->attendance_percentage ?? 100;
                                    $pctBadge = $pct >= 75 ? 'badge-emerald' : ($pct >= 50 ? 'badge-amber' : 'badge-danger');
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-6 font-extrabold text-emerald-700 align-top pt-4 text-xs font-mono">
                                        {{ $student->roll_number ?? 'STD-'.str_pad($student->id, 4, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-3.5 px-6 text-slate-900 align-top pt-4">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-extrabold text-slate-900 text-sm">{{ $student->first_name }} {{ $student->last_name }}</span>
                                            <span class="badge {{ $pctBadge }} text-[11px] font-bold">
                                                📊 {{ $pct }}% Attendance ({{ $student->attendance_present_sessions ?? 0 }}/{{ $student->attendance_total_sessions ?? 0 }} sessions)
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-500 font-medium mt-0.5">{{ $student->email }}</div>

                                        <div id="reason_container_{{ $student->id }}" class="mt-2.5 {{ $currentStatus === 'leave' ? '' : 'hidden' }}">
                                            <input type="text" name="leave_reason[{{ $student->id }}]" 
                                                   placeholder="Reason for Leave" 
                                                   {{ $isLockedForUser ? 'disabled' : '' }}
                                                   class="w-full p-2.5 rounded-xl text-xs text-slate-900 bg-white border border-slate-300 shadow-2xs font-medium outline-none focus:border-pink-500">
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-6 align-top pt-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <label class="{{ $isLockedForUser ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}">
                                                <input type="radio" name="status[{{ $student->id }}]" value="present" 
                                                       {{ $currentStatus === 'present' ? 'checked' : '' }} 
                                                       {{ $isLockedForUser ? 'disabled' : '' }}
                                                       class="peer hidden" onchange="toggleReason({{ $student->id }}, false)">
                                                <div class="px-3.5 py-1.5 rounded-xl text-xs font-bold border border-slate-300 text-slate-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:border-emerald-300 shadow-2xs transition">
                                                    Present
                                                </div>
                                            </label>
                                            <label class="{{ $isLockedForUser ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}">
                                                <input type="radio" name="status[{{ $student->id }}]" value="absent" 
                                                       {{ $currentStatus === 'absent' ? 'checked' : '' }} 
                                                       {{ $isLockedForUser ? 'disabled' : '' }}
                                                       class="peer hidden" onchange="toggleReason({{ $student->id }}, false)">
                                                <div class="px-3.5 py-1.5 rounded-xl text-xs font-bold border border-slate-300 text-slate-600 peer-checked:bg-rose-50 peer-checked:text-rose-700 peer-checked:border-rose-300 shadow-2xs transition">
                                                    Absent
                                                </div>
                                            </label>
                                            <label class="{{ $isLockedForUser ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}">
                                                <input type="radio" name="status[{{ $student->id }}]" value="leave" 
                                                       {{ $currentStatus === 'leave' ? 'checked' : '' }} 
                                                       {{ $isLockedForUser ? 'disabled' : '' }}
                                                       class="peer hidden" onchange="toggleReason({{ $student->id }}, true)">
                                                <div class="px-3.5 py-1.5 rounded-xl text-xs font-bold border border-slate-300 text-slate-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:border-indigo-300 shadow-2xs transition">
                                                    Leave
                                                </div>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-500 text-sm font-medium">
                                        No active students enrolled in this section.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($students->count() > 0)
                    <div class="p-6 bg-slate-50/80 border-t border-slate-200 flex items-center justify-between">
                        <div class="text-xs text-slate-600 font-semibold">
                            Total Students in Roster: <strong class="text-slate-900 font-extrabold">{{ $students->count() }}</strong>
                        </div>
                        <div>
                            @if($isLockedForUser)
                                <button type="button" disabled class="px-6 py-2.5 rounded-xl bg-slate-200 text-slate-400 text-sm font-bold opacity-70 cursor-not-allowed flex items-center gap-2">
                                    <span>🔒 Attendance Locked</span>
                                </button>
                            @else
                                <button type="submit" class="btn-success px-6 py-2.5 text-sm font-bold flex items-center gap-2 shadow-sm">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Save &amp; Record Attendance</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </form>

    @endif
</div>
@endsection

@section('scripts')
<script>
    function toggleReason(studentId, show) {
        const container = document.getElementById('reason_container_' + studentId);
        if (!container) return;
        if (show) {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }
</script>
@endsection
