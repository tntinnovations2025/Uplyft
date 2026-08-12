@extends('layouts.app')

@section('title', 'Students Directory')
@section('page-header', 'Student Directory')

@section('content')
<div class="space-y-6">

    <div class="glass-panel p-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white">All Enrolled Students</h2>
            <p class="text-xs text-slate-400">Total Enrolled: <span class="text-indigo-400 font-bold">{{ $students->count() }}</span></p>
        </div>
        <a href="{{ route('admissions.index') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition">
            <i class="fa-solid fa-plus mr-2"></i> Enroll New
        </a>
    </div>

    <div class="glass-panel p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-white/10 text-xs text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6">Roll Number</th>
                        <th class="py-4 px-6">Student Name</th>
                        <th class="py-4 px-6">Program/Class</th>
                        <th class="py-4 px-6">Joined Date</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-300">
                    @forelse($students as $student)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-3 px-6 font-semibold text-indigo-400">{{ $student->roll_number }}</td>
                            <td class="py-3 px-6 text-white">{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td class="py-3 px-6">{{ $student->enrolled_program ?? 'N/A' }}</td>
                            <td class="py-3 px-6">{{ $student->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-500 text-sm">No students enrolled yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
