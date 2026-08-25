<?php
// /src/Controller/StatsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\PlayerStat;
use App\Models\Season;
use App\Models\Stat;
use App\Models\Team;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;

/**
 * Team standings + player season stats. Ported from legacy cas-sports.
 *
 * There is deliberately no game-outcome inference anywhere in this
 * controller: `schedules` carries no score columns, and gamesheets' (if
 * this project ever builds that feature) per-player goals are never summed
 * into a team total. Win/loss/tie/goals-for/goals-against and player
 * goals/assists are 100% hand-maintained by admins via inline-editable
 * inputs (see save()), exactly matching legacy.
 */
class StatsController
{
    use RecentActivityLogger;

    public function index(array $data = []): array
    {
        $action = $data['action'] ?? 'detail';
        return match ($action) {
            'detail' => $this->getStatsDetail($data['encoded_id'] ?? ''),
            default => ['success' => false, 'messages' => ['Invalid stats action']],
        };
    }

    /**
     * Used by the PDF export -- a single is_playoff flag rather than the
     * detail page's "both regular and playoff, pick a tab" shape.
     */
    public function getStatsData(int $seasonId, bool $isPlayoff = false): array
    {
        $playoffFlag = $isPlayoff ? 1 : 0;

        $teams = Team::with(['stats' => function ($query) use ($seasonId, $playoffFlag) {
            $query->where('season_id', $seasonId)->where('is_playoff', $playoffFlag);
        }])->where('season_id', $seasonId)->where('status_id', Team::STATUS_ACTIVE)->get();

        return [
            'groupedData' => $this->formatGroupedData($teams),
            'rosters' => $this->buildRosters($seasonId),
        ];
    }

    private function getStatsDetail(string $encodedId): array
    {
        if (empty($encodedId)) return ['success' => false, 'messages' => ['No season ID provided']];

        try {
            $seasonId = (int)IdEncoder::decode($encodedId);
            $season = Season::with('division')->where('season_id', $seasonId)->first();
            if (!$season) return ['success' => false, 'messages' => ['Season not found.']];

            $regularTeams = Team::with(['stats' => function ($query) use ($seasonId) {
                $query->where('season_id', $seasonId)->where('is_playoff', 0);
            }])->where('season_id', $seasonId)->where('status_id', Team::STATUS_ACTIVE)->get();

            $playoffTeams = Team::with(['stats' => function ($query) use ($seasonId) {
                $query->where('season_id', $seasonId)->where('is_playoff', 1);
            }])->where('season_id', $seasonId)->where('status_id', Team::STATUS_ACTIVE)->get();

            return [
                'success' => true,
                'data' => [
                    'season_id' => $season->season_id,
                    'divisionName' => $season->division->division ?? 'Unknown Division',
                    'seasonYear' => $season->season_year ?? '',
                    'encoded_id' => $encodedId,
                    'groupedStats' => $this->formatGroupedData($regularTeams),
                    'playoffGroupedStats' => $this->formatGroupedData($playoffTeams),
                    'rosters' => $this->buildRosters($seasonId),
                ],
            ];
        } catch (\Throwable $e) {
            static::logActivity('Stats detail error: ' . $e->getMessage(), 'Stats');
            return ['success' => false, 'messages' => ['Error loading statistics: ' . $e->getMessage()]];
        }
    }

    /**
     * Per-team roster split into skaters (ranked by points desc) and
     * goalies (ranked by GAA asc, players with 0 GP sorted last).
     */
    private function buildRosters(int $seasonId)
    {
        return Team::with(['players' => function ($query) use ($seasonId) {
            $query->with(['profile', 'stats' => function ($q) use ($seasonId) {
                $q->where('season_id', $seasonId);
            }]);
        }])->where('season_id', $seasonId)->where('status_id', Team::STATUS_ACTIVE)->get()
            ->map(function ($team) {
                return [
                    'team_name' => $team->team_name,
                    'team_id' => $team->team_id,
                    'skaters' => $team->players->filter(fn($p) => (int)$p->is_goalie === 0)
                        ->sortByDesc(function ($player) {
                            $s = $player->stats->first();
                            return ($s->goals ?? 0) + ($s->assists ?? 0);
                        })->values(),
                    'goalies' => $team->players->filter(fn($p) => (int)$p->is_goalie === 1)
                        ->sortBy(function ($player) {
                            $s = $player->stats->first();
                            $gp = $s->games_played ?? 0;
                            $ga = $s->goals_against ?? 0;
                            return $gp > 0 ? ($ga / $gp) : 999;
                        })->values(),
                ];
            });
    }

