<?php
// /src/Controller/SportsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Sport;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/**
 * Admin management of Sports -- the top of the League Management hierarchy
 * (Sport -> League -> Division). Small list, so search returns every match
 * in one shot rather than paging (see resources/js/components/table-search.js).
 */
class SportsController
{
    use RecentActivityLogger;

    /**
     * Active sports only -- used by the public registration wizard.
     */
    public static function list()
    {
        return Sport::where('status_id', Sport::STATUS_ACTIVE)
            ->orderBy('sport_name')
            ->get();
    }

    /**
     * All sports regardless of status -- used by the admin League/Division
     * forms' parent-sport dropdown, so an existing League/Division whose
     * parent Sport has since been archived still shows correctly.
     */
    public static function listAll()
    {
        return Sport::orderBy('sport_name')->get();
    }

    /**
     * Admin list: all sports (active + inactive), optionally filtered by
     * `?q=` (name search). Renders into $GLOBALS on a plain page load, or
     * answers JSON for the live-search box.
     */
    public function index(): void
    {
        $query = trim((string)($_GET['q'] ?? ''));

        $builder = Sport::withCount('leagues')->orderBy('sport_name');
        if ($query !== '') {
            $builder->where('sport_name', 'LIKE', "%{$query}%");
        }

        $sports = $builder->get();

        if (isset($_GET['q'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $sports->map(fn($s) => ['rowHtml' => self::renderRow($s)])->values(),
                'meta' => ['total' => $sports->count()],
            ]);
            exit;
        }

        $html = '';
        foreach ($sports as $sport) {
            $html .= self::renderRow($sport);
        }

        $GLOBALS['sportRows'] = $html;
        $GLOBALS['totalSportsCount'] = $sports->count();
    }

    public static function renderRow(Sport $sport): string
    {
        $rowItem = $sport->toArray();
        $rowItem['encoded_id'] = IdEncoder::encode((int)$sport->sport_id);
        $rowItem['league_count'] = $sport->leagues_count ?? $sport->leagues()->count();

        $path = __DIR__ . '/../../resources/views/components/league-management/sport-row.php';

        ob_start();
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='3'>Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

    public function save(array $data): array
    {
        try {
            if (!AuthService::isLoggedIn()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $name = trim((string)($data['sport_name'] ?? ''));
            if ($name === '') {
                throw new \Exception('Sport name is required.');
            }

            $encodedId = $data['encoded_id'] ?? null;
            $isNew = empty($encodedId);
            $sportId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $sport = $sportId ? Sport::find($sportId) : new Sport();

            if (!$sport) {
                throw new \Exception('Sport not found.');
            }

            $existingQuery = Sport::where('sport_name', $name);
            if ($sport->exists) {
                $existingQuery->where('sport_id', '!=', $sport->sport_id);
            }
            if ($existingQuery->exists()) {
                throw new \Exception("A sport named '{$name}' already exists.");
            }

            $sport->sport_name = $name;
            $sport->status_id = array_key_exists('status_id', $data) && (int)$data['status_id'] === 1 ? 1 : 0;
            $sport->save();

            $actionLabel = $isNew ? 'Created sport' : 'Updated sport';
            static::logActivity("{$actionLabel}: {$sport->sport_name}", 'League Management', $sport->sport_id);

            return [
                'success' => true,
                'rowHtml' => self::renderRow($sport),
                'messages' => ['Sport saved successfully.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function delete(?string $id): array
    {
        try {
            if (!AuthService::isLoggedIn()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $sport = $rawId ? Sport::find($rawId) : null;
            if (!$sport) {
                throw new \Exception('Failed to delete sport.');
            }

            if ($sport->leagues()->count() > 0) {
                throw new \Exception('Cannot delete: this sport has leagues assigned to it.');
            }

            $name = $sport->sport_name;

            if ($sport->delete()) {
                static::logActivity("Deleted sport: {$name}", 'League Management');
                return ['success' => true, 'messages' => ['Sport deleted successfully.']];
            }

            return ['success' => false, 'messages' => ['Failed to delete sport.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
