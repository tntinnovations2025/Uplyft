@extends('principal.layouts.app')
@section('title', 'Section Allocation')
@section('breadcrumb', 'Section Allocation')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">Section Allocation</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Partition classes or professional modules into manageable student subsections (e.g., 10-A, 10-B, FA1-Sec 1).
        </p>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
    <!-- Class Sections Roster -->
    <div style="display:flex;flex-direction:column;gap:20px">
        @if($classes->count() > 0)
            @foreach($classes as $class)
            <div class="card" style="margin-bottom:0">
                <div class="card-header">
                    <div class="card-title">Class: {{ $class->custom_name }}</div>
                    <span class="badge badge-purple">{{ $class->sections->count() }} Sections</span>
                </div>

                @if($class->sections->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Student Capacity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($class->sections as $section)
                        <tr>
                            <td><strong>{{ $section->section_name }}</strong></td>
                            <td>{{ $section->capacity }} Seats</td>
                            <td>
                                <form method="POST" action="{{ route('principal.sections.destroy', $section) }}" onsubmit="return confirm('Remove section?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p style="color:var(--text-muted);font-size:13px">No sections created for this class yet.</p>
                @endif
            </div>
            @endforeach
        @else
        <div class="card">
            <p style="color:var(--text-muted);font-size:14px">Please provision classes first in order to allocate sections.</p>
            <a href="{{ route('principal.classes-subjects.index') }}" class="btn btn-primary btn-sm" style="margin-top:12px">Provision Classes &rarr;</a>
        </div>
        @endif
    </div>

    <!-- Create Section Form -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">➕ Create New Section</div>
            </div>

            <form method="POST" action="{{ route('principal.sections.store') }}">
                @csrf

                <div class="form-group">
                    <label for="institute_class_id">Target Class / Module *</label>
                    <select id="institute_class_id" name="institute_class_id" required>
                        <option value="">-- Select Class --</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->custom_name }}</option>
                        @endforeach
                    </select>
                    @error('institute_class_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="section_name">Section Name *</label>
                    <input id="section_name" type="text" name="section_name" placeholder="e.g. 10-A, Section 1" required value="{{ old('section_name') }}">
                    @error('section_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label for="capacity">Student Capacity *</label>
                    <input id="capacity" type="number" name="capacity" value="40" min="1" max="200" required>
                    @error('capacity')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%">Create Section</button>
            </form>
        </div>
    </div>
</div>
@endsection