    /**
     * Groups active teams by team_group ("A"/"B"/"C1"/etc, blank/"N/A"
     * excluded), computes PTS (win=2, tie=1) / GP / goal differential on
     * read from the stored W/L/T/GF/GA, and sorts each group by
     * points -> wins -> differential.
     */
    private function formatGroupedData($teamsCollection): array
    {
        return $teamsCollection
            ->filter(function ($team) {
                $group = trim((string)$team->team_group);
                return $group !== '' && strtolower($group) !== 'n/a';
            })
            ->groupBy('team_group')
            ->sortKeys()
            ->map(function ($groupTeams) {
                return $groupTeams->map(function ($team) {
                    $stat = $team->stats->first();
                    $wins = $stat->wins ?? 0;
                    $losses = $stat->losses ?? 0;
                    $ties = $stat->ties ?? 0;
                    $gf = $stat->goals_for ?? 0;
                    $ga = $stat->goals_against ?? 0;

                    return [
                        'team_id' => $team->team_id,
                        'team_number' => $team->team_number,
                        'team_name' => $team->team_name,
                        'wins' => $wins,
                        'losses' => $losses,
                        'ties' => $ties,
                        'gf' => $gf,
                        'ga' => $ga,
                        'diff' => $gf - $ga,
                        'pts' => ($wins * 2) + $ties,
                        'gp' => $wins + $losses + $ties,
                    ];
                })->sort(function ($a, $b) {
                    if ($a['pts'] !== $b['pts']) return $b['pts'] <=> $a['pts'];
                    if ($a['wins'] !== $b['wins']) return $b['wins'] <=> $a['wins'];
                    return $b['diff'] <=> $a['diff'];
                })->values();
            })->toArray();
    }

    /**
     * Admin-only inline-edit auto-save target for both a team's standings
     * row and a player's stat row (see resources/js/utils/stats/
     * auto-save-handler.js).
     */
    public function save(array $data): array
    {
        try {
            $id = $data['id'] ?? null;
            $type = $data['type'] ?? null;
            $stats = is_array($data['stats'] ?? null) ? $data['stats'] : [];
            $seasonId = $data['season_id'] ?? null;
            $isPlayoff = (int)($data['is_playoff'] ?? 0);

            if (!$id || !$seasonId) {
                throw new \Exception('Missing required ID or Season context.');
            }

            if ($type === 'team') {
                $team = Team::find($id);
                $teamName = $team->team_name ?? 'Unknown Team';

                Stat::updateOrCreate(
                    ['team_id' => $id, 'season_id' => $seasonId, 'is_playoff' => $isPlayoff],
                    $this->sanitizeIntFields($stats, ['wins', 'losses', 'ties', 'goals_for', 'goals_against'])
                );

                $context = $isPlayoff ? 'Playoffs' : 'Regular Season';
                static::logActivity("Updated standings for {$teamName} ({$context})", 'Standings');
            } else {
                $sanitized = $this->sanitizeIntFields($stats, ['goals', 'assists', 'games_played', 'goals_against', 'shots_on_goal']);
                $sanitized['points'] = ($sanitized['goals'] ?? 0) + ($sanitized['assists'] ?? 0);

                $player = \App\Models\Player::with('profile')->find($id);
                $playerName = $player->profile->full_name ?? 'Unknown Player';

                if (!isset($sanitized['team_id'])) {
                    $sanitized['team_id'] = $player->team_id ?? 0;
                }

                PlayerStat::updateOrCreate(['player_id' => $id, 'season_id' => $seasonId], $sanitized);
                static::logActivity("Synchronized career stats for {$playerName}", 'Stats');
            }

            return ['success' => true, 'messages' => ['Stats synchronized.']];
        } catch (\Throwable $e) {
            static::logActivity('Stats sync error: ' . $e->getMessage(), 'Stats');
            return ['success' => false, 'messages' => ['Update failed: ' . $e->getMessage()]];
        }
    }

    private function sanitizeIntFields(array $stats, array $allowedFields): array
    {
        $clean = [];
        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $stats)) {
                $clean[$field] = (int)$stats[$field];
            }
        }
        return $clean;
    }
}
