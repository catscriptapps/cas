<?php
// /scripts/reset/faqs.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Faq;

function resetFaqsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Faq())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('status_id')->default(Faq::STATUS_ACTIVE)->index();
            $table->integer('display_order')->default(0)->index();
            $table->unsignedBigInteger('orig_user_id')->nullable()->index();
            $table->timestamps();

            // Foreign key to users table
            $table->foreign('orig_user_id')->references('id')->on('users')->onDelete('set null');
        });

        $messages[] = "created {$tableName} table";

        // ---------------------------------------------------------
        // Default FAQ entries for Home Comfort Reports
        // ---------------------------------------------------------
        $defaultFaqs = [
            // --- Platform overview ---
            [
                'question' => 'What is Home Comfort Reports?',
                'answer'   => 'Home Comfort Reports is the platform inspection companies use to turn a completed on-site visit into a clear, professional report. Once an inspector has walked a property, they sign in and document every finding through the same disciplined, standards-based structure -- photo and video evidence included -- and a secure access code is issued so the client can view and download the finished report.'
            ],
            [
                'question' => 'Who is the platform for?',
                'answer'   => 'Inspection companies and the certified inspectors who work for them, who use the platform to document visits and generate reports -- and the clients who receive those reports, who need nothing more than an access code to view theirs.'
            ],
            [
                'question' => 'Does Home Comfort Reports conduct the inspection itself?',
                'answer'   => 'No. The on-site visit is carried out by your inspector and their company. Home Comfort Reports picks up from there -- it\'s the platform used to document what was found and turn it into your report.'
            ],

            // --- For inspection companies ---
            [
                'question' => 'How does an inspection company get set up on the platform?',
                'answer'   => 'Reach out through the Contact page and tell us a bit about your company. We\'ll walk you through getting your team set up from there.'
            ],
            [
                'question' => 'Can more than one inspection company use Home Comfort Reports?',
                'answer'   => 'Yes. The platform is built for multiple companies at once -- each with its own inspectors, branding, and reports, kept completely separate from every other company on the platform.'
            ],
            [
                'question' => 'How does an inspector create a report?',
                'answer'   => 'After signing in, an inspector documents the completed visit section by section -- recording findings and attaching the photo and video evidence captured during the walkthrough. Once every section is complete, the report is finalized and an access code is generated automatically.'
            ],
            [
                'question' => 'Does the report have to be documented on-site, or can it be done later?',
                'answer'   => 'Whatever works best for the inspector -- some document right after wrapping up the walkthrough, others prefer to sit down with their notes later. Either way, from a phone, tablet, or computer.'
            ],

            // --- The inspection report ---
            [
                'question' => 'What does an inspection report cover?',
                'answer'   => 'Every major system: roofing, exterior and structure, electrical, plumbing, heating and cooling, insulation and ventilation, and interior components. Each is its own report section with its own checklist, so nothing gets lumped together or skipped.'
            ],
            [
                'question' => 'What standards does the platform follow?',
                'answer'   => 'Report checklists are built against recognized home inspection standards of practice, so every report has the same consistent scope and structure -- no matter which company or inspector created it.'
            ],
            [
                'question' => 'What\'s the difference between a "finding" and a "limitation" in a report?',
                'answer'   => 'A finding is something the inspector directly observed and evaluated -- a condition, a defect, a maintenance item. A limitation is a note about something that couldn\'t be fully accessed or evaluated during the visit (a locked area, a snow-covered roof, a disconnected utility), so you know exactly where the inspection\'s visibility ended.'
            ],
            [
                'question' => 'Do reports distinguish between items that were tested versus just observed?',
                'answer'   => 'Yes. Each checklist item is categorized as either observed and reported on, or actually operated and tested during the visit, so the report is precise about how thoroughly each item was evaluated.'
            ],

            // --- Documentation & technology ---
            [
                'question' => 'How is a report documented?',
                'answer'   => 'With photos and video, attached directly to the section they belong to -- not notes reconstructed from memory afterward. A photo of the electrical panel lives right there in the Electrical section of the report, not in a separate folder somewhere.'
            ],
            [
                'question' => 'Tell me about the photo and video upload process.',
                'answer'   => 'Inspectors use a purpose-built upload tool right from their phone, tablet, or computer: a quick modal lets them select multiple photos or video clips at once, see instant thumbnail previews, and tag them straight to the section they belong to -- no shuffling files between apps or emailing them to themselves first.'
            ],
            [
                'question' => 'Will iPhone photos work with the platform?',
                'answer'   => 'Yes. Photos captured in Apple\'s HEIC format are automatically converted during upload, so nothing gets lost or shows up broken in the final report regardless of what device was used to document the visit.'
            ],
            [
                'question' => 'Can reports include diagrams or reference images?',
                'answer'   => 'Where they help explain a finding, yes -- alongside the actual photos and video captured at the property, sections can include reference diagrams to make a technical issue easier to understand at a glance.'
            ],

            // --- Reports & delivery ---
            [
                'question' => 'What does the final report look like?',
                'answer'   => 'A polished, cover-paged PDF: property details up front, the standards of practice the inspection was performed against, a section-by-section breakdown of every system with findings and the photos/video backing them up, followed by a full media gallery and summary.'
            ],
            [
                'question' => 'How soon will my report be ready?',
                'answer'   => 'As soon as your inspector finishes documenting the visit, your report and access code are generated immediately -- there\'s no extra processing delay on our end. Exact turnaround depends on when your inspector completes documentation.'
            ],
            [
                'question' => 'Can I share my report with my realtor, lender, or the seller?',
                'answer'   => 'Absolutely. Once your report is ready, share the access code with anyone who needs to see it -- they\'ll view or download the exact same report you do.'
            ],

            // --- Accessing your report ---
            [
                'question' => 'How do I access my report?',
                'answer'   => 'Enter the access code your inspector provided into the "Have a Report Waiting?" box on our home page and click "Get My Report." No account or password required.'
            ],
            [
                'question' => 'Do I need to create an account to view my report?',
                'answer'   => 'No. The access code alone is enough to view and download your report. Accounts are only needed by inspectors and companies using the platform to create reports.'
            ],
            [
                'question' => 'Does my access code expire?',
                'answer'   => 'Access codes stay valid for a limited window after your report is issued. If yours has expired, contact us with your name and the property address and we\'ll help you get access again.'
            ],
            [
                'question' => 'I lost my access code -- what do I do?',
                'answer'   => 'Reach out through the Contact page with your name and the property address, and we\'ll help track it down.'
            ],

            // --- Security / misc ---
            [
                'question' => 'Is my information kept secure?',
                'answer'   => 'Yes. Report access codes are single-purpose, and every account on the platform is protected by its own authenticated session. Your report and property details are never shared with anyone you haven\'t authorized.'
            ],
            [
                'question' => 'How do I contact support?',
                'answer'   => 'Use the Contact page linked in the site footer, or email us directly -- whichever\'s easier. Most report-access and platform questions are answered above.'
            ],
        ];

        foreach ($defaultFaqs as $index => $faq) {
            Faq::create([
                'question'      => $faq['question'],
                'answer'        => $faq['answer'],
                'status_id'     => Faq::STATUS_ACTIVE,
                'display_order' => $index + 1,
                'orig_user_id'  => 1,
            ]);
        }

        $messages[] = "seeded " . count($defaultFaqs) . " faqs with active status";
    } catch (\Throwable $e) {
        $messages[] = "Error resetting " . ($tableName ?? 'faqs') . " table: " . $e->getMessage();
    }

    return $messages;
}
