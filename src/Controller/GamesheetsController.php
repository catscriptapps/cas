<?php
// /src/Controller/GamesheetsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Gamesheet;
use App\Models\Player;
use App\Models\Schedule;
use App\Models\Season;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;

/**
 * Per-player, per-game stat sheets (goals/assists/games_played/period/
 * time_of_goal), one row per (schedule_id, player_id) in the `gamesheets`
 * table. Ported from legacy cas-sports.
 *
 * Season-keyed like Schedules/Stats (/gamesheets/{encoded_season_id}), but
 * individual games are drilled into via an in-page roster accordion rather
 * than a route change -- there is no cross-navigation link from Schedules'
 * game rows into a gamesheet; the two modules are fully independent.
 *
 * There is no game-score/result concept anywhere in this app (see
 * StatsController's docblock) -- Gamesheets is not where scores live either,
 * it's purely a per-player stat sheet with zero team-level aggregation.
 */
class GamesheetsController
{
    use RecentActivityLogger;

    public function index(array $data = []): array
    {
        if (isset($_GET['action']) && $_GET['action'] === 'view_all') {
            return $this->handleViewAllApi();
        }

        $action = $data['action'] ?? 'detail';
        return match ($action) {
            'detail' => $this->getGamesheetDetail($data['encoded_id'] ?? ''),
            default => ['success' => false, 'messages' => ['Invalid gamesheet action']],
        };
    }

