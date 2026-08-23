<?php
// /src/Controller/RecentActivitiesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\RecentActivity;
use Illuminate\Database\Eloquent\Collection;
use Src\Service\AuthService;

/**
 * RecentActivitiesController
 * Feeds the recent-activity widget on the dashboard.
 */
class RecentActivitiesController
{
    /**
     * Retrieve the most recent N activity records (for dashboard widget).
     *
     * @param int $limit Number of activities to retrieve.
     * @return Collection
     */
    public static function latest(int $limit = 10): Collection
    {
        $query = RecentActivity::with('user')
            ->where('archived', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        // --- PRIVACY LAYER ---
        if (!AuthService::isAdmin()) {
            $query->where('user_id', AuthService::userId());
        }

        return $query->get();
    }

    /**
     * Record a new activity event.
     *
     * @param int|null $userId
     * @param string $action
     * @param string|null $entityType
     * @param int|null $entityId
     * @return RecentActivity
     */
    public static function log(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null): RecentActivity
    {
        return RecentActivity::log($userId, $action, $entityType, $entityId);
    }
}
