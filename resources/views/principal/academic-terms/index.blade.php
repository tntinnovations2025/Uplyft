@extends(auth()->check() && auth()->user()->isTeacher() ? 'layouts.app' : 'principal.layouts.app')
@section('title', 'Academic Years & Terms')
@section('breadcrumb', 'Academic Years & Terms')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">🗓️ Academic Years &amp; Terms</h1>
        <p style="color:#64748b;font-size:14px;margin-top:4px;font-weight:500">
            Configure academic sessions, semester terms, and student promotion deadlines across campus.
        </p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.8fr 1fr;gap:24px;margin-bottom:28px">
    <!-- Terms Roster Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title" style="display:flex;align-items:center;gap:10px">
                <span>🗓️ Academic Terms Roster</span>
                <span class="badge badge-purple" style="font-size:11px;font-weight:700">{{ $terms->count() }} Sessions</span>
            </div>
        </div>

        @if($terms->count() > 0)
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Term Name</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Promotion Window</th>
                        <th style="text-align:right">Action &amp; Operations</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($terms as $term)
                    <tr>
                        <td>
                            <strong style="font-size:15px;color:#0f172a">{{ $term->name }}</strong>
                        </td>
                        <td style="font-size:13px;color:#64748b;font-weight:500">
                            {{ $term->start_date->format('d M Y') }} &rarr; {{ $term->end_date->format('d M Y') }}
                        </td>
                        <td>
                            @if($term->is_active)
                                <span class="badge badge-green">● Active Session</span>
                            @else
                                <span class="badge badge-yellow">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('principal.academic-terms.update-deadline', $term) }}" style="display:flex;flex-direction:column;gap:4px">
                                @csrf
                                @method('PUT')
                                @if($term->promotion_deadline)
                                    @if($term->isPromotionWindowOpen())
                                        <span class="badge badge-green" style="font-size:10px">
                                            🎓 Open until {{ $term->promotion_deadline->format('d M Y, h:i A') }}
                                        </span>
                                    @else
                                        <span class="badge badge-yellow" style="font-size:10px">
                                            ⏳ Closed (Expired {{ $term->promotion_deadline->format('d M Y, h:i A') }})
                                        </span>
                                    @endif
                                @else
                                    <span style="font-size:11px;color:#64748b">No deadline set</span>
                                @endif
                                <div style="display:flex;align-items:center;gap:4px;margin-top:2px">
                                    <input type="datetime-local" 
                                           name="promotion_deadline" 
                                           value="{{ $term->promotion_deadline ? $term->promotion_deadline->format('Y-m-d\TH:i') : '' }}"
                                           style="padding:5px 8px;font-size:11px;background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;color:#0f172a;width:160px">
                                    <button type="submit" class="btn btn-ghost btn-sm" style="padding:4px 8px;font-size:10px;color:#059669;border-color:#a7f3d0" title="Save Promotion Deadline">
                                        💾 Save
                                    </button>
                                </div>
                            </form>
                        </td>
                        <td style="text-align:right">
                            <div style="display:inline-flex;gap:8px;align-items:center">
                                @if(!$term->is_active)
                                    <form method="POST" action="{{ route('principal.academic-terms.set-active', $term) }}" style="margin:0">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm" style="padding:6px 12px;font-size:11px">
                                            ⚡ Set Active
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size:12px;color:#059669;font-weight:700;padding:4px 8px;background:#ecfdf5;border-radius:6px;border:1px solid #a7f3d0">
                                        Active
                                    </span>
                                @endif

                                <button type="button" 
                                        onclick="prefillCloneModal({{ $term->id }})" 
                                        class="btn btn-ghost btn-sm" 
                                        style="padding:6px 12px;font-size:11px"
                                        title="Import or clone data from/to this term">
                                    📦 Import / Clone
                                </button>

                                <form method="POST" action="{{ route('principal.academic-terms.destroy', $term) }}" style="margin:0">
                                    @csrf @method('DELETE')
                                    <button type="button" 
                                            onclick="classyConfirmForm(this, 'Delete Academic Term {{ $term->name }}?', 'WARNING: This will permanently delete term classes, sections, subjects, faculty allocations, and timetable slots associated with {{ $term->name }}.', {danger: true, confirmText: 'Yes, Delete Term & Data', icon: '🗑️'})" 
                                            class="btn btn-danger btn-sm" 
                                            style="padding:6px 12px;font-size:11px">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p style="color:#64748b;font-size:14px">No academic terms created yet. Create your first session using the form on the right.</p>
        @endif
    </div>

    <!-- Create Term Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">➕ Create New Academic Term</div>
        </div>

        <form method="POST" action="{{ route('principal.academic-terms.store') }}">
            @csrf

            <div class="form-group">
                <label for="name">Term Name *</label>
                <input id="name" type="text" name="name" placeholder="e.g. 2025-2026, Fall 2026, AUG 2026 - Dec 2027" required value="{{ old('name') }}">
                @error('name')<p class="form-error" style="color:#ef4444;font-size:12px;margin-top:4px">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="start_date">Start Date *</label>
                <input id="start_date" type="date" name="start_date" required value="{{ old('start_date') }}">
                @error('start_date')<p class="form-error" style="color:#ef4444;font-size:12px;margin-top:4px">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="end_date">End Date *</label>
                <input id="end_date" type="date" name="end_date" required value="{{ old('end_date') }}">
                @error('end_date')<p class="form-error" style="color:#ef4444;font-size:12px;margin-top:4px">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="promotion_deadline" style="color:#059669">🎓 Class Promotion Deadline (Date &amp; Time)</label>
                <input id="promotion_deadline" type="datetime-local" name="promotion_deadline" value="{{ old('promotion_deadline') }}">
                <div style="font-size:11px;color:#64748b;margin-top:4px">
                    The "Promote Class" feature will automatically disappear after this date &amp; time.
                </div>
                @error('promotion_deadline')<p class="form-error" style="color:#ef4444;font-size:12px;margin-top:4px">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:8px">
                    <input type="checkbox" name="is_active" value="1" style="width:16px;height:16px;accent-color:#e1306c">
                    <span style="font-size:12px;color:#0f172a;font-weight:600">Set as Active Session immediately</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:10px">Create Academic Term</button>
        </form>
    </div>
