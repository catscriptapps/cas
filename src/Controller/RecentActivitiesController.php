<?php
// /src/Controller/RecentActivitiesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\RecentActivity;
use Illuminate\Database\Eloquent\Collection;
use Src\Service\AuthService;

/**
 * RecentActivitiesController
 * Handles retrieval, pagination, and management (archive/delete)
 * of user activity logs for the Catscript-13 dashboard.
 */
class RecentActivitiesController
{
    /**
     * Prepare data for the History List Page. Supports the per-column text
     * filters + sortable columns + infinite scroll driven by
     * resources/js/components/data-table.js: `filter[user]` (matched
     * against the acting user's name OR the action text), `filter[entity_type]`,
     * `filter[date]` (matched against the same `M j, Y` formatted date the
     * row displays), each a case-insensitive LIKE, ANDed together; `sort` +
     * `dir` for column sorting (defaults to newest-first); `page` for
     * pagination. A plain unparameterized request renders the full page
     * with the first batch already server-rendered; any of page/filter/sort
     * being present signals an AJAX call, answered with JSON instead.
     */
    public function index(): void
    {
        $filters = is_array($_GET['filter'] ?? null) ? $_GET['filter'] : [];
        $sort = $_GET['sort'] ?? null;
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $builder = RecentActivity::with('user')
            ->leftJoin('users', 'recent_activities.user_id', '=', 'users.id')
            ->where('recent_activities.archived', false)
            ->select('recent_activities.*');

        // --- PRIVACY LAYER --- a non-Admin only ever sees their own activity.
        if (!AuthService::isAdmin()) {
            $builder->where('recent_activities.user_id', AuthService::userId());
        }

        if (!empty($filters['user'])) {
            $needle = $filters['user'];
            $builder->where(function ($q) use ($needle) {
                $q->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$needle}%"])
                    ->orWhere('recent_activities.action', 'LIKE', "%{$needle}%");
            });
        }
        if (!empty($filters['entity_type'])) {
            $builder->where('recent_activities.entity_type', 'LIKE', '%' . $filters['entity_type'] . '%');
        }
        if (!empty($filters['date'])) {
            $builder->whereRaw("DATE_FORMAT(recent_activities.created_at, '%b %e, %Y') LIKE ?", ['%' . $filters['date'] . '%']);
        }

        $total = (clone $builder)->count();

        $sortColumns = [
            'user' => 'users.first_name',
            'entity_type' => 'recent_activities.entity_type',
            'date' => 'recent_activities.created_at',
        ];

        if (isset($sortColumns[$sort])) {
            $builder->orderBy($sortColumns[$sort], $dir);
            if ($sort === 'user') {
                $builder->orderBy('users.last_name', $dir);
            }
        } else {
            $builder->orderBy('recent_activities.created_at', 'desc');
        }

        $activities = $builder->offset($offset)->limit($perPage)->get();

        $isAjax = isset($_GET['page']) || isset($_GET['filter']) || isset($_GET['sort']);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => array_map(fn($item) => ['rowHtml' => self::renderRow($item)], $activities->all()),
                'meta' => [
                    'total' => $total,
                    'loaded' => $activities->count(),
                    'hasMore' => ($offset + $activities->count()) < $total,
                ],
            ]);
            exit;
        }

        $html = '';
        foreach ($activities as $activity) {
            $html .= self::renderRow($activity);
        }

        $GLOBALS['historyRows'] = $html;
        $GLOBALS['historyCount'] = $total;
        $GLOBALS['title'] = "History";
    }

    /**
     * Render individual activity row HTML via component file
     */
    public static function renderRow(RecentActivity $activity): string
    {
        $user = $activity->user;

        // Pull assetBase into the local scope so the included view can see it
        $GLOBALS['assetBase'] = getAssetBase();
        $assetBase = getAssetBase();

        // Prepare data array for the component
        $row = $activity->toArray();
        $row['user_name'] = $user ? $user->full_name : 'System';
        $row['user_avatar'] = $user ? $user->avatar_url : null;

        // Date formatting using Carbon (assuming created_at is cast to datetime)
        $row['date_formatted'] = $activity->created_at->format('M j, Y');
        $row['time_formatted'] = $activity->created_at->format('H:i');

        // Logic for icon selection
        $row['icon'] = match ($activity->entity_type) {
            'Users'     => '👤',
            'Cash Flow' => '💰',
            'CashFlow'  => '💰',
            'Invoices'  => '📄',
            'FAQ'       => '❓',
            default     => '⚙️'
        };

        $path = __DIR__ . '/../../resources/views/components/history/data-row.php';

        ob_start();
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='4' class='p-4 text-center text-rose-500 font-bold'>History Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

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
     * Find a specific activity record by its ID.
     *
     * @param int $id
     * @return RecentActivity|null
     */
    public static function find(int $id): ?RecentActivity
    {
        return RecentActivity::find($id);
    }

    /**
     * Archive an activity (soft hide from main lists).
     *
     * @param int $id
     * @return bool
     */
    public static function archive(int $id): bool
    {
        $activity = RecentActivity::find($id);
        if (!$activity) {
            return false;
        }

        $activity->archived = true;
        return $activity->save();
    }

    /**
     * Unarchive an activity (restore to active list).
     *
     * @param int $id
     * @return bool
     */
    public static function unarchive(int $id): bool
    {
        $activity = RecentActivity::find($id);
        if (!$activity) {
            return false;
        }

        $activity->archived = false;
        return $activity->save();
    }

    /**
     * Permanently delete an activity.
     *
     * @param int $id
     * @return bool
     */
    public static function delete(int $id): bool
    {
        $activity = RecentActivity::find($id);
        if (!$activity) {
            return false;
        }

        return (bool) $activity->delete();
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
