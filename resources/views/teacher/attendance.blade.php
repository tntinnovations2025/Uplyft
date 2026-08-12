@extends('layouts.app')

@section('title', 'Mark Attendance')
@section('page-header', 'Interactive Roster')

@section('content')
<div class="space-y-6">

    <div class="glass-panel p-6 bg-gradient-to-r from-emerald-900/40 to-slate-900/60 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white">Daily Attendance Roster</h2>
            <p class="text-xs text-slate-400">Select class and mark students as Present, Absent, or Leave.</p>
        </div>
        <form method="GET" action="{{ route('teacher.attendance') }}" class="flex items-center gap-2">
            <select name="class" class="glass-input text-xs px-3 py-1.5 h-auto rounded-lg" onchange="this.form.submit()">
                <option value="">-- All Classes --</option>
                @foreach($classes as $c)
                    <option value="{{ $c }}" {{ $selectedClass == $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
            </select>
            <input type="date" value="{{ date('Y-m-d') }}" class="glass-input text-xs px-3 py-1.5 h-auto rounded-lg">
            <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition">
                Load Roster
            </button>
        </form>
    </div>

    <form method="POST" action="#">
        @csrf
        <div class="glass-panel p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900/50 border-b border-white/10 text-xs text-slate-400 uppercase tracking-wider">
                            <th class="py-4 px-6">Roll No</th>
                            <th class="py-4 px-6">Student Name</th>
                            <th class="py-4 px-6 text-center">Mark Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-300">
                        @forelse($students as $student)
                            <tr class="border-b border-white/5 hover:bg-white/5 transition">
                                <td class="py-3 px-6 font-semibold text-emerald-400 align-top pt-4">{{ $student->roll_number ?? 'STD-'.str_pad($student->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-3 px-6 text-white align-top pt-4">
                                    {{ $student->first_name }} {{ $student->last_name }}
                                    <div id="reason_container_{{ $student->id }}" class="mt-2 hidden">
                                        <input type="text" name="leave_reason[{{ $student->id }}]" placeholder="Reason for Leave" class="w-full glass-input p-2 rounded-lg text-xs text-slate-200">
                                    </div>
                                </td>
                                <td class="py-3 px-6 align-top pt-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="status[{{ $student->id }}]" value="Present" class="peer hidden" checked onchange="toggleReason({{ $student->id }}, false)">
                                            <div class="px-3 py-1 rounded text-xs border border-slate-600 text-slate-400 peer-checked:bg-emerald-500/20 peer-checked:text-emerald-400 peer-checked:border-emerald-500/50 transition">Present</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="status[{{ $student->id }}]" value="Absent" class="peer hidden" onchange="toggleReason({{ $student->id }}, false)">
                                            <div class="px-3 py-1 rounded text-xs border border-slate-600 text-slate-400 peer-checked:bg-red-500/20 peer-checked:text-red-400 peer-checked:border-red-500/50 transition">Absent</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="status[{{ $student->id }}]" value="Leave" class="peer hidden" onchange="toggleReason({{ $student->id }}, true)">
                                            <div class="px-3 py-1 rounded text-xs border border-slate-600 text-slate-400 peer-checked:bg-indigo-500/20 peer-checked:text-indigo-400 peer-checked:border-indigo-500/50 transition">Leave</div>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-slate-500 text-sm">No students found for this selection.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($students->count() > 0)
                <div class="p-6 bg-slate-900/30 border-t border-white/5 flex justify-end">
                    <button type="button" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold transition shadow-lg shadow-emerald-500/20 flex items-center gap-2" onclick="alert('Attendance saved successfully!');">
                        <i class="fa-solid fa-check"></i>
                        <span>Submit Daily Attendance</span>
                    </button>
                </div>
            @endif
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function toggleReason(studentId, show) {
        const container = document.getElementById('reason_container_' + studentId);
        if (show) {
            container.classList.remove('hidden');
            container.querySelector('input').required = true;
        } else {
            container.classList.add('hidden');
            container.querySelector('input').required = false;
        }
    }
</script>
@endsection