    private function handleViewAllApi(): array
    {
        try {
            $activeSeasonIds = Season::where('status_id', Season::STATUS_ACTIVE)->pluck('season_id')->toArray();

            $games = Schedule::with(['homeTeam', 'awayTeam', 'season.division', 'locationRelation'])
                ->whereIn('season_id', $activeSeasonIds)
                ->where('status_id', Schedule::STATUS_ACTIVE)
                ->orderBy('game_date', 'asc')
                ->orderByRaw("STR_TO_DATE(game_time, '%l:%i %p') ASC")
                ->get();

            ob_start();
            include __DIR__ . '/../../resources/views/components/gamesheets/games-table.php';
            $html = ob_get_clean();

            return ['success' => true, 'html' => $html];
        } catch (\Throwable $e) {
            static::logActivity('Error viewing global gamesheets: ' . $e->getMessage(), 'Gamesheets');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Same team/game data shape as SchedulesController::getScheduleDetail()
     * -- both modules present the same season's teams + games, just with a
     * different games-table component/click behavior below it.
     */
    private function getGamesheetDetail(string $encodedId): array
    {
        if (empty($encodedId)) return ['success' => false, 'messages' => ['No gamesheet context provided']];

        try {
            $seasonId = (int)IdEncoder::decode($encodedId);
            $season = Season::with('division')->where('season_id', $seasonId)->first();

            if (!$season) return ['success' => false, 'messages' => ['Gamesheet details not found.']];

            $teamsData = TeamsController::getBySeason($seasonId)->map(fn($team) => [
                'team_id' => $team->team_id,
                'team_name' => $team->team_name,
                'group_name' => $team->group->group_name ?? $team->team_group,
                'rep_name' => $team->representative->full_name ?? 'N/A',
                'player_count' => $team->players_count ?? 0,
            ])->toArray();

            $games = Schedule::with(['homeTeam', 'awayTeam', 'season.division', 'locationRelation'])
                ->where('season_id', $seasonId)
                ->where('status_id', Schedule::STATUS_ACTIVE)
                ->orderBy('game_date', 'asc')
                ->orderByRaw("STR_TO_DATE(game_time, '%l:%i %p') ASC")
                ->get();

            return [
                'success' => true,
                'data' => [
                    'divisionName' => $season->division->division ?? 'Unknown Division',
                    'seasonYear' => $season->season_year ?? '',
                    'encoded_id' => $encodedId,
                    'teams' => $teamsData,
                    'games' => $games,
                ],
            ];
        } catch (\Throwable $e) {
            static::logActivity('Error loading gamesheets: ' . $e->getMessage(), 'Gamesheets');
            return ['success' => false, 'messages' => ['Error loading gamesheets: ' . $e->getMessage()]];
        }
    }

    /**
     * Renders the roster-accordion HTML fragment for one game (home/away
     * side-by-side stat-entry tables). `$encodedId` is the IdEncoder-encoded
     * schedule_id (game), consistent with every other schedule_id reference
     * in the app -- unlike legacy, which leaked a raw int through this one
     * path (see roster-tables.php's data-game-id).
     */
    public function getRosters(string $encodedId): array
    {
        try {
            $scheduleId = (int)IdEncoder::decode($encodedId);
            if (!$scheduleId) throw new \Exception('Invalid Game ID.');

            $game = Schedule::with(['homeTeam', 'awayTeam'])->find($scheduleId);
            if (!$game) throw new \Exception('Game not found.');

            $rosters = $this->buildGameRosters($game);

            ob_start();
            include __DIR__ . '/../../resources/views/components/gamesheets/roster-tables.php';
            $html = ob_get_clean();

            return ['success' => true, 'html' => $html];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Raw (non-HTML) roster data for the PDF export, which needs to render
     * every game in a season in one pass rather than one fetch per game.
     */
    public function getRawRosterData(Schedule $game): array
    {
        $rosters = $this->buildGameRosters($game);
        return [
            'home' => $rosters['home'],
            'away' => $rosters['away'],
        ];
    }

    /**
     * Shared roster builder for both the accordion HTML fragment and the PDF
     * export. One `Gamesheet::whereIn('player_id', ...)` query per side
     * (not one query per player, unlike legacy's N+1) keyed by player_id for
     * an O(1) lookup while mapping the roster.
     */
    private function buildGameRosters(Schedule $game): array
    {
        $isPlayoff = (bool)$game->is_playoff;
        $gameType = $isPlayoff ? 'PLAYOFFS' : 'REGULAR';

        $fetchRoster = function (?int $teamId) use ($game) {
            $players = Player::where('team_id', $teamId)
                ->with(['profile:entry_id,full_name'])
                ->orderBy('is_goalie', 'asc')
                ->orderBy('player_number', 'asc')
                ->get();

            $stats = Gamesheet::where('schedule_id', $game->entry_id)
                ->whereIn('player_id', $players->pluck('player_id'))
                ->get()
                ->keyBy('player_id');

            return $players->map(function ($player) use ($stats) {
                $stat = $stats->get($player->player_id);

                return [
                    'player_id' => $player->player_id,
                    'number' => $player->is_goalie ? 'G' : $player->player_number,
                    'is_goalie' => (bool)$player->is_goalie,
                    'name' => $player->profile->full_name ?? 'Unknown Player',
                    'period' => $stat->period ?? '',
                    'time_of_goal' => $stat->time_of_goal ?? '',
                    'games_played' => $stat->games_played ?? 0,
                    'goals' => $stat->goals ?? 0,
                    'assists' => $stat->assists ?? 0,
                ];
            });
        };

        return [
            'game_id' => $game->entry_id,
            'encoded_game_id' => IdEncoder::encode($game->entry_id),
            'game_type' => $gameType,
            'home' => [
                'name' => $game->homeTeam->team_name ?? 'Home Team',
                'players' => $fetchRoster($game->home),
            ],
            'away' => [
                'name' => $game->awayTeam->team_name ?? 'Away Team',
                'players' => $fetchRoster($game->away),
            ],
        ];
    }

    /**
     * Admin-only inline-edit auto-save target for a single player/game stat
     * cell. `player_number` is the one field that writes to `players`
     * instead of `gamesheets` (it's a roster attribute, not a per-game one).
     */
    public function updateStat(array $data): array
    {
        try {
            $encodedGameId = $data['game_id'] ?? '';
            $playerId = (int)($data['player_id'] ?? 0);
            $field = $data['field'] ?? '';
            $value = $data['value'] ?? '';

            $scheduleId = is_numeric($encodedGameId) ? (int)$encodedGameId : (int)IdEncoder::decode((string)$encodedGameId);

            if (!$scheduleId || !$playerId || !$field) {
                throw new \Exception('Missing required save data.');
            }

            $game = Schedule::find($scheduleId);
            if (!$game) throw new \Exception('Game context not found.');

            $allowedFields = ['period', 'time_of_goal', 'games_played', 'goals', 'assists', 'player_number'];
            if (!in_array($field, $allowedFields, true)) {
                throw new \Exception('Invalid field update.');
            }

            $player = Player::with('profile')->find($playerId);
            if (!$player) throw new \Exception('Player not found.');
            $playerName = $player->profile->full_name ?? 'Unknown Player';

            if ($field === 'player_number') {
                $oldNum = $player->player_number;
                $player->player_number = $value;
                $player->save();

                static::logActivity("Updated player # for {$playerName}: {$oldNum} -> {$value}", 'Gamesheets');
            } else {
                Gamesheet::updateOrCreate(
                    ['schedule_id' => $scheduleId, 'player_id' => $playerId],
                    ['team_id' => $player->team_id, 'season_id' => $game->season_id, $field => $value]
                );

                $fieldLabel = str_replace('_', ' ', $field);
                static::logActivity("Updated {$fieldLabel} to '{$value}' for {$playerName} (Game: {$scheduleId})", 'Gamesheets');
            }

            return ['success' => true, 'messages' => ['Saved']];
        } catch (\Throwable $e) {
            static::logActivity('Gamesheet stat update error: ' . $e->getMessage(), 'Gamesheets');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
