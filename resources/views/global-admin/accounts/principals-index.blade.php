@extends('global-admin.layouts.app')

@section('title', 'Principal Accounts')
@section('breadcrumb', 'Principal Accounts')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-family:'Outfit',sans-serif;font-size:26px;font-weight:800;color:#0f172a;letter-spacing:-0.5px">👤 Principal Accounts</h1>
        <p style="color:#64748b;font-size:14px;margin-top:4px;font-weight:500">
            Manage principal accounts, login credentials, and institutional campus assignments.
        </p>
    </div>
    <a href="{{ route('global-admin.accounts.principals.create') }}" class="btn btn-primary">
        <span>➕</span> New Principal Account
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">👤 Registered Principals</div>
        <span class="badge badge-purple" style="font-size:11px;font-weight:700">{{ $principals->total() }} Total</span>
    </div>

    <div style="overflow-x:hidden;width:100%">
        <table class="data-table" style="width:100%">
            <thead>
                <tr>
                    <th style="width:20%">Name</th>
                    <th style="width:22%">Email</th>
                    <th style="width:12%">Identifier</th>
                    <th style="width:20%">Institute</th>
                    <th style="width:11%">Created</th>
                    <th style="width:15%;text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($principals as $principal)
                    <tr>
                        <td>
                            <div style="font-weight:800;color:#0f172a;font-size:14px">{{ $principal->name }}</div>
                        </td>
                        <td style="color:#64748b;font-weight:600;font-size:13px">{{ $principal->email ?? '—' }}</td>
                        <td>
                            <span class="badge badge-purple" style="font-size:10px">{{ $principal->identifier ?? '—' }}</span>
                        </td>
                        <td>
                            <div style="font-weight:700;color:#0f172a;font-size:13px">{{ $principal->institute->name ?? '—' }}</div>
                        </td>
                        <td style="color:#64748b;font-size:12.5px;font-weight:500">{{ $principal->created_at->format('M d, Y') }}</td>
                        <td style="text-align:right">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:5px;flex-wrap:wrap">
                                <button type="button" 
                                        onclick="openRoleModal('{{ $principal->id }}', '{{ addslashes($principal->name) }}', '{{ addslashes($principal->email) }}', '{{ $principal->role }}')"
                                        class="btn btn-secondary btn-sm"
                                        style="padding:5px 10px;border-radius:8px;font-size:11.5px;font-weight:700;color:#0284c7;border:1px solid #bae6fd;background:#f0f9ff;white-space:nowrap"
                                        title="Change User Role (Principal, Teacher, Student, Staff)">
                                    🔄 Role
                                </button>
                                <button type="button" 
                                        onclick="openPasswordModal('{{ $principal->id }}', '{{ addslashes($principal->name) }}', '{{ addslashes($principal->email) }}')"
                                        class="btn btn-secondary btn-sm"
                                        style="padding:5px 10px;border-radius:8px;font-size:11.5px;font-weight:700;color:#4f46e5;border:1px solid #c7d2fe;background:#eef2ff;white-space:nowrap">
                                    🔑 Password
                                </button>
                                <form method="POST" action="{{ route('global-admin.accounts.principals.destroy', $principal) }}"
                                      onsubmit="return false;"
                                      style="display:inline-block;margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" 
                                            onclick="openDeletePrincipalModal(this.form, '{{ addslashes($principal->name) }}', '{{ addslashes($principal->email) }}', '{{ addslashes($principal->institute->name ?? 'this institute') }}')"
                                            class="btn btn-danger btn-sm"
                                            style="padding:5px 10px;border-radius:8px;font-size:11.5px;font-weight:700;background:linear-gradient(135deg,#ef4444,#dc2626);border:none;color:#fff;white-space:nowrap;display:inline-flex;align-items:center;gap:4px"
                                            title="Permanently delete principal credentials">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#64748b;padding:36px;font-weight:500">
                        No principals registered yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $principals->links() }}
    </div>
</div>

