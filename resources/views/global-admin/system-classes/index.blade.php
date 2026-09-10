@extends('global-admin.layouts.app')
@section('breadcrumb', 'System Classes')
@section('title', 'System Classes')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">System Classes &amp; Programs</h1>
        <p style="color:#64748b;font-size:14px;margin-top:4px;font-weight:500">
            Configure global class templates, short codes, and educational stream mappings.
        </p>
    </div>
    <a href="{{ route('global-admin.system-classes.create') }}" class="btn btn-primary">➕ Add Class</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Class Name</th>
                <th>Short Code</th>
                <th>Education Type</th>
                <th>Sort</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($classes as $cls)
            <tr style="{{ $cls->trashed() ? 'opacity:.45;' : '' }}">
                <td style="color:#64748b;font-weight:600">{{ $cls->id }}</td>
                <td style="font-weight:800;color:#0f172a;font-size:14.5px">{{ $cls->name }}</td>
                <td><code class="badge badge-cyan">{{ $cls->short_code }}</code></td>
                <td style="color:#64748b;font-size:13px;font-weight:600">{{ $educationTypeLabels[$cls->education_type] ?? $cls->education_type }}</td>
                <td style="color:#64748b;font-weight:600">{{ $cls->sort_order }}</td>
                <td>
                    @if($cls->trashed())
                        <span class="badge badge-red">Deleted</span>
                    @elseif($cls->is_active)
                        <span class="badge badge-green">Active</span>
                    @else
                        <span class="badge badge-red">Inactive</span>
                    @endif
                </td>
                <td style="display:flex;gap:6px">
                    @if(!$cls->trashed())
                        <a href="{{ route('global-admin.system-classes.edit', $cls) }}" class="btn btn-secondary btn-sm">Edit</a>
                        <form method="POST" action="{{ route('global-admin.system-classes.destroy', $cls) }}"
                              onsubmit="return confirm('Deactivate this class?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                    @else
                        <span style="color:#64748b;font-size:13px;font-weight:500">Deleted</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;color:#64748b;padding:40px;font-weight:500">
                    No classes defined yet. <a href="{{ route('global-admin.system-classes.create') }}" style="color:#0284c7;font-weight:700">Add one →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
