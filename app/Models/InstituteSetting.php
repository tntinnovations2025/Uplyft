<?php

namespace App\Models;

use App\Models\Scopes\InstituteScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InstituteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'attendance_mode',
        'min_required_attendance_pct',
        'attendance_start_time',
        'attendance_end_time',
        'allow_past_attendance_edits',
        'is_attendance_locked_override',
        'monthly_income_target',
        'monthly_expense_budget',
        'fee_due_day_of_month',
        'late_fee_fine_amount',
        'filer_tax_rate',
        'non_filer_tax_rate',
        'base_admission_fee',
        'currency_symbol',
        'show_payment_details',
        'bank_name',
        'bank_account_title',
        'bank_account_number',
        'bank_iban',
        'onebill_voucher_prefix',
        'notification_email',
        'notification_phone',
        'payment_instructions',
        'passing_percentage',
        'grade_scale_type',
        'monthly_staff_salary_budget',
        'salary_disbursement_day',
        'allowed_absent_days_per_month',
        'faculty_off_days',
        'salary_deduction_type',
        'fixed_absent_deduction_amount',
    ];

    protected $casts = [
        'min_required_attendance_pct' => 'float',
        'allow_past_attendance_edits' => 'boolean',
        'is_attendance_locked_override' => 'boolean',
        'show_payment_details' => 'boolean',
        'bank_account_number' => 'encrypted',
        'bank_iban' => 'encrypted',
        'monthly_income_target' => 'float',
        'monthly_expense_budget' => 'float',
        'fee_due_day_of_month' => 'integer',
        'late_fee_fine_amount' => 'float',
        'filer_tax_rate' => 'float',
        'non_filer_tax_rate' => 'float',
        'base_admission_fee' => 'float',
        'passing_percentage' => 'float',
        'monthly_staff_salary_budget' => 'float',
        'salary_disbursement_day' => 'integer',
        'allowed_absent_days_per_month' => 'integer',
        'fixed_absent_deduction_amount' => 'float',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new InstituteScope);

        static::creating(function ($setting) {
            if (empty($setting->institute_id)) {
                if (Auth::check() && isset(Auth::user()->institute_id)) {
                    $setting->institute_id = Auth::user()->institute_id;
                } elseif (app()->bound('current_institute_id')) {
                    $setting->institute_id = app('current_institute_id');
                }
            }
        });
    }

    protected static array $instanceCache = [];

    public static function clearInstanceCache(): void
    {
        static::$instanceCache = [];
    }

    /**
     * Get or initialize settings for an institute.
     */
    public static function getForInstitute(?int $instituteId = null): self
    {
        $instId = $instituteId ?? (Auth::check() && Auth::user()->institute_id ? Auth::user()->institute_id : 1);

        if (isset(static::$instanceCache[$instId])) {
            return static::$instanceCache[$instId];
        }

        $setting = static::withoutGlobalScopes()->firstOrCreate(
            ['institute_id' => $instId],
            [
                'attendance_mode' => 'subject',
                'min_required_attendance_pct' => 75.00,
                'attendance_start_time' => '08:00',
                'attendance_end_time' => '16:00',
                'allow_past_attendance_edits' => false,
                'is_attendance_locked_override' => false,
                'monthly_income_target' => 350000.00,
                'monthly_expense_budget' => 200000.00,
                'fee_due_day_of_month' => 10,
                'late_fee_fine_amount' => 500.00,
                'filer_tax_rate' => 0.0500,
                'non_filer_tax_rate' => 0.1500,
                'base_admission_fee' => 10000.00,
                'currency_symbol' => 'PKR',
                'show_payment_details' => true,
                'bank_name' => 'Habib Bank Limited (HBL)',
                'bank_account_title' => 'Apex Educational Institute',
                'bank_iban' => 'PK75 HABB 0001 2345 6789 0123',
                'onebill_voucher_prefix' => '100',
                'payment_instructions' => 'Fees can be deposited at any partner bank branch nationwide or paid online via Mobile Banking Apps, ATM, Easypaisa, or JazzCash using your 1Bill Consumer Number.',
                'passing_percentage' => 40.00,
                'grade_scale_type' => 'percentage',
                'monthly_staff_salary_budget' => 250000.00,
                'salary_disbursement_day' => 1,
                'allowed_absent_days_per_month' => 2,
                'faculty_off_days' => 'Sunday',
                'salary_deduction_type' => 'pro_rata',
                'fixed_absent_deduction_amount' => 0.00,
            ]
        );

        static::$instanceCache[$instId] = $setting;
        return $setting;
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
