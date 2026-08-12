@extends('layouts.app')

@section('title', 'Teachers Directory')
@section('page-header', 'Teacher Directory')

@section('content')
<div class="space-y-6">

    <div class="glass-panel p-6 flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white">Onboarded Teachers</h2>
            <p class="text-xs text-slate-400">Total Faculty: <span class="text-cyan-400 font-bold">{{ $teachers->count() }}</span></p>
        </div>
        <a href="{{ route('teachers.onboarding') }}" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-semibold transition">
            <i class="fa-solid fa-plus mr-2"></i> Onboard New
        </a>
    </div>

    <div class="glass-panel p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900/50 border-b border-white/10 text-xs text-slate-400 uppercase tracking-wider">
                        <th class="py-4 px-6">Employee ID</th>
                        <th class="py-4 px-6">Teacher Name</th>
                        <th class="py-4 px-6">Specialization</th>
                        <th class="py-4 px-6">Experience</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-300">
                    @forelse($teachers as $teacher)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-3 px-6 font-semibold text-cyan-400">{{ $teacher->employee_id }}</td>
                            <td class="py-3 px-6 text-white">{{ $teacher->first_name }} {{ $teacher->last_name }}</td>
                            <td class="py-3 px-6">{{ $teacher->specialization_subjects ?? 'N/A' }}</td>
                            <td class="py-3 px-6">{{ $teacher->years_of_experience ? $teacher->years_of_experience . ' Years' : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-500 text-sm">No teachers onboarded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
