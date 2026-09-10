@extends('global-admin.layouts.app')
@section('breadcrumb', 'Edit: ' . $systemClass->name)
@section('title', 'Edit ' . $systemClass->name)

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Class: {{ $systemClass->name }}</h1>
    <a href="{{ route('global-admin.system-classes.index') }}" class="btn btn-ghost">← Back</a>
</div>

<div class="card" style="max-width:560px">
    <form method="POST" action="{{ route('global-admin.system-classes.update', $systemClass) }}">
        @csrf @method('PUT')
        <div class="form-group">
            <label for="sc_name">Class / Program Name *</label>
            <input id="sc_name" type="text" name="name" value="{{ old('name', $systemClass->name) }}" required />
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
            <label for="sc_code">Short Code *</label>
            <input id="sc_code" type="text" name="short_code" value="{{ old('short_code', $systemClass->short_code) }}" required />
            @error('short_code')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
            <label for="sc_type">Education Type *</label>
            <select id="sc_type" name="education_type" required>
                @foreach($educationTypeLabels as $val => $label)
                <option value="{{ $val }}" {{ old('education_type', $systemClass->education_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="sc_sort">Sort Order</label>
            <input id="sc_sort" type="number" name="sort_order" value="{{ old('sort_order', $systemClass->sort_order) }}" min="0" max="255" />
        </div>
        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" name="is_active" value="1" style="width:auto;accent-color:var(--accent)"
                    {{ old('is_active', $systemClass->is_active) ? 'checked' : '' }}>
                &nbsp; Active
            </label>
        </div>
        <div style="display:flex;gap:12px">
            <button type="submit" class="btn btn-primary">💾 Save</button>
            <a href="{{ route('global-admin.system-classes.index') }}" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>
@endsection
