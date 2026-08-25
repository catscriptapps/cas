<?php
// /src/Controller/PlayersController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Player;
use App\Models\Registration;
use App\Traits\RecentActivityLogger;
use Src\Service\AuthService;

/**
 * Roster CRUD, ported from legacy cas-sports (player_id used raw, not
 * IdEncoder-obfuscated, matching legacy exactly).
 */
class PlayersController
{
    use RecentActivityLogger;

    public function index(array $data): array
    {
        if (!AuthService::isLoggedIn()) {
            return ['success' => false, 'messages' => ["You don't have permission to do that."]];
        }

        $action = $data['action'] ?? 'roster';

        return match ($action) {
            'roster' => $this->getRoster((int)($data['team_id'] ?? 0)),
            'available' => $this->getAvailableRegistrants((int)($data['team_id'] ?? 0)),
            'add' => $this->addPlayer($data),
            'delete' => $this->removePlayer((int)($data['player_id'] ?? 0)),
            'toggle-goalie' => $this->toggleGoalieStatus($data),
            default => ['success' => false, 'messages' => ['Invalid player action']],
        };
    }

    private function getRoster(int $teamId): array
    {
        if (!$teamId) return ['success' => false, 'players' => []];

        $players = Player::where('team_id', $teamId)
            ->with('profile')
            ->get()
            ->map(fn($player) => [
                'player_id' => $player->player_id,
                'full_name' => $player->profile->full_name ?? 'Unknown Player',
                'is_goalie' => $player->is_goalie,
                'user_id' => $player->user_id,
                'date_created' => $player->date_created,
            ]);

        return ['success' => true, 'players' => $players];
    }

    /**
     * All active registrants league-wide who aren't already on this team,
     * unique by full_name (matches legacy exactly -- a real quirk: two
     * distinct registrations sharing a name would dedupe to one option).
     */
    private function getAvailableRegistrants(int $teamId): array
    {
        $assignedUserIds = Player::where('team_id', $teamId)->pluck('user_id')->toArray();

        $registrants = Registration::where('status_id', 1)
            ->whereNotIn('entry_id', $assignedUserIds)
            ->orderBy('full_name', 'ASC')
            ->get(['entry_id', 'full_name', 'position'])
            ->unique('full_name')
            ->values();

        return ['success' => true, 'registrants' => $registrants];
    }

    /**
     * A registrant can only be on one team per season, globally -- matches
     * legacy's duplicate guard exactly.
     */
    private function addPlayer(array $data): array
    {
        $teamId = (int)($data['team_id'] ?? 0);
        $userId = (int)($data['user_id'] ?? 0);
        $seasonId = (int)($data['season_id'] ?? 0);

        if (!$teamId || !$userId || !$seasonId) {
            return ['success' => false, 'messages' => ["Missing required data: Team({$teamId}), User({$userId}), Season({$seasonId})"]];
        }

        try {
            $existingEntry = Player::where('season_id', $seasonId)->where('user_id', $userId)->first();

            if ($existingEntry) {
                if ($existingEntry->team_id === $teamId) {
                    return ['success' => false, 'messages' => ['Player is already on this team roster.']];
                }
                return ['success' => false, 'messages' => ['Player is already registered to a different team in this season.']];
            }

            $player = new Player();
            $player->team_id = $teamId;
            $player->user_id = $userId;
            $player->season_id = $seasonId;
            $player->is_goalie = (int)($data['is_goalie'] ?? 0);
            $player->status_id = 1;
            $player->player_number = '';
            $player->date_created = date('Y-m-d');
            $player->timestamp = date('Y-m-d H:i:s');
            $player->save();

            static::logActivity('Added player to roster', 'Schedules', $player->player_id);

            return ['success' => true, 'messages' => ['Player added to roster.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => ['SQL Error: ' . $e->getMessage()]];
        }
    }

    private function removePlayer(int $playerId): array
    {
        if (!$playerId) return ['success' => false, 'messages' => ['Invalid Player ID']];

        try {
            $player = Player::find($playerId);
            if ($player) {
                static::logActivity('Removed player from roster', 'Schedules');
                $player->delete();
            }
            return ['success' => true, 'messages' => ['Player removed from roster.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => ['Database error: ' . $e->getMessage()]];
        }
    }

    private function toggleGoalieStatus(array $data): array
    {
        $playerId = (int)($data['player_id'] ?? 0);
        $isGoalie = (int)($data['is_goalie'] ?? 0);

        if (!$playerId) {
            return ['success' => false, 'messages' => ['Invalid Player ID']];
        }

        try {
            $player = Player::find($playerId);
            if (!$player) {
                return ['success' => false, 'messages' => ['Player record not found.']];
            }

            $player->is_goalie = $isGoalie;
            $player->save();

            return ['success' => true, 'messages' => [$isGoalie ? 'Player set as Goalie.' : 'Goalie status removed.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => ['Database error: ' . $e->getMessage()]];
        }
    }
}
