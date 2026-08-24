<?php
// /src/Controller/LeaguesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\League;
use App\Models\Sport;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/**
 * Admin management of Leagues (Sport -> League -> Division).
 */
class LeaguesController
{
    use RecentActivityLogger;

    /**
     * Active leagues only, optionally scoped to one sport -- used by the
     * public registration wizard.
     */
    public static function list(?int $sportId = null)
    {
        $query = League::where('status_id', League::STATUS_ACTIVE);

        if ($sportId !== null) {
            $query->where('sport_id', $sportId);
        }

        return $query->orderBy('league')->get();
    }

    /**
     * All leagues regardless of status, optionally scoped to one sport --
     * used by the admin Division form's parent-league dropdown.
     */
    public static function listAll(?int $sportId = null)
    {
        $query = League::query();

        if ($sportId !== null) {
            $query->where('sport_id', $sportId);
        }

        return $query->orderBy('league')->get();
    }

    /**
     * Admin list. Supports the same per-column text filters + sortable
     * columns + infinite scroll as UsersController::index() (see
     * resources/js/components/data-table.js): `filter[league]` (league or
     * sport name), `filter[status]`, `sort` + `dir`, `page`.
     */
    public function index(): void
    {
        $filters = is_array($_GET['filter'] ?? null) ? $_GET['filter'] : [];
        $sort = $_GET['sort'] ?? null;
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = League::with('sport')->withCount('divisions');

        if (!empty($filters['league'])) {
            $needle = $filters['league'];
            $builder->where(function ($q) use ($needle) {
                $q->where('league', 'LIKE', "%{$needle}%")
                    ->orWhereHas('sport', fn($sq) => $sq->where('sport_name', 'LIKE', "%{$needle}%"));
            });
        }
        if (!empty($filters['status'])) {
            $needle = strtolower($filters['status']);
            if (str_contains('active', $needle) && !str_contains('inactive', $needle)) {
                $builder->where('status_id', League::STATUS_ACTIVE);
            } elseif (str_contains('inactive', $needle)) {
                $builder->where('status_id', League::STATUS_INACTIVE);
            }
        }

        $totalFiltered = (clone $builder)->count();

        $sortColumns = ['divisions' => 'divisions_count', 'status' => 'status_id'];
        if ($sort === 'league') {
            $builder->orderBy('league', $dir);
        } elseif (isset($sortColumns[$sort])) {
            $builder->orderBy($sortColumns[$sort], $dir);
        } else {
            $builder->orderBy('league', 'asc');
        }

        $leagues = $builder->offset($offset)->limit($perPage)->get();

        if (isset($_GET['page']) || isset($_GET['filter']) || isset($_GET['sort'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $leagues->map(fn($l) => ['rowHtml' => self::renderRow($l)])->values(),
                'meta' => [
                    'total' => $totalFiltered,
                    'loaded' => $leagues->count(),
                    'hasMore' => ($offset + $leagues->count()) < $totalFiltered,
                ],
            ]);
            exit;
        }

        $html = '';
        foreach ($leagues as $league) {
            $html .= self::renderRow($league);
        }

        $GLOBALS['leagueRows'] = $html;
        $GLOBALS['totalLeaguesCount'] = $totalFiltered;
    }

    public static function renderRow(League $league): string
    {
        $rowItem = $league->toArray();
        $rowItem['encoded_id'] = IdEncoder::encode((int)$league->league_id);
        $rowItem['sport_name'] = $league->sport->sport_name ?? 'N/A';
        $rowItem['division_count'] = $league->divisions_count ?? $league->divisions()->count();

        $path = __DIR__ . '/../../resources/views/components/league-management/league-row.php';

        ob_start();
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='4'>Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

    public function save(array $data): array
    {
        try {
            if (!AuthService::isLoggedIn()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $name = trim((string)($data['league'] ?? ''));
            $sportId = (int)($data['sport_id'] ?? 0);

            if ($name === '') {
                throw new \Exception('League name is required.');
            }
            if (!$sportId || !Sport::find($sportId)) {
                throw new \Exception('Please choose a valid sport.');
            }

            $encodedId = $data['encoded_id'] ?? null;
            $isNew = empty($encodedId);
            $leagueId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $league = $leagueId ? League::find($leagueId) : new League();

            if (!$league) {
                throw new \Exception('League not found.');
            }

            // Uniqueness is scoped per sport -- the same league name is fine
            // across two different sports.
            $existingQuery = League::where('league', $name)->where('sport_id', $sportId);
            if ($league->exists) {
                $existingQuery->where('league_id', '!=', $league->league_id);
            }
            if ($existingQuery->exists()) {
                throw new \Exception("A league named '{$name}' already exists for that sport.");
            }

            $league->league = $name;
            $league->sport_id = $sportId;
            $league->is_ball = !empty($data['is_ball']) ? 1 : 0;
            $league->status_id = array_key_exists('status_id', $data) && (int)$data['status_id'] === 1 ? 1 : 0;
            $league->save();
            $league->load('sport');

            $actionLabel = $isNew ? 'Created league' : 'Updated league';
            static::logActivity("{$actionLabel}: {$league->league}", 'League Management', $league->league_id);

            return [
                'success' => true,
                'rowHtml' => self::renderRow($league),
                'messages' => ['League saved successfully.'],
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
            $league = $rawId ? League::find($rawId) : null;
            if (!$league) {
                throw new \Exception('Failed to delete league.');
            }

            if ($league->divisions()->count() > 0) {
                throw new \Exception('Cannot delete: this league has divisions assigned to it.');
            }

            $name = $league->league;

            if ($league->delete()) {
                static::logActivity("Deleted league: {$name}", 'League Management');
                return ['success' => true, 'messages' => ['League deleted successfully.']];
            }

            return ['success' => false, 'messages' => ['Failed to delete league.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