{{-- ── Global Admin Principal Password Change Modal (Apple Liquid Glass) ── --}}
<div id="changePasswordModal" class="apple-liquid-glass-overlay" style="display:none;">
    <div class="apple-liquid-glass-card" style="max-width:490px;width:92%;padding:28px 32px;">
        
        {{-- Modal Top Specular Highlight --}}
        <div style="position:absolute;top:0;left:10%;right:10%;height:1px;background:linear-gradient(90deg,transparent,rgba(255,255,255,0.9),transparent)"></div>

        <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(226,232,240,0.7);padding-bottom:16px;margin-bottom:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#eef2ff 0%,#e0e7ff 100%);color:#4f46e5;border:1px solid rgba(199,210,254,0.6);display:flex;align-items:center;justify-content:center;font-size:19px;box-shadow:0 2px 8px rgba(79,70,229,0.12)">
                    🔑
                </div>
                <div>
                    <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.4px">Override Principal Password</h3>
                    <p id="modalPrincipalSubtitle" style="font-size:12px;color:#64748b;margin-top:2px;font-weight:500"></p>
                </div>
            </div>
            <button type="button" onclick="closePasswordModal()" class="apple-liquid-close-btn">✕</button>
        </div>

        <form id="changePasswordForm" method="POST" action="">
            @csrf
            
            <div style="display:flex;flex-direction:column;gap:16px">
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                        <label for="new_password" style="font-size:12px;font-weight:800;color:#334155;letter-spacing:0.3px">NEW PASSWORD *</label>
                        <button type="button" onclick="generateRandomPass()" style="font-size:11.5px;font-weight:800;color:#4f46e5;background:rgba(238,242,255,0.7);border:1px solid #c7d2fe;padding:2px 8px;border-radius:6px;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                            🎲 Auto-Generate
                        </button>
                    </div>
                    <input type="text" id="new_password" name="password" required minlength="6" class="apple-liquid-input" placeholder="e.g. PrincipalPass2026!#" style="width:100%;font-family:monospace;font-size:14px;font-weight:600" />
                </div>

                <div>
                    <label for="new_password_confirmation" style="display:block;font-size:12px;font-weight:800;color:#334155;margin-bottom:6px;letter-spacing:0.3px">CONFIRM NEW PASSWORD *</label>
                    <input type="text" id="new_password_confirmation" name="password_confirmation" required minlength="6" class="apple-liquid-input" placeholder="Re-type new password" style="width:100%;font-family:monospace;font-size:14px;font-weight:600" />
                </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px;margin-top:24px;border-top:1px solid rgba(226,232,240,0.7);padding-top:18px">
                <button type="button" onclick="closePasswordModal()" class="apple-liquid-btn-cancel">
                    Cancel
                </button>
                <button type="submit" class="apple-liquid-btn-primary">
                    <span>💾</span> Save New Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Global Admin Role Change Modal ===== -->
<div id="roleModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);z-index:10000;align-items:center;justify-content:center;backdrop-filter:blur(4px)">
    <div style="background:#fff;border-radius:18px;width:100%;max-width:440px;padding:26px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.15);border:1px solid #e2e8f0">
        <h3 style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:#0f172a;margin:0 0 4px">🔄 Change Account Role</h3>
        <p style="font-size:13px;color:#64748b;margin:0 0 18px" id="roleModalSub">Modify system access role.</p>
        <form id="roleModalForm" method="POST" action="">
            @csrf
            <div style="margin-bottom:18px">
                <label style="font-size:12px;font-weight:800;color:#334155;display:block;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px">Select Target Role *</label>
                <select name="role" id="roleSelect" style="width:100%;padding:11px 14px;border-radius:10px;border:1.5px solid #cbd5e1;font-weight:700;color:#0f172a;outline:none;cursor:pointer">
                    <option value="principal">👔 Executive Campus Principal (Principal Portal)</option>
                    <option value="teacher">👨‍🏫 Faculty Teacher (Academic LMS Portal)</option>
                    <option value="student">🎓 Student (Student Workspace)</option>
                    <option value="staff">🛡️ Administrative Staff</option>
                </select>
            </div>
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px">
                <button type="button" onclick="closeRoleModal()" class="apple-liquid-btn-cancel" style="padding:9px 16px;border-radius:8px">Cancel</button>
                <button type="submit" class="apple-liquid-btn-primary" style="padding:9px 20px;background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff;border:none;font-weight:800;border-radius:8px">💾 Save New Role</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Global Admin Principal Delete Confirmation Modal (Classy Apple Liquid Glass Centered) ── --}}
