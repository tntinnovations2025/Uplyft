@extends('lms.layouts.app')

@section('title', 'Create New Assessment')
@section('breadcrumb', 'Assessments / Create')

@section('content')
<div style="max-width:800px;margin:0 auto">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
        <div>
            <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">➕ Create Assessment</h1>
            <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
                Configure assessment parameters and assign to class sections.
            </p>
        </div>
        <a href="{{ route('lms.assessments.index') }}" class="btn btn-ghost">&larr; Back to Assessments</a>
    </div>

    @if(session('error'))
        <div style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#fca5a5;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:14px">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('lms.assessments.store') }}">
            @csrf

            <div class="form-group" style="margin-bottom:16px">
                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px">Assessment Title *</label>
                <input type="text" name="title" required value="{{ old('title') }}" placeholder="e.g. Midterm Chapter 1-4 Test" style="width:100%;padding:10px 14px;background:#0f172a;border:1px solid #334155;border-radius:8px;color:#fff" />
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                <div class="form-group">
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px">Subject *</label>
                    <select name="subject_id" required style="width:100%;padding:10px 14px;background:#0f172a;border:1px solid #334155;border-radius:8px;color:#fff">
                        <option value="">Select Subject</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" @selected(old('subject_id') == $s->id)>{{ $s->subject_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px">Active Academic Term *</label>
                    <select name="academic_term_id" required style="width:100%;padding:10px 14px;background:#0f172a;border:1px solid #334155;border-radius:8px;color:#fff">
                        @foreach($academicTerms as $term)
                            <option value="{{ $term->id }}" @selected(old('academic_term_id') == $term->id)>{{ $term->name }} (Active)</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                <div class="form-group">
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px">Target Section *</label>
                    <select name="class_section_id" required style="width:100%;padding:10px 14px;background:#0f172a;border:1px solid #334155;border-radius:8px;color:#fff">
                        <option value="">Select Class & Section</option>
                        @foreach($classSections as $sec)
                            <option value="{{ $sec->id }}" @selected(old('class_section_id') == $sec->id)>
                                {{ $sec->instituteClass?->name ?? 'Class' }} - {{ $sec->section_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px">Assessment Type *</label>
                    <select name="type" required style="width:100%;padding:10px 14px;background:#0f172a;border:1px solid #334155;border-radius:8px;color:#fff">
                        <option value="quiz">Quiz</option>
                        <option value="assignment">Assignment</option>
                        <option value="midterm">Midterm Examination</option>
                        <option value="final">Final Term Examination</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                <div class="form-group">
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px">Total Marks *</label>
                    <input type="number" name="total_marks" min="1" max="1000" value="{{ old('total_marks', 100) }}" required style="width:100%;padding:10px 14px;background:#0f172a;border:1px solid #334155;border-radius:8px;color:#fff" />
                </div>
                <div class="form-group">
                    <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px">Evaluation Mode</label>
                    <select name="evaluation_mode" style="width:100%;padding:10px 14px;background:#0f172a;border:1px solid #334155;border-radius:8px;color:#fff">
                        <option value="manual">Manual Grading</option>
                        <option value="ai">AI-Assisted Auto Grading</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:24px">
                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px">Instructions / Guidelines</label>
                <textarea name="instructions" rows="4" placeholder="Enter instructions for students..." style="width:100%;padding:10px 14px;background:#0f172a;border:1px solid #334155;border-radius:8px;color:#fff">{{ old('instructions') }}</textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px">
                <a href="{{ route('lms.assessments.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary" style="background:#0284c7;padding:10px 24px;color:#fff;border-radius:8px;border:none;font-weight:600;cursor:pointer">
                    Save &amp; Continue &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
