<?php
// /src/Controller/MyAccountController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Gamesheet;
use App\Models\Player;
use App\Models\PlayerStat;
use App\Models\Registration;
use App\Models\Schedule;
use App\Models\Stat;
use App\Traits\RecentActivityLogger;
use Src\Service\AuthService;

/**
 * Powers the self-service registrant dashboard (/my-account) -- everything
 * a registrant is entitled to see or edit about themselves, scoped strictly
 * to the registrations under their own logged-in email (see
 * AuthService::currentRegistrations()). Distinct from RegistrationsController,
 * which is the admin-only equivalent with no such scoping.
 */
class MyAccountController
{
    use RecentActivityLogger;

    /**
     * One entry per registration row under the signed-in registrant's email
     * (usually one, but a parent with multiple kids/seasons registered under
     * the same email sees all of them here), each enriched with team
     * assignment, upcoming games, personal/team stats, and gamesheets --
     * everything the dashboard needs in one call.
     */
    public function dashboardData(): array
    {
        $registrations = AuthService::currentRegistrations();
        $items = [];

        foreach ($registrations as $registration) {
            $players = Player::with('team')
                ->where('user_id', $registration->entry_id)
                ->where('status_id', Player::STATUS_ACTIVE)
                ->get();

            $teams = [];
            foreach ($players as $player) {
                $team = $player->team;
                if (!$team) {
                    continue;
                }

                $upcomingGames = Schedule::with(['homeTeam', 'awayTeam', 'locationRelation'])
                    ->where(function ($q) use ($team) {
                        $q->where('home', $team->team_id)->orWhere('away', $team->team_id);
                    })
                    ->where('status_id', Schedule::STATUS_ACTIVE)
                    ->orderBy('game_date')
                    ->get()
                    ->map(fn($game) => [
                        'date' => $game->game_date?->format('M j, Y'),
                        'time' => $game->game_time,
                        'opponent' => $game->home === $team->team_id
                            ? ($game->awayTeam->team_name ?? 'TBD')
                            : ($game->homeTeam->team_name ?? 'TBD'),
                        'is_home' => $game->home === $team->team_id,
                        'location' => $game->locationRelation->location_desc ?? null,
                        'is_playoff' => (bool)$game->is_playoff,
                    ]);

                $teamStat = Stat::where('team_id', $team->team_id)
                    ->where('season_id', $team->season_id)
                    ->where('is_playoff', 0)
                    ->first();

                $playerStat = PlayerStat::where('player_id', $player->player_id)
                    ->where('season_id', $team->season_id)
                    ->first();

                $gamesheets = Gamesheet::with('schedule')
                    ->where('player_id', $player->player_id)
                    ->get()
                    ->sortByDesc(fn($g) => $g->schedule?->game_date)
                    ->values()
                    ->map(fn($g) => [
                        'date' => $g->schedule?->game_date?->format('M j, Y'),
                        'goals' => $g->goals,
                        'assists' => $g->assists,
                        'games_played' => $g->games_played,
                    ]);

                $teams[] = [
                    'team_name' => $team->team_name,
                    'team_number' => $team->team_number,
                    'is_goalie' => (bool)$player->is_goalie,
                    'player_number' => $player->player_number,
                    'upcoming_games' => $upcomingGames,
                    'team_record' => $teamStat ? [
                        'wins' => $teamStat->wins,
                        'losses' => $teamStat->losses,
                        'ties' => $teamStat->ties,
                        'goals_for' => $teamStat->goals_for,
                        'goals_against' => $teamStat->goals_against,
                    ] : null,
                    'player_stats' => $playerStat ? [
                        'goals' => $playerStat->goals,
                        'assists' => $playerStat->assists,
                        'points' => $playerStat->points,
                        'games_played' => $playerStat->games_played,
                    ] : null,
                    'gamesheets' => $gamesheets,
                ];
            }

            $items[] = [
                'entry_id' => $registration->entry_id,
                'full_name' => $registration->full_name,
                'age' => $registration->age,
                'email' => $registration->email,
                'phone' => $registration->phone,
                'address' => $registration->address,
                'city' => $registration->city,
                'province_id' => $registration->province_id,
                'postal_code' => $registration->postal_code,
                'position' => $registration->position,
                'hear_about_us' => $registration->hear_about_us,
                'team_name' => $registration->team_name,
                'special_requests' => $registration->special_requests,
                'division' => $registration->division?->division,
                'league' => $registration->division?->league?->league,
                'has_paid' => (bool)$registration->has_paid,
                'amount_paid' => (float)$registration->amount_paid,
                'date_created' => $registration->date_created?->format('M j, Y'),
                'teams' => $teams,
            ];
        }

        return $items;
    }

    /**
     * Editable fields intentionally exclude email (their login identity --
     * changing it here would silently detach them from their own account),
     * and every payment/status/division field (has_paid, amount_paid,
     * transaction_id, paypal_order_id, status_id, division_id) -- those stay
     * admin-only via RegistrationsController, same as before this feature.
     */
    public function updateRegistration(int $entryId, array $data): array
    {
        try {
            $registration = Registration::find($entryId);
            if (!$registration) {
                throw new \Exception('Registration not found.');
            }

            // Ownership check -- the entry_id in the URL/payload must belong
            // to the signed-in registrant's own email, not just any valid id.
            $ownedEntryIds = AuthService::currentRegistrations()->pluck('entry_id')->all();
            if (!in_array($entryId, $ownedEntryIds, true)) {
                throw new \Exception("You don't have permission to edit that registration.");
            }

            $fullName = trim((string)($data['full_name'] ?? $registration->full_name));
            if ($fullName === '') {
                throw new \Exception('Full name is required.');
            }

            $registration->full_name = $fullName;
            $registration->age = isset($data['age']) && $data['age'] !== '' ? (string)(int)$data['age'] : $registration->age;
            $registration->phone = $data['phone'] ?? $registration->phone;
            $registration->address = $data['address'] ?? $registration->address;
            $registration->city = $data['city'] ?? $registration->city;
            $registration->province_id = !empty($data['province_id']) ? (int)$data['province_id'] : $registration->province_id;
            $registration->postal_code = $data['postal_code'] ?? $registration->postal_code;
            $registration->position = $data['position'] ?? $registration->position;
            $registration->team_name = $data['team_name'] ?? $registration->team_name;
            $registration->special_requests = $data['special_requests'] ?? $registration->special_requests;
            $registration->save();

            static::logActivity(
                "Registrant self-updated their info: {$registration->full_name}",
                'Registrations',
                $registration->entry_id
            );

            return ['success' => true, 'messages' => ['Your information has been updated.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}
