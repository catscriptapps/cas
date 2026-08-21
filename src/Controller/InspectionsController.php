<?php
// /src/Controller/InspectionsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Inspection;
use App\Models\Question;
use App\Models\InspectionQuestion;
use App\Models\InspectionQuestionOption;
use App\Models\InspectionQuestionField;
use App\Models\InspectionSectionComment;
use App\Models\InspectionPicture;
use App\Utils\IdEncoder;
use App\Traits\RecentActivityLogger;
use Src\Service\AuthService;

/**
 * Manages Inspection headers: the list page (with its expiry-driven row
 * coloring), create/edit of the property-address header fields, deletion,
 * and the finalize/reopen lifecycle actions that set the 14-day access-code
 * expiry window PDF generation will eventually also trigger.
 */
class InspectionsController
{
    use RecentActivityLogger;

    /**
     * Scope inspections to what the signed-in account is allowed to see:
     * Admin sees everything, a Company Admin sees their whole company's
     * inspections, and an Inspector only sees inspections they created
     * themselves -- mirrors the legacy app's ownership model exactly.
     */
    private static function scopedQuery()
    {
        $currentUser = AuthService::currentUser();
        $query = Inspection::with(['inspector', 'country', 'region']);

        if (AuthService::isAdmin()) {
            return $query;
        }

        // Qualified with the table name -- index() conditionally left-joins
        // `users` (for sort-by-originator), which also has a company_id
        // column, so an unqualified reference here becomes ambiguous SQL
        // the moment that join is present.
        $query->where('inspections.company_id', $currentUser->company_id ?? 0);

        if (AuthService::isInspector()) {
            $query->where('inspections.orig_user_id', $currentUser->id);
        }

        return $query;
    }

    /**
     * Prepare data for the list page. `view=archived` shows inspections
     * whose report has already expired; the default shows everything else
     * (no report yet, or not yet expired) -- mirrors legacy's Current/
     * Archived toggle, which is keyed off date_expires, not a status flag.
     *
     * Also supports the per-column text filters + sortable columns +
     * infinite scroll driven by resources/js/components/data-table.js:
     * `filter[property_address]`, `filter[city]`, `filter[originator]`,
     * `filter[access_code]`, `filter[expiry]`, `filter[last_saved]` (the
     * latter two matched against the same `M j, Y` formatted date the row
     * displays), each matched as a case-insensitive LIKE, ANDed together;
     * `sort` + `dir` for column sorting (defaults to most-recently-saved
     * first) -- sorting by `originator` joins `users` for the name, which
     * every other sort/filter avoids needing; plus `page` for pagination.
     * A plain unparameterized request renders the full page with the first
     * batch already server-rendered; any of view/page/filter/sort being
     * present signals an AJAX call, answered with JSON instead.
     */
    public function index(): void
    {
        $view = $_GET['view'] ?? 'current';
        $filters = is_array($_GET['filter'] ?? null) ? $_GET['filter'] : [];
        $sort = $_GET['sort'] ?? null;
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $builder = self::scopedQuery();
        $currentTimestamp = date('Y-m-d H:i:s');

        if ($view === 'archived') {
            $builder->whereNotNull('inspections.date_expires')->where('inspections.date_expires', '<', $currentTimestamp);
        } else {
            $builder->where(function ($q) use ($currentTimestamp) {
                $q->whereNull('inspections.date_expires')->orWhere('inspections.date_expires', '>=', $currentTimestamp);
            });
        }

        if (!empty($filters['property_address'])) {
            $builder->where('inspections.property_address', 'LIKE', '%' . $filters['property_address'] . '%');
        }
        if (!empty($filters['city'])) {
            $builder->where('inspections.city', 'LIKE', '%' . $filters['city'] . '%');
        }
        if (!empty($filters['access_code'])) {
            $builder->where('inspections.access_code', 'LIKE', '%' . $filters['access_code'] . '%');
        }
        if (!empty($filters['originator'])) {
            $needle = $filters['originator'];
            $builder->whereHas('inspector', function ($q) use ($needle) {
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$needle}%"]);
            });
        }
        if (!empty($filters['expiry'])) {
            $builder->whereRaw("DATE_FORMAT(inspections.date_expires, '%b %e, %Y') LIKE ?", ['%' . $filters['expiry'] . '%']);
        }
        if (!empty($filters['last_saved'])) {
            $builder->whereRaw("DATE_FORMAT(inspections.timestamp, '%b %e, %Y') LIKE ?", ['%' . $filters['last_saved'] . '%']);
        }

        $total = (clone $builder)->count();

        $sortColumns = [
            'property_address' => 'inspections.property_address',
            'city' => 'inspections.city',
            'access_code' => 'inspections.access_code',
            'expiry' => 'inspections.date_expires',
            'last_saved' => 'inspections.timestamp',
        ];

