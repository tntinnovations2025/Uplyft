<?php

namespace App\Services;

use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\Institute;
use App\Models\Room;
use App\Models\Timetable;
use ZipArchive;

class TimetableMultiSheetExcelService
{
    /**
     * Entry point for timetable export.
     * If $classSectionId is provided, generates single class timetable.
     * Otherwise generates complete institute multi-sheet timetable.
     */
    public function generate(int $instituteId, int $academicTermId, ?int $classSectionId = null): string
    {
        if ($classSectionId) {
            return $this->generateClassTimetable($instituteId, $academicTermId, $classSectionId);
        }

        return $this->generateCompleteInstitute($instituteId, $academicTermId);
    }

    /**
     * Generate complete whole institute timetable workbook (.xlsx).
     * Includes:
     * 1. Master Schedule (Day | Room | Time 1 | Time 2 | ...)
     * 2. Summary Overview sheet
     * 3. Dedicated Sheet for each Class/Section
     */
    public function generateCompleteInstitute(int $instituteId, int $academicTermId): string
    {
        $institute = Institute::find($instituteId);
        $term = AcademicTerm::find($academicTermId);

        $sections = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId, $academicTermId) {
            $q->where('institute_id', $instituteId)
              ->where('academic_term_id', $academicTermId);
        })
        ->with(['instituteClass', 'room'])
        ->get();

        $rooms = Room::where('institute_id', $instituteId)->orderBy('room_number')->get();

        $allSlots = Timetable::where('academic_term_id', $academicTermId)
            ->with(['subject', 'teacher', 'section.instituteClass', 'room'])
            ->orderBy('start_time')
            ->get();

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        // Build unique dynamic time slots
        $timeSlots = $this->extractTimeSlots($allSlots);

        // Group sections by Class (e.g. "Grade 10", "BSCS")
        $groupedSections = $sections->groupBy(function ($s) {
            $name = trim((string)($s->instituteClass->custom_name ?: $s->instituteClass->name ?: 'Class'));
            if (is_numeric($name)) {
                $name = "Grade " . $name;
            }
            return $name;
        });

        $tempFile = tempnam(sys_get_temp_dir(), 'timetable_all_') . '.xlsx';
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create temporary Excel archive.');
        }

        $sheetNames = [];
        $sheetXmls = [];

        // ── 1. Master Schedule Sheet (Day | Room | Time 1 | Time 2 | ...) ──
        $sheetNames[] = 'Master Timetable';
        $sheetXmls[] = $this->buildMasterExcelGridSheetXml(
            $institute?->name ?? 'Institute',
            $term?->name ?? 'Active Session',
            $days,
            $rooms,
            $timeSlots,
            $allSlots
        );

        // ── 2. Master Overview Summary Sheet ──
        $sheetNames[] = 'Summary Overview';
        $sheetXmls[] = $this->buildOverviewSheetXml(
            $institute?->name ?? 'Institute',
            $term?->name ?? 'Active Session',
            $groupedSections,
            $allSlots
        );

        // ── 3. Dedicated Sheet for Each Class ──
        $usedNames = ['master timetable', 'summary overview'];
        foreach ($groupedSections as $className => $classSections) {
            $rawName = trim((string) $className);
            if (empty($rawName)) {
                $rawName = 'Class';
            }

            $cleanName = substr(preg_replace('/[\\\\\\/\?\*\[\]\:]/', '-', $rawName), 0, 31);
            $uniqueName = $cleanName;
            $counter = 2;
            while (in_array(strtolower($uniqueName), $usedNames)) {
                $suffix = " ($counter)";
                $uniqueName = substr($cleanName, 0, 31 - strlen($suffix)) . $suffix;
                $counter++;
            }
            $usedNames[] = strtolower($uniqueName);
            $sheetNames[] = $uniqueName;

            $sheetXmls[] = $this->buildClassSheetXml(
                $institute?->name ?? 'Institute',
                $term?->name ?? 'Active Session',
                $className,
                $classSections,
                $days,
                $timeSlots,
                $allSlots
            );
        }

        $this->packageZipWorkbook($zip, $sheetNames, $sheetXmls);
        $zip->close();

        return $tempFile;
    }

    /**
     * Generate a dedicated Single Class Timetable workbook (.xlsx)
     * Contains ONLY that class section's verified timetable data.
     */
    public function generateClassTimetable(int $instituteId, int $academicTermId, int $classSectionId): string
    {
        $institute = Institute::find($instituteId);
        $term = AcademicTerm::find($academicTermId);

        $section = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId) {
            $q->where('institute_id', $instituteId);
        })
        ->with(['instituteClass', 'room'])
        ->findOrFail($classSectionId);

        $allSlots = Timetable::where('class_section_id', $classSectionId)
            ->with(['subject', 'teacher', 'section.instituteClass', 'room'])
            ->orderBy('start_time')
            ->get();

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $timeSlots = $this->extractTimeSlots($allSlots);

        $className = $section->instituteClass?->custom_name ?: ($section->instituteClass?->class_name ?: 'Class');
        $secName = $section->section_name ?: 'A';
        $fullClassTitle = "{$className} {$secName}";

        $tempFile = tempnam(sys_get_temp_dir(), 'timetable_class_') . '.xlsx';
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create temporary Excel archive.');
        }

        $sheetNames = [substr($fullClassTitle, 0, 31)];
        $sheetXmls = [
            $this->buildSingleClassDedicatedSheetXml(
                $institute?->name ?? 'Institute',
                $term?->name ?? 'Active Session',
                $section,
                $days,
                $timeSlots,
                $allSlots
            )
        ];

        $this->packageZipWorkbook($zip, $sheetNames, $sheetXmls);
        $zip->close();

        return $tempFile;
    }

    /**
     * Generate a Teacher-specific timetable workbook (.xlsx).
     * One sheet: rows = Days, columns = Time slots, cells = Class/Section + Subject.
     */
    public function generateTeacherTimetable(int $instituteId, int $academicTermId, $teacher, $allSlots): string
    {
        $institute = Institute::find($instituteId);
        $term      = AcademicTerm::find($academicTermId);

        $days      = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $timeSlots = $this->extractTimeSlots($allSlots);

        $teacherName = $teacher->name ?? 'Teacher';

        // Build sheet XML
        $sheetXml = $this->buildTeacherSheetXml(
            $institute?->name ?? 'Institute',
            $term?->name ?? 'Active Session',
            $teacherName,
            $days,
            $timeSlots,
            $allSlots
        );

        $tempFile = tempnam(sys_get_temp_dir(), 'timetable_teacher_') . '.xlsx';
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create temporary Excel archive.');
        }

        $safeSheetName = substr(preg_replace('/[^A-Za-z0-9 _]/', '', $teacherName), 0, 31);
        $this->packageZipWorkbook($zip, [$safeSheetName], [$sheetXml]);
        $zip->close();

        return $tempFile;
    }

    /**
     * Build sheet XML for teacher-specific timetable (Day rows × Time columns).
     */
    protected function buildTeacherSheetXml(
        string $instituteName,
        string $termName,
        string $teacherName,
        array $days,
        $timeSlots,
        $allSlots
    ): string {
        $timeOverlap = function (string $s1, string $e1, string $s2, string $e2): bool {
            return $s1 < $e2 && $s2 < $e1;
        };

        $cols      = count($timeSlots) + 1; // +1 for Day label col
        $colLetter = fn(int $n) => $this->getColumnLetter($n);

        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetData>';

        $rowIdx = 1;

        // Title row
        $xml .= "<row r=\"{$rowIdx}\">";
        $xml .= "<c r=\"A{$rowIdx}\" s=\"1\" t=\"inlineStr\"><is><t>" . $this->escapeXml("{$instituteName} — {$teacherName} — {$termName}") . "</t></is></c>";
        $xml .= "</row>";
        $rowIdx++;

        // Blank row
        $xml .= "<row r=\"{$rowIdx}\"></row>";
        $rowIdx++;

        // Header row: Day | slot1 | slot2 | ...
        $xml .= "<row r=\"{$rowIdx}\">";
        $xml .= "<c r=\"A{$rowIdx}\" s=\"3\" t=\"inlineStr\"><is><t>Day</t></is></c>";
        foreach ($timeSlots as $ci => $ts) {
            $col = $colLetter($ci + 2);
            $label = $this->escapeXml("{$ts['start']}–{$ts['end']}");
            $xml .= "<c r=\"{$col}{$rowIdx}\" s=\"3\" t=\"inlineStr\"><is><t>{$label}</t></is></c>";
        }
        $xml .= "</row>";
        $rowIdx++;

        // Day rows
        foreach ($days as $day) {
            $daySlots = $allSlots->filter(fn($s) => strtolower($s->day_of_week) === $day);
            $xml .= "<row r=\"{$rowIdx}\">";
            $dayLabel = $this->escapeXml(ucfirst($day));
            $xml .= "<c r=\"A{$rowIdx}\" s=\"5\" t=\"inlineStr\"><is><t>{$dayLabel}</t></is></c>";

            foreach ($timeSlots as $ci => $ts) {
                $col   = $colLetter($ci + 2);
                $tsStart = $ts['start'];
                $tsEnd   = $ts['end'];

                $matched = $daySlots->first(function ($s) use ($tsStart, $tsEnd, $timeOverlap) {
                    $sStart = substr($s->start_time, 0, 5);
                    $sEnd   = substr($s->end_time, 0, 5);
                    return $timeOverlap($sStart, $sEnd, $tsStart, $tsEnd);
                });

                if ($matched) {
                    $subjectName = $matched->subject?->name ?? '—';
                    $className   = $matched->section?->instituteClass?->custom_name ?? '';
                    $secName     = $matched->section?->section_name ?? '';
                    $room        = $matched->room?->room_number ?? '';
                    $cell        = trim("{$subjectName}\n{$className} {$secName}" . ($room ? "\n📍 {$room}" : ''));
                    $xml .= "<c r=\"{$col}{$rowIdx}\" s=\"6\" t=\"inlineStr\"><is><t>" . $this->escapeXml($cell) . "</t></is></c>";
                } else {
                    $xml .= "<c r=\"{$col}{$rowIdx}\" s=\"2\"><v></v></c>";
                }
            }
            $xml .= "</row>";
            $rowIdx++;
        }

        $xml .= '</sheetData>';
        // Auto-fit columns
        $xml .= '<cols><col min="1" max="1" width="12" customWidth="1"/><col min="2" max="' . $cols . '" width="22" customWidth="1"/></cols>';
        $xml .= '</worksheet>';

        return $xml;
    }


    protected function extractTimeSlots($slots)
    {
        $timeSlots = $slots->map(fn ($s) => [
            'start' => substr($s->start_time, 0, 5),
            'end'   => substr($s->end_time, 0, 5),
            'key'   => substr($s->start_time, 0, 5) . '-' . substr($s->end_time, 0, 5),
            'sort'  => substr($s->start_time, 0, 5) . '_' . substr($s->end_time, 0, 5),
        ])->unique('key')->sortBy('sort')->values();

        if ($timeSlots->isEmpty()) {
            $defaultSlots = ['08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00', '15:00-16:00'];
            foreach ($defaultSlots as $ds) {
                [$s, $e] = explode('-', $ds);
                $timeSlots->push(['start' => $s, 'end' => $e, 'key' => $ds, 'sort' => $s . '_' . $e]);
            }
        }

        return $timeSlots;
    }

    /**
     * Build the XML for the Master Excel Timetable Sheet (Day | Room | Time 1 | Time 2 | ...)
     */
    protected function buildMasterExcelGridSheetXml(
        string $instituteName,
        string $termName,
        array $days,
        $rooms,
        $timeSlots,
        $allSlots
    ): string {
        $rowsXml = '';
        $mergeCells = [];

        $totalCols = 2 + $timeSlots->count();
        $lastColLetter = $this->getColumnLetter($totalCols);
        $genDate = date('F d, Y • g:i A');

        // Row 1: Main Title Banner (INSTITUTE MASTER TIMETABLE)
        $rowsXml .= '<row r="1" ht="36" customHeight="1">';
        $rowsXml .= '<c r="A1" s="1" t="inlineStr"><is><t>' . $this->escapeXml('INSTITUTE MASTER TIMETABLE') . '</t></is></c>';
        for ($c = 2; $c <= $totalCols; $c++) {
            $colLetter = $this->getColumnLetter($c);
            $rowsXml .= "<c r=\"{$colLetter}1\" s=\"1\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = "A1:{$lastColLetter}1";

        // Row 2: Subtitle (Institute Name & Academic Session)
        $rowsXml .= '<row r="2" ht="22" customHeight="1">';
        $rowsXml .= '<c r="A2" s="2" t="inlineStr"><is><t>' . $this->escapeXml("{$instituteName} • Academic Session: {$termName}") . '</t></is></c>';
        for ($c = 2; $c <= $totalCols; $c++) {
            $colLetter = $this->getColumnLetter($c);
            $rowsXml .= "<c r=\"{$colLetter}2\" s=\"2\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = "A2:{$lastColLetter}2";

        // Row 3: Metadata Row
        $roomsCount = $rooms->count();
        $slotsCount = $allSlots->count();
        $metaText = "Rooms Configured: {$roomsCount} • Total Scheduled Periods: {$slotsCount} • Generated on {$genDate}";
        $rowsXml .= '<row r="3" ht="20" customHeight="1">';
        $rowsXml .= '<c r="A3" s="3" t="inlineStr"><is><t>' . $this->escapeXml($metaText) . '</t></is></c>';
        for ($c = 2; $c <= $totalCols; $c++) {
            $colLetter = $this->getColumnLetter($c);
            $rowsXml .= "<c r=\"{$colLetter}3\" s=\"3\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = "A3:{$lastColLetter}3";

        // Row 4: Blank Spacer Row
        $rowsXml .= '<row r="4" ht="10" customHeight="1"/>';

        // Row 5: Column Headers: DAY | ROOM | Time 1 | Time 2 | ...
        $rowsXml .= '<row r="5" ht="28" customHeight="1">';
        $rowsXml .= '<c r="A5" s="4" t="inlineStr"><is><t>DAY</t></is></c>';
        $rowsXml .= '<c r="B5" s="4" t="inlineStr"><is><t>ROOM</t></is></c>';

        foreach ($timeSlots as $tsIdx => $ts) {
            $colLetter = $this->getColumnLetter(3 + $tsIdx);
            $timeHeader = date('g:i', strtotime($ts['start'])) . ' – ' . date('g:i A', strtotime($ts['end']));
            $rowsXml .= "<c r=\"{$colLetter}5\" s=\"4\" t=\"inlineStr\"><is><t>" . $this->escapeXml($timeHeader) . "</t></is></c>";
        }
        $rowsXml .= '</row>';

        $currentRow = 6;
        $roomList = $rooms->isNotEmpty() ? $rooms : collect([(object)['id' => 0, 'room_number' => 'Main Room', 'room_type' => 'Default']]);

        $timeOverlap = function (string $s1, string $e1, string $s2, string $e2): bool {
            return ($s1 < $e2) && ($e1 > $s2);
        };

        foreach ($days as $day) {
            $cleanDay = strtolower($day);
            $dayStartRow = $currentRow;

            foreach ($roomList as $rIdx => $rm) {
                $rId = $rm->id;
                $rowsXml .= "<row r=\"{$currentRow}\" ht=\"52\" customHeight=\"1\">";

                // Column A: Day
                $dayLabel = ucfirst($day);
                $rowsXml .= "<c r=\"A{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml($dayLabel) . "</t></is></c>";

                // Column B: Room
                $roomLabel = $rm->room_number ?? 'Room';
                $rowsXml .= "<c r=\"B{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml($roomLabel) . "</t></is></c>";

                // Columns C+: Time Slots
                foreach ($timeSlots as $tsIdx => $ts) {
                    $colLetter = $this->getColumnLetter(3 + $tsIdx);
                    $tsStart = $ts['start'];
                    $tsEnd   = $ts['end'];

                    $matchedSlot = $allSlots->first(function ($s) use ($rId, $cleanDay, $tsStart, $tsEnd, $timeOverlap) {
                        $sRoomId = $s->room_id ?: 0;
                        if ($sRoomId != $rId || strtolower($s->day_of_week) !== $cleanDay) {
                            return false;
                        }
                        $sStart = substr($s->start_time, 0, 5);
                        $sEnd   = substr($s->end_time, 0, 5);
                        return $timeOverlap($sStart, $sEnd, $tsStart, $tsEnd);
                    });

                    if ($matchedSlot) {
                        $subj = $matchedSlot->subject->subject_name ?? $matchedSlot->subject->name ?? 'Subject';
                        $cls  = ($matchedSlot->section->instituteClass->custom_name ?? 'Class') . ' ' . ($matchedSlot->section->section_name ?? 'A');
                        $tchr = $matchedSlot->teacher->name ?? 'Teacher';

                        $cellContent = "{$subj}\n{$cls}\n{$tchr}";
                        $rowsXml .= "<c r=\"{$colLetter}{$currentRow}\" s=\"7\" t=\"inlineStr\"><is><t>" . $this->escapeXml($cellContent) . "</t></is></c>";
                    } else {
                        $rowsXml .= "<c r=\"{$colLetter}{$currentRow}\" s=\"6\" t=\"inlineStr\"><is><t>Free</t></is></c>";
                    }
                }

                $rowsXml .= '</row>';
                $currentRow++;
            }

            // Merge Day column vertically for all rooms of that day
            if ($roomList->count() > 1) {
                $dayEndRow = $currentRow - 1;
                $mergeCells[] = "A{$dayStartRow}:A{$dayEndRow}";
            }
        }

        $mergeXml = '';
        if (!empty($mergeCells)) {
            $mergeXml = '<mergeCells count="' . count($mergeCells) . '">';
            foreach ($mergeCells as $ref) {
                $mergeXml .= '<mergeCell ref="' . $ref . '"/>';
            }
            $mergeXml .= '</mergeCells>';
        }

        // Columns definition with auto-adjusted widths
        $colsXml = '<cols>'
            . '<col min="1" max="1" width="18" customWidth="1"/>'
            . '<col min="2" max="2" width="20" customWidth="1"/>'
            . '<col min="3" max="' . $totalCols . '" width="28" customWidth="1"/>'
            . '</cols>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews>'
            . '<sheetView tabSelected="1" workbookViewId="0" showGridLines="1">'
            . '<pane ySplit="5" topLeftCell="A6" activePane="bottomLeft" state="frozen"/>'
            . '</sheetView>'
            . '</sheetViews>'
            . $colsXml
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . $mergeXml
            . '<pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>'
            . '</worksheet>';
    }

    /**
     * Build the XML for a Single Class Timetable Sheet.
     * Formatted as: Day | Room | Time 1 | Time 2 | Time 3 | ...
     */
    protected function buildSingleClassDedicatedSheetXml(
        string $instituteName,
        string $termName,
        ClassSection $section,
        array $days,
        $timeSlots,
        $allSlots
    ): string {
        $rowsXml = '';
        $mergeCells = [];

        $totalCols = 2 + $timeSlots->count();
        $lastColLetter = $this->getColumnLetter($totalCols);
        $genDate = date('F d, Y • g:i A');

        $className = $section->instituteClass?->custom_name ?: ($section->instituteClass?->class_name ?: 'Class');
        $secName = $section->section_name ?: 'A';
        $fullClassName = "{$className} {$secName}";
        $roomName = $section->room ? "Room {$section->room->room_number}" : 'Assigned Classrooms';

        // Row 1: Title Banner
        $rowsXml .= '<row r="1" ht="36" customHeight="1">';
        $rowsXml .= '<c r="A1" s="1" t="inlineStr"><is><t>' . $this->escapeXml("CLASS TIMETABLE: {$fullClassName}") . '</t></is></c>';
        for ($c = 2; $c <= $totalCols; $c++) {
            $colLetter = $this->getColumnLetter($c);
            $rowsXml .= "<c r=\"{$colLetter}1\" s=\"1\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = "A1:{$lastColLetter}1";

        // Row 2: Subtitle
        $rowsXml .= '<row r="2" ht="22" customHeight="1">';
        $rowsXml .= '<c r="A2" s="2" t="inlineStr"><is><t>' . $this->escapeXml("{$instituteName} • Academic Session: {$termName}") . '</t></is></c>';
        for ($c = 2; $c <= $totalCols; $c++) {
            $colLetter = $this->getColumnLetter($c);
            $rowsXml .= "<c r=\"{$colLetter}2\" s=\"2\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = "A2:{$lastColLetter}2";

        // Row 3: Metadata Row
        $metaText = "Enrolled Class: {$fullClassName} • Default Room: {$roomName} • Generated on {$genDate}";
        $rowsXml .= '<row r="3" ht="20" customHeight="1">';
        $rowsXml .= '<c r="A3" s="3" t="inlineStr"><is><t>' . $this->escapeXml($metaText) . '</t></is></c>';
        for ($c = 2; $c <= $totalCols; $c++) {
            $colLetter = $this->getColumnLetter($c);
            $rowsXml .= "<c r=\"{$colLetter}3\" s=\"3\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = "A3:{$lastColLetter}3";

        // Row 4: Blank Spacer Row
        $rowsXml .= '<row r="4" ht="10" customHeight="1"/>';

        // Row 5: Column Headers: DAY | ROOM | Time 1 | Time 2 | ...
        $rowsXml .= '<row r="5" ht="28" customHeight="1">';
        $rowsXml .= '<c r="A5" s="4" t="inlineStr"><is><t>DAY</t></is></c>';
        $rowsXml .= '<c r="B5" s="4" t="inlineStr"><is><t>ROOM</t></is></c>';

        foreach ($timeSlots as $tsIdx => $ts) {
            $colLetter = $this->getColumnLetter(3 + $tsIdx);
            $timeHeader = date('g:i', strtotime($ts['start'])) . ' – ' . date('g:i A', strtotime($ts['end']));
            $rowsXml .= "<c r=\"{$colLetter}5\" s=\"4\" t=\"inlineStr\"><is><t>" . $this->escapeXml($timeHeader) . "</t></is></c>";
        }
        $rowsXml .= '</row>';

        $currentRow = 6;
        $timeOverlap = function (string $s1, string $e1, string $s2, string $e2): bool {
            return ($s1 < $e2) && ($e1 > $s2);
        };

        foreach ($days as $day) {
            $cleanDay = strtolower($day);
            $rowsXml .= "<row r=\"{$currentRow}\" ht=\"48\" customHeight=\"1\">";

            // Column A: Day
            $rowsXml .= "<c r=\"A{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml(ucfirst($day)) . "</t></is></c>";

            // Determine room label for this day
            $daySlots = $allSlots->filter(fn ($s) => strtolower($s->day_of_week) === $cleanDay);
            $firstRoom = $daySlots->first()?->room;
            $dayRoomLabel = $firstRoom ? "Room {$firstRoom->room_number}" : ($section->room ? "Room {$section->room->room_number}" : 'Main Room');

            // Column B: Room
            $rowsXml .= "<c r=\"B{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml($dayRoomLabel) . "</t></is></c>";

            // Columns C+: Time Slots
            foreach ($timeSlots as $tsIdx => $ts) {
                $colLetter = $this->getColumnLetter(3 + $tsIdx);
                $tsStart = $ts['start'];
                $tsEnd   = $ts['end'];

                $matchedSlot = $daySlots->first(function ($s) use ($tsStart, $tsEnd, $timeOverlap) {
                    $sStart = substr($s->start_time, 0, 5);
                    $sEnd   = substr($s->end_time, 0, 5);
                    return $timeOverlap($sStart, $sEnd, $tsStart, $tsEnd);
                });

                if ($matchedSlot) {
                    $subj = $matchedSlot->subject->subject_name ?? $matchedSlot->subject->name ?? 'Subject';
                    $tchr = $matchedSlot->teacher->name ?? 'Teacher';
                    $rm = $matchedSlot->room ? "[Room {$matchedSlot->room->room_number}]" : '';

                    $cellContent = "{$subj}\n{$tchr}";
                    if (!empty($rm)) {
                        $cellContent .= "\n{$rm}";
                    }

                    $rowsXml .= "<c r=\"{$colLetter}{$currentRow}\" s=\"7\" t=\"inlineStr\"><is><t>" . $this->escapeXml($cellContent) . "</t></is></c>";
                } else {
                    $rowsXml .= "<c r=\"{$colLetter}{$currentRow}\" s=\"6\" t=\"inlineStr\"><is><t>Free</t></is></c>";
                }
            }

            $rowsXml .= '</row>';
            $currentRow++;
        }

        $mergeXml = '';
        if (!empty($mergeCells)) {
            $mergeXml = '<mergeCells count="' . count($mergeCells) . '">';
            foreach ($mergeCells as $ref) {
                $mergeXml .= '<mergeCell ref="' . $ref . '"/>';
            }
            $mergeXml .= '</mergeCells>';
        }

        $colsXml = '<cols>'
            . '<col min="1" max="1" width="18" customWidth="1"/>'
            . '<col min="2" max="2" width="20" customWidth="1"/>'
            . '<col min="3" max="' . $totalCols . '" width="28" customWidth="1"/>'
            . '</cols>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews>'
            . '<sheetView tabSelected="1" workbookViewId="0" showGridLines="1">'
            . '<pane ySplit="5" topLeftCell="A6" activePane="bottomLeft" state="frozen"/>'
            . '</sheetView>'
            . '</sheetViews>'
            . $colsXml
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . $mergeXml
            . '<pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>'
            . '</worksheet>';
    }

    /**
     * Build the XML for the Master Overview summary sheet.
     */
    protected function buildOverviewSheetXml(string $instituteName, string $termName, $groupedSections, $allSlots): string
    {
        $rowsXml = '';
        $mergeCells = [];

        // Row 1: Title Banner
        $rowsXml .= '<row r="1" ht="36" customHeight="1">';
        $rowsXml .= '<c r="A1" s="1" t="inlineStr"><is><t>' . $this->escapeXml("{$instituteName} • Master Timetable Overview") . '</t></is></c>';
        for ($col = 'B'; $col <= 'E'; $col++) {
            $rowsXml .= "<c r=\"{$col}1\" s=\"1\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = 'A1:E1';

        // Row 2: Subtitle
        $rowsXml .= '<row r="2" ht="22" customHeight="1">';
        $rowsXml .= '<c r="A2" s="2" t="inlineStr"><is><t>' . $this->escapeXml("ACADEMIC CURRICULUM ALLOCATION & CLASS SCHEDULE SUMMARY ({$termName})") . '</t></is></c>';
        for ($col = 'B'; $col <= 'E'; $col++) {
            $rowsXml .= "<c r=\"{$col}2\" s=\"2\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = 'A2:E2';

        // Row 3: Blank Spacer
        $rowsXml .= '<row r="3" ht="12" customHeight="1"/>';

        // Row 4: Summary Table Headers
        $rowsXml .= '<row r="4" ht="26" customHeight="1">';
        $rowsXml .= '<c r="A4" s="4" t="inlineStr"><is><t>CLASS / GRADE</t></is></c>';
        $rowsXml .= '<c r="B4" s="4" t="inlineStr"><is><t>SECTION NAME</t></is></c>';
        $rowsXml .= '<c r="C4" s="4" t="inlineStr"><is><t>CLASSROOM</t></is></c>';
        $rowsXml .= '<c r="D4" s="4" t="inlineStr"><is><t>SCHEDULED PERIODS / WEEK</t></is></c>';
        $rowsXml .= '<c r="E4" s="4" t="inlineStr"><is><t>STATUS</t></is></c>';
        $rowsXml .= '</row>';

        $currentRow = 5;
        $totalSlotsCount = 0;
        $totalSectionsCount = 0;

        foreach ($groupedSections as $className => $sections) {
            foreach ($sections as $sec) {
                $totalSectionsCount++;
                $secId = $sec->id;
                $secSlotsCount = $allSlots->where('class_section_id', $secId)->count();
                $totalSlotsCount += $secSlotsCount;
                $roomName = $sec->room ? "Room {$sec->room->room_number}" : 'Unassigned';

                $rowsXml .= "<row r=\"{$currentRow}\" ht=\"22\" customHeight=\"1\">";
                $rowsXml .= "<c r=\"A{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml($className) . "</t></is></c>";
                $rowsXml .= "<c r=\"B{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml($sec->section_name ?? 'A') . "</t></is></c>";
                $rowsXml .= "<c r=\"C{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml($roomName) . "</t></is></c>";
                $rowsXml .= "<c r=\"D{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml("{$secSlotsCount} Periods") . "</t></is></c>";
                $rowsXml .= "<c r=\"E{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>Active &amp; Ready</t></is></c>";
                $rowsXml .= "</row>";
                $currentRow++;
            }
        }

        // Total Summary Row
        $rowsXml .= "<row r=\"{$currentRow}\" ht=\"26\" customHeight=\"1\">";
        $rowsXml .= "<c r=\"A{$currentRow}\" s=\"1\" t=\"inlineStr\"><is><t>TOTALS</t></is></c>";
        $rowsXml .= "<c r=\"B{$currentRow}\" s=\"1\" t=\"inlineStr\"><is><t>" . $this->escapeXml(count($groupedSections) . " Classes") . "</t></is></c>";
        $rowsXml .= "<c r=\"C{$currentRow}\" s=\"1\" t=\"inlineStr\"><is><t>" . $this->escapeXml("{$totalSectionsCount} Sections") . "</t></is></c>";
        $rowsXml .= "<c r=\"D{$currentRow}\" s=\"1\" t=\"inlineStr\"><is><t>" . $this->escapeXml("{$totalSlotsCount} Lectures") . "</t></is></c>";
        $rowsXml .= "<c r=\"E{$currentRow}\" s=\"1\" t=\"inlineStr\"><is><t>Verified Feasible</t></is></c>";
        $rowsXml .= "</row>";

        $mergeXml = '';
        if (!empty($mergeCells)) {
            $mergeXml = '<mergeCells count="' . count($mergeCells) . '">';
            foreach ($mergeCells as $ref) {
                $mergeXml .= '<mergeCell ref="' . $ref . '"/>';
            }
            $mergeXml .= '</mergeCells>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews>'
            . '<sheetView tabSelected="0" workbookViewId="0" showGridLines="1">'
            . '<pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/>'
            . '</sheetView>'
            . '</sheetViews>'
            . '<cols>'
            . '<col min="1" max="1" width="26" customWidth="1"/>'
            . '<col min="2" max="2" width="22" customWidth="1"/>'
            . '<col min="3" max="3" width="22" customWidth="1"/>'
            . '<col min="4" max="4" width="28" customWidth="1"/>'
            . '<col min="5" max="5" width="24" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . $mergeXml
            . '<pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>'
            . '</worksheet>';
    }

    /**
     * Build the XML for a single Class's timetable sheet (used in whole institute multi-sheet workbook).
     */
    protected function buildClassSheetXml(
        string $instituteName,
        string $termName,
        string $className,
        $classSections,
        array $days,
        $timeSlots,
        $allSlots
    ): string {
        $rowsXml = '';
        $mergeCells = [];

        $totalCols = 2 + $timeSlots->count();
        $lastColLetter = $this->getColumnLetter($totalCols);

        // Row 1: Title Banner
        $rowsXml .= '<row r="1" ht="36" customHeight="1">';
        $rowsXml .= '<c r="A1" s="1" t="inlineStr"><is><t>' . $this->escapeXml("CLASS TIMETABLE: {$className}") . '</t></is></c>';
        for ($c = 2; $c <= $totalCols; $c++) {
            $colLetter = $this->getColumnLetter($c);
            $rowsXml .= "<c r=\"{$colLetter}1\" s=\"1\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = "A1:{$lastColLetter}1";

        // Row 2: Subtitle
        $rowsXml .= '<row r="2" ht="22" customHeight="1">';
        $rowsXml .= '<c r="A2" s="2" t="inlineStr"><is><t>' . $this->escapeXml("{$instituteName} • Academic Session: {$termName}") . '</t></is></c>';
        for ($c = 2; $c <= $totalCols; $c++) {
            $colLetter = $this->getColumnLetter($c);
            $rowsXml .= "<c r=\"{$colLetter}2\" s=\"2\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = "A2:{$lastColLetter}2";

        $currentRow = 3;
        $timeOverlap = function (string $s1, string $e1, string $s2, string $e2): bool {
            return ($s1 < $e2) && ($e1 > $s2);
        };

        foreach ($classSections as $section) {
            $sectionLabel = ($section->instituteClass->custom_name ?? $className) . ' - ' . ($section->section_name ?? 'Default');
            $roomLabel = $section->room ? " (Default Room: Room {$section->room->room_number})" : '';

            // Blank spacer
            $rowsXml .= "<row r=\"{$currentRow}\" ht=\"12\" customHeight=\"1\"/>";
            $currentRow++;

            // Section Banner Row
            $rowsXml .= "<row r=\"{$currentRow}\" ht=\"26\" customHeight=\"1\">";
            $rowsXml .= "<c r=\"A{$currentRow}\" s=\"3\" t=\"inlineStr\"><is><t>" . $this->escapeXml("SECTION: {$sectionLabel}{$roomLabel}") . "</t></is></c>";
            for ($c = 2; $c <= $totalCols; $c++) {
                $colLetter = $this->getColumnLetter($c);
                $rowsXml .= "<c r=\"{$colLetter}{$currentRow}\" s=\"3\"/>";
            }
            $rowsXml .= '</row>';
            $mergeCells[] = "A{$currentRow}:{$lastColLetter}{$currentRow}";
            $currentRow++;

            // Column Headers Row: DAY | ROOM | Time 1 | Time 2 | ...
            $rowsXml .= "<row r=\"{$currentRow}\" ht=\"28\" customHeight=\"1\">";
            $rowsXml .= "<c r=\"A{$currentRow}\" s=\"4\" t=\"inlineStr\"><is><t>DAY</t></is></c>";
            $rowsXml .= "<c r=\"B{$currentRow}\" s=\"4\" t=\"inlineStr\"><is><t>ROOM</t></is></c>";
            foreach ($timeSlots as $tsIdx => $ts) {
                $colLetter = $this->getColumnLetter(3 + $tsIdx);
                $timeHeader = date('g:i', strtotime($ts['start'])) . ' – ' . date('g:i A', strtotime($ts['end']));
                $rowsXml .= "<c r=\"{$colLetter}{$currentRow}\" s=\"4\" t=\"inlineStr\"><is><t>" . $this->escapeXml($timeHeader) . "</t></is></c>";
            }
            $rowsXml .= '</row>';
            $currentRow++;

            // Rows per Day
            $secSlots = $allSlots->where('class_section_id', $section->id);
            foreach ($days as $day) {
                $cleanDay = strtolower($day);
                $daySlots = $secSlots->filter(fn ($s) => strtolower($s->day_of_week) === $cleanDay);

                $rowsXml .= "<row r=\"{$currentRow}\" ht=\"48\" customHeight=\"1\">";
                $rowsXml .= "<c r=\"A{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml(ucfirst($day)) . "</t></is></c>";

                $firstRoom = $daySlots->first()?->room;
                $dayRoomLabel = $firstRoom ? "Room {$firstRoom->room_number}" : ($section->room ? "Room {$section->room->room_number}" : 'Assigned');
                $rowsXml .= "<c r=\"B{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml($dayRoomLabel) . "</t></is></c>";

                foreach ($timeSlots as $tsIdx => $ts) {
                    $colLetter = $this->getColumnLetter(3 + $tsIdx);
                    $tsStart = $ts['start'];
                    $tsEnd   = $ts['end'];

                    $matchedSlot = $daySlots->first(function ($s) use ($tsStart, $tsEnd, $timeOverlap) {
                        $sStart = substr($s->start_time, 0, 5);
                        $sEnd   = substr($s->end_time, 0, 5);
                        return $timeOverlap($sStart, $sEnd, $tsStart, $tsEnd);
                    });

                    if ($matchedSlot) {
                        $subj = $matchedSlot->subject->subject_name ?? $matchedSlot->subject->name ?? 'Subject';
                        $tchr = $matchedSlot->teacher->name ?? 'Teacher';
                        $rm = $matchedSlot->room ? "[Room {$matchedSlot->room->room_number}]" : '';

                        $cellContent = "{$subj}\n{$tchr}";
                        if (!empty($rm)) {
                            $cellContent .= "\n{$rm}";
                        }

                        $rowsXml .= "<c r=\"{$colLetter}{$currentRow}\" s=\"7\" t=\"inlineStr\"><is><t>" . $this->escapeXml($cellContent) . "</t></is></c>";
                    } else {
                        $rowsXml .= "<c r=\"{$colLetter}{$currentRow}\" s=\"6\" t=\"inlineStr\"><is><t>Free</t></is></c>";
                    }
                }

                $rowsXml .= '</row>';
                $currentRow++;
            }
        }

        $mergeXml = '';
        if (!empty($mergeCells)) {
            $mergeXml = '<mergeCells count="' . count($mergeCells) . '">';
            foreach ($mergeCells as $ref) {
                $mergeXml .= '<mergeCell ref="' . $ref . '"/>';
            }
            $mergeXml .= '</mergeCells>';
        }

        $colsXml = '<cols>'
            . '<col min="1" max="1" width="18" customWidth="1"/>'
            . '<col min="2" max="2" width="20" customWidth="1"/>'
            . '<col min="3" max="' . $totalCols . '" width="28" customWidth="1"/>'
            . '</cols>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews>'
            . '<sheetView tabSelected="0" workbookViewId="0" showGridLines="1">'
            . '<pane ySplit="2" topLeftCell="A3" activePane="bottomLeft" state="frozen"/>'
            . '</sheetView>'
            . '</sheetViews>'
            . $colsXml
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . $mergeXml
            . '<pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>'
            . '</worksheet>';
    }

    /**
     * Package sheets into ZipArchive workbook.
     */
    protected function packageZipWorkbook(ZipArchive $zip, array $sheetNames, array $sheetXmls): void
    {
        // Add [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', $this->buildContentTypesXml(count($sheetNames)));

        // Add _rels/.rels
        $zip->addFromString('_rels/.rels', $this->buildRootRelsXml());

        // Add xl/styles.xml
        $zip->addFromString('xl/styles.xml', $this->buildStylesXml());

        // Add xl/workbook.xml
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbookXml($sheetNames));

        // Add xl/_rels/workbook.xml.rels
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelsXml(count($sheetNames)));

        // Add individual worksheets
        foreach ($sheetXmls as $index => $xml) {
            $sheetNum = $index + 1;
            $zip->addFromString("xl/worksheets/sheet{$sheetNum}.xml", $xml);
        }
    }

    /**
     * Styles XML defining fonts, fills, borders, and cell formatting (xf).
     */
    protected function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="9">'
            . '<font><sz val="10"/><name val="Segoe UI"/><color rgb="FF0F172A"/></font>' // 0: Regular body
            . '<font><b/><sz val="14"/><name val="Segoe UI"/><color rgb="FFFFFFFF"/></font>' // 1: Main Title Banner
            . '<font><b/><sz val="11"/><name val="Segoe UI"/><color rgb="FF1E1B4B"/></font>' // 2: Subtitle
            . '<font><sz val="9.5"/><name val="Segoe UI"/><color rgb="FF475569"/></font>' // 3: Metadata Info
            . '<font><b/><sz val="10.5"/><name val="Segoe UI"/><color rgb="FFFFFFFF"/></font>' // 4: Column Header
            . '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FF334155"/></font>' // 5: Time Slot Header
            . '<font><sz val="10"/><name val="Segoe UI"/><color rgb="FF94A3B8"/></font>' // 6: Empty Slot Free/Dash
            . '<font><sz val="9.5"/><name val="Segoe UI"/><color rgb="FF0F172A"/></font>' // 7: Data Cell Regular
            . '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FF0F172A"/></font>' // 8: Bold Cell (Day/Room)
            . '</fonts>'
            . '<fills count="8">'
            . '<fill><patternFill patternType="none"/></fill>' // 0: None
            . '<fill><patternFill patternType="gray125"/></fill>' // 1: Gray125
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1E293B"/></patternFill></fill>' // 2: Dark Slate Navy (#1e293b)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFE0E7FF"/></patternFill></fill>' // 3: Light Indigo (#e0e7ff)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/></patternFill></fill>' // 4: Light Slate (#f1f5f9)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF312E81"/></patternFill></fill>' // 5: Deep Indigo (#312e81)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' // 6: Light Gray (#f8fafc)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>' // 7: White
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>' // 0: None
            . '<border>'
            . '<left style="thin"><color rgb="FFCBD5E1"/></left>'
            . '<right style="thin"><color rgb="FFCBD5E1"/></right>'
            . '<top style="thin"><color rgb="FFCBD5E1"/></top>'
            . '<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>'
            . '</border>' // 1: Thin Gray
            . '</borders>'
            . '<cellStyleXfs count="1">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
            . '</cellStyleXfs>'
            . '<cellXfs count="9">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1"/>' // 0: Default
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' // 1: Main Title
            . '<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' // 2: Subtitle
            . '<xf numFmtId="0" fontId="3" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' // 3: Metadata Row
            . '<xf numFmtId="0" fontId="4" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' // 4: Column Header
            . '<xf numFmtId="0" fontId="5" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' // 5: Time Slot Header
            . '<xf numFmtId="0" fontId="6" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' // 6: Empty Slot Free
            . '<xf numFmtId="0" fontId="7" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' // 7: Lecture Slot Data
            . '<xf numFmtId="0" fontId="8" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" indent="0"/></xf>' // 8: Bold Center Cell (Day/Room)
            . '</cellXfs>'
            . '</styleSheet>';
    }

    /**
     * Workbook XML defining sheet names and order.
     */
    protected function buildWorkbookXml(array $sheetNames): string
    {
        $sheetsXml = '';
        foreach ($sheetNames as $index => $name) {
            $sheetId = $index + 1;
            $rId = "rId{$sheetId}";
            $sheetsXml .= '<sheet name="' . $this->escapeXml($name) . '" sheetId="' . $sheetId . '" r:id="' . $rId . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheetsXml . '</sheets>'
            . '</workbook>';
    }

    /**
     * Workbook relationships XML linking sheets to their XML parts.
     */
    protected function buildWorkbookRelsXml(int $sheetCount): string
    {
        $relsXml = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $relsXml .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
        }
        $stylesRId = $sheetCount + 1;
        $relsXml .= '<Relationship Id="rId' . $stylesRId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $relsXml
            . '</Relationships>';
    }

    /**
     * Content Types XML declaring all XML parts.
     */
    protected function buildContentTypesXml(int $sheetCount): string
    {
        $sheetsOverrideXml = '';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $sheetsOverrideXml .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . $sheetsOverrideXml
            . '</Types>';
    }

    /**
     * Root package relationships XML.
     */
    protected function buildRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    /**
     * Convert 1-based column number to Excel column letter (1 -> A, 27 -> AA).
     */
    protected function getColumnLetter(int $colNum): string
    {
        $letter = '';
        while ($colNum > 0) {
            $mod = ($colNum - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $colNum = (int)(($colNum - $mod) / 26);
        }
        return $letter;
    }

    /**
     * Escape XML special characters.
     */
    protected function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