<div id="deletePrincipalModal" class="apple-liquid-glass-overlay" style="display:none;">
    <div class="apple-liquid-glass-card" style="max-width:500px;width:92%;padding:28px 32px;text-align:left">
        
        {{-- Modal Top Red Highlight --}}
        <div style="position:absolute;top:0;left:10%;right:10%;height:2px;background:linear-gradient(90deg,transparent,#ef4444,transparent)"></div>

        <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:18px">
            <div style="width:46px;height:46px;border-radius:14px;background:#fef2f2;color:#ef4444;border:1px solid #fecaca;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;box-shadow:0 4px 12px rgba(239,68,68,0.15)">
                ⚠️
            </div>
            <div>
                <h3 style="font-family:'Outfit',sans-serif;font-size:19px;font-weight:800;color:#0f172a;margin:0;letter-spacing:-0.4px">Delete Principal Credentials</h3>
                <p style="font-size:12.5px;color:#64748b;margin-top:2px;font-weight:500">This will unlink portal access permanently.</p>
            </div>
        </div>

        <div style="background:rgba(254,242,242,0.7);border:1px solid #fecaca;border-radius:14px;padding:16px;margin-bottom:24px">
            <div style="font-size:13.5px;color:#991b1b;line-height:1.55;font-weight:600">
                WARNING: Are you sure you want to permanently delete credentials for <strong id="deletePrincipalName" style="color:#7f1d1d"></strong>?
            </div>
            <div style="font-size:12px;color:#b91c1c;margin-top:8px;line-height:1.4">
                The principal portal for <strong id="deletePrincipalInstitute" style="color:#7f1d1d"></strong> will no longer be linked to any database account.
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px">
            <button type="button" onclick="closeDeletePrincipalModal()" class="apple-liquid-btn-cancel" style="padding:10px 20px;border-radius:12px;font-weight:700;cursor:pointer">
                Cancel
            </button>
            <button type="button" onclick="submitDeletePrincipalForm()" class="apple-liquid-btn-primary" style="padding:10px 22px;border-radius:12px;background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);color:#fff;border:none;font-weight:800;box-shadow:0 4px 14px rgba(220,38,38,0.35);cursor:pointer">
                🗑️ Confirm Delete
            </button>
        </div>
    </div>
</div>

<script>
    let activeDeleteForm = null;

    function openDeletePrincipalModal(formEl, name, email, instituteName) {
        activeDeleteForm = formEl;
        document.getElementById('deletePrincipalName').textContent = `${name} (${email})`;
        document.getElementById('deletePrincipalInstitute').textContent = instituteName;
        document.getElementById('deletePrincipalModal').style.display = 'flex';
    }

    function closeDeletePrincipalModal() {
        activeDeleteForm = null;
        document.getElementById('deletePrincipalModal').style.display = 'none';
    }

    function submitDeletePrincipalForm() {
        if (activeDeleteForm) {
            const form = activeDeleteForm;
            activeDeleteForm = null;
            form.submit();
        }
    }

    function openPasswordModal(id, name, email) {
        const form = document.getElementById('changePasswordForm');
        form.action = `/global-admin/accounts/principals/${id}/change-password`;
        document.getElementById('modalPrincipalSubtitle').textContent = `${name} (${email})`;
        document.getElementById('new_password').value = '';
        document.getElementById('new_password_confirmation').value = '';
        document.getElementById('changePasswordModal').style.display = 'flex';
    }

    function closePasswordModal() {
        document.getElementById('changePasswordModal').style.display = 'none';
    }

    function openRoleModal(id, name, email, currentRole) {
        document.getElementById('roleModalSub').textContent = 'Modify system access role for ' + name + ' (' + email + ').';
        document.getElementById('roleSelect').value = currentRole;
        document.getElementById('roleModalForm').action = '/global-admin/accounts/users/' + id + '/update-role';
        document.getElementById('roleModal').style.display = 'flex';
    }

    function closeRoleModal() {
        document.getElementById('roleModal').style.display = 'none';
    }

    function generateRandomPass() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%&*';
        let pass = '';
        for (let i = 0; i < 12; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('new_password').value = pass;
        document.getElementById('new_password_confirmation').value = pass;
    }
</script>
@endsection
