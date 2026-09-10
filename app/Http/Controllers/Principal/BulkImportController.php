<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\AccountHead;
use App\Models\AccountTransaction;
use App\Models\ClassSection;
use App\Models\InstituteClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkImportController extends Controller
{
    /**
     * Import Students from CSV File
     */
    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
            'fallback_class_section_id' => 'nullable|exists:class_sections,id'
        ]);

        $instituteId = Auth::user()->institute_id;
        $file = $request->file('file');
        $rows = $this->parseCsvFile($file->getRealPath());

        if (empty($rows)) {
            return back()->with('error', 'The uploaded CSV file is empty or missing valid headers.');
        }

        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                // Normalize keys to lowercase trimmed strings
                $norm = [];
                foreach ($row as $k => $v) {
                    $norm[strtolower(trim($k))] = trim($v);
                }

                $fullName = $norm['full_name'] ?? $norm['name'] ?? $norm['student_name'] ?? $norm['student name'] ?? null;
                
                // If row has no name, skip row safely
                if (empty($fullName)) {
                    $skipped++;
                    continue;
                }

                $names = explode(' ', trim($fullName), 2);
                $firstName = $names[0] ?? $fullName;
                $lastName = $names[1] ?? '';

                $phone = $norm['phone'] ?? $norm['guardian_phone'] ?? $norm['contact'] ?? $norm['guardian contact'] ?? null;
                $guardianName = $norm['guardian_name'] ?? $norm['father_name'] ?? $norm['guardian name'] ?? $norm['father_guardian_name'] ?? 'Parent/Guardian';
                $email = $norm['email'] ?? $norm['student_email'] ?? null;

                if (empty($email)) {
                    $email = strtolower(Str::slug($fullName)) . rand(100, 999) . '@student.local';
                }

                // Roll Number Generation if missing
                $rollNumber = $norm['roll_number'] ?? $norm['roll'] ?? $norm['roll_no'] ?? $norm['student_id'] ?? null;
                if (empty($rollNumber)) {
                    $rollNumber = 'STU-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                }

                // Resolve Class Section
                $classSectionId = $request->fallback_class_section_id;
                $className = $norm['class'] ?? $norm['class_name'] ?? null;
                $sectionName = $norm['section'] ?? $norm['section_name'] ?? null;

                if (!empty($className)) {
                    $class = InstituteClass::where('institute_id', $instituteId)
                        ->where('name', 'LIKE', '%' . $className . '%')
                        ->first();
                    if ($class) {
                        $secQuery = ClassSection::where('institute_class_id', $class->id);
                        if (!empty($sectionName)) {
                            $secQuery->where('section_name', 'LIKE', '%' . $sectionName . '%');
                        }
                        $sec = $secQuery->first();
                        if ($sec) {
                            $classSectionId = $sec->id;
                        }
                    }
                }

                // Optional User account creation
                $user = User::where('email', $email)->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $fullName,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => Hash::make('Student@123'),
                        'role' => 'student',
                        'institute_id' => $instituteId,
                        'is_active' => true,
                    ]);
                }

                // Create Student record in DB
                Student::create([
                    'institute_id' => $instituteId,
                    'user_id' => $user->id,
                    'class_section_id' => $classSectionId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'roll_number' => $rollNumber,
                    'father_guardian_name' => $guardianName,
                    'guardian_phone' => $phone,
                    'email' => $email,
                    'date_of_birth' => $norm['dob'] ?? $norm['date_of_birth'] ?? date('Y-m-d', strtotime('-12 years')),
                    'previous_marks' => (float)($norm['previous_marks'] ?? 75.00),
                    'guardian_tax_status' => 'non-filer',
                    'blood_group' => $norm['blood_group'] ?? null,
                    'student_bform_cnic' => $norm['cnic'] ?? $norm['bform'] ?? null,
                    'father_guardian_cnic' => $norm['guardian_cnic'] ?? null,
                    'address' => $norm['address'] ?? null,
                    'enrolled_program' => $className ? ($className . ($sectionName ? ' - ' . $sectionName : '')) : 'General',
                    'base_fee' => (float)($norm['base_fee'] ?? 5000),
                ]);

                $imported++;
            }

            DB::commit();
            return back()->with('success', "Bulk Import Complete: Successfully imported {$imported} student(s) into database.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Student import failed: ' . $e->getMessage());
        }
    }

    /**
     * Import Faculty & Staff Members from CSV File
     */
    public function importStaff(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $instituteId = Auth::user()->institute_id;
        $file = $request->file('file');
        $rows = $this->parseCsvFile($file->getRealPath());

        if (empty($rows)) {
            return back()->with('error', 'The uploaded CSV file is empty or missing valid headers.');
        }

        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $norm = [];
                foreach ($row as $k => $v) {
                    $norm[strtolower(trim($k))] = trim($v);
                }

                $name = $norm['name'] ?? $norm['full_name'] ?? $norm['first_name'] ?? $norm['staff_name'] ?? null;
                if (empty($name)) {
                    $skipped++;
                    continue;
                }

                $email = $norm['email'] ?? null;
                if (empty($email)) {
                    $email = strtolower(Str::slug($name)) . rand(100, 999) . '@staff.local';
                }

                $phone = $norm['phone'] ?? $norm['contact'] ?? $norm['mobile'] ?? null;
                $role = strtolower($norm['role'] ?? $norm['type'] ?? 'teacher');
                $isTeacher = in_array($role, ['teacher', 'faculty', 'instructor', 'teaching']);

                $qualification = $norm['qualification'] ?? $norm['degree'] ?? 'Degree Verified';
                $salary = (float)($norm['salary'] ?? $norm['basic_salary'] ?? $norm['basic_salary_pkr'] ?? 0);
                $employeeId = $norm['employee_id'] ?? $norm['emp_id'] ?? ('EMP-' . date('Y') . '-' . rand(1000, 9999));

                $names = explode(' ', trim($name), 2);
                $firstName = $names[0] ?? $name;
                $lastName = $names[1] ?? '';

                // Create User login account
                $user = User::where('email', $email)->first();
                if (!$user) {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'phone' => $phone,
                        'password' => Hash::make('Staff@123'),
                        'role' => $isTeacher ? 'teacher' : 'staff',
                        'institute_id' => $instituteId,
                        'is_active' => true,
                    ]);
                }

                if ($isTeacher) {
                    Teacher::create([
                        'user_id' => $user->id,
                        'institute_id' => $instituteId,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'phone' => $phone,
                        'qualification' => $qualification,
                        'basic_salary_pkr' => $salary,
                        'employee_id' => $employeeId,
                    ]);
                }

                $imported++;
            }

            DB::commit();
            return back()->with('success', "Bulk Import Complete: Successfully onboarded {$imported} faculty & staff member(s) into database.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Staff onboarding failed: ' . $e->getMessage());
        }
    }

    /**
     * Import Expense / Income Transactions from CSV File
     */
    public function importFinance(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $instituteId = Auth::user()->institute_id;
        $file = $request->file('file');
        $rows = $this->parseCsvFile($file->getRealPath());

        if (empty($rows)) {
            return back()->with('error', 'The uploaded CSV file is empty or missing valid headers.');
        }

        $imported = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                $norm = [];
                foreach ($row as $k => $v) {
                    $norm[strtolower(trim($k))] = trim($v);
                }

                $title = $norm['category'] ?? $norm['title'] ?? $norm['head'] ?? $norm['head_name'] ?? $norm['description'] ?? null;
                $amount = (float)($norm['amount'] ?? $norm['amount_pkr'] ?? $norm['total'] ?? 0);

                if (empty($title) || $amount <= 0) {
                    $skipped++;
                    continue;
                }

                $type = strtolower($norm['type'] ?? 'expense');
                $type = in_array($type, ['income', 'expense']) ? $type : 'expense';
                $date = $norm['date'] ?? $norm['transaction_date'] ?? date('Y-m-d');
                $notes = $norm['notes'] ?? $norm['remarks'] ?? 'Bulk imported historical transaction.';

                // Find or create matching Account Head
                $head = AccountHead::where('institute_id', $instituteId)
                    ->where('name', 'LIKE', '%' . $title . '%')
                    ->first();

                if (!$head) {
                    $head = AccountHead::create([
                        'institute_id' => $instituteId,
                        'name' => ucfirst($title),
                        'type' => $type,
                        'description' => 'Auto-created category via CSV Bulk Import',
                        'is_active' => true,
                        'created_by' => Auth::id(),
                    ]);
                }

                \App\Models\FinancialTransaction::create([
                    'institute_id' => $instituteId,
                    'account_head_id' => $head->id,
                    'title' => $title,
                    'amount' => $amount,
                    'type' => $type,
                    'transaction_date' => $date,
                    'payment_method' => $norm['payment_method'] ?? 'cash',
                    'notes' => $notes,
                    'created_by' => Auth::id(),
                ]);

                $imported++;
            }

            DB::commit();
            return back()->with('success', "Bulk Import Complete: Successfully recorded {$imported} financial transaction(s) into database.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Financial import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download Sample CSV Files
     */
    public function downloadSample($type)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        if ($type === 'students') {
            $filename = 'sample_students_import.csv';
            $csvData = "Full Name,Roll Number,Gender,Guardian Name,Phone,Email,Class,Section\n";
            $csvData .= "Muhammad Ali,STU-2026-001,Male,Tariq Ali,+92 300 1234567,ali@example.com,Class 10,Section A\n";
            $csvData .= "Fatima Khan,STU-2026-002,Female,Zubair Khan,+92 321 9876543,fatima@example.com,Class 9,Section B\n";
        } elseif ($type === 'staff') {
            $filename = 'sample_staff_import.csv';
            $csvData = "Name,Email,Phone,Role,Qualification,Basic Salary,Employee ID\n";
            $csvData .= "Dr. Ahmed Hassan,ahmed@example.com,+92 301 5551234,Faculty,M.Sc Mathematics,65000,EMP-2026-001\n";
            $csvData .= "Sara Tariq,sara@example.com,+92 333 4445555,Admin Staff,B.Com Accounts,45000,EMP-2026-002\n";
        } else {
            $filename = 'sample_finance_import.csv';
            $csvData = "Category,Type,Amount,Date,Notes\n";
            $csvData .= "Electricity Bill,Expense,35000,2026-09-01,Monthly utility bill\n";
            $csvData .= "Annual Prospectus Sales,Income,15000,2026-09-02,Admission prospectus revenue\n";
        }

        $headers['Content-Disposition'] = "attachment; filename=\"{$filename}\"";

        return response($csvData, 200, $headers);
    }

    /**
     * Internal Helper: Parse CSV File into Associative Rows
     */
    private function parseCsvFile($filepath)
    {
        $rows = [];
        if (($handle = fopen($filepath, 'r')) !== false) {
            // Read headers row
            $headers = fgetcsv($handle, 2000, ',');
            if (!$headers) {
                fclose($handle);
                return [];
            }

            // Strip UTF-8 BOM if present
            $headers[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', $headers[0]);

            while (($data = fgetcsv($handle, 2000, ',')) !== false) {
                if (count($data) === count($headers)) {
                    $rows[] = array_combine($headers, $data);
                } elseif (count($data) > 0 && !empty(array_filter($data))) {
                    // Fill or slice array to match headers
                    $padded = array_pad($data, count($headers), '');
                    $rows[] = array_combine($headers, array_slice($padded, 0, count($headers)));
                }
            }
            fclose($handle);
        }
        return $rows;
    }
}
