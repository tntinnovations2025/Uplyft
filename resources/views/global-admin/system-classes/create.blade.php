@extends('global-admin.layouts.app')
@section('breadcrumb', 'Add System Class')
@section('title', 'Add System Class')

@section('content')
<div class="page-header">
    <h1 class="page-title">Add System Class / Program</h1>
    <a href="{{ route('global-admin.system-classes.index') }}" class="btn btn-ghost">← Back</a>
</div>

<div class="card" style="max-width:560px">
    <form method="POST" action="{{ route('global-admin.system-classes.store') }}">
        @csrf
        <div class="form-group">
            <label for="sc_name">Class / Program Name *</label>
            <input id="sc_name" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Grade 10, ACCA - FA1, 1st Year" required />
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
            <label for="sc_code">Short Code * (unique identifier)</label>
            <input id="sc_code" type="text" name="short_code" value="{{ old('short_code') }}" placeholder="e.g. G10, ACCA-FA1" required />
            @error('short_code')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
            <label for="sc_type">Education Type *</label>
            <select id="sc_type" name="education_type" required>
                @foreach($educationTypeLabels as $val => $label)
                <option value="{{ $val }}" {{ old('education_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="sc_sort">Display Sort Order</label>
            <input id="sc_sort" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="255" />
        </div>
        <div style="display:flex;gap:12px">
            <button type="submit" class="btn btn-primary">✅ Add Class</button>
            <a href="{{ route('global-admin.system-classes.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
