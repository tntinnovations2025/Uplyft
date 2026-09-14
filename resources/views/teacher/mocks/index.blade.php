@extends('layouts.app')

@section('title', 'Generated Mock Examinations')
@section('page-header', 'Generated Mock Examinations')

@section('content')
<div class="mock-engine-container space-y-6 max-w-full overflow-x-hidden">

    <!-- TOP HEADER (Ink & Amber Daylight Surface) -->
    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-6 shadow-sm flex flex-wrap justify-between items-center gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-2 flex-wrap">
                <span class="bg-[#F8E9D3] text-[#8A5A10] border border-[#E8CEAA] font-extrabold text-[11px] px-2.5 py-1 rounded-md uppercase tracking-wider font-display">
                    EXAMINATION BANK
                </span>
                <span class="bg-[#E7ECF6] text-[#3A529C] border border-[#CAD5EC] font-bold text-[11px] px-2.5 py-1 rounded-md">
                    <i class="fa-solid fa-graduation-cap"></i> Cambridge O/A Levels
                </span>
            </div>
            <h1 class="text-2xl font-extrabold text-[#1B1A17] tracking-tight font-display">
                Generated Mock Examinations
            </h1>
            <p class="text-xs text-[#68665D] mt-1 font-medium">
                Review, live-edit questions, and schedule Cambridge CAIE &amp; Edexcel international standard mock exams.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('lms.mocks.create') }}" class="bg-[#D48A2E] hover:bg-[#C07A22] text-white px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Generate New Mock
            </a>
            <a href="{{ route('lms.assessments.index') }}" class="bg-[#FFFFFF] border border-[#E1DFD7] text-[#68665D] hover:text-[#1B1A17] px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all">
                <i class="fa-solid fa-arrow-left"></i> All Assessments
            </a>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-[#F9F8F5] border border-[#E1DFD7] rounded-2xl p-4 shadow-sm">
        <form method="GET" action="{{ route('lms.mocks.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <select name="subject_id" onchange="this.form.submit()" class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E]">
                    <option value="">-- All Subjects --</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject_id') == $sub->id ? 'selected' : '' }}>
                            {{ $sub->name }} ({{ $sub->code ?? 'CAIE' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-48">
                <select name="status" onchange="this.form.submit()" class="w-full bg-[#FFFFFF] border border-[#E1DFD7] rounded-xl px-3 py-2 text-xs font-semibold text-[#1B1A17] focus:outline-none focus:border-[#D48A2E]">
                    <option value="">-- All Statuses --</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published / Active</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            @if(request('subject_id') || request('status'))
                <a href="{{ route('lms.mocks.index') }}" class="bg-[#FFFFFF] border border-[#E1DFD7] text-[#68665D] hover:text-[#1B1A17] px-3 py-2 rounded-xl text-xs font-bold transition-all">
                    <i class="fa-solid fa-xmark"></i> Clear Filters
                </a>
            @endif
        </form>
    </div>

    <!-- MOCKS LIST TABLE / CARDS -->
    @if($mocks->isEmpty())
        <div class="bg-[#F9F8F5] border border-dashed border-[#E1DFD7] rounded-2xl p-14 text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#F8E9D3] border border-[#E8CEAA] flex items-center justify-center mx-auto mb-4 text-[#D48A2E] text-2xl shadow-sm">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <h3 class="text-base font-extrabold text-[#1B1A17] mb-2 font-display">
                No Mock Examinations Found
            </h3>
            <p class="text-xs text-[#68665D] max-w-md mx-auto mb-5 leading-relaxed">
                Generate your first Cambridge O/A Level mock exam with 8–10 PDF past-paper style conditioning and zero-duplication filtering.
            </p>
            <a href="{{ route('lms.mocks.create') }}" class="bg-[#D48A2E] hover:bg-[#C07A22] text-white font-extrabold text-xs px-5 py-2.5 rounded-xl inline-flex items-center gap-2 shadow-sm transition-all">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Launch Mock Generator
            </a>
        </div>
    @else
        <div class="space-y-3.5">
            @foreach($mocks as $mock)
                <div class="bg-[#F9F8F5] border border-[#E1DFD7] hover:border-[#D48A2E]/50 rounded-2xl p-5 shadow-sm flex flex-wrap justify-between items-center gap-4 transition-all">
                    <div class="flex-1 min-w-[280px]">
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span class="bg-[#F8E9D3] text-[#8A5A10] border border-[#E8CEAA] text-[10px] font-extrabold px-2 py-0.5 rounded-md uppercase font-display">
                                {{ strtoupper(str_replace('_', ' ', $mock->exam_standard ?? 'CAIE O LEVEL')) }}
                            </span>
                            <span class="bg-[#FFFFFF] border border-[#E1DFD7] text-[#1B1A17] text-[10px] font-bold px-2 py-0.5 rounded-md">
                                {{ $mock->total_mcqs ?? $mock->questions_count }} MCQs · {{ $mock->options_per_mcq ?? 4 }} Options
                            </span>
                            <span class="bg-[#E7ECF6] text-[#3A529C] border border-[#CAD5EC] text-[10px] font-bold px-2 py-0.5 rounded-md">
                                <i class="fa-solid fa-stopwatch"></i> {{ $mock->duration_minutes ?? 45 }} Mins
                            </span>
                            @if($mock->is_published_student)
                                <span class="bg-[#E3EFE2] text-[#2E6E42] border border-[#C5DDC3] text-[10px] font-bold px-2 py-0.5 rounded-md flex items-center gap-1">
                                    <i class="fa-solid fa-globe text-[9px]"></i> Online on Portal
                                </span>
                            @else
                                <span class="bg-[#F8E9D3] text-[#8A5A10] border border-[#E8CEAA] text-[10px] font-bold px-2 py-0.5 rounded-md flex items-center gap-1">
                                    <i class="fa-solid fa-print text-[9px]"></i> Print / Offline Only
                                </span>
                            @endif
                        </div>

                        <h3 class="text-base font-extrabold text-[#1B1A17] mb-1 font-display">
                            <a href="{{ route('lms.mocks.show', $mock->id) }}" class="text-[#1B1A17] hover:text-[#D48A2E] transition-colors">
                                {{ $mock->title }}
                            </a>
                        </h3>

                        <div class="flex items-center gap-3.5 text-xs text-[#68665D] flex-wrap">
                            <span><i class="fa-solid fa-book text-[#D48A2E]"></i> {{ $mock->subject->name ?? 'Subject' }}</span>
                            <span><i class="fa-solid fa-users text-[#3A529C]"></i> {{ $mock->classSection->instituteClass->name ?? 'Class' }} - {{ $mock->classSection->name ?? 'Section' }}</span>
                            <span><i class="fa-solid fa-calendar text-[#8A5A10]"></i> {{ $mock->academicTerm->name ?? 'Term' }}</span>
                            <span><i class="fa-solid fa-clock text-[#A19E92]"></i> {{ $mock->created_at->format('M d, Y · h:i A') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('lms.mocks.print', $mock->id) }}" target="_blank" class="bg-[#FFFFFF] border border-[#E1DFD7] hover:border-[#D48A2E] text-[#1B1A17] hover:text-[#D48A2E] font-bold text-xs px-3 py-2 rounded-xl transition-all shadow-sm flex items-center gap-1.5" title="Print Cambridge Examination Paper">
                            <i class="fa-solid fa-print text-[#D48A2E]"></i> Print
                        </a>
                        <a href="{{ route('lms.mocks.pdf', $mock->id) }}" class="bg-[#FFFFFF] border border-[#E1DFD7] hover:border-[#D48A2E] text-[#1B1A17] hover:text-[#D48A2E] font-bold text-xs px-3 py-2 rounded-xl transition-all shadow-sm flex items-center gap-1.5" title="Download Standard PDF">
                            <i class="fa-solid fa-file-pdf text-[#D48A2E]"></i> PDF
                        </a>
                        <a href="{{ route('lms.mocks.show', $mock->id) }}" class="bg-[#FFFFFF] border border-[#D48A2E] text-[#8A5A10] hover:bg-[#D48A2E] hover:text-white font-extrabold text-xs px-3.5 py-2 rounded-xl transition-all shadow-sm flex items-center gap-2">
                            <i class="fa-solid fa-eye"></i> Review &amp; Edit ({{ $mock->questions_count }})
                        </a>
                        <form method="POST" action="{{ route('lms.mocks.destroy', $mock->id) }}" onsubmit="return confirm('Are you sure you want to delete this Mock Examination and all its questions?');" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-[#F6E4E1] hover:bg-[#A2412C] text-[#A2412C] hover:text-white border border-[#ECCAC4] p-2 rounded-xl text-xs cursor-pointer transition-all" title="Delete Mock">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach

            <div class="mt-4">
                {{ $mocks->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
