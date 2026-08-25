<?php
// /src/Controller/IncidentReportsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\IncidentReport;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/**
 * Admin-internal log of on-floor incidents (altercations, injuries,
 * disciplinary outcomes) -- a flat CRUD resource with no relational linkage
 * to Schedules/Teams/Players/Registrations, modeled on Contacts' filter[]/
 * sort/page data-table contract rather than legacy cas-sports' single `?q=`
 * search box. Ported from legacy cas-sports.
 */
class IncidentReportsController
{
    use RecentActivityLogger;

    public function index(): void
    {
        $filters = is_array($_GET['filter'] ?? null) ? $_GET['filter'] : [];
        $sort = $_GET['sort'] ?? null;
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = IncidentReport::query();

        if (!empty($filters['report'])) {
            $needle = $filters['report'];
            $builder->where(function ($q) use ($needle) {
                $q->where('location', 'LIKE', "%{$needle}%")
                    ->orWhere('teams_involved', 'LIKE', "%{$needle}%")
                    ->orWhere('persons_involved', 'LIKE', "%{$needle}%")
                    ->orWhere('ref_involved', 'LIKE', "%{$needle}%")
                    ->orWhere('incident', 'LIKE', "%{$needle}%");
            });
        }
        if (!empty($filters['location'])) {
            $builder->where('location', 'LIKE', '%' . $filters['location'] . '%');
        }
        if (!empty($filters['teams'])) {
            $builder->where('teams_involved', 'LIKE', '%' . $filters['teams'] . '%');
        }
        if (!empty($filters['status'])) {
            $needle = strtolower($filters['status']);
            if (str_contains('filed', $needle)) {
                $builder->where('status_id', 1);
            } elseif (str_contains('draft', $needle)) {
                $builder->where('status_id', 0);
            }
        }

        $totalFiltered = (clone $builder)->count();

        $sortColumns = [
            'date' => 'incident_date',
            'location' => 'location',
            'teams' => 'teams_involved',
            'status' => 'status_id',
        ];
        if (isset($sortColumns[$sort])) {
            $builder->orderBy($sortColumns[$sort], $dir);
        } else {
            $builder->orderBy('incident_date', 'desc');
        }

        $reports = $builder->offset($offset)->limit($perPage)->get();

        if (isset($_GET['page']) || isset($_GET['filter']) || isset($_GET['sort'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => array_map(fn($r) => ['rowHtml' => self::renderRow($r)], $reports->all()),
                'meta' => [
                    'total' => $totalFiltered,
                    'loaded' => $reports->count(),
                    'hasMore' => ($offset + $reports->count()) < $totalFiltered,
                ],
            ]);
            exit;
        }

        $html = '';
        foreach ($reports as $report) {
            $html .= self::renderRow($report);
        }

        $GLOBALS['incidentRows'] = $html;
        $GLOBALS['title'] = 'Incident Reports';
        $GLOBALS['totalIncidentsCount'] = $totalFiltered;
    }

    public static function renderRow(IncidentReport $report): string
    {
        $rowItem = $report->toArray();
        $rowItem['encoded_id'] = IdEncoder::encode((int)$report->entry_id);
        $rowItem['date_formatted'] = $report->incident_date ? $report->incident_date->format('M j, Y') : 'N/A';

        $path = __DIR__ . '/../../resources/views/components/incident-reports/data-row.php';

        ob_start();
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='5'>Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

    public function save(array $data): array
    {
        try {
            if (!AuthService::isLoggedIn()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $encodedId = $data['encoded_id'] ?? null;
            $isNew = empty($encodedId);

            $reportId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $report = $reportId ? IncidentReport::find($reportId) : new IncidentReport();

            if (!$report) throw new \Exception('Report not found.');

            $report->incident_date = $data['incident_date'] ?? null;
            $report->incident_time = $data['incident_time'] ?? null;
            $report->teams_involved = $data['teams_involved'] ?? null;
            $report->persons_involved = $data['persons_involved'] ?? null;
            $report->location = $data['location'] ?? null;
            $report->ref_involved = $data['ref_involved'] ?? null;
            $report->timekeeper = $data['timekeeper'] ?? null;
            $report->incident = $data['incident'] ?? null;
            $report->equipment_worn = $data['equipment_worn'] ?? null;
            $report->medical_assistance = $data['medical_assistance'] ?? null;
            $report->manager_name = $data['manager_name'] ?? null;
            $report->manager_time = $data['manager_time'] ?? null;
            $report->referee_outcome = $data['referee_outcome'] ?? null;
            $report->name_e_signature = $data['name_e_signature'] ?? null;

            $statusVal = $data['status_id'] ?? null;
            if ($statusVal !== null) {
                $report->status_id = ($statusVal === '0' || $statusVal === 0 || $statusVal === false) ? 0 : 1;
            } elseif (!$report->exists) {
                $report->status_id = 1;
            }

            $report->save();

            $actionLabel = $isNew ? 'Filed new incident report' : 'Updated incident report';
            $dateStr = $report->incident_date ? $report->incident_date->format('Y-m-d') : 'Unknown Date';
            static::logActivity("{$actionLabel}: {$dateStr} at " . ($report->location ?: 'Unknown Location'), 'Incident Reports');

            return [
                'success' => true,
                'rowHtml' => self::renderRow($report),
                'messages' => ['Incident report saved successfully.'],
            ];
        } catch (\Throwable $e) {
            static::logActivity('Incident report save error: ' . $e->getMessage(), 'Incident Reports');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function delete($id): array
    {
        try {
            if (!AuthService::isLoggedIn()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $report = $rawId ? IncidentReport::find($rawId) : null;
            if (!$report) throw new \Exception('Failed to delete. Report not found.');

            $dateLabel = $report->incident_date ? $report->incident_date->format('Y-m-d') : 'Unknown Date';
            $locationLabel = $report->location ?: 'Unknown Location';

            if ($report->delete()) {
                static::logActivity("Deleted incident report: {$dateLabel} at {$locationLabel}", 'Incident Reports');
                return ['success' => true, 'messages' => ['Report deleted.']];
            }

            return ['success' => false, 'messages' => ['Failed to delete report.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
