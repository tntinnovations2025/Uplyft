<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\InstituteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstituteSettingController extends Controller
{
    /**
     * Display Institute Configuration, Financial Targets & Attendance Rules.
     */
    public function index(): View
    {
        $user = auth()->user();
        $institute = $user->institute;
        $setting = InstituteSetting::getForInstitute($user->institute_id);

        $staffMembers = \App\Models\User::whereIn('institute_id', $user->authorizedCampusIds() ?? [])
            ->whereIn('role', [\App\Models\User::ROLE_TEACHER, \App\Models\User::ROLE_PRINCIPAL])
            ->with(['teacherProfile'])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('principal.settings.index', compact('setting', 'institute', 'staffMembers'));
    }

    /**
     * Update Institute Configuration, Financial Targets & Attendance Rules.
     */
    public function update(Request $request): RedirectResponse
    {
        $activeTab = $request->input('active_tab', 'all');
        $isAll = $activeTab === 'all';
        $showTab = fn (string $tab): bool => $isAll || $activeTab === $tab;

        $validated = $request->validate([
            // Financial Targets & Budgets
            'monthly_income_target' => ['sometimes', 'numeric', 'min:0'],
            'monthly_expense_budget' => ['sometimes', 'numeric', 'min:0'],
            'fee_due_day_of_month' => ['sometimes', 'integer', 'between:1,31'],
            'late_fee_fine_amount' => ['sometimes', 'numeric', 'min:0'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],

            // Bank Details & Payment Channels (Student Portals)
            'show_payment_details' => ['nullable', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_title' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_iban' => ['nullable', 'string', 'max:255'],
            'onebill_voucher_prefix' => ['nullable', 'string', 'max:50'],
            'payment_instructions' => ['nullable', 'string', 'max:1000'],

            // Notifications & Contact (OTP sender + future SMS bot)
            'notification_email' => ['nullable', 'email', 'max:255'],
            'notification_phone' => ['nullable', 'string', 'max:50'],

            // Attendance Mode & Rules
            'attendance_mode' => ['sometimes', 'string', 'in:subject,daily'],
            'min_required_attendance_pct' => ['sometimes', 'numeric', 'between:10,100'],
            'attendance_start_time' => ['sometimes', 'string'],
            'attendance_end_time' => ['sometimes', 'string'],
            'allow_past_attendance_edits' => ['nullable', 'boolean'],
            'is_attendance_locked_override' => ['nullable', 'boolean'],

            // Academic & Passing Thresholds
            'passing_percentage' => ['sometimes', 'numeric', 'min:0', 'max:1000'],
            'grade_scale_type' => ['nullable', 'string', 'in:percentage,gpa,marks'],

            // Staff Payroll Defaults
            'monthly_staff_salary_budget' => ['nullable', 'numeric', 'min:0'],
            'salary_disbursement_day' => ['nullable', 'integer', 'between:1,31'],
            'allowed_absent_days_per_month' => ['nullable', 'integer', 'between:0,31'],
            'salary_deduction_type' => ['nullable', 'string', 'in:pro_rata,fixed,none'],
            'fixed_absent_deduction_amount' => ['nullable', 'numeric', 'min:0'],

            // Institute Profile (Optional)
            'institute_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $instituteId = $user->institute_id;
        $setting = InstituteSetting::getForInstitute($instituteId);

        $updates = [];

        // ── Financial Targets & Budgets ──
        if ($showTab('financial')) {
            $updates += [
                'monthly_income_target' => (float) ($validated['monthly_income_target'] ?? $setting->monthly_income_target),
                'monthly_expense_budget' => (float) ($validated['monthly_expense_budget'] ?? $setting->monthly_expense_budget),
                'fee_due_day_of_month' => (int) ($validated['fee_due_day_of_month'] ?? $setting->fee_due_day_of_month),
                'late_fee_fine_amount' => (float) ($validated['late_fee_fine_amount'] ?? $setting->late_fee_fine_amount),
                'currency_symbol' => $validated['currency_symbol'] ?? $setting->currency_symbol ?? 'PKR',
            ];
        }

        // ── Bank Details & Payment Channels ──
        if ($showTab('financial') || $showTab('bank')) {
            $updates += [
                'show_payment_details' => $request->has('show_payment_details'),
                'bank_name' => $validated['bank_name'] ?? $setting->bank_name,
                'bank_account_title' => $validated['bank_account_title'] ?? $setting->bank_account_title,
                'bank_account_number' => $validated['bank_account_number'] ?? $setting->bank_account_number,
                'bank_iban' => $validated['bank_iban'] ?? $setting->bank_iban,
                'onebill_voucher_prefix' => $validated['onebill_voucher_prefix'] ?? $setting->onebill_voucher_prefix ?? '100',
                'payment_instructions' => $validated['payment_instructions'] ?? $setting->payment_instructions,
            ];
        }

        // ── Notifications & Contact (OTP sender + future SMS bot) ──
        if ($showTab('financial') || $showTab('bank') || $showTab('notifications')) {
            $updates += [
                'notification_email' => $validated['notification_email'] ?? $setting->notification_email,
                'notification_phone' => $validated['notification_phone'] ?? $setting->notification_phone,
            ];
        }

        // ── Staff Payroll Defaults ──
        if ($showTab('payroll')) {
            $updates += [
                'monthly_staff_salary_budget' => (float) ($validated['monthly_staff_salary_budget'] ?? $setting->monthly_staff_salary_budget ?? 0.00),
                'salary_disbursement_day' => (int) ($validated['salary_disbursement_day'] ?? $setting->salary_disbursement_day ?? 1),
                'allowed_absent_days_per_month' => (int) ($validated['allowed_absent_days_per_month'] ?? $setting->allowed_absent_days_per_month ?? 2),
                'salary_deduction_type' => $validated['salary_deduction_type'] ?? $setting->salary_deduction_type ?? 'pro_rata',
                'fixed_absent_deduction_amount' => (float) ($validated['fixed_absent_deduction_amount'] ?? $setting->fixed_absent_deduction_amount ?? 0.00),
            ];
        }

        // ── Academic & Passing Thresholds ──
        if ($showTab('academic') || $isAll) {
            $updates += [
                'passing_percentage' => (float) ($validated['passing_percentage'] ?? $setting->passing_percentage),
                'grade_scale_type' => $validated['grade_scale_type'] ?? $setting->grade_scale_type ?? 'percentage',
            ];
        }

        // Persist only the groups belonging to the submitted tab(s)
        if (! empty($updates)) {
            $setting->update($updates);
        }

        // Keep AttendanceSetting in sync for legacy compatibility
        if ($request->hasAny(['attendance_mode', 'attendance_start_time', 'attendance_end_time'])) {
            try {
                $attSetting = AttendanceSetting::getForInstitute($instituteId);
                $attSetting->update([
                    'attendance_mode' => $validated['attendance_mode'],
                    'start_time' => $validated['attendance_start_time'],
                    'end_time' => $validated['attendance_end_time'],
                    'allow_past_edits' => $request->has('allow_past_attendance_edits'),
                    'is_locked_override' => $request->has('is_attendance_locked_override'),
                ]);
            } catch (\Throwable $e) {
                // Non-blocking sync
            }
        }

        // Update Institute Model fields & Brand Assets if supplied
        if ($user->institute) {
            $institute = $user->institute;
            $instituteUpdates = [];
            if (!empty($validated['institute_name'])) $instituteUpdates['name'] = $validated['institute_name'];
            if (!empty($validated['contact_email'])) $instituteUpdates['contact_email'] = $validated['contact_email'];
            if (!empty($validated['contact_phone'])) $instituteUpdates['contact_phone'] = $validated['contact_phone'];
            if (!empty($validated['city'])) $instituteUpdates['city'] = $validated['city'];

            // Handle logo remove, cropped base64, or direct file upload
            if ($request->boolean('remove_logo')) {
                if ($institute->logo_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($institute->logo_path);
                }
                if ($institute->icon_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($institute->icon_path);
                }
                $instituteUpdates['logo_path'] = null;
                $instituteUpdates['icon_path'] = null;
            } elseif ($request->filled('cropped_logo') && str_starts_with($request->input('cropped_logo'), 'data:image')) {
                $dataUri = $request->input('cropped_logo');
                if (str_contains($dataUri, ',')) {
                    [$meta, $encoded] = explode(',', $dataUri, 2);
                    $imageData = base64_decode($encoded);
                    if ($imageData !== false) {
                        $filename = 'institute-logos/logo_' . $institute->id . '_' . time() . '.png';
                        if ($institute->logo_path) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($institute->logo_path);
                        }
                        if ($institute->icon_path && $institute->icon_path !== $institute->logo_path) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($institute->icon_path);
                        }
                        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $imageData);
                        $instituteUpdates['logo_path'] = $filename;
                        $instituteUpdates['icon_path'] = $filename;
                    }
                }
            } elseif ($request->hasFile('logo')) {
                $request->validate([
                    'logo' => 'required|image|mimes:jpeg,jpg,png,svg,webp|max:2048',
                ]);
                if ($institute->logo_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($institute->logo_path);
                }
                $instituteUpdates['logo_path'] = $request->file('logo')->store('institute-logos', 'public');
                $instituteUpdates['icon_path'] = $instituteUpdates['logo_path'];
            }

            if (!empty($instituteUpdates)) {
                $institute->update($instituteUpdates);
            }
        }

        return redirect()->back()->with('success', 'Institute Settings, Branding & Attendance Policies updated successfully!');
    }

    /**
     * Update individual staff member salary & leave policy.
     */
    public function updateStaffPayroll(Request $request, \App\Models\User $staff): RedirectResponse
    {
        $authUser = auth()->user();
        if ($staff->institute_id !== $authUser->institute_id) {
            abort(403);
        }

        $validated = $request->validate([
            'basic_salary_pkr' => ['nullable', 'numeric', 'min:0'],
            'allowed_absent_days_per_month' => ['required', 'integer', 'between:0,31'],
            'salary_disbursement_day' => ['required', 'integer', 'between:1,31'],
            'salary_deduction_type' => ['required', 'string', 'in:pro_rata,fixed,none'],
            'fixed_absent_deduction_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $salary = isset($validated['basic_salary_pkr']) ? (float) $validated['basic_salary_pkr'] : null;

        $staff->update([
            'basic_salary_pkr' => $salary,
            'allowed_absent_days_per_month' => (int) $validated['allowed_absent_days_per_month'],
            'salary_disbursement_day' => (int) $validated['salary_disbursement_day'],
            'salary_deduction_type' => $validated['salary_deduction_type'],
            'fixed_absent_deduction_amount' => (float) ($validated['fixed_absent_deduction_amount'] ?? 0.00),
        ]);

        if ($staff->teacherProfile) {
            $staff->teacherProfile->update([
                'basic_salary_pkr' => $salary,
                'allowed_absent_days_per_month' => (int) $validated['allowed_absent_days_per_month'],
                'salary_disbursement_day' => (int) $validated['salary_disbursement_day'],
                'salary_deduction_type' => $validated['salary_deduction_type'],
                'fixed_absent_deduction_amount' => (float) ($validated['fixed_absent_deduction_amount'] ?? 0.00),
            ]);
        }

        return redirect()->back()->with('success', "🎉 Salary & leave allowance settings updated for '{$staff->name}' successfully!");
    }
}
