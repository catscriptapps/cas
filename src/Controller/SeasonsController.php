<?php
// /src/Controller/SeasonsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Registration;
use App\Models\Season;
use App\Models\TeamGroup;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

/**
 * The "Schedules" landing page is really a Seasons list (one row per
 * Division+Year). Ported from legacy cas-sports' SeasonsController, with the
 * list endpoint adapted to this project's filter[]/sort/page data-table
 * contract (see resources/js/components/data-table.js) instead of legacy's
 * single `?q=` search box.
 */
class SeasonsController
{
    use RecentActivityLogger;

    public function index(string $pageContext = 'schedules'): void
    {
        $pageContext = $_GET['context'] ?? $pageContext;
        $filters = is_array($_GET['filter'] ?? null) ? $_GET['filter'] : [];
        $sort = $_GET['sort'] ?? null;
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = Season::with('division.league');

        if (!empty($filters['division'])) {
            $needle = $filters['division'];
            $builder->whereHas('division', fn($sq) => $sq->where('division', 'LIKE', "%{$needle}%"));
        }
        if (!empty($filters['league'])) {
            $needle = $filters['league'];
            $builder->whereHas('division.league', fn($sq) => $sq->where('league', 'LIKE', "%{$needle}%"));
        }
        if (!empty($filters['year'])) {
            $builder->where('season_year', 'LIKE', '%' . $filters['year'] . '%');
        }
        if (!empty($filters['status'])) {
            // Arguments were backwards here (str_contains($haystack,
            // $needle) reversed) -- since "inactive" is a superstring of
            // "active", typing "active" matched the inactive branch instead,
            // and typing "inactive" only "worked" because there happened to
            // be no active rows left to wrongly include.
            $needle = strtolower($filters['status']);
            if (str_contains($needle, 'inactive')) {
                $builder->where('status_id', Season::STATUS_INACTIVE);
            } elseif (str_contains($needle, 'active')) {
                $builder->where('status_id', Season::STATUS_ACTIVE);
            }
        }

        $totalFiltered = (clone $builder)->count();

        // `select('seasons.*')` on the join branches matters -- without it,
        // divisions'/leagues' own status_id/date_created/timestamp columns
        // (same names as seasons' own) would silently overwrite the season's
        // real values when Eloquent hydrates the joined result set.
        switch ($sort) {
            case 'division':
                $builder->join('divisions', 'seasons.division_id', '=', 'divisions.division_id')
                    ->orderBy('divisions.division', $dir)
                    ->select('seasons.*');
                break;
            case 'league':
                $builder->join('divisions', 'seasons.division_id', '=', 'divisions.division_id')
                    ->join('leagues', 'divisions.league_id', '=', 'leagues.league_id')
                    ->orderBy('leagues.league', $dir)
                    ->select('seasons.*');
                break;
            case 'year':
                $builder->orderBy('season_year', $dir);
                break;
            case 'status':
                $builder->orderBy('status_id', $dir);
                break;
            default:
                $builder->orderBy('season_year', 'desc')->orderBy('season_id', 'desc');
        }

        $seasons = $builder->offset($offset)->limit($perPage)->get();

        if (isset($_GET['page']) || isset($_GET['filter']) || isset($_GET['sort'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => array_map(fn($s) => ['rowHtml' => self::renderRow($s, $pageContext)], $seasons->all()),
                'meta' => [
                    'total' => $totalFiltered,
                    'loaded' => $seasons->count(),
                    'hasMore' => ($offset + $seasons->count()) < $totalFiltered,
                ],
            ]);
            exit;
        }

        $html = '';
        foreach ($seasons as $season) {
            $html .= self::renderRow($season, $pageContext);
        }

        $reps = Registration::selectRaw('MIN(entry_id) as entry_id, full_name')
            ->whereNotNull('full_name')
            ->where('full_name', '!=', '')
            ->groupBy('full_name')
            ->orderBy('full_name', 'asc')
            ->get();

        $groups = TeamGroup::orderBy('sort_order', 'asc')->orderBy('group_name', 'asc')->get();

        $GLOBALS['seasonRows'] = $html;
        $GLOBALS['teamReps'] = $reps;
        $GLOBALS['teamGroups'] = $groups;
        $GLOBALS['title'] = match ($pageContext) {
            'stats' => 'Stats+Standings',
            'gamesheets' => 'Gamesheets',
            default => 'Schedules',
        };
        $GLOBALS['totalSeasonsCount'] = $totalFiltered;
    }

    public static function renderRow(Season $season, string $pageContext = 'schedules'): string
    {
        $rowItem = $season->toArray();
        $rowItem['division'] = $season->division->division ?? 'Unknown Division';
        $rowItem['league'] = $season->division->league->league ?? 'Unknown League';

        $teams = TeamsController::getBySeason((int)$season->season_id);
        $rowItem['team_count'] = $teams->count();
        $rowItem['teams'] = $teams->map(function ($team) {
            $data = $team->toArray();
            $data['player_count'] = $team->players_count ?? 0;
            return $data;
        })->toArray();

        $rowItem['encoded_id'] = IdEncoder::encode((int)$season->season_id);

        $path = __DIR__ . '/../../resources/views/components/seasons/data-row.php';

        ob_start();
        extract(['rowItem' => $rowItem, 'pageContext' => $pageContext]);
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

            $divisionId = (int)($data['division_id'] ?? 0);
            $seasonYear = (int)($data['season_year'] ?? 0);
            $pageContext = $data['page_context'] ?? 'schedules';

            if ($divisionId <= 0 || $seasonYear < 2000) {
                throw new \Exception('Invalid division or year provided.');
            }

            if (Season::where('division_id', $divisionId)->where('season_year', $seasonYear)->exists()) {
                throw new \Exception('A season entry for this division and year already exists.');
            }

            $season = new Season();
            $season->division_id = $divisionId;
            $season->season_year = (string)$seasonYear;
            $season->status_id = Season::STATUS_ACTIVE;

            $now = date('Y-m-d H:i:s');
            $season->date_created = $now;
            $season->timestamp = $now;
            $season->save();
            $season->load('division');

            $divName = $season->division->division ?? 'Unknown';
            static::logActivity("Created new season: {$divName} ({$seasonYear})", 'Schedules', $season->season_id);

            return [
                'success' => true,
                'rowHtml' => self::renderRow($season, $pageContext),
                'messages' => ['Season entry created successfully.'],
            ];
        } catch (\Throwable $e) {
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
            $season = $rawId ? Season::with('division')->find($rawId) : null;

            if (!$season) {
                throw new \Exception('Failed to find season entry.');
            }

            $divisionName = $season->division->division ?? 'Unknown Division';
            $details = "{$divisionName} ({$season->season_year})";

            if ($season->delete()) {
                static::logActivity("Deleted season: {$details}", 'Schedules');
                return ['success' => true, 'messages' => ['Season entry removed.']];
            }

            return ['success' => false, 'messages' => ['Failed to delete season entry.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
