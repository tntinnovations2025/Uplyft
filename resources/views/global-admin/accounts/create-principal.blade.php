@extends('global-admin.layouts.app')

@section('title', 'Issue Principal Account')
@section('breadcrumb', 'Issue Principal')

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">👤 Issue Principal Account</div>
                <div class="card-subtitle">Generate primary login credentials and bind to an Institute</div>
            </div>
        </div>

        <form method="POST" action="{{ route('global-admin.accounts.principals.store') }}">
            @csrf

            <!-- Name -->
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Dr. Ahmed Khan" required autofocus />
                @error('name')
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px">{{ $message }}</span>
                @enderror
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="e.g. principal@institute.edu.pk" required />
                @error('email')
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px">{{ $message }}</span>
                @enderror
            </div>

            <!-- Identifier (Optional for Principal) -->
            <div class="form-group">
                <label for="identifier">Employee ID / Custom Identifier (Optional)</label>
                <input id="identifier" type="text" name="identifier" value="{{ old('identifier') }}" placeholder="e.g. PRIN-001, ADM#101" />
                @error('identifier')
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px">{{ $message }}</span>
                @enderror
            </div>

            <!-- Institute -->
            <div class="form-group">
                <label for="institute_id">Assign to Institute *</label>
                <select id="institute_id" name="institute_id" required>
                    <option value="">-- Select Institute --</option>
                    @foreach($institutes as $institute)
                        <option value="{{ $institute->id }}" {{ old('institute_id') == $institute->id ? 'selected' : '' }}>
                            🏫 {{ $institute->name }} ({{ $institute->city ?? 'Main Campus' }})
                        </option>
                    @endforeach
                </select>
                @error('institute_id')
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password *</label>
                <input id="password" type="password" name="password" required placeholder="Enter strong password (min 8 characters)..." />
                <p style="font-size:11px;color:#64748b;margin-top:4px">Must contain uppercase, lowercase, number, and special character.</p>
                @error('password')
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px">{{ $message }}</span>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation">Confirm Password *</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Re-type password to confirm..." />
                @error('password_confirmation')
                    <span style="color:var(--danger);font-size:12px;display:block;margin-top:4px">{{ $message }}</span>
                @enderror
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:28px">
                <a href="{{ route('global-admin.accounts.principals.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <span>Create Principal Account</span>
                    <span>&rarr;</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
