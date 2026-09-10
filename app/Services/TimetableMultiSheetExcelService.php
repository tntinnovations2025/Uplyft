<?php

namespace App\Services;

use App\Models\AcademicTerm;
use App\Models\ClassSection;
use App\Models\Institute;
use App\Models\Timetable;
use ZipArchive;

class TimetableMultiSheetExcelService
{
    /**
     * Generate a multi-sheet Excel (.xlsx) file containing separate tabs for each class.
     *
     * @param int $instituteId
     * @param int $academicTermId
     * @return string Path to temporary .xlsx file
     */
    public function generate(int $instituteId, int $academicTermId): string
    {
        $institute = Institute::find($instituteId);
        $term = AcademicTerm::find($academicTermId);

        $sections = ClassSection::whereHas('instituteClass', function ($q) use ($instituteId, $academicTermId) {
            $q->where('institute_id', $instituteId)
              ->where('academic_term_id', $academicTermId);
        })
        ->with(['instituteClass', 'room'])
        ->get();

        $allSlots = Timetable::where('academic_term_id', $academicTermId)
            ->with(['subject', 'teacher', 'section.instituteClass', 'room'])
            ->orderBy('start_time')
            ->get();

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        // Build unique time slots
        $timeSlots = $allSlots->map(fn ($s) => [
            'start' => substr($s->start_time, 0, 5),
            'end' => substr($s->end_time, 0, 5),
            'key' => substr($s->start_time, 0, 5) . '-' . substr($s->end_time, 0, 5),
        ])->unique('key')->sortBy('start')->values();

        if ($timeSlots->isEmpty()) {
            $defaultSlots = ['08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00'];
            foreach ($defaultSlots as $ds) {
                [$s, $e] = explode('-', $ds);
                $timeSlots->push(['start' => $s, 'end' => $e, 'key' => $ds]);
            }
        }

        // Build grid[section_id][day][timeKey]
        $grid = [];
        foreach ($allSlots as $slot) {
            $secId = $slot->class_section_id;
            $day = strtolower($slot->day_of_week);
            $timeKey = substr($slot->start_time, 0, 5) . '-' . substr($slot->end_time, 0, 5);
            $grid[$secId][$day][$timeKey] = $slot;
        }

        // Group sections by Class (e.g. "Grade 10", "Grade 9")
        $groupedSections = $sections->groupBy(function ($s) {
            $name = trim((string)($s->instituteClass->custom_name ?: $s->instituteClass->name ?: 'Class'));
            if (is_numeric($name)) {
                $name = "Grade " . $name;
            }
            return $name;
        });

        // Create ZipArchive
        $tempFile = tempnam(sys_get_temp_dir(), 'timetable_xlsx_') . '.xlsx';
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create temporary Excel archive.');
        }

        $sheetNames = [];
        $sheetXmls = [];

        // ── 1. Master Overview Sheet ──
        $sheetNames[] = 'Master Summary';
        $sheetXmls[] = $this->buildOverviewSheetXml($institute?->name ?? 'Institute', $term?->name ?? 'Session', $groupedSections, $allSlots);

