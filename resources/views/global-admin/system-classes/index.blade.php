@extends('global-admin.layouts.app')
@section('breadcrumb', 'System Classes')
@section('title', 'System Classes')

@section('content')
<div class="page-header">
    <h1 class="page-title">System Classes & Programs</h1>
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
            <tr style="{{ $cls->trashed() ? 'opacity:.45' : '' }}">
                <td style="color:var(--text-muted)">{{ $cls->id }}</td>
                <td style="font-weight:600">{{ $cls->name }}</td>
                <td><code class="badge badge-blue">{{ $cls->short_code }}</code></td>
                <td style="color:var(--text-muted);font-size:13px">{{ $educationTypeLabels[$cls->education_type] ?? $cls->education_type }}</td>
                <td style="color:var(--text-muted)">{{ $cls->sort_order }}</td>
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
                        <a href="{{ route('global-admin.system-classes.edit', $cls) }}" class="btn btn-ghost btn-sm">Edit</a>
                        <form method="POST" action="{{ route('global-admin.system-classes.destroy', $cls) }}"
                              onsubmit="return confirm('Deactivate this class?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Del</button>
                        </form>
                    @else
                        <span style="color:var(--text-muted);font-size:13px">Deleted</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px">
                    No classes defined yet. <a href="{{ route('global-admin.system-classes.create') }}" style="color:var(--accent2)">Add one →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
