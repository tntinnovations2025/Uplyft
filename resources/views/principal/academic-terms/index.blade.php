@extends('principal.layouts.app')
@section('title', 'Academic Terms Lifecycle')
@section('breadcrumb', 'Academic Terms')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700">Academic Terms Lifecycle</h1>
        <p style="color:var(--text-muted);font-size:14px;margin-top:2px">
            Define academic sessions (e.g., 2025–2026, Fall 2026). Exactly ONE term can be marked Active at a time.
        </p>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
    <!-- Terms List -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Academic Terms Roster</div>
        </div>

        @if($terms->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Term Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($terms as $term)
                <tr>
                    <td><strong>{{ $term->name }}</strong></td>
                    <td>{{ $term->start_date->format('d M Y') }}</td>
                    <td>{{ $term->end_date->format('d M Y') }}</td>
                    <td>
                        @if($term->is_active)
                            <span class="badge badge-green">Active Session</span>
                        @else
                            <span class="badge badge-yellow">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:8px">
                            @if(!$term->is_active)
                                <form method="POST" action="{{ route('principal.academic-terms.set-active', $term) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">⚡ Set Active</button>
                                </form>
                                <form method="POST" action="{{ route('principal.academic-terms.destroy', $term) }}" onsubmit="return confirm('Delete this term?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            @else
                                <span style="font-size:12px;color:var(--success);font-weight:600">Current Active</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="color:var(--text-muted);font-size:14px">No academic terms created yet. Create your first term using the form on the right.</p>
        @endif
    </div>

    <!-- Create Term Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">➕ Create New Term</div>
        </div>

        <form method="POST" action="{{ route('principal.academic-terms.store') }}">
            @csrf

            <div class="form-group">
                <label for="name">Term Name *</label>
                <input id="name" type="text" name="name" placeholder="e.g. 2025-2026, Fall 2026" required value="{{ old('name') }}">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="start_date">Start Date *</label>
                <input id="start_date" type="date" name="start_date" required value="{{ old('start_date') }}">
                @error('start_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="end_date">End Date *</label>
                <input id="end_date" type="date" name="end_date" required value="{{ old('end_date') }}">
                @error('end_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="is_active" value="1" style="width:16px;height:16px">
                    <span>Mark as Active Session immediately</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%">Create Academic Term</button>
        </form>
    </div>
</div>
@endsection
