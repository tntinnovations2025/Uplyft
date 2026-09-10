<?php

namespace App\Services;

use App\Models\ClassSection;
use App\Models\InstituteClass;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class ClassSectionConsolidationService
{
    /**
     * Parse raw input string like "9A", "9-B", "Grade 10A" into base class name and section name.
     * Prevents false stripping of multi-letter custom aliases like "FMMA" or "ACCA".
     */
    public function parseClassAndSection(string $inputClassName, ?string $inputSectionName = null): array
    {
        $rawName = trim($inputClassName);
        $section = $inputSectionName ? trim($inputSectionName) : null;

        // Matches strings like "9A", "9-A", "Grade 9 A", "Class 10-B" where trailing letter is preceded by numbers/hyphen/space
        if (preg_match('/^([0-9]+|Grade\s*[0-9]+|Class\s*[0-9]+)\s*[\-_\s]+([A-Za-z])$/i', $rawName, $matches) ||
            preg_match('/^([0-9]+)\s*([A-Za-z])$/i', $rawName, $matches)) {
            $baseClass = trim($matches[1]);
            $extractedSec = strtoupper(trim($matches[2]));

            if (! empty($baseClass)) {
                return [
                    'class_name' => $baseClass,
                    'section_name' => $section ?: $extractedSec,
                ];
            }
        }

        return [
            'class_name' => $rawName,
            'section_name' => $section ?: 'A',
        ];
    }

    /**
     * Consolidate all existing classes in an institute so that classes like "9A" and "9B"
     * are merged into class "9" with sections "A" and "B".
     */
    public function consolidateInstituteClasses(int $instituteId, ?int $academicTermId = null): void
    {
        DB::transaction(function () use ($instituteId, $academicTermId) {
            $classes = InstituteClass::where('institute_id', $instituteId)
                ->when($academicTermId, fn ($q) => $q->where('academic_term_id', $academicTermId))
                ->with(['sections', 'subjects'])
                ->get();

            $grouped = [];

            foreach ($classes as $cls) {
                $parsed = $this->parseClassAndSection($cls->custom_name);
                $baseName = strtolower($parsed['class_name']);

                if (! isset($grouped[$baseName])) {
                    $grouped[$baseName] = [
                        'canonical_name' => $parsed['class_name'],
                        'classes' => [],
                    ];
                }
                $grouped[$baseName]['classes'][] = [
                    'model' => $cls,
                    'inferred_sec' => $parsed['section_name'],
                ];
            }

            foreach ($grouped as $baseKey => $data) {
                // If multiple class records match the same base name (e.g. 9A & 9B both map to 9)
                if (count($data['classes']) > 1) {
                    // Pick or create the main target class (e.g. named "9" or first item)
                    $targetClass = null;
                    foreach ($data['classes'] as $item) {
                        if (strtolower(trim($item['model']->custom_name)) === strtolower($data['canonical_name'])) {
                            $targetClass = $item['model'];
                            break;
                        }
                    }

                    if (! $targetClass) {
                        $targetClass = $data['classes'][0]['model'];
                        $targetClass->update(['custom_name' => $data['canonical_name']]);
                    }

                    foreach ($data['classes'] as $item) {
                        $cls = $item['model'];
                        if ($cls->id === $targetClass->id) {
                            continue;
                        }

                        // Re-link subjects to target class
                        Subject::where('institute_class_id', $cls->id)
                            ->update(['institute_class_id' => $targetClass->id]);

                        // Move sections to target class
                        foreach ($cls->sections as $sec) {
                            $newSecName = ($sec->section_name === 'A' || empty($sec->section_name)) ? $item['inferred_sec'] : $sec->section_name;

                            // Ensure section name doesn't conflict
                            $exists = ClassSection::where('institute_class_id', $targetClass->id)
                                ->where('section_name', $newSecName)
                                ->exists();

                            if ($exists) {
                                $newSecName = $item['inferred_sec'];
                            }

                            $sec->update([
                                'institute_class_id' => $targetClass->id,
                                'section_name' => $newSecName,
                            ]);
                        }

                        // Delete merged class record
                        $cls->delete();
                    }
                }
            }
        });
    }
}
