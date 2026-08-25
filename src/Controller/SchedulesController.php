<?php
// /src/Controller/SchedulesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Schedule;
use App\Models\Season;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;

/**
 * Games within a season, plus the "View All" master schedule across every
 * active season. Ported from legacy cas-sports.
 */
class SchedulesController
{
    use RecentActivityLogger;

    public function index(array $data = []): array
    {
        if (isset($_GET['action']) && $_GET['action'] === 'view_all') {
            return $this->handleViewAllApi();
        }

        $action = $data['action'] ?? 'detail';
        return match ($action) {
            'detail' => $this->getScheduleDetail($data['encoded_id'] ?? ''),
            default => ['success' => false, 'messages' => ['Invalid action']],
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
                // The "secret sauce" to sort game_time's free-text "7:15 PM" strings chronologically.
                ->orderByRaw("STR_TO_DATE(game_time, '%l:%i %p') ASC")
                ->get();

            ob_start();
            include __DIR__ . '/../../resources/views/components/schedules/games-table.php';
            $html = ob_get_clean();

            return ['success' => true, 'html' => $html];
        } catch (\Throwable $e) {
            static::logActivity('Error viewing global schedules: ' . $e->getMessage(), 'Schedules');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function save(array $input): array
    {
        try {
            $gameIdEnc = $input['txt_schedule_id'] ?? null;
            $seasonIdEnc = $input['txt_season_id'] ?? null;

            if (!$seasonIdEnc) {
                return ['success' => false, 'messages' => ['Missing season context.']];
            }

            $hrs = (int)($input['sel_hrs'] ?? 7);
            $mins = $input['sel_mins'] ?? '00';
            $ampm = $input['sel_am_pm'] ?? 'PM';
            $formattedTime = "{$hrs}:{$mins} {$ampm}";

            $data = [
                'season_id' => (int)IdEncoder::decode($seasonIdEnc),
                'game_date' => $input['txt_game_date'] ?? null,
                'game_time' => $formattedTime,
                'location' => (int)($input['sel_location'] ?? 0),
                'home' => (int)($input['sel_teams_home'] ?? 0),
                'away' => (int)($input['sel_teams_away'] ?? 0),
                'referee1' => $input['txt_referee_1'] ?? null,
                'referee2' => $input['txt_referee_2'] ?? null,
                'timekeep' => $input['txt_timekeep'] ?? null,
                'is_playoff' => (int)($input['sel_is_playoff'] ?? 0),
                'status_id' => 1,
            ];

            if ($gameIdEnc) {
                $gameId = (int)IdEncoder::decode($gameIdEnc);
                $game = Schedule::with(['homeTeam', 'awayTeam'])->find($gameId);
                if (!$game) return ['success' => false, 'messages' => ['Game not found.']];

                $game->update($data);

                static::logActivity("Updated game: {$game->homeTeam->team_name} vs {$game->awayTeam->team_name} on {$data['game_date']} @ {$data['game_time']}", 'Schedules');
                $msg = 'Game updated successfully.';
                $savedGameId = $game->entry_id;
            } else {
                $data['date_created'] = date('Y-m-d H:i:s');
                $data['timestamp'] = date('Y-m-d H:i:s');

                $newGame = Schedule::create($data);
                $newGame->load(['homeTeam', 'awayTeam']);

                static::logActivity("Scheduled new game: {$newGame->homeTeam->team_name} vs {$newGame->awayTeam->team_name} for {$data['game_date']} @ {$data['game_time']}", 'Schedules', $newGame->entry_id);
                $msg = 'Game added to schedule.';
                $savedGameId = $newGame->entry_id;
            }

            // The full games table always re-sorts by date/time on reload
            // (see getScheduleDetail()), so the saved game's chronological
            // position is already correct with no client-side re-sorting
            // needed -- the caller just needs this ID to scroll to it, since
            // "correct position" could be anywhere in a season's full list.
            return ['success' => true, 'messages' => [$msg], 'gameId' => IdEncoder::encode((int)$savedGameId)];
        } catch (\Throwable $e) {
            static::logActivity('Schedule save error: ' . $e->getMessage(), 'Schedules');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function delete($encodedId): array
    {
        try {
            $id = (int)IdEncoder::decode((string)$encodedId);
            $game = Schedule::with(['homeTeam', 'awayTeam'])->find($id);

            if (!$game) return ['success' => false, 'messages' => ['Game not found.']];

            $home = $game->homeTeam->team_name ?? 'Unknown';
            $away = $game->awayTeam->team_name ?? 'Unknown';
            $date = $game->game_date;

            if ($game->delete()) {
                static::logActivity("Removed game from schedule: {$home} vs {$away} ({$date})", 'Schedules');
                return ['success' => true, 'messages' => ['Game removed from schedule.']];
            }

            return ['success' => false, 'messages' => ['Failed to remove game.']];
        } catch (\Throwable $e) {
            static::logActivity('Schedule deletion error: ' . $e->getMessage(), 'Schedules');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function getScheduleDetail(string $encodedId): array
    {
        if (empty($encodedId)) return ['success' => false, 'messages' => ['No schedule ID provided']];

        try {
            $seasonId = (int)IdEncoder::decode($encodedId);
            $season = Season::with('division')->where('season_id', $seasonId)->first();

            if (!$season) return ['success' => false, 'messages' => ['Schedule details not found.']];

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
            static::logActivity('Error loading schedule details: ' . $e->getMessage(), 'Schedules');
            return ['success' => false, 'messages' => ['Error loading schedule: ' . $e->getMessage()]];
        }
    }
}