        if ($sort === 'originator') {
            $builder->leftJoin('users', 'inspections.orig_user_id', '=', 'users.id')
                ->select('inspections.*')
                ->orderBy('users.first_name', $dir)
                ->orderBy('users.last_name', $dir);
        } elseif (isset($sortColumns[$sort])) {
            $builder->orderBy($sortColumns[$sort], $dir);
        } else {
            $builder->orderBy('inspections.timestamp', 'desc');
        }

        $inspections = $builder->offset($offset)
            ->limit($perPage)
            ->get();

        $isAjax = isset($_GET['view']) || isset($_GET['page']) || isset($_GET['filter']) || isset($_GET['sort']);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => array_map(fn(Inspection $i) => ['rowHtml' => self::renderRow($i)], $inspections->all()),
                'meta' => [
                    'total' => $total,
                    'loaded' => $inspections->count(),
                    'hasMore' => ($offset + $inspections->count()) < $total,
                ],
            ]);
            exit;
        }

        $html = '';
        foreach ($inspections as $inspection) {
            $html .= self::renderRow($inspection);
        }

        $GLOBALS['inspectionRows'] = $html;
        $GLOBALS['inspectionsCount'] = $total;
        $GLOBALS['inspectionsView'] = $view;
    }

    /**
     * Render a single inspection as a table row, including the inline
     * background-color style driven by Inspection::rowColor().
     */
    public static function renderRow(Inspection $inspection): string
    {
        $GLOBALS['assetBase'] = getAssetBase();

        $path = __DIR__ . '/../../resources/views/components/inspections/data-row.php';

        ob_start();
        try {
            $assetBase = getAssetBase();
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='7'>Render Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
        return ob_get_clean();
    }

    /**
     * Create or update an inspection's header (property address fields
     * only -- section answers are saved separately from the detail page).
     */
    public function save(array $data): array
    {
        try {
            $currentUser = AuthService::currentUser();
            $companyId = $currentUser->company_id ?? 0;
            if (!$companyId) throw new \Exception("No company associated with this account.");

            $encodedId = $data['encoded_id'] ?? null;
            $isNew = empty($encodedId);

            $address = trim($data['property_address'] ?? '');
            if ($address === '') throw new \Exception("Property address is required.");

            $inspectionId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $inspection = $inspectionId ? Inspection::where('id', $inspectionId)->where('company_id', $companyId)->first() : new Inspection();
            if (!$inspection) throw new \Exception("Inspection not found.");

            if (!$isNew && $inspection->hasReport()) {
                throw new \Exception("This report has been finalized and is locked. Reopen it for editing to make changes.");
            }

            $inspection->company_id = $companyId;
            $inspection->property_address = $address;
            $inspection->city = trim($data['city'] ?? '') ?: null;
            $inspection->country_id = (int)($data['country_id'] ?? 0) ?: null;
            $inspection->region_id = (int)($data['region_id'] ?? 0) ?: null;
            $inspection->postal_code = trim($data['postal_code'] ?? '') ?: null;

            if ($isNew) {
                $inspection->orig_user_id = $currentUser->id;
                $inspection->access_code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
                $inspection->status_id = 1;
            }

            $inspection->save();
            $inspection->load(['inspector', 'country', 'region']);

            // Every question starts as N/A rather than blank -- the inspector
            // then actively marks each one Inspected/Not Inspected as they go,
            // instead of an unset dropdown looking indistinguishable from one
            // that was simply never reached.
            if ($isNew) {
                $now = date('Y-m-d H:i:s');
                $rows = Question::where('company_id', $companyId)
                    ->where('status_id', 1)
                    ->get(['id', 'section_id'])
                    ->map(fn($question) => [
                        'inspection_id' => $inspection->id,
                        'section_id' => $question->section_id,
                        'question_id' => $question->id,
                        'tasks' => InspectionQuestion::TASK_NOT_APPLICABLE,
                        'notes' => null,
                    ])->all();

                if (!empty($rows)) {
                    foreach (array_chunk($rows, 500) as $chunk) {
                        InspectionQuestion::insert($chunk);
                    }
                }
            }

            $actionLabel = $isNew ? "Created inspection" : "Updated inspection";
            static::logActivity("{$actionLabel}: {$inspection->property_address}", 'Inspections');

            return [
                'success'       => true,
                'isNew'         => $isNew,
                'rowHtml'       => self::renderRow($inspection),
                'inspectionId'  => IdEncoder::encode((int)$inspection->id),
                'messages'      => ['Inspection saved successfully.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Hard-deletes an inspection and every child row (answers, options,
     * fields, section comments, photos) plus the photo files on disk --
     * mirrors legacy's del(), since there's no soft-delete/archive concept
     * for inspections, only the date-driven Current/Archived list filter.
     */
    public function delete(?string $id): array
    {
        try {
            $inspection = self::findForCurrentUser($id);
            if (!$inspection) {
                return ['success' => false, 'messages' => ['Inspection not found.']];
            }

            $address = $inspection->property_address;

            $uploadDir = realpath(__DIR__ . '/../../public/images/uploads/inspections/');

            $pictures = InspectionPicture::where('inspection_id', $inspection->id)->get();
            foreach ($pictures as $picture) {
                if ($uploadDir) {
                    $absolute = realpath($uploadDir . '/' . $picture->filename);
                    if ($absolute && strpos($absolute, $uploadDir) === 0 && file_exists($absolute)) {
                        @unlink($absolute);
                    }
                }
            }
            \App\Models\InspectionPictureSection::where('inspection_id', $inspection->id)->delete();
            \App\Models\InspectionPictureContract::where('inspection_id', $inspection->id)->delete();
            InspectionPicture::where('inspection_id', $inspection->id)->delete();

            $videos = \App\Models\InspectionVideo::where('inspection_id', $inspection->id)->get();
            foreach ($videos as $video) {
                if ($uploadDir) {
                    $absolute = realpath($uploadDir . '/' . $inspection->id . '/videos/' . $video->filename);
                    if ($absolute && strpos($absolute, $uploadDir) === 0 && file_exists($absolute)) {
                        @unlink($absolute);
                    }
                }
            }
            \App\Models\InspectionVideoSection::where('inspection_id', $inspection->id)->delete();
            \App\Models\InspectionVideo::where('inspection_id', $inspection->id)->delete();

            InspectionQuestionOption::where('inspection_id', $inspection->id)->delete();
            InspectionQuestionField::where('inspection_id', $inspection->id)->delete();
            InspectionQuestion::where('inspection_id', $inspection->id)->delete();
            InspectionSectionComment::where('inspection_id', $inspection->id)->delete();

            (new \Src\Service\InspectionPdfService())->deleteExisting($inspection);

            $inspection->delete();

            static::logActivity("Deleted inspection: {$address}", 'Inspections');

            return ['success' => true, 'messages' => ['Inspection deleted successfully.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * "Finalize" an inspection's report: generates the downloadable PDF
     * (see InspectionPdfService), then sets the 14-day access-code expiry
     * window and issues a fresh access code -- the same side effects
     * legacy's PdfModel::set() triggers. PDF generation happens first and
     * the whole action fails if it throws, since a "finalized" report with
     * no actual PDF behind it would leave the access code pointing at
     * nothing.
     */
    public function finalize(?string $id): array
    {
        try {
            $inspection = self::findForCurrentUser($id);
            if (!$inspection) {
                return ['success' => false, 'messages' => ['Inspection not found.']];
            }

            $pdfService = new \Src\Service\InspectionPdfService();
            $pdfService->deleteExisting($inspection);
            $inspection->file_name = $pdfService->generate($inspection);

            $inspection->date_posted = date('Y-m-d H:i:s');
            $inspection->date_expires = date('Y-m-d H:i:s', strtotime('+14 days'));
            $inspection->access_code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $inspection->save();

            static::logActivity("Finalized report for inspection: {$inspection->property_address}", 'Inspections');

            return [
                'success'  => true,
                'rowHtml'  => self::renderRow($inspection),
                'messages' => ['Report finalized. A 14-day access window has started.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Reverses finalize() -- clears the expiry window so the inspection
     * goes back to showing as "No Report Yet" (white row).
     */
    public function reopen(?string $id): array
    {
        try {
            $inspection = self::findForCurrentUser($id);
            if (!$inspection) {
                return ['success' => false, 'messages' => ['Inspection not found.']];
            }

            $inspection->date_posted = null;
            $inspection->date_expires = null;
            $inspection->save();

            static::logActivity("Reopened inspection for editing: {$inspection->property_address}", 'Inspections');

            return [
                'success'  => true,
                'rowHtml'  => self::renderRow($inspection),
                'messages' => ['Inspection reopened for editing.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Resolve an inspection by encoded id, scoped to what the current
     * account is allowed to touch (same rule as scopedQuery()).
     */
    public static function findForCurrentUser(?string $encodedId): ?Inspection
    {
        $rawId = $encodedId ? IdEncoder::decode($encodedId) : null;
        if (!$rawId) return null;

        return self::scopedQuery()->where('id', $rawId)->first();
    }

    /**
     * Resolve a finalized, still-current report by its plain-text access
     * code -- the public /report/{code} lookup a client uses, not an
     * authenticated account. Deliberately does not distinguish "wrong code"
     * from "expired code" in what it returns; the caller shows one generic
     * not-found message either way so an expired code can't be used to
     * probe whether it was ever valid.
     */
    public static function findByAccessCode(?string $accessCode): ?Inspection
    {
        $accessCode = trim((string)$accessCode);
        if ($accessCode === '') return null;

        return Inspection::with(['company', 'country', 'region'])
            ->where('access_code', $accessCode)
            ->whereNotNull('date_expires')
            ->where('date_expires', '>=', date('Y-m-d H:i:s'))
            ->first();
    }
}
