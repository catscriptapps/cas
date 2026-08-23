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

    public function index(): void
    {
        $query = trim((string)($_GET['q'] ?? ''));

        $builder = Division::with(['league.sport'])->activeLeagues()->orderBy('division');
        if ($query !== '') {
            $builder->where(function ($q) use ($query) {
                $q->where('division', 'LIKE', "%{$query}%")
                    ->orWhereHas('league', fn($lq) => $lq->where('league', 'LIKE', "%{$query}%"));
            });
        }

        $divisions = $builder->get();

        if (isset($_GET['q'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $divisions->map(fn($d) => ['rowHtml' => self::renderRow($d)])->values(),
                'meta' => ['total' => $divisions->count()],
            ]);
            exit;
        }

        $html = '';
        foreach ($divisions as $division) {
            $html .= self::renderRow($division);
        }

        $GLOBALS['divisionRows'] = $html;
        $GLOBALS['totalDivisionsCount'] = $divisions->count();
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
