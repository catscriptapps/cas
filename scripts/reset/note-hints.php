<?php
// /scripts/reset/note-hints.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Section;
use App\Models\NoteHint;

/**
 * Resets the Note Hints table -- reusable canned phrases a Company Admin
 * (or platform Admin) can save from any notes-type field on the inspection
 * detail page, so anyone filling out a report can insert them again later
 * instead of retyping the same finding. Mirrors legacy's per-field hints
 * tables (hit_inspections_summaries_hints, hit_sections_hints), but unified
 * into one table discriminated by `type`, since all three pools share the
 * exact same shape and CRUD behavior:
 *   - 'inspection_summary' -- company-wide (section_id null), matches
 *     legacy's actual scoping (inspection_id is recorded on that table but
 *     never filtered on).
 *   - 'question_notes'     -- shared by every question in one Section,
 *     matching legacy's hit_sections_hints (scoped by section, not by the
 *     individual question).
 *   - 'section_comment'    -- same section-level scoping as question_notes;
 *     legacy never built this one, but the user asked for parity across all
 *     three note fields.
 */
function resetNoteHintsTable(): array
{
    $messages = [];
    try {
        Capsule::schema()->dropIfExists('note_hints');

        Capsule::schema()->create('note_hints', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->string('type', 30);
            $table->text('hint_text');
            $table->integer('pos_index')->default(0);
            $table->timestamps();
        });

        $messages[] = "table 'note_hints' created.";

        // Seeded from legacy's hit_sections_hints -- the only company with
        // any hint content there was HomeWorks Advantages Inc. (legacy
        // company_id=2, same company/section names as this app's own
        // company_id=2), ported verbatim (including its original wording)
        // rather than rewritten. Seeded identically for every company, same
        // as the section/question starter template, so every company
        // starts with a working example set instead of an empty pool.
        $sectionHints = [
            'Roofing' => [
                'Roof deflections can be structure, concern, and further investigations recomended',
                'Roof tune-ups recomend as needed on all roofs This can help prevent water penetrations and extend the roof performance and life!',
                'Skylights are a common cocner of leaks monitor closely repair as needed if needed',
                'Roof tune ups can help prevent water leaks and extend the life of roof protection',
            ],
            'Exterior' => [
                'Grading plays a very big part in your homes drainage and protection of waters concerns',
                'Spalding bricks common reason water, repair brick restoration as needed',
                'step cracks and Spalding bricks water penetrations concerns',
                'brick stone step cracks movement repair and monitor for new movement Repair as needed most brick stone exterior finishes none structure but the foundation they sit on is',
                'cracks and step cracks in foundation walls are structure and need your attention. Repair as needed and monitor.',
            ],
            'Structure' => [
                'cracks and step cracks in foundation walls are structure and need your attention. Repair as needed and monitor',
                'Foundation not likely to have weeping tile around exterior of foundation increasing the possibility of water penetration',
                'Repair floor crack outside of garage door on exterior.',
            ],
            'Insulation and Ventilation' => [
                'Repair increase soffit venting a time of insualtion top up recommended.',
                'An electrical power fan can be installed directly over a shower or bathtub provided the unit is a hardwired model that is specifically rated as "suitable for wet locations" and is protected by a Ground-Fault Circuit Interrupter (GFCI)',
            ],
            'Electrical' => [
                'Update GFCI protection as needed recomended!',
            ],
            'Heating and Cooling' => [
                'HRV working as should not performance tested Keep filters changed cleaned as needed and or as per manufacture requirements',
            ],
            'Interior' => [
                'Update repair replaced as needed for your safety and by law',
                'Windows seals foggy window noted Low R value repair replace as needed.',
                'repair all damages as needed before paint recomended.',
                'Possibel mold noted not tested, testing needed to confirm Recomend treat as mold till tested No testing done day of inspection',
                'age of home many building product like ceiling tile Lath and plaster drywall and compounds could contain asbestos. Treat as is till tested recomended. NO testing done day of inspection',
            ],
        ];

        $hintCount = 0;
        foreach ([1, 2] as $companyId) {
            foreach ($sectionHints as $sectionName => $hints) {
                $sectionId = Section::where('company_id', $companyId)->where('name', $sectionName)->value('id');
                if (!$sectionId) continue;

                foreach ($hints as $posIndex => $hintText) {
                    NoteHint::create([
                        'company_id' => $companyId,
                        'section_id' => $sectionId,
                        'type'       => NoteHint::TYPE_QUESTION_NOTES,
                        'hint_text'  => $hintText,
                        'pos_index'  => $posIndex,
                    ]);
                    $hintCount++;
                }
            }
        }

        $messages[] = "Successfully imported $hintCount section hints.";
    } catch (\Throwable $e) {
        $messages[] = "ERROR resetting note_hints: " . $e->getMessage();
    }
    return $messages;
}
