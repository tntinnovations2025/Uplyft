@extends('layouts.app')

@section('title', 'Academic Gradebook')
@section('page-header', 'Academic Gradebook & Results')

@section('content')
<div class="space-y-6">
    <div class="liquid-glass-card p-6 bg-gradient-to-r from-pink-500/[0.06] via-purple-500/[0.04] to-indigo-500/[0.06] border border-slate-200/90 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-2xs glass-specular-top">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2 font-display">
                <i class="fa-solid fa-file-contract text-pink-600"></i>
                <span>Official Term Examination Report</span>
            </h2>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Class: <strong class="text-pink-700 font-bold">{{ $classSection?->instituteClass?->custom_name ?? 'Not Assigned' }}</strong> 
                | Section: <strong class="text-pink-700 font-bold">{{ $classSection?->section_name ?? 'N/A' }}</strong>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="badge badge-pink text-xs font-bold">
                <i class="fa-solid fa-award"></i> Midterm &amp; Final Term Transcript
            </span>
        </div>
    </div>

    @if($examReports->isEmpty())
        <div class="liquid-glass-card p-12 text-center border border-dashed border-slate-300 shadow-2xs">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-clipboard-question"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-800 font-display">No Exam Results Published Yet</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto font-medium">
                No formal examination results (Midterm / Final Term) have been released for 
                <strong class="text-slate-700">{{ $classSection?->instituteClass?->custom_name }} - {{ $classSection?->section_name }}</strong> yet.
            </p>
        </div>
    @else
        <div class="liquid-glass-card p-6 border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between mb-4 border-b border-slate-200 pb-3">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2 font-display">
                    <i class="fa-solid fa-award text-pink-600"></i>
                    <span>Official Term Exam Scorecard</span>
                </h3>
                <span class="badge badge-purple text-xs font-bold">
                    {{ $examReports->count() }} Exam Marksheets
                </span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200/80">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase text-[11px] font-extrabold tracking-wider">
                            <th class="py-3.5 px-4">Exam Date</th>
                            <th class="py-3.5 px-4">Subject</th>
                            <th class="py-3.5 px-4">Exam Title</th>
                            <th class="py-3.5 px-4">Exam Type</th>
                            <th class="py-3.5 px-4">Marks Obtained / Max Marks</th>
                            <th class="py-3.5 px-4 text-right">Grade Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($examReports as $exam)
                            @php
                                $examDate = $exam->start_time ? $exam->start_time->format('M d, Y') : $exam->created_at->format('M d, Y');
                                $obtained = $exam->obtained_marks;
                                $max = $exam->total_marks;
                                $pct = ($exam->is_graded && $max > 0) ? round(($obtained / $max) * 100, 1) : 0;
                                $grade = 'F';
                                if ($pct >= 90) $grade = 'A+';
                                elseif ($pct >= 80) $grade = 'A';
                                elseif ($pct >= 70) $grade = 'B';
                                elseif ($pct >= 60) $grade = 'C';
                                elseif ($pct >= 50) $grade = 'D';

                                $badgeClass = match($grade) {
                                    'A+', 'A' => 'badge-emerald',
                                    'B', 'C' => 'badge-purple',
                                    'D' => 'badge-amber',
                                    default => 'badge-rose',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-800">
                                    📅 {{ $examDate }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-slate-900">📘 {{ $exam->subject->subject_name ?? 'Subject' }}</div>
                                    <div class="text-[10px] font-mono text-pink-700 font-bold">{{ $exam->subject->subject_code ?? '' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-slate-900">{{ $exam->title }}</div>
                                    @if($exam->is_graded && !empty($exam->question_breakdown))
                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            @foreach($exam->question_breakdown as $qb)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200" title="{{ $qb['statement'] }}">
                                                    <strong class="text-indigo-700">{{ $qb['statement'] }}:</strong>
                                                    <span class="text-emerald-700 font-bold">{{ $qb['obtained_marks'] !== null ? $qb['obtained_marks'] : '—' }}</span>/<span class="text-slate-500">{{ $qb['max_marks'] }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="badge badge-purple text-[10px] font-extrabold uppercase">
                                        {{ strtoupper($exam->type) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-sm">
                                    @if($exam->is_graded)
                                        <span class="text-emerald-700 font-extrabold">{{ $obtained }}</span> <span class="text-slate-500 text-xs">/ {{ $max }} pts</span>
                                    @else
                                        <span class="badge badge-amber text-xs font-bold">Pending Result</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($exam->is_graded)
                                        <span class="badge {{ $badgeClass }} text-[11px] font-extrabold">
                                            Grade {{ $grade }} ({{ $pct }}%)
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
