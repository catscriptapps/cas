<?php
// /src/Controller/DivisionsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Division;
use App\Models\League;
use App\Models\Registration;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/**
 * Admin management of Divisions (Sport -> League -> Division). A division
 * only shows up for registration (and, deliberately, in the admin list's
 * default view) while its parent league is active -- see the
 * `activeLeagues` scope on the Division model.
 */
class DivisionsController
{
    use RecentActivityLogger;

    /**
     * Active divisions under an active league, optionally scoped to one
     * league -- used by the public registration wizard.
     */
    public static function list(?int $leagueId = null)
    {
        $query = Division::where('status_id', Division::STATUS_ACTIVE)->activeLeagues();

        if ($leagueId !== null) {
            $query->where('league_id', $leagueId);
        }

        return $query->orderBy('division')->get();
    }

    public static function find(int $divisionId): ?Division
    {
        return Division::find($divisionId);
    }

    /**
     * Admin list. Supports the same per-column text filters + sortable
     * columns + infinite scroll as UsersController::index() (see
     * resources/js/components/data-table.js): `filter[division]` (division
     * or league name), `filter[status]`, `sort` + `dir`, `page`.
     */
    public function index(): void
    {
        $filters = is_array($_GET['filter'] ?? null) ? $_GET['filter'] : [];
        $sort = $_GET['sort'] ?? null;
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = Division::with(['league.sport'])->activeLeagues();

        if (!empty($filters['division'])) {
            $needle = $filters['division'];
            $builder->where(function ($q) use ($needle) {
                $q->where('division', 'LIKE', "%{$needle}%")
                    ->orWhereHas('league', fn($lq) => $lq->where('league', 'LIKE', "%{$needle}%"));
            });
        }
        if (!empty($filters['status'])) {
            $needle = strtolower($filters['status']);
            if (str_contains('active', $needle) && !str_contains('inactive', $needle)) {
                $builder->where('status_id', Division::STATUS_ACTIVE);
            } elseif (str_contains('inactive', $needle)) {
                $builder->where('status_id', Division::STATUS_INACTIVE);
            }
        }

        $totalFiltered = (clone $builder)->count();

        $sortColumns = ['division' => 'division', 'price' => 'price', 'status' => 'status_id'];
        if (isset($sortColumns[$sort])) {
            $builder->orderBy($sortColumns[$sort], $dir);
        } else {
            $builder->orderBy('division', 'asc');
        }

        $divisions = $builder->offset($offset)->limit($perPage)->get();

        if (isset($_GET['page']) || isset($_GET['filter']) || isset($_GET['sort'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $divisions->map(fn($d) => ['rowHtml' => self::renderRow($d)])->values(),
                'meta' => [
                    'total' => $totalFiltered,
                    'loaded' => $divisions->count(),
                    'hasMore' => ($offset + $divisions->count()) < $totalFiltered,
                ],
            ]);
            exit;
        }

        $html = '';
        foreach ($divisions as $division) {
            $html .= self::renderRow($division);
        }

        $GLOBALS['divisionRows'] = $html;
        $GLOBALS['totalDivisionsCount'] = $totalFiltered;
    }

    public static function renderRow(Division $division): string
    {
        $rowItem = $division->toArray();
        $rowItem['encoded_id'] = IdEncoder::encode((int)$division->division_id);
        $rowItem['league_name'] = $division->league->league ?? 'N/A';
        $rowItem['sport_name'] = $division->league->sport->sport_name ?? 'N/A';

        $path = __DIR__ . '/../../resources/views/components/league-management/division-row.php';

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

            $name = trim((string)($data['division_name'] ?? ''));
            $leagueId = (int)($data['league_id'] ?? 0);

            if ($name === '') {
                throw new \Exception('Division name is required.');
            }
            if (!$leagueId || !League::find($leagueId)) {
                throw new \Exception('Please choose a valid league.');
            }

            $encodedId = $data['encoded_id'] ?? null;
            $isNew = empty($encodedId);
            $divisionId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $division = $divisionId ? Division::find($divisionId) : new Division();

            if (!$division) {
                throw new \Exception('Division not found.');
            }

            // Uniqueness is scoped per league -- the same division name is
            // fine across two different leagues.
            $existingQuery = Division::where('division', $name)->where('league_id', $leagueId);
            if ($division->exists) {
                $existingQuery->where('division_id', '!=', $division->division_id);
            }
            if ($existingQuery->exists()) {
                throw new \Exception("A division named '{$name}' already exists for that league.");
            }

            $division->division = $name;
            $division->league_id = $leagueId;
            $division->price = (float)($data['price'] ?? 0);
            $division->status_id = array_key_exists('status_id', $data) && (int)$data['status_id'] === 1 ? 1 : 0;
            $division->save();
            $division->load('league.sport');

            $actionLabel = $isNew ? 'Created division' : 'Updated division';
            static::logActivity("{$actionLabel}: {$division->division}", 'League Management', $division->division_id);

            return [
                'success' => true,
                'rowHtml' => self::renderRow($division),
                'messages' => ['Division saved successfully.'],
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
            $division = $rawId ? Division::find($rawId) : null;
            if (!$division) {
                throw new \Exception('Failed to delete division.');
            }

            if (Registration::where('division_id', $division->division_id)->exists()) {
                throw new \Exception('Cannot delete: this division has registrations on file. Archive it instead.');
            }

            $name = $division->division;

            if ($division->delete()) {
                static::logActivity("Deleted division: {$name}", 'League Management');
                return ['success' => true, 'messages' => ['Division deleted successfully.']];
            }

            return ['success' => false, 'messages' => ['Failed to delete division.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
