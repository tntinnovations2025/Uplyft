@extends('layouts.app')

@section('title', 'Daily Diary - Student Portal')
@section('page-header', 'Daily Diary')

@section('content')
<style>
    /* =========================================================================
       STUDENT DAILY DIARY: COMPACT INK & AMBER DESIGN
       ========================================================================= */
    .student-diary-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 100%;
        max-width: 920px;
        margin: 0 auto;
    }

    .diary-glass-card {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 2px 8px rgba(27, 26, 23, 0.02);
    }

    /* Subject Selection Cards */
    .subject-pill-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 7px 11px;
        background: #FFFFFF;
        border: 1px solid #E1DFD7;
        border-radius: 9px;
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .subject-pill-card:hover {
        border-color: #D48A2E;
        background: #FDFBF7;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(212, 138, 46, 0.1);
    }
    .subject-pill-card.active {
        background: #F8E9D3 !important;
        border-color: #D48A2E !important;
        box-shadow: 0 2px 8px rgba(212, 138, 46, 0.18) !important;
    }

    /* Timeline Badges */
    .type-pill-homework { background: #F8E9D3; color: #8A5A10; border: 1px solid #EAC8A4; }
    .type-pill-test { background: #F6E4E1; color: #A2412C; border: 1px solid #EAC8C1; }
    .type-pill-assignment { background: #E7ECF6; color: #3A529C; border: 1px solid #C4D2EC; }
    .type-pill-announcement { background: #E3EFE2; color: #2E6E42; border: 1px solid #BFD9BE; }
    .type-pill-note { background: #F2EFEB; color: #68665D; border: 1px solid #D5D2C8; }

    .diary-feed-item {
        background: #FFFFFF;
        border: 1px solid #E1DFD7;
        border-radius: 9px;
        padding: 11px 14px;
        display: flex;
        flex-direction: column;
        gap: 7px;
        transition: all 0.15s ease;
    }
    .diary-feed-item:hover {
        border-color: #D48A2E;
        box-shadow: 0 3px 10px rgba(212, 138, 46, 0.06);
    }

    .date-header-strip {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 10px;
        background: #EFECE6;
        border: 1px solid #E1DFD7;
        border-radius: 7px;
        margin-top: 8px;
        margin-bottom: 6px;
    }
</style>

<div class="student-diary-container">
    {{-- Header Banner --}}
    <div class="diary-glass-card" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; padding: 10px 14px; background: linear-gradient(135deg, #F9F8F5 0%, #F4EFE6 100%);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 34px; height: 34px; border-radius: 9px; background: #F8E9D3; border: 1.5px solid #EAC8A4; color: #8A5A10; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                📖
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <h1 style="font-family: 'Manrope', sans-serif; font-size: 15px; font-weight: 800; color: #1B1A17; margin: 0; letter-spacing: -0.3px;">
                        Student Daily Diary
                    </h1>
                    <span style="font-size: 9.5px; font-weight: 800; background: #E3EFE2; color: #2E6E42; border: 1px solid #BFD9BE; padding: 1px 6px; border-radius: 9999px;">
                        Live Feed
                    </span>
                </div>
                <p style="font-size: 11px; color: #68665D; margin: 1px 0 0; font-weight: 500;">
                    Class: <strong>{{ $student->classSection?->instituteClass?->custom_name ?? 'Class' }} — {{ $student->classSection?->section_name ?? 'Section' }}</strong>
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 10.5px; font-weight: 800; color: #1B1A17; background: #FFFFFF; border: 1px solid #E1DFD7; padding: 3px 9px; border-radius: 7px;">
                📝 {{ $allEntries->count() }} Total Updates
            </span>
        </div>
    </div>

    {{-- Subject Selector Grid --}}
    <div class="diary-glass-card" style="padding: 10px 14px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 7px;">
            <span style="font-size: 11.5px; font-weight: 800; color: #1B1A17; display: flex; align-items: center; gap: 5px;">
                📚 Subjects:
            </span>
            @if($selectedSubjectId)
                <a href="{{ route('student.diary.index') }}" style="font-size: 10.5px; font-weight: 700; color: #A2412C; text-decoration: underline;">
                    ✕ Clear Filter (Show All)
                </a>
            @endif
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 6px;">
            {{-- All Subjects Tile --}}
            <a href="{{ route('student.diary.index') }}" class="subject-pill-card {{ empty($selectedSubjectId) ? 'active' : '' }}">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="font-size: 13px;">📑</span>
                    <div>
                        <div style="font-size: 11.5px; font-weight: 800; color: #1B1A17;">All Subjects</div>
                        <div style="font-size: 9.5px; color: #68665D;">Combined</div>
                    </div>
                </div>
                <span style="font-size: 10px; font-weight: 800; background: #F2EFEB; color: #1B1A17; padding: 2px 5px; border-radius: 4px;">
                    {{ $allEntries->count() }}
                </span>
            </a>

            {{-- Dynamic Subject Tiles --}}
            @foreach($subjects as $sub)
                @php
                    $count = $subjectCounts[$sub->id] ?? 0;
                    $isActive = ($selectedSubjectId == $sub->id);
                @endphp
                <a href="{{ route('student.diary.index', ['subject_id' => $sub->id]) }}" class="subject-pill-card {{ $isActive ? 'active' : '' }}">
                    <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
                        <span style="font-size: 13px; flex-shrink: 0;">📘</span>
                        <div style="min-width: 0;">
                            <div style="font-size: 11.5px; font-weight: 800; color: #1B1A17; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $sub->subject_name }}
                            </div>
                            <div style="font-size: 9px; color: #68665D;">
                                {{ $sub->subject_code ?? 'Subject' }}
                            </div>
                        </div>
                    </div>
                    <span style="font-size: 10px; font-weight: 800; background: {{ $isActive ? '#1A1200' : '#F2EFEB' }}; color: {{ $isActive ? '#FFFFFF' : '#1B1A17' }}; padding: 2px 5px; border-radius: 4px; flex-shrink: 0;">
                        {{ $count }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Structured Diary Timeline (Grouped by Day & Date) --}}
    <div class="diary-glass-card" style="padding: 14px 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #EAE7DF; padding-bottom: 8px; margin-bottom: 10px;">
            <div>
                <h2 style="font-family: 'Manrope', sans-serif; font-size: 13.5px; font-weight: 800; color: #1B1A17; margin: 0;">
                    @if($activeSubject)
                        Diary Feed: {{ $activeSubject->subject_name }}
                    @else
                        Daily Homework &amp; Tests Timeline
                    @endif
                </h2>
                <p style="font-size: 10.5px; color: #68665D; margin: 1px 0 0;">
                    Grouped chronologically by assignment day &amp; date
                </p>
            </div>

            <span style="font-size: 10px; font-weight: 700; color: #68665D;">
                👁️ Read-Only Student View
            </span>
        </div>

        @if($groupedEntries->isEmpty())
            <div style="padding: 24px 14px; text-align: center; background: #FFFFFF; border: 1px dashed #E1DFD7; border-radius: 9px;">
                <div style="font-size: 24px; margin-bottom: 6px;">🎉</div>
                <h3 style="font-size: 13px; font-weight: 800; color: #1B1A17; margin: 0 0 3px;">No Diary Updates for This Subject</h3>
                <p style="font-size: 11.5px; color: #68665D; margin: 0;">
                    Your teachers have not posted any homework or test notifications here yet.
                </p>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($groupedEntries as $dateGroup => $entries)
                    @php
                        $isToday = (\Carbon\Carbon::parse($entries->first()->assigned_date)->isToday());
                        $isYesterday = (\Carbon\Carbon::parse($entries->first()->assigned_date)->isYesterday());
                    @endphp

                    <div>
                        {{-- Day & Date Header Strip --}}
                        <div class="date-header-strip">
                            <span style="font-size: 13px;">📅</span>
                            <span style="font-family: 'Manrope', sans-serif; font-size: 12px; font-weight: 800; color: #1B1A17;">
                                {{ $dateGroup }}
                            </span>
                            @if($isToday)
                                <span style="font-size: 9px; font-weight: 800; background: #E3EFE2; color: #2E6E42; border: 1px solid #BFD9BE; padding: 1px 5px; border-radius: 4px;">
                                    Today
                                </span>
                            @elseif($isYesterday)
                                <span style="font-size: 9px; font-weight: 800; background: #F8E9D3; color: #8A5A10; border: 1px solid #EAC8A4; padding: 1px 5px; border-radius: 4px;">
                                    Yesterday
                                </span>
                            @endif
                            <span style="font-size: 10px; font-weight: 700; color: #8A877E; margin-left: auto;">
                                {{ $entries->count() }} {{ \Illuminate\Support\Str::plural('entry', $entries->count()) }}
                            </span>
                        </div>

                        {{-- Entries on this Date --}}
                        <div style="display: flex; flex-direction: column; gap: 7px; margin-top: 5px;">
                            @foreach($entries as $entry)
                                @php
                                    $typeClass = match(strtolower($entry->entry_type)) {
                                        'test', 'test_alert', 'quiz' => 'type-pill-test',
                                        'assignment' => 'type-pill-assignment',
                                        'announcement' => 'type-pill-announcement',
                                        'note', 'classwork' => 'type-pill-note',
                                        default => 'type-pill-homework'
                                    };
                                    $typeLabel = match(strtolower($entry->entry_type)) {
                                        'test', 'test_alert', 'quiz' => '📝 Test',
                                        'assignment' => '📋 Assignment',
                                        'announcement' => '📢 Announcement',
                                        'note', 'classwork' => '💡 Notes',
                                        default => '📚 Homework'
                                    };
                                @endphp

                                <div class="diary-feed-item">
                                    {{-- Item Header --}}
                                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                                        <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                                            <span style="font-size: 10.5px; font-weight: 800; padding: 2px 7px; border-radius: 5px;" class="{{ $typeClass }}">
                                                {{ $typeLabel }}
                                            </span>
                                            <span style="font-size: 10.5px; font-weight: 800; color: #1B1A17; background: #F2EFEB; border: 1px solid #E1DFD7; padding: 2px 7px; border-radius: 5px;">
                                                📘 {{ $entry->subject?->subject_name ?? 'Subject' }}
                                            </span>
                                        </div>

                                        <div style="font-size: 10.5px; font-weight: 600; color: #68665D;">
                                            Assigned by: <strong style="color: #1B1A17;">{{ $entry->teacher?->name ?? 'Faculty' }}</strong>
                                        </div>
                                    </div>

                                    {{-- Title & Body --}}
                                    <div>
                                        <h3 style="font-family: 'Manrope', sans-serif; font-size: 13px; font-weight: 800; color: #1B1A17; margin: 0 0 3px;">
                                            {{ $entry->title }}
                                        </h3>
                                        <div style="font-size: 12px; color: #3A3935; line-height: 1.45; white-space: pre-line;">
                                            {{ $entry->content }}
                                        </div>
                                    </div>

                                    {{-- Footer: ONLY render if due_date OR attachment exists --}}
                                    @if($entry->due_date || $entry->hasAttachment())
                                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; padding-top: 6px; margin-top: 1px; border-top: 1px dashed #E5E3DC;">
                                            <div>
                                                @if($entry->due_date)
                                                    <span style="display: inline-flex; align-items: center; gap: 3px; font-size: 10.5px; font-weight: 800; color: #A2412C; background: #F6E4E1; padding: 2px 7px; border-radius: 5px; border: 1px solid #EAC8C1;">
                                                        ⏰ {{ $entry->isTest() ? 'Test Date' : 'Due' }}: {{ $entry->due_date->format('M d, Y') }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($entry->hasAttachment())
                                                <a href="{{ $entry->attachment_url }}" target="_blank" download style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; background: #FDFBF7; border: 1px solid #D48A2E; border-radius: 6px; font-size: 10.5px; font-weight: 800; color: #8A5A10; text-decoration: none; transition: all 0.15s ease;">
                                                    <span>📎</span>
                                                    <span>{{ $entry->file_name ?? 'Attachment' }}</span>
                                                    @if($entry->formatted_file_size)
                                                        <span style="color: #68665D; font-weight: 600;">({{ $entry->formatted_file_size }})</span>
                                                    @endif
                                                    <span>⬇️</span>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