        // ── 2. Dedicated Sheet for Each Class ──
        $usedNames = ['master summary'];
        foreach ($groupedSections as $className => $classSections) {
            $rawName = trim((string) $className);
            if (empty($rawName)) {
                $rawName = 'Class';
            }

            // Clean tab name for Excel (max 31 chars, no special chars)
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
                $term?->name ?? 'Session',
                $className,
                $classSections,
                $days,
                $timeSlots,
                $grid
            );
        }

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

        $zip->close();

        return $tempFile;
    }

    /**
     * Build the XML for the Master Overview summary sheet.
     */
    protected function buildOverviewSheetXml(string $instituteName, string $termName, $groupedSections, $allSlots): string
    {
        $rowsXml = '';
        $mergeCells = [];

        // Row 1: Title Banner
        $rowsXml .= '<row r="1" ht="32" customHeight="1">';
        $rowsXml .= '<c r="A1" s="1" t="inlineStr"><is><t>' . $this->escapeXml("{$instituteName} • Master Timetable Overview ({$termName})") . '</t></is></c>';
        for ($col = 'B'; $col <= 'E'; $col++) {
            $rowsXml .= "<c r=\"{$col}1\" s=\"1\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = 'A1:E1';

        // Row 2: Subtitle
        $rowsXml .= '<row r="2" ht="22" customHeight="1">';
        $rowsXml .= '<c r="A2" s="2" t="inlineStr"><is><t>' . $this->escapeXml('ACADEMIC CURRICULUM ALLOCATION & CLASS SCHEDULE SUMMARY') . '</t></is></c>';
        for ($col = 'B'; $col <= 'E'; $col++) {
            $rowsXml .= "<c r=\"{$col}2\" s=\"2\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = 'A2:E2';

        // Row 3: Blank
        $rowsXml .= '<row r="3" ht="12" customHeight="1"/>';

        // Row 4: Summary Table Headers
        $rowsXml .= '<row r="4" ht="24" customHeight="1">';
        $rowsXml .= '<c r="A4" s="4" t="inlineStr"><is><t>CLASS / GRADE</t></is></c>';
        $rowsXml .= '<c r="B4" s="4" t="inlineStr"><is><t>SECTION NAME</t></is></c>';
        $rowsXml .= '<c r="C4" s="4" t="inlineStr"><is><t>CLASSROOM</t></is></c>';
        $rowsXml .= '<c r="D4" s="4" t="inlineStr"><is><t>SCHEDULED PERIODS / WEEK</t></is></c>';
        $rowsXml .= '<c r="E4" s="4" t="inlineStr"><is><t>SHEET TAB LINK</t></is></c>';
        $rowsXml .= '</row>';

        $currentRow = 5;
        $totalSlotsCount = 0;
        foreach ($groupedSections as $className => $sections) {
            foreach ($sections as $sec) {
                $secId = $sec->id;
                $secSlotsCount = $allSlots->where('class_section_id', $secId)->count();
                $totalSlotsCount += $secSlotsCount;
                $roomName = $sec->room ? "Room {$sec->room->room_number}" : 'Unassigned';

                $rowsXml .= "<row r=\"{$currentRow}\" ht=\"20\" customHeight=\"1\">";
                $rowsXml .= "<c r=\"A{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml($className) . "</t></is></c>";
                $rowsXml .= "<c r=\"B{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml($sec->section_name ?? 'A') . "</t></is></c>";
                $rowsXml .= "<c r=\"C{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml($roomName) . "</t></is></c>";
                $rowsXml .= "<c r=\"D{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml("{$secSlotsCount} Periods") . "</t></is></c>";
                $rowsXml .= "<c r=\"E{$currentRow}\" s=\"8\" t=\"inlineStr\"><is><t>" . $this->escapeXml("See '{$className}' tab at bottom") . "</t></is></c>";
                $rowsXml .= "</row>";
                $currentRow++;
            }
        }

        // Total Summary Row
        $rowsXml .= "<row r=\"{$currentRow}\" ht=\"24\" customHeight=\"1\">";
        $rowsXml .= "<c r=\"A{$currentRow}\" s=\"1\" t=\"inlineStr\"><is><t>TOTALS</t></is></c>";
        $rowsXml .= "<c r=\"B{$currentRow}\" s=\"1\" t=\"inlineStr\"><is><t>" . $this->escapeXml(count($groupedSections) . " Classes") . "</t></is></c>";
        $rowsXml .= "<c r=\"C{$currentRow}\" s=\"1\" t=\"inlineStr\"><is><t>" . $this->escapeXml($sections->count() . " Total Sections") . "</t></is></c>";
        $rowsXml .= "<c r=\"D{$currentRow}\" s=\"1\" t=\"inlineStr\"><is><t>" . $this->escapeXml("{$totalSlotsCount} Total Lectures") . "</t></is></c>";
        $rowsXml .= "<c r=\"E{$currentRow}\" s=\"1\" t=\"inlineStr\"><is><t>Active &amp; Ready</t></is></c>";
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
            . '<sheetViews><sheetView tabSelected="1" workbookViewId="0" showGridLines="1"/></sheetViews>'
            . '<cols>'
            . '<col min="1" max="1" width="26" customWidth="1"/>'
            . '<col min="2" max="2" width="22" customWidth="1"/>'
            . '<col min="3" max="3" width="22" customWidth="1"/>'
            . '<col min="4" max="4" width="28" customWidth="1"/>'
            . '<col min="5" max="5" width="30" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . $mergeXml
            . '</worksheet>';
    }

    /**
     * Build the XML for a single Class's timetable sheet.
     */
    protected function buildClassSheetXml(string $instituteName, string $termName, string $className, $classSections, array $days, $timeSlots, array $grid): string
    {
        $rowsXml = '';
        $mergeCells = [];

        // Row 1: Title Banner
        $rowsXml .= '<row r="1" ht="32" customHeight="1">';
        $rowsXml .= '<c r="A1" s="1" t="inlineStr"><is><t>' . $this->escapeXml("{$instituteName} • Timetable Schedule") . '</t></is></c>';
        for ($col = 'B'; $col <= 'G'; $col++) {
            $rowsXml .= "<c r=\"{$col}1\" s=\"1\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = 'A1:G1';

        // Row 2: Subtitle
        $rowsXml .= '<row r="2" ht="22" customHeight="1">';
        $rowsXml .= '<c r="A2" s="2" t="inlineStr"><is><t>' . $this->escapeXml("CLASS: {$className} • SESSION: {$termName}") . '</t></is></c>';
        for ($col = 'B'; $col <= 'G'; $col++) {
            $rowsXml .= "<c r=\"{$col}2\" s=\"2\"/>";
        }
        $rowsXml .= '</row>';
        $mergeCells[] = 'A2:G2';

        $currentRow = 3;

        // Render each Section belonging to this Class
        foreach ($classSections as $section) {
            $sectionLabel = ($section->instituteClass->custom_name ?? $className) . ' - ' . ($section->section_name ?? 'Default');
            $roomLabel = $section->room ? " (Assigned Room: Room {$section->room->room_number})" : '';

            // Blank spacer
            $rowsXml .= "<row r=\"{$currentRow}\" ht=\"12\" customHeight=\"1\"/>";
            $currentRow++;

            // Section Banner Row
            $rowsXml .= "<row r=\"{$currentRow}\" ht=\"24\" customHeight=\"1\">";
            $rowsXml .= "<c r=\"A{$currentRow}\" s=\"3\" t=\"inlineStr\"><is><t>" . $this->escapeXml("SECTION: {$sectionLabel}{$roomLabel}") . "</t></is></c>";
            for ($col = 'B'; $col <= 'G'; $col++) {
                $rowsXml .= "<c r=\"{$col}{$currentRow}\" s=\"3\"/>";
            }
            $rowsXml .= '</row>';
            $mergeCells[] = "A{$currentRow}:G{$currentRow}";
            $currentRow++;

            // Column Headers Row: Time Slot | Monday | Tuesday | Wednesday | Thursday | Friday | Saturday
            $rowsXml .= "<row r=\"{$currentRow}\" ht=\"24\" customHeight=\"1\">";
            $rowsXml .= "<c r=\"A{$currentRow}\" s=\"4\" t=\"inlineStr\"><is><t>TIME SLOT</t></is></c>";
            $colLetters = ['B', 'C', 'D', 'E', 'F', 'G'];
            foreach ($days as $dIdx => $day) {
                $colLetter = $colLetters[$dIdx];
                $rowsXml .= "<c r=\"{$colLetter}{$currentRow}\" s=\"4\" t=\"inlineStr\"><is><t>" . $this->escapeXml(strtoupper($day)) . "</t></is></c>";
            }
            $rowsXml .= '</row>';
            $currentRow++;

            // Timetable Slot Rows
            foreach ($timeSlots as $ts) {
                $timeKey = $ts['key'];
                $timeDisplay = "{$ts['start']} - {$ts['end']}";

                $rowsXml .= "<row r=\"{$currentRow}\" ht=\"48\" customHeight=\"1\">";
                // Time Slot Column
                $rowsXml .= "<c r=\"A{$currentRow}\" s=\"5\" t=\"inlineStr\"><is><t>" . $this->escapeXml($timeDisplay) . "</t></is></c>";

                // Day Columns
                foreach ($days as $dIdx => $day) {
                    $colLetter = $colLetters[$dIdx];
                    $slot = $grid[$section->id][$day][$timeKey] ?? null;

                    if ($slot) {
                        $subjectName = $slot->subject?->name ?? $slot->subject?->subject_name ?? 'Subject';
                        $teacherName = $slot->teacher?->name ?? 'Teacher';
                        $roomInfo = $slot->room ? "[Room {$slot->room->room_number}]" : '';
                        
                        $cellText = "{$subjectName}\n{$teacherName}";
                        if (!empty($roomInfo)) {
                            $cellText .= "\n{$roomInfo}";
                        }

                        $rowsXml .= "<c r=\"{$colLetter}{$currentRow}\" s=\"7\" t=\"inlineStr\"><is><t>" . $this->escapeXml($cellText) . "</t></is></c>";
                    } else {
                        $rowsXml .= "<c r=\"{$colLetter}{$currentRow}\" s=\"6\" t=\"inlineStr\"><is><t>-</t></is></c>";
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

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView tabSelected="0" workbookViewId="0" showGridLines="1"/></sheetViews>'
            . '<cols>'
            . '<col min="1" max="1" width="18" customWidth="1"/>'
            . '<col min="2" max="7" width="30" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . $mergeXml
            . '</worksheet>';
    }

    /**
     * Styles XML defining fonts, fills, borders, and cell formatting (xf).
     */
    protected function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="8">'
            . '<font><sz val="10"/><name val="Segoe UI"/><color rgb="FF0F172A"/></font>' // 0: Regular body
            . '<font><b/><sz val="14"/><name val="Segoe UI"/><color rgb="FFFFFFFF"/></font>' // 1: Main Title Banner
            . '<font><b/><sz val="11"/><name val="Segoe UI"/><color rgb="FF1E1B4B"/></font>' // 2: Subtitle
            . '<font><b/><sz val="11"/><name val="Segoe UI"/><color rgb="FFFFFFFF"/></font>' // 3: Section Banner
            . '<font><b/><sz val="10.5"/><name val="Segoe UI"/><color rgb="FFFFFFFF"/></font>' // 4: Column Header
            . '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FF334155"/></font>' // 5: Time Slot Header
            . '<font><sz val="10"/><name val="Segoe UI"/><color rgb="FF94A3B8"/></font>' // 6: Empty Slot Dash
            . '<font><sz val="9.5"/><name val="Segoe UI"/><color rgb="FF334155"/></font>' // 7: Data Cell Regular
            . '</fonts>'
            . '<fills count="8">'
            . '<fill><patternFill patternType="none"/></fill>' // 0: None
            . '<fill><patternFill patternType="gray125"/></fill>' // 1: Gray125
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1E293B"/></patternFill></fill>' // 2: Dark Slate Navy (#1e293b)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFE0E7FF"/></patternFill></fill>' // 3: Light Indigo (#e0e7ff)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF312E81"/></patternFill></fill>' // 4: Deep Indigo (#312e81)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF4338CA"/></patternFill></fill>' // 5: Vibrant Indigo (#4338ca)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/></patternFill></fill>' // 6: Soft Light Slate (#f1f5f9)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>' // 7: Pure White (#ffffff)
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>' // 0: None
            . '<border>' // 1: Thin border
            . '<left style="thin"><color rgb="FFCBD5E1"/></left>'
            . '<right style="thin"><color rgb="FFCBD5E1"/></right>'
            . '<top style="thin"><color rgb="FFCBD5E1"/></top>'
            . '<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>'
            . '</border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="9">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' // 0: Normal
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' // 1: Main Title
            . '<xf numFmtId="0" fontId="2" fillId="3" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' // 2: Subtitle
            . '<xf numFmtId="0" fontId="3" fillId="4" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="left" vertical="center" indent="1"/></xf>' // 3: Section Banner
            . '<xf numFmtId="0" fontId="4" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' // 4: Column Header
            . '<xf numFmtId="0" fontId="5" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' // 5: Time Slot Cell
            . '<xf numFmtId="0" fontId="6" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' // 6: Empty Slot Cell
            . '<xf numFmtId="0" fontId="0" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' // 7: Lecture Slot (Wrap Text)
            . '<xf numFmtId="0" fontId="7" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' // 8: Overview Data Cell
            . '</cellXfs>'
            . '</styleSheet>';
    }

    /**
     * Build workbook.xml listing all sheet tabs.
     */
    protected function buildWorkbookXml(array $sheetNames): string
    {
        $sheetsXml = '';
        foreach ($sheetNames as $index => $name) {
            $sheetId = $index + 1;
            $rId = "rId" . ($index + 2); // rId1 is styles
            $sheetsXml .= '<sheet name="' . $this->escapeXml($name) . '" sheetId="' . $sheetId . '" r:id="' . $rId . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheetsXml . '</sheets>'
            . '</workbook>';
    }

    /**
     * Build workbook.xml.rels mapping relationships.
     */
    protected function buildWorkbookRelsXml(int $sheetCount): string
    {
        $relsXml = '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        for ($i = 1; $i <= $sheetCount; $i++) {
            $rId = "rId" . ($i + 1);
            $relsXml .= '<Relationship Id="' . $rId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $relsXml
            . '</Relationships>';
    }

    /**
     * Build [Content_Types].xml.
     */
    protected function buildContentTypesXml(int $sheetCount): string
    {
        $overrides = '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $overrides .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';

        for ($i = 1; $i <= $sheetCount; $i++) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . $overrides
            . '</Types>';
    }

    /**
     * Build _rels/.rels.
     */
    protected function buildRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    /**
     * Escape XML special characters.
     */
    protected function escapeXml(string $str): string
    {
        return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