</div>

<!-- Term Data Clone & Import Engine Card -->
<div class="card" id="clone-section" style="background:#ffffff;border:1px solid rgba(226,232,240,0.85)">
    <div class="card-header" style="border-bottom:1px solid #e2e8f0">
        <div>
            <div class="card-title" style="color:#9333ea;display:flex;align-items:center;gap:8px">
                <span>📦 Clone &amp; Import Term Data</span>
            </div>
            <p style="font-size:13px;color:#64748b;margin-top:4px;font-weight:500">
                Instantly import entire classes, subjects, faculty allocations, and student rosters from an existing term into a target term without manually re-entering data.
            </p>
        </div>
    </div>

    @if($terms->count() >= 2)
    <form method="POST" action="{{ route('principal.academic-terms.clone') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
            <div class="form-group" style="margin-bottom:0">
                <label for="source_term_id" style="color:#9333ea;font-weight:700">Source Term (Copy Data FROM) *</label>
                <select id="source_term_id" name="source_term_id" required style="border-color:#cbd5e1">
                    <option value="">-- Select Source Term --</option>
                    @foreach($terms as $t)
                        <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->start_date->format('M Y') }} - {{ $t->end_date->format('M Y') }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom:0">
                <label for="target_term_id" style="color:#059669;font-weight:700">Target Term (Import Data INTO) *</label>
                <select id="target_term_id" name="target_term_id" required style="border-color:#cbd5e1">
                    <option value="">-- Select Target Term --</option>
                    @foreach($terms as $t)
                        <option value="{{ $t->id }}" {{ $t->is_active ? 'selected' : '' }}>
                            {{ $t->name }} {{ $t->is_active ? '(Active Session)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="padding:16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:20px">
            <div style="font-size:12px;font-weight:800;color:#0f172a;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px">
                Select Datasets To Import:
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:14px">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                    <input type="checkbox" name="import_classes" value="1" checked style="width:18px;height:18px;accent-color:#e1306c">
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#0f172a">Classes, Sections &amp; Subjects</div>
                        <div style="font-size:11px;color:#64748b">Copies class structure &amp; subject catalog</div>
                    </div>
                </label>

                <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                    <input type="checkbox" name="import_allocations" value="1" checked style="width:18px;height:18px;accent-color:#e1306c">
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#0f172a">Faculty &amp; Staff Allocations</div>
                        <div style="font-size:11px;color:#64748b">Copies teacher subject-section assignments</div>
                    </div>
                </label>

                <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                    <input type="checkbox" name="import_students" value="1" style="width:18px;height:18px;accent-color:#e1306c">
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#0f172a">Enrolled Students Roster</div>
                        <div style="font-size:11px;color:#64748b">Enrolls existing class students into target term</div>
                    </div>
                </label>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button type="submit" 
                    onclick="return classyConfirmForm(this, 'Run Term Data Import?', 'This will copy selected datasets from the source term into the target term.', {confirmText: 'Run Import Now', icon: '🚀'})" 
                    class="btn btn-primary" 
                    style="background:linear-gradient(135deg, #fd1d1d, #e1306c, #833ab4);border:none;box-shadow:0 6px 20px rgba(225,48,108,0.35)">
                🚀 Run Term Data Import
            </button>
        </div>
    </form>
    @else
    <p style="color:#64748b;font-size:13.5px">
        💡 You need at least <strong>2 Academic Terms</strong> configured to perform data cloning. Create a new term above to import data from an existing term.
    </p>
    @endif
</div>

<script>
function prefillCloneModal(sourceTermId) {
    const sourceSelect = document.getElementById('source_term_id');
    if (sourceSelect) {
        sourceSelect.value = sourceTermId;
        const section = document.getElementById('clone-section');
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
    }
}
</script>
@endsection
