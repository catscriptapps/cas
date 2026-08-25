<?php
// /src/Controller/TeamsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Player;
use App\Models\Registration;
use App\Models\Team;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Illuminate\Database\Eloquent\Collection;
use Src\Service\AuthService;

/**
 * Team CRUD, ported from legacy cas-sports. team_id is used raw (not
 * IdEncoder-obfuscated) throughout -- matches legacy exactly, unlike
 * Seasons/Schedules which do encode their IDs.
 */
class TeamsController
{
    use RecentActivityLogger;

    public static function getBySeason(int $seasonId): Collection
    {
        return Team::where('season_id', $seasonId)
            ->withCount('players')
            ->with(['group', 'season.division'])
            ->orderBy('team_group', 'asc')
            ->orderBy('team_name', 'asc')
            ->get();
    }

    public function index(array $params): array
    {
        // The one caller of this endpoint (schedules-modal.js's Edit-game
        // flow, fetchTeams()) always sends season_id IdEncoder-encoded --
        // matches every other season_id passed around the Schedules feature
        // (Team management's own season_id field is the exception, kept
        // raw). A plain (int) cast on the encoded string silently evaluated
        // to 0, returning an empty team list with no error -- invisible in
        // normal single-season Edit (a DOM-scrape fallback in
        // forms/schedule-form.js happened to paper over it there) but a
        // completely empty, unsubmittable Home/Away dropdown when editing a
        // game from View All mode, where that fallback has nothing to
        // scrape.
        $rawSeasonId = $params['season_id'] ?? null;
        $seasonId = (is_string($rawSeasonId) && !is_numeric($rawSeasonId))
            ? (int)IdEncoder::decode($rawSeasonId)
            : (int)$rawSeasonId;

        if (!$seasonId) {
            return ['success' => false, 'messages' => ['Season ID is required to fetch teams.']];
        }

        try {
            return ['success' => true, 'data' => self::getBySeason($seasonId)->toArray()];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function save(array $data): array
    {
        if (!AuthService::isLoggedIn()) {
            return ['success' => false, 'messages' => ["You don't have permission to do that."]];
        }
        if (empty($data['team_name'])) {
            return ['success' => false, 'messages' => ['Team Name is required.']];
        }
        if (empty($data['season_id'])) {
            return ['success' => false, 'messages' => ['Missing Season ID context.']];
        }

        try {
            $teamId = $data['team_id'] ?? null;
            $isNew = empty($teamId);

            if (!$isNew) {
                $team = Team::find($teamId);
                if (!$team) {
                    return ['success' => false, 'messages' => ['Team not found.']];
                }
            } else {
                $team = new Team();
                $team->date_created = date('Y-m-d');
            }

            $team->season_id = (int)$data['season_id'];
            $team->team_name = $data['team_name'];
            $team->team_number = $data['team_number'] ?? '';
            $team->team_group = $data['team_group'] ?? 'N/A';
            $team->team_rep_id = !empty($data['team_rep_id']) ? (int)$data['team_rep_id'] : null;
            $team->status_id = 1;
            $team->timestamp = date('Y-m-d H:i:s');
            $team->save();

            $repName = 'N/A';
            if (!empty($team->team_rep_id)) {
                $rep = Registration::find($team->team_rep_id);
                $repName = $rep ? $rep->full_name : 'N/A';
            }

            $actionLabel = $isNew ? 'Registered new team' : 'Updated team details';
            static::logActivity("{$actionLabel}: {$team->team_name} (Rep: {$repName})", 'Schedules');

            $playerCount = Player::where('team_id', $team->team_id)->count();

            return [
                'success' => true,
                'messages' => [$isNew ? 'Team registered successfully!' : 'Team updated successfully!'],
                'teamData' => [
                    'team_id' => $team->team_id,
                    'team_name' => $team->team_name,
                    'team_group' => $team->team_group,
                    'team_number' => $team->team_number,
                    'team_rep_id' => $team->team_rep_id,
                    'rep_name' => $repName,
                    'date_created' => $team->date_created,
                    'player_count' => $playerCount,
                ],
            ];
        } catch (\Throwable $e) {
            static::logActivity('Team save error: ' . $e->getMessage(), 'Schedules');
            return ['success' => false, 'messages' => ['Database error: ' . $e->getMessage()]];
        }
    }

    public function delete(array $data): array
    {
        if (!AuthService::isLoggedIn()) {
            return ['success' => false, 'messages' => ["You don't have permission to do that."]];
        }

        $teamId = $data['id'] ?? null;
        if (!$teamId) {
            return ['success' => false, 'messages' => ['Team ID is required']];
        }

        try {
            $team = Team::find($teamId);
            if (!$team) {
                // Idempotent, matching legacy: already gone counts as success.
                return ['success' => true, 'messages' => ['Team removed successfully']];
            }

            $teamName = $team->team_name;

            if ($team->delete()) {
                static::logActivity("Removed team: {$teamName}", 'Schedules');
                return ['success' => true, 'messages' => ['Team successfully removed']];
            }

            return ['success' => false, 'messages' => ['Failed to delete team.']];
        } catch (\Throwable $e) {
            static::logActivity('Team deletion error: ' . $e->getMessage(), 'Schedules');
            return ['success' => false, 'messages' => ['Database error: ' . $e->getMessage()]];
        }
    }
}
