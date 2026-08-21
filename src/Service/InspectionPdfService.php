<?php
// /src/Service/InspectionPdfService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\Inspection;
use App\Models\CoverPage;
use App\Models\Standard;
use App\Models\Section;
use App\Models\Question;
use App\Models\InspectionQuestion;
use App\Models\InspectionQuestionOption;
use App\Models\InspectionQuestionField;
use App\Models\InspectionSectionComment;
use App\Models\InspectionPicture;
use App\Models\InspectionPictureSection;
use App\Models\InspectionPictureContract;
use App\Models\SectionDiagram;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Assembles a finished inspection into a downloadable PDF report: Cover
 * Pages front, an auto-generated title page, Standards of Practice, Contract
 * Documents (if any photo is assigned as one -- deliberately placed right
 * before Section 1, per this app's own Photo Library design rather than
 * legacy's earlier front-of-book placement), one page-broken block per
 * checklist Section with its photos immediately followed by that section's
 * full-bleed Diagrams (if any), the per-inspection summary, the company's
 * general summary, Cover Pages back.
 */
class InspectionPdfService
{
    private const STORAGE_SUBDIR = 'pdfs/inspections';

    /**
     * Whether any content block has been written yet this generate() call.
     * Every block after the first gets page-break-before instead of every
     * block getting page-break-after, so the report never ends with a
     * dangling blank page when nothing follows the last written block
     * (e.g. no back cover images, or no inspection summary).
     */
    private bool $hasWrittenBlock = false;

    /** Whether the page currently being written to is zero-margin ("bleed", for cover images) or the normal content margin. */
    private string $marginMode = 'content';

    private float $pageWidthMm = 0.0;
    private float $pageHeightMm = 0.0;
    private const CONTENT_MARGIN_MM = 14;

    /**
     * Generates the PDF for a finalized inspection, saves it to disk, and
     * returns the bare filename (matching the convention already used for
     * company logos / cover page images -- only the filename is persisted
     * on the model, not a full path).
     */
    public function generate(Inspection $inspection): string
    {
        $this->hasWrittenBlock = false;
        $this->marginMode = 'content';

        $inspection->loadMissing(['company', 'country', 'region', 'inspector']);
        $company = $inspection->company;
        if (!$company) {
            throw new \RuntimeException('Inspection has no associated company.');
        }

        $fullAddress = $this->formatAddress($inspection);

        $mpdf = $this->createMpdf();
        $this->pageWidthMm = (float)$mpdf->w;
        $this->pageHeightMm = (float)$mpdf->h;
        $mpdf->WriteHTML($this->renderPartial('styles', []), \Mpdf\HTMLParserMode::HEADER_CSS);

        $this->writeCoverImages($mpdf, $company->id, CoverPage::LOCATION_FRONT);

        $this->writeBlock($mpdf, $this->renderPartial('cover-detail', [
            'company' => $company,
            'inspection' => $inspection,
            'fullAddress' => $fullAddress,
        ]));

        $this->writeStandards($mpdf, $company->id);
        $this->writeContracts($mpdf, $inspection, $fullAddress);
        $this->writeSections($mpdf, $inspection, $fullAddress);
        $this->writePhotoLibrary($mpdf, $inspection, $fullAddress);

        if (trim((string)$inspection->inspection_summary) !== '') {
            $this->writeBlock($mpdf, $this->renderPartial('summary', [
                'inspection' => $inspection,
            ]));
        }

        if (trim((string)$company->general_summary) !== '') {
            $this->writeBlock($mpdf, $this->renderPartial('general-summary', [
                'company' => $company,
            ]));
        }

        $this->writeCoverImages($mpdf, $company->id, CoverPage::LOCATION_BACK);

        return $this->save($mpdf, $inspection);
    }

    /**
     * Deletes a previously generated PDF (if any) -- called before
     * re-finalizing so a stale file never lingers alongside the fresh one.
     */
    public function deleteExisting(Inspection $inspection): void
    {
        if (empty($inspection->file_name)) return;

        $dir = $this->storageDir((int)$inspection->company_id);
        $path = realpath($dir . '/' . $inspection->file_name);
        if ($path && strpos($path, realpath($dir)) === 0 && file_exists($path)) {
            @unlink($path);
        }
    }

    private function createMpdf(): Mpdf
    {
        $fontDirPath = dirname(__DIR__, 2) . '/server/storage/fonts';
        $tempDir = dirname(__DIR__, 2) . '/server/storage/mpdf-temp';

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'margin_top' => 14,
            'margin_bottom' => 14,
            'margin_left' => 14,
            'margin_right' => 14,
            'default_font_size' => 9,
            'tempDir' => $tempDir,
            'fontDir' => array_merge($fontDirs, [$fontDirPath]),
            'fontdata' => $fontData + [
                // No italic Quicksand file is on hand -- I/BI fall back to
                // the upright weight rather than mPDF fake-slanting it.
                'quicksand' => [
                    'R' => 'Quicksand-Regular.ttf',
                    'B' => 'Quicksand-Bold.ttf',
                    'I' => 'Quicksand-Regular.ttf',
                    'BI' => 'Quicksand-Bold.ttf',
                ],
                'quicksandlight' => ['R' => 'Quicksand-Light.ttf'],
                'quicksandmedium' => ['R' => 'Quicksand-Medium.ttf'],
                'quicksandsemibold' => ['R' => 'Quicksand-SemiBold.ttf'],
            ],
            'default_font' => 'quicksand',
        ]);
    }

    /**
     * Writes one HTML block as its own page-break unit. `$marginMode`
     * controls whether the page this block starts on uses the normal
     * content margin or a zero margin for full-bleed images -- switching
     * modes always costs an explicit mPDF <pagebreak> tag (the only way to
     * change a page's margins), since plain CSS page-break-before can't do
     * that; staying in the same mode as the previous block just uses the
     * cheaper CSS class. The very first block of the document sets margins
     * directly with no page-break markup at all, since there's no prior
     * page to break from.
     */
    private function writeBlock(Mpdf $mpdf, string $innerHtml, string $marginMode = 'content', string $extraClass = ''): void
    {
        $modeChanged = $marginMode !== $this->marginMode;
        $margin = $marginMode === 'bleed' ? 0 : self::CONTENT_MARGIN_MM;

        if (!$this->hasWrittenBlock) {
            $mpdf->SetMargins($margin, $margin, $margin);
            $mpdf->SetAutoPageBreak(true, $margin);
        } elseif ($modeChanged) {
            $mpdf->WriteHTML(sprintf(
                '<pagebreak margin-left="%1$smm" margin-right="%1$smm" margin-top="%1$smm" margin-bottom="%1$smm" />',
                $margin
            ));
        }
        $this->marginMode = $marginMode;

        $class = trim('pdf-block ' . $extraClass . (($this->hasWrittenBlock && !$modeChanged) ? ' pdf-page-break-before' : ''));
        $mpdf->WriteHTML('<div class="' . $class . '">' . $innerHtml . '</div>');
        $this->hasWrittenBlock = true;
    }

    /**
     * Writes one full-bleed image page per Cover Page in the given
     * front/back group, ordered by pos_index, each its own page-break unit,
     * sized to exactly fill the zero-margin page (see writeBlock()) rather
     * than sitting inset within the normal content margin.
     */
    private function writeCoverImages(Mpdf $mpdf, int $companyId, int $location): void
    {
        $pages = CoverPage::where('company_id', $companyId)
            ->where('page_location', $location)
            ->orderBy('pos_index')
            ->get();

        $dir = dirname(__DIR__, 2) . '/public/images/uploads/cover-pages/' . $companyId . '/';
        $imgStyle = sprintf('width:%1$smm;height:%2$smm;', $this->pageWidthMm, $this->pageHeightMm);

        foreach ($pages as $page) {
            $path = realpath($dir . $page->image_name);
            if (!$path) continue;

            $this->writeBlock($mpdf, '<img src="' . $path . '" style="' . $imgStyle . '" alt="">', 'bleed', 'pdf-cover-image-page');
        }
    }

    /**
     * Writes one page per Standards of Practice page, in order.
     */
    private function writeStandards(Mpdf $mpdf, int $companyId): void
    {
        $pages = Standard::where('company_id', $companyId)->orderBy('pos_index')->get();

        foreach ($pages as $page) {
            $this->writeBlock($mpdf, $page->content, 'content', 'pdf-standards-page');
        }
    }

    /**
     * Writes the Contract Documents page, if any photos are assigned as
     * contracts (via the Photo Library's "Contract" checkbox) -- right
     * before Section 1, same photo-grid treatment as a section's own
     * photos rather than legacy's full-bleed single-image-per-page layout.
     */
    private function writeContracts(Mpdf $mpdf, Inspection $inspection, string $fullAddress): void
    {
        $links = InspectionPictureContract::with('picture')
            ->where('inspection_id', $inspection->id)
            ->orderBy('pos_index')
            ->get()
            ->filter(fn($link) => $link->picture !== null)
            ->values();

        if ($links->isEmpty()) return;

        $html = $this->renderPartial('contracts', [
            'inspection' => $inspection,
            'links' => $links,
            'fullAddress' => $fullAddress,
        ]);

        $this->writeBlock($mpdf, $html);
    }

    /**
     * Writes one page-break block per active checklist Section: its
     * questions (joined against this inspection's saved answers), the
     * section's free-text comment, and its photo grid.
     */
    private function writeSections(Mpdf $mpdf, Inspection $inspection, string $fullAddress): void
    {
        $sections = Section::where('company_id', $inspection->company_id)
            ->where('status_id', 1)
            ->orderBy('pos_index')
            ->get();

        foreach ($sections as $section) {
            $questions = Question::with(['options', 'fields'])
                ->where('company_id', $inspection->company_id)
                ->where('section_id', $section->id)
                ->where('status_id', 1)
                ->orderBy('pos_index')
                ->get();

            $questionIds = $questions->pluck('id');

            $answers = InspectionQuestion::where('inspection_id', $inspection->id)
                ->whereIn('question_id', $questionIds)->get()->keyBy('question_id');
            $selectedOptionsByQuestion = InspectionQuestionOption::where('inspection_id', $inspection->id)
                ->whereIn('question_id', $questionIds)->get()->groupBy('question_id');
            $fieldResponsesByQuestion = InspectionQuestionField::where('inspection_id', $inspection->id)
                ->whereIn('question_id', $questionIds)->get()->groupBy('question_id');

            $sectionComment = InspectionSectionComment::where('inspection_id', $inspection->id)
                ->where('section_id', $section->id)->first();

            $pictureLinks = InspectionPictureSection::with('picture')
                ->where('inspection_id', $inspection->id)
                ->where('section_id', $section->id)
                ->orderBy('pos_index')
                ->get()
                ->filter(fn($link) => $link->picture !== null)
                ->values();

            $html = $this->renderPartial('section', [
                'inspection' => $inspection,
                'section' => $section,
                'questions' => $questions,
                'answers' => $answers,
                'selectedOptionsByQuestion' => $selectedOptionsByQuestion,
                'fieldResponsesByQuestion' => $fieldResponsesByQuestion,
                'sectionComment' => $sectionComment,
                'pictureLinks' => $pictureLinks,
                'fullAddress' => $fullAddress,
            ]);

            $this->writeBlock($mpdf, $html);

            $this->writeSectionDiagrams($mpdf, (int)$inspection->company_id, (int)$section->id);
        }
    }

    /**
     * Writes one full-bleed image page per Diagram uploaded for this
     * Section (company-wide, not per-inspection -- see SectionDiagram),
     * ordered by pos_index, immediately after that section's own
     * content/photos block -- same full-bleed treatment as Cover Pages,
     * sized to exactly fill the zero-margin page. The admin-facing caption
     * is an organizational note only and isn't printed here, matching Cover
     * Pages (which carry no caption at all).
     */
    private function writeSectionDiagrams(Mpdf $mpdf, int $companyId, int $sectionId): void
    {
        $diagrams = SectionDiagram::where('company_id', $companyId)
            ->where('section_id', $sectionId)
            ->orderBy('pos_index')
            ->get();

        $dir = dirname(__DIR__, 2) . '/public/images/uploads/section-diagrams/' . $companyId . '/' . $sectionId . '/';
        $imgStyle = sprintf('width:%1$smm;height:%2$smm;', $this->pageWidthMm, $this->pageHeightMm);

        foreach ($diagrams as $diagram) {
            $path = realpath($dir . $diagram->image_name);
            if (!$path) continue;

            $this->writeBlock($mpdf, '<img src="' . $path . '" style="' . $imgStyle . '" alt="">', 'bleed', 'pdf-section-diagram-page');
        }
    }

    /**
     * Writes the Photo Library page(s) -- every photo in the inspection's
     * shared library (see InspectionPicture), regardless of which
     * section(s) it's assigned to or whether it's assigned anywhere at all
     * -- right after the last checklist Section (and its Diagrams), before
     * the inspection/company summaries. Matches legacy's "overall photo
     * gallery" step at that same point in the assembly order.
     */
    private function writePhotoLibrary(Mpdf $mpdf, Inspection $inspection, string $fullAddress): void
    {
        $pictures = InspectionPicture::where('inspection_id', $inspection->id)
            ->orderBy('id')
            ->get();

        if ($pictures->isEmpty()) return;

        $html = $this->renderPartial('photo-library', [
            'inspection' => $inspection,
            'pictures' => $pictures,
            'fullAddress' => $fullAddress,
        ]);

        $this->writeBlock($mpdf, $html);
    }

    private function formatAddress(Inspection $inspection): string
    {
        $parts = array_filter([
            $inspection->city,
            $inspection->region?->region,
            $inspection->postal_code,
            $inspection->country?->country,
        ]);
        return implode(', ', $parts);
    }

    private function renderPartial(string $name, array $vars): string
    {
        $path = __DIR__ . '/../../resources/views/pdf/' . $name . '.php';

        // `include` on a missing file is only a warning in PHP, not a
        // throwable -- silently producing an empty string for that block
        // (which then loses even its page break, since an empty block
        // doesn't force one) rather than failing the whole generation
        // loudly the way a real template bug does. Fail loudly instead, so
        // a partial deployment missing this file surfaces as an error on
        // the finalize action rather than a silently incomplete PDF.
        if (!is_file($path)) {
            throw new \RuntimeException("PDF partial not found: {$name} (expected at {$path})");
        }

        extract($vars);
        ob_start();
        include $path;
        return ob_get_clean();
    }

    private function storageDir(int $companyId): string
    {
        $dir = dirname(__DIR__, 2) . '/public/' . self::STORAGE_SUBDIR . '/' . $companyId . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private function save(Mpdf $mpdf, Inspection $inspection): string
    {
        $dir = $this->storageDir((int)$inspection->company_id);
        $filename = 'inspection-' . $inspection->id . '-' . bin2hex(random_bytes(4)) . '.pdf';
        $mpdf->Output($dir . $filename, Destination::FILE);
        return $filename;
    }
}
