<?php

namespace Database\Seeders;

use App\Models\AccountHead;
use App\Models\FinancialTransaction;
use App\Models\Institute;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DemoFinancialLedgerSeeder extends Seeder
{
    /**
     * Seed 6-month historical financial transactions and paid fee records
     * from April 2026 to September 2026 across all campuses.
     */
    public function run(): void
    {
        $institutes = Institute::all();

        foreach ($institutes as $institute) {
            $this->seedInstituteFinancialHistory($institute);
        }

        Cache::flush();
    }

    public function seedInstituteFinancialHistory(Institute $institute): void
    {
        $creator = User::where('institute_id', $institute->id)
            ->whereIn('role', ['principal', 'accountant', 'admin'])
            ->first();

        $creatorId = $creator?->id;

        // 1. Account Heads
        $headTuition = AccountHead::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'name' => 'Tuition & Academic Dues'],
            [
                'type' => 'income',
                'description' => 'Tuition, term admissions, and course enrollment dues',
                'is_active' => true,
                'created_by' => $creatorId,
            ]
        );

        $headFacility = AccountHead::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'name' => 'Lab, Library & Facility Charges'],
            [
                'type' => 'income',
                'description' => 'Specialized labs, digital libraries, and student campus resources',
                'is_active' => true,
                'created_by' => $creatorId,
            ]
        );

        $headSalaries = AccountHead::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'name' => 'Faculty & Staff Payroll'],
            [
                'type' => 'expense',
                'description' => 'Academic staff remuneration, teaching compensations, and administration salaries',
                'is_active' => true,
                'created_by' => $creatorId,
            ]
        );

        $headUtilities = AccountHead::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'name' => 'Campus Utilities & Fiber Connectivity'],
            [
                'type' => 'expense',
                'description' => 'Electricity, high-speed campus internet, water, and campus cooling/heating',
                'is_active' => true,
                'created_by' => $creatorId,
            ]
        );

        $headConsumables = AccountHead::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'name' => 'Academic Consumables & Cloud Software'],
            [
                'type' => 'expense',
                'description' => 'Lab supplies, LMS server hosting, examinations stationery, and licenses',
                'is_active' => true,
                'created_by' => $creatorId,
            ]
        );

        $headTax = AccountHead::withoutGlobalScopes()->updateOrCreate(
            ['institute_id' => $institute->id, 'name' => 'Government Tax Withholding & Levies'],
            [
                'type' => 'expense',
                'description' => 'Statutory federal education tax withholding and municipal service levies',
                'is_active' => true,
                'created_by' => $creatorId,
            ]
        );

        // 2. Fetch enrolled students for this institute
        $students = Student::withoutGlobalScopes()->where('institute_id', $institute->id)->get();

        // 3. Six-month dataset specifications (Apr 2026 to Sep 2026)
        // Target is PKR 350k. These realistic values form a clear, healthy upward trajectory.
        $monthsConfig = [
            [
                'year'         => 2026,
                'month'        => 4,
                'label'        => 'Apr 2026',
                'studentFee'   => 130000,
                'otherIncome'  => 165000,
                'incomeTitle'  => 'Spring Term Registration & Campus Dues',
                'salaries'     => 110000,
                'utilities'    => 35000,
                'consumables'  => 20000,
                'tax'          => 14750,
            ],
            [
                'year'         => 2026,
                'month'        => 5,
                'label'        => 'May 2026',
                'studentFee'   => 140000,
                'otherIncome'  => 178000,
                'incomeTitle'  => 'Digital Resource & STEM Laboratory Dues',
                'salaries'     => 115000,
                'utilities'    => 38000,
                'consumables'  => 19000,
                'tax'          => 15900,
            ],
            [
                'year'         => 2026,
                'month'        => 6,
                'label'        => 'Jun 2026',
                'studentFee'   => 145000,
                'otherIncome'  => 193000,
                'incomeTitle'  => 'Midterm Assessment & Examination Fees',
                'salaries'     => 120000,
                'utilities'    => 40000,
                'consumables'  => 18000,
                'tax'          => 16900,
            ],
            [
                'year'         => 2026,
                'month'        => 7,
                'label'        => 'Jul 2026',
                'studentFee'   => 150000,
                'otherIncome'  => 198000,
                'incomeTitle'  => 'Summer Enrichment & Sports Complex Dues',
                'salaries'     => 122000,
                'utilities'    => 42000,
                'consumables'  => 18000,
                'tax'          => 17400,
            ],
            [
                'year'         => 2026,
                'month'        => 8,
                'label'        => 'Aug 2026',
                'studentFee'   => 155000,
                'otherIncome'  => 207000,
                'incomeTitle'  => 'Fall Admissions, Prospectus & Advance Dues',
                'salaries'     => 125000,
                'utilities'    => 43000,
                'consumables'  => 20000,
                'tax'          => 18100,
            ],
            [
                'year'         => 2026,
                'month'        => 9,
                'label'        => 'Sep 2026',
                'studentFee'   => null, // Existing September vouchers are already in place
                'otherIncome'  => 245000,
                'incomeTitle'  => 'Academic Term Course Enrollments & Research Dues',
                'salaries'     => 128000,
                'utilities'    => 44000,
                'consumables'  => 22000,
                'tax'          => 19250,
            ],
        ];

        foreach ($monthsConfig as $cfg) {
            $monthDate = Carbon::create($cfg['year'], $cfg['month'], 15, 10, 0, 0);

            // A. Seed Student Monthly Paid Invoices (for April to August)
            if ($cfg['studentFee'] !== null && $students->isNotEmpty()) {
                $perStudent = round($cfg['studentFee'] / $students->count(), 2);

                foreach ($students as $student) {
                    Invoice::withoutGlobalScopes()->updateOrCreate(
                        [
                            'institute_id' => $institute->id,
                            'student_id'   => $student->id,
                            'fee_month'    => $cfg['label'],
                        ],
                        [
                            'academic_term_id' => $student->academic_term_id,
                            'class_section_id' => $student->class_section_id,
                            'title'            => "Tuition Fee Voucher — {$cfg['label']}",
                            'amount_pkr'       => $perStudent,
                            'status'           => 'paid',
                            'due_date'         => $monthDate->copy()->addDays(10)->toDateString(),
                            'paid_slip_path'   => 'slips/receipt.pdf',
                            'created_at'       => $monthDate,
                            'updated_at'       => $monthDate,
                        ]
                    );
                }
            }

            // B. Financial Transaction: Additional Operating Income
            FinancialTransaction::withoutGlobalScopes()->updateOrCreate(
                [
                    'institute_id'     => $institute->id,
                    'title'            => $cfg['incomeTitle'],
                    'transaction_date' => $monthDate->copy()->addDays(1)->toDateString(),
                ],
                [
                    'account_head_id'  => $headFacility->id,
                    'type'             => 'income',
                    'amount'           => $cfg['otherIncome'],
                    'payment_method'   => 'bank_transfer',
                    'reference_number' => 'REF-INC-' . $cfg['month'] . '-' . $institute->id,
                    'notes'            => 'Verified bank deposit received for ' . $cfg['label'],
                    'created_by'       => $creatorId,
                    'created_at'       => $monthDate,
                    'updated_at'       => $monthDate,
                ]
            );

            // C. Financial Transaction: Faculty & Staff Salaries (Expense)
            FinancialTransaction::withoutGlobalScopes()->updateOrCreate(
                [
                    'institute_id'     => $institute->id,
                    'title'            => "Faculty & Staff Monthly Payroll — {$cfg['label']}",
                    'transaction_date' => $monthDate->copy()->addDays(12)->toDateString(),
                ],
                [
                    'account_head_id'  => $headSalaries->id,
                    'type'             => 'expense',
                    'amount'           => $cfg['salaries'],
                    'payment_method'   => 'bank_transfer',
                    'reference_number' => 'PAYROLL-' . $cfg['month'] . '-' . $institute->id,
                    'notes'            => 'Disbursed via automated salary direct deposit',
                    'created_by'       => $creatorId,
                    'created_at'       => $monthDate,
                    'updated_at'       => $monthDate,
                ]
            );

            // D. Financial Transaction: Campus Utilities & Fiber (Expense)
            FinancialTransaction::withoutGlobalScopes()->updateOrCreate(
                [
                    'institute_id'     => $institute->id,
                    'title'            => "Campus Utilities, Power & Connectivity — {$cfg['label']}",
                    'transaction_date' => $monthDate->copy()->addDays(7)->toDateString(),
                ],
                [
                    'account_head_id'  => $headUtilities->id,
                    'type'             => 'expense',
                    'amount'           => $cfg['utilities'],
                    'payment_method'   => 'online',
                    'reference_number' => 'UTIL-' . $cfg['month'] . '-' . $institute->id,
                    'notes'            => 'Cleared utility invoices',
                    'created_by'       => $creatorId,
                    'created_at'       => $monthDate,
                    'updated_at'       => $monthDate,
                ]
            );

            // E. Financial Transaction: Academic Consumables & Cloud (Expense)
            FinancialTransaction::withoutGlobalScopes()->updateOrCreate(
                [
                    'institute_id'     => $institute->id,
                    'title'            => "STEM Lab Consumables & LMS Cloud Hosting — {$cfg['label']}",
                    'transaction_date' => $monthDate->copy()->addDays(4)->toDateString(),
                ],
                [
                    'account_head_id'  => $headConsumables->id,
                    'type'             => 'expense',
                    'amount'           => $cfg['consumables'],
                    'payment_method'   => 'credit_card',
                    'reference_number' => 'EXP-CONS-' . $cfg['month'] . '-' . $institute->id,
                    'notes'            => 'Campus resources and academic cloud server license',
                    'created_by'       => $creatorId,
                    'created_at'       => $monthDate,
                    'updated_at'       => $monthDate,
                ]
            );

            // F. Financial Transaction: Federal Tax & Levies (Tax Paid)
            FinancialTransaction::withoutGlobalScopes()->updateOrCreate(
                [
                    'institute_id'     => $institute->id,
                    'title'            => "Statutory Education Tax Withholding & Levies — {$cfg['label']}",
                    'transaction_date' => $monthDate->copy()->addDays(14)->toDateString(),
                ],
                [
                    'account_head_id'  => $headTax->id,
                    'type'             => 'expense',
                    'amount'           => $cfg['tax'],
                    'payment_method'   => 'bank_transfer',
                    'reference_number' => 'TAX-DEP-' . $cfg['month'] . '-' . $institute->id,
                    'notes'            => 'Deposited to federal revenue board treasury',
                    'created_by'       => $creatorId,
                    'created_at'       => $monthDate,
                    'updated_at'       => $monthDate,
                ]
            );
        }

        Cache::forget("principal_dash_finance_{$institute->id}");
    }
}
