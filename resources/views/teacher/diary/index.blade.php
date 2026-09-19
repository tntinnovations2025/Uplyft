@extends('layouts.app')

@section('title', 'Daily Diary Studio - Teacher Portal')
@section('page-header', 'Daily Diary Studio')

@section('content')
<style>
    /* =========================================================================
       TEACHER DAILY DIARY: INK & AMBER PREMIUM DESIGN SYSTEM
       ========================================================================= */
    .diary-container {
        display: flex;
        flex-direction: column;
        gap: 14px;
        width: 100%;
        max-width: 960px;
        margin: 0 auto;
    }

    .diary-glass-card {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 12px;
        padding: 14px 18px;
        box-shadow: 0 2px 8px rgba(27, 26, 23, 0.02);
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .type-pill-homework { background: #F8E9D3; color: #8A5A10; border: 1px solid #EAC8A4; }
    .type-pill-test { background: #F6E4E1; color: #A2412C; border: 1px solid #EAC8C1; }
    .type-pill-assignment { background: #E7ECF6; color: #3A529C; border: 1px solid #C4D2EC; }
    .type-pill-announcement { background: #E3EFE2; color: #2E6E42; border: 1px solid #BFD9BE; }
    .type-pill-note { background: #F2EFEB; color: #68665D; border: 1px solid #D5D2C8; }

    .diary-entry-card {
        background: #FFFFFF;
        border: 1px solid #E1DFD7;
        border-radius: 10px;
        padding: 12px 16px;
        transition: all 0.15s ease;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .diary-entry-card:hover {
        border-color: #D48A2E;
        box-shadow: 0 8px 24px rgba(212, 138, 46, 0.08);
        transform: translateY(-2px);
    }

    /* Modal / Drawer Overlay */
    .diary-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(14, 14, 17, 0.65);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .diary-modal-card {
        background: #F9F8F5;
        border: 1.5px solid #E1DFD7;
        border-radius: 20px;
        width: 100%;
        max-width: 580px;
        max-height: 90vh;
        overflow-y: auto;
        padding: 28px;
        box-shadow: 0 24px 60px -10px rgba(0,0,0,0.25);
    }

    .form-label {
        font-size: 11.5px;
        font-weight: 800;
        color: #1B1A17;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 6px;
        display: block;
    }
    .form-control {
        width: 100%;
        background: #FFFFFF;
        border: 1.5px solid #E1DFD7;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 13.5px;
        font-weight: 600;
        color: #1B1A17;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .form-control:focus {
        border-color: #D48A2E;
        box-shadow: 0 0 0 3px rgba(212, 138, 46, 0.15);
    }
</style>

<div class="diary-container">
    {{-- Header Banner & Stats --}}
    <div class="diary-glass-card" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; background: linear-gradient(135deg, #F9F8F5 0%, #F4EFE6 100%);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: #F8E9D3; border: 1.5px solid #EAC8A4; color: #8A5A10; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 4px 12px rgba(212, 138, 46, 0.2);">
                📖
            </div>
            <div>
                <h1 style="font-family: 'Manrope', sans-serif; font-size: 22px; font-weight: 800; color: #1B1A17; margin: 0; letter-spacing: -0.4px;">
                    Daily Diary Management
                </h1>
                <p style="font-size: 13px; color: #68665D; margin: 4px 0 0; font-weight: 500;">
                    Push homework, test announcements, notes &amp; syllabus files to your assigned students in real time.
                </p>
            </div>
        </div>

        <button type="button" onclick="openNewDiaryModal()" class="btn btn-primary" style="background: linear-gradient(135deg, #D48A2E 0%, #C07A22 100%); border: none; color: #1A1200; font-weight: 800; font-size: 13px; padding: 11px 22px; border-radius: 12px; box-shadow: 0 4px 14px rgba(212, 138, 46, 0.3); display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
            <span>✍️</span> Publish New Diary Entry
        </button>
    </div>

    {{-- Filter & Control Bar --}}
    <div class="diary-glass-card" style="padding: 16px 20px;">
        <form method="GET" action="{{ route('teacher.diary.index') }}" id="filterForm" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin: 0;">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; flex: 1;">
                <span style="font-size: 11.5px; font-weight: 800; color: #68665D; text-transform: uppercase;">Filter By:</span>

                {{-- Class Section Filter --}}
                <select name="section_id" onchange="document.getElementById('filterForm').submit()" class="form-control" style="width: auto; padding: 7px 12px; font-size: 12.5px;">
                    <option value="">All Class Sections</option>
                    @foreach($classSections as $sec)
                        <option value="{{ $sec->id }}" {{ $selectedSectionId == $sec->id ? 'selected' : '' }}>
                            {{ $sec->instituteClass?->custom_name ?? 'Class' }} - {{ $sec->section_name }}
                        </option>
                    @endforeach
                </select>

                {{-- Subject Filter --}}
                <select name="subject_id" onchange="document.getElementById('filterForm').submit()" class="form-control" style="width: auto; padding: 7px 12px; font-size: 12.5px;">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ $selectedSubjectId == $sub->id ? 'selected' : '' }}>
                            {{ $sub->subject_name }}
                        </option>
                    @endforeach
                </select>

                {{-- Type Filter --}}
                <select name="type" onchange="document.getElementById('filterForm').submit()" class="form-control" style="width: auto; padding: 7px 12px; font-size: 12.5px;">
                    <option value="all" {{ $selectedType === 'all' || empty($selectedType) ? 'selected' : '' }}>All Entry Types</option>
                    <option value="homework" {{ $selectedType === 'homework' ? 'selected' : '' }}>📚 Homework</option>
                    <option value="test" {{ $selectedType === 'test' ? 'selected' : '' }}>📝 Tests &amp; Quizzes</option>
                    <option value="assignment" {{ $selectedType === 'assignment' ? 'selected' : '' }}>📋 Assignments</option>
                    <option value="announcement" {{ $selectedType === 'announcement' ? 'selected' : '' }}>📢 Announcements</option>
                    <option value="note" {{ $selectedType === 'note' ? 'selected' : '' }}>💡 Class Notes</option>
                </select>
            </div>

            @if($selectedSectionId || $selectedSubjectId || ($selectedType && $selectedType !== 'all'))
                <a href="{{ route('teacher.diary.index') }}" style="font-size: 12px; font-weight: 700; color: #A2412C; text-decoration: underline;">
                    ✕ Clear Filters
                </a>
            @endif
        </form>
    </div>

    {{-- Diary Entries Timeline Feed --}}
    <div class="diary-glass-card" style="padding: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h2 style="font-family: 'Manrope', sans-serif; font-size: 16px; font-weight: 800; color: #1B1A17; margin: 0;">
                Published Diary Feed ({{ $diaries->total() }})
            </h2>
            <span style="font-size: 12px; color: #68665D; font-weight: 600;">
                Showing latest entries visible to enrolled students
            </span>
        </div>

        @if($diaries->isEmpty())
            <div style="padding: 48px 24px; text-align: center; background: #FFFFFF; border: 1.5px dashed #E1DFD7; border-radius: 14px;">
                <div style="font-size: 38px; margin-bottom: 12px;">📝</div>
                <h3 style="font-size: 16px; font-weight: 800; color: #1B1A17; margin: 0 0 6px;">No Daily Diary Entries Yet</h3>
                <p style="font-size: 13px; color: #68665D; margin: 0 0 18px; max-width: 440px; margin-left: auto; margin-right: auto;">
                    You haven't pushed any homework or test notifications yet. Click the button below to publish your first entry.
                </p>
                <button type="button" onclick="openNewDiaryModal()" class="btn btn-primary" style="background: #D48A2E; color: #1A1200; font-weight: 800; padding: 9px 20px; border-radius: 10px; border: none; cursor: pointer;">
                    ✍️ Push First Diary Entry
                </button>
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach($diaries as $diary)
                    @php
                        $typeClass = match(strtolower($diary->entry_type)) {
                            'test', 'test_alert', 'quiz' => 'type-pill-test',
                            'assignment' => 'type-pill-assignment',
                            'announcement' => 'type-pill-announcement',
                            'note', 'classwork' => 'type-pill-note',
                            default => 'type-pill-homework'
                        };
                        $typeLabel = match(strtolower($diary->entry_type)) {
                            'test', 'test_alert', 'quiz' => '📝 Test / Quiz',
                            'assignment' => '📋 Assignment',
                            'announcement' => '📢 Announcement',
                            'note', 'classwork' => '💡 Class Notes',
                            default => '📚 Homework'
                        };
                    @endphp

                    <div class="diary-entry-card">
                        {{-- Card Header: Meta Badges & Actions --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; border-bottom: 1px solid #F2EFEB; padding-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span class="stat-badge {{ $typeClass }}">
                                    {{ $typeLabel }}
                                </span>
                                <span style="font-size: 12px; font-weight: 800; color: #1B1A17; background: #F2EFEB; padding: 4px 10px; border-radius: 7px; border: 1px solid #E1DFD7;">
                                    🏷️ {{ $diary->subject?->subject_name ?? 'Subject' }}
                                </span>
                                <span style="font-size: 12px; font-weight: 700; color: #68665D;">
                                    🏫 {{ $diary->classSection?->instituteClass?->custom_name ?? 'Class' }} - {{ $diary->classSection?->section_name }}
                                </span>
                            </div>

                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span style="font-size: 11.5px; font-weight: 700; color: #8A877E;">
                                    📅 Assigned: {{ $diary->assigned_date ? $diary->assigned_date->format('D, M d, Y') : 'Today' }}
                                </span>
                                <form method="POST" action="{{ route('teacher.diary.destroy', $diary->id) }}" onsubmit="return confirm('⚠️ Are you sure you want to remove this diary entry?');" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: transparent; border: none; color: #A2412C; font-size: 12px; font-weight: 700; cursor: pointer; padding: 2px 6px;" title="Delete Entry">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Entry Content --}}
                        <div>
                            <h3 style="font-family: 'Manrope', sans-serif; font-size: 16px; font-weight: 800; color: #1B1A17; margin: 0 0 8px;">
                                {{ $diary->title }}
                            </h3>
                            <div style="font-size: 13.5px; color: #3A3935; line-height: 1.6; white-space: pre-line;">
                                {{ $diary->content }}
                            </div>
                        </div>

                        {{-- Card Footer: Due Date & Attached Document (Only if present) --}}
                        @if($diary->due_date || $diary->reminder_morning || $diary->hasAttachment())
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; padding-top: 8px; margin-top: 2px; border-top: 1px dashed #E5E3DC;">
                            <div>
                                @if($diary->due_date)
                                    <span style="font-size: 11px; font-weight: 800; color: #A2412C; background: #F6E4E1; padding: 2px 8px; border-radius: 5px; border: 1px solid #EAC8C1;">
                                        ⏰ {{ $diary->isTest() ? 'Test Date' : 'Due Date' }}: {{ $diary->due_date->format('M d, Y') }}
                                    </span>
                                @endif
                                @if($diary->reminder_morning)
                                    <span style="font-size: 10.5px; font-weight: 700; color: #2E6E42; background: #E3EFE2; padding: 2px 7px; border-radius: 5px; margin-left: 4px;">
                                        🔔 Morning Reminder Active
                                    </span>
                                @endif
                            </div>

                            @if($diary->hasAttachment())
                                <a href="{{ $diary->attachment_url }}" target="_blank" download style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; background: #FDFBF7; border: 1px solid #D48A2E; border-radius: 7px; font-size: 11px; font-weight: 800; color: #8A5A10; text-decoration: none; transition: all 0.15s ease;">
                                    <span>📎</span>
                                    <span>{{ $diary->file_name ?? 'Download Attachment' }}</span>
                                    @if($diary->formatted_file_size)
                                        <span style="color: #68665D; font-weight: 600;">({{ $diary->formatted_file_size }})</span>
                                    @endif
                                    <span>⬇️</span>
                                </a>
                            @endif
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div style="margin-top: 24px;">
                {{ $diaries->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ── PUBLISH NEW DIARY ENTRY MODAL ── --}}
<div id="newDiaryModal" class="diary-modal-overlay" style="display: none;">
    <div class="diary-modal-card">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #E1DFD7; padding-bottom: 14px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 22px;">✍️</span>
                <div>
                    <h3 style="font-family: 'Manrope', sans-serif; font-size: 17px; font-weight: 800; color: #1B1A17; margin: 0;">
                        Publish Daily Diary Update
                    </h3>
                    <p style="font-size: 12px; color: #68665D; margin: 2px 0 0;">
                        Notify students with homework, tests, or class notes.
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeNewDiaryModal()" style="background: none; border: none; font-size: 20px; color: #A19E92; cursor: pointer; padding: 4px;">✕</button>
        </div>

        <form method="POST" action="{{ route('teacher.diary.store') }}" enctype="multipart/form-data" id="publishDiaryForm" onsubmit="return validateDiaryForm()">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                {{-- Class Section --}}
                <div>
                    <label class="form-label" for="modal_class_section_id">Class Section *</label>
                    <select name="class_section_id" id="modal_class_section_id" required class="form-control" onchange="filterSubjectsForSelectedSection(this.value)">
                        <option value="">-- Select Class --</option>
                        @foreach($classSections as $sec)
                            <option value="{{ $sec->id }}" {{ old('class_section_id') == $sec->id ? 'selected' : '' }}>
                                {{ $sec->instituteClass?->custom_name ?? 'Class' }} - {{ $sec->section_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject --}}
                <div>
                    <label class="form-label" for="modal_subject_id">Subject *</label>
                    <select name="subject_id" id="modal_subject_id" required class="form-control">
                        <option value="">-- Select Subject --</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ old('subject_id') == $sub->id ? 'selected' : '' }}>
                                {{ $sub->subject_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Entry Type --}}
            <div style="margin-bottom: 16px;">
                <label class="form-label">Category / Type *</label>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; padding: 9px 14px; border: 1.5px solid #E1DFD7; border-radius: 10px; cursor: pointer; background: #FFFFFF; font-size: 13px; font-weight: 700; color: #1B1A17;">
                        <input type="radio" name="entry_type" value="homework" checked style="accent-color: #D48A2E;">
                        <span>📚 Homework</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 9px 14px; border: 1.5px solid #E1DFD7; border-radius: 10px; cursor: pointer; background: #FFFFFF; font-size: 13px; font-weight: 700; color: #1B1A17;">
                        <input type="radio" name="entry_type" value="test" style="accent-color: #A2412C;">
                        <span>📝 Test / Quiz</span>
                    </label>
                </div>
            </div>

            {{-- Title --}}
            <div style="margin-bottom: 16px;">
                <label class="form-label" for="diary_title">Title / Topic *</label>
                <input type="text" name="title" id="diary_title" required class="form-control" placeholder="e.g. Chapter 3: Exercise 3.1 Q 1 to 5" value="{{ old('title') }}">
            </div>

            {{-- Text Instructions --}}
            <div style="margin-bottom: 16px;">
                <label class="form-label" for="diary_content">Instructions &amp; Details *</label>
                <textarea name="content" id="diary_content" rows="4" required class="form-control" placeholder="Write specific instructions for students, problem numbers, or exam syllabus...">{{ old('content') }}</textarea>
            </div>

            {{-- Dates & Morning Reminder --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label class="form-label" for="assigned_date">Assigned Date</label>
                    <input type="date" name="assigned_date" id="assigned_date" class="form-control" value="{{ now()->toDateString() }}">
                </div>
                <div>
                    <label class="form-label" for="due_date">Due / Test Date (Optional)</label>
                    <input type="date" name="due_date" id="due_date" class="form-control" value="{{ old('due_date') }}">
                </div>
            </div>

            <div style="margin-bottom: 16px; background: #FFFFFF; border: 1px solid #E1DFD7; border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="reminder_morning" id="reminder_morning" value="1" style="width: 18px; height: 18px; accent-color: #D48A2E; cursor: pointer;">
                <label for="reminder_morning" style="font-size: 12.5px; font-weight: 700; color: #1B1A17; cursor: pointer; margin: 0;">
                    🔔 Set morning reminder alert on the test / due date
                </label>
            </div>

            {{-- File Attachment (Strictly < 10 MB) --}}
            <div style="margin-bottom: 24px;">
                <label class="form-label">Attachment (PDF, Excel, Word, Image) — Max 10 MB</label>
                <div style="border: 2px dashed #D48A2E; border-radius: 12px; padding: 18px; text-align: center; background: #FDF9F2; cursor: pointer;" onclick="document.getElementById('diary_file_input').click()">
                    <input type="file" name="attachment" id="diary_file_input" style="display: none;" onchange="handleDiaryFileSelect(this)" accept=".pdf,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.png,.jpg,.jpeg,.webp,.txt,.zip">
                    <div id="file_idle_view">
                        <span style="font-size: 26px;">📁</span>
                        <div style="font-size: 13px; font-weight: 800; color: #1B1A17; margin-top: 4px;">Click to Browse or Drop File</div>
                        <div style="font-size: 11.5px; color: #68665D; margin-top: 2px;">Supported: PDF, Excel, Word, PPT, JPG, PNG &bull; Strict 10 MB Limit</div>
                    </div>
                    <div id="file_selected_view" style="display: none; align-items: center; justify-content: center; gap: 10px;">
                        <span style="font-size: 20px;">📄</span>
                        <div style="text-align: left;">
                            <div id="file_selected_name" style="font-size: 13px; font-weight: 800; color: #1B1A17;"></div>
                            <div id="file_selected_size" style="font-size: 11px; font-weight: 700; color: #2E6E42;"></div>
                        </div>
                        <button type="button" onclick="event.stopPropagation(); clearDiaryFile();" style="border: none; background: #F6E4E1; color: #A2412C; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; cursor: pointer; margin-left: 10px;">✕ Remove</button>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; border-top: 1px solid #E1DFD7; padding-top: 16px;">
                <button type="button" onclick="closeNewDiaryModal()" style="padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; background: #F2EFEB; border: 1px solid #E1DFD7; color: #68665D; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit" style="padding: 10px 24px; border-radius: 10px; font-size: 13px; font-weight: 800; background: #D48A2E; color: #1A1200; border: none; cursor: pointer; box-shadow: 0 4px 14px rgba(212, 138, 46, 0.3);">
                    🚀 Publish &amp; Push to Students
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openNewDiaryModal() {
        document.getElementById('newDiaryModal').style.display = 'flex';
    }

    function closeNewDiaryModal() {
        document.getElementById('newDiaryModal').style.display = 'none';
    }

    function handleDiaryFileSelect(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const maxBytes = 10 * 1024 * 1024; // 10 MB

            if (file.size > maxBytes) {
                alert('⚠️ File exceeds the 10 MB limit! (' + (file.size / (1024 * 1024)).toFixed(1) + ' MB). Please choose a file smaller than 10 MB.');
                input.value = '';
                return;
            }

            document.getElementById('file_selected_name').textContent = file.name;
            const sizeStr = file.size > 1048576 
                ? (file.size / 1048576).toFixed(2) + ' MB' 
                : (file.size / 1024).toFixed(0) + ' KB';
            document.getElementById('file_selected_size').textContent = '✓ Ready to upload (' + sizeStr + ')';

            document.getElementById('file_idle_view').style.display = 'none';
            document.getElementById('file_selected_view').style.display = 'flex';
        }
    }

    function clearDiaryFile() {
        document.getElementById('diary_file_input').value = '';
        document.getElementById('file_idle_view').style.display = 'block';
        document.getElementById('file_selected_view').style.display = 'none';
    }

    function validateDiaryForm() {
        const fileInput = document.getElementById('diary_file_input');
        if (fileInput.files && fileInput.files[0]) {
            if (fileInput.files[0].size > 10 * 1024 * 1024) {
                alert('⚠️ Cannot publish: File size is greater than 10 MB.');
                return false;
            }
        }
        return true;
    }
</script>
@endsection
