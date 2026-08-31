<?php
// /server/helpers.php

use Src\Config\NavigationConfig;

/**
 * Sends a successful JSON response and logs the API call.
 *
 * @param array $data The response payload to return to the client.
 * @param int   $code Optional HTTP status code (default is 200).
 */
function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    log_api_call($_SERVER['REQUEST_URI'] ?? '', $code, $_SERVER['REQUEST_METHOD'] ?? '');
    exit;
}

/**
 * Logs details about each API call to a file.
 */
function log_api_call(string $path, int $status, string $method, ?string $error = null): void {}

/**
 * Returns the asset base for either DEV or PRODUCTION.
 */
function getAssetBase()
{
    $untrimmedBasePath = $_ENV['APP_BASE_PATH'] ?? '';
    $baseUrl = '/' . (trim($untrimmedBasePath, '/') ? trim($untrimmedBasePath, '/') . '/' : '');
    $assetBase = $baseUrl;

    return rtrim($assetBase, '/') . '/';
}

/**
 * Returns the limit for media upload.
 */
function getMediaLimit()
{
    return 12;
}

/**
 * Generates the HTML status badge for an entry based on its current state.
 * * Maps internal status strings to Tailwind CSS styled span elements.
 * - 'active'   => Green (Active)
 * - 'pending'  => Yellow (Pending)
 * - 'inactive' => Red (Expired)
 * - 'rejected' => Slate (Rejected)
 * - default    => Gray (Archived)
 *
 * @param string|null $status The status key to match (active, pending, inactive, rejected).
 * @return string The complete HTML string for the status badge.
 */
function getStatusBadgeHtml($status)
{
    return match ($status) {
        'active'   => '<span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-green-600 dark:text-green-400 border border-green-100 dark:border-green-800/30">Active</span>',
        'pending'  => '<span class="inline-flex items-center rounded-full bg-yellow-50 dark:bg-yellow-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-yellow-600 dark:text-yellow-400 border border-yellow-100 dark:border-yellow-800/30">Pending</span>',
        'inactive' => '<span class="inline-flex items-center rounded-full bg-red-50 dark:bg-red-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-red-600 dark:text-red-400 border border-red-100 dark:border-red-800/30">Expired</span>',
        'rejected' => '<span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Rejected</span>',
        default    => '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Archived</span>'
    };
}

/**
 * Returns the human‑readable role name for a given owner's user type ID,
 * wrapped in a single-element array so consumers built around a JSON array
 * of role names (e.g. modal-detail-owner.js) don't need to change now that
 * a user only ever has one type.
 *
 * This function loads all user types once and caches them in $GLOBALS['allUserTypes']
 * to avoid repeated lookups. If the type ID doesn't exist in the cached list,
 * the fallback role name "User" is used.
 *
 * @param object $owner  An object containing a `user_type_id` property.
 *
 * @return string  JSON‑encoded array containing the owner's single role name.
 */
function getUserRoles($owner)
{
    if (!isset($GLOBALS['allUserTypes'])) {
        $types = \Src\Controller\UserTypesController::list();
        $GLOBALS['allUserTypes'] = [];
        if ($types) {
            foreach ($types as $t) {
                $GLOBALS['allUserTypes'][$t->user_type_id] = $t->user_type;
            }
        }
    }

    $typeId = $owner->user_type_id ?? null;

    return json_encode($typeId ? [$GLOBALS['allUserTypes'][$typeId] ?? 'User'] : []);
}

/**
 * Normalize the incoming URI by removing the base path (if present).
 */
function normalizePath(string $uri, string $basePath): string
{
    $uri = '/' . ltrim($uri, '/');

    if ($basePath && str_starts_with($uri, '/' . $basePath)) {
        $uri = substr($uri, strlen('/' . $basePath));
    }

    $path = '/' . ltrim($uri, '/');
    return $path === '/' ? '/home' : $path;
}

/**
 * Handle API route resolution and execution.
 */
function resolveApiRoute(string $path): bool
{
    if (!str_starts_with($path, '/api/')) return false;

    $segments = explode('/', ltrim($path, '/'));
    $apiFile = __DIR__ . '/../server/api/' . ($segments[1] ?? 'index') . '.php';

    if (file_exists($apiFile)) {
        include $apiFile;
        exit;
    }

    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'API endpoint not found']);
    exit;
}

/**
 * Resolve metadata (icon + URL) for a given page title.
 */
function resolvePageMeta(string $pageTitle, bool $isLoggedIn = false): array
{
    $icons = NavigationConfig::getIcons();
    $links = NavigationConfig::getNavLinks($isLoggedIn);

    return [
        'icon' => $icons[$pageTitle] ?? '',
        'url'  => $links[$pageTitle] ?? '',
    ];
}

/**
 * Resolve a page route and derive a human-friendly title.
 */
function resolvePageRoute(string $path): array
{
    if (preg_match('#^/([^/]+)/([^/]+)$#', $path, $m)) {
        $resource = $m[1];
        $id = urldecode($m[2]);

        $detailFile = __DIR__ . "/../resources/views/pages/{$resource}/detail.php";

        if (file_exists($detailFile)) {
            $GLOBALS['encodedId'] = $id;

            // Detail-route pages are dynamic and can't be matched by NavigationConfig,
            // so the layout header can't compute a title/summary for them on its own.
            // Give the resource a chance to supply one here, before the layout renders.
            $dynamicMeta = resolveDynamicPageMeta($resource, $id);
            if ($dynamicMeta) {
                $GLOBALS['pageSummary'] = $dynamicMeta['summary'];
                return [$detailFile, $dynamicMeta['title']];
            }

            return [$detailFile, ucfirst($resource) . ' Details'];
        }
    }

    $pageFile = __DIR__ . '/../resources/views/pages/' . ltrim($path, '/') . '.php';

    if (!file_exists($pageFile)) {
        http_response_code(404);
        $pageFile = __DIR__ . '/../resources/views/pages/404.php';
        $title = 'Page Not Found';
    } else {
        $slug = basename($path);
        $title = ucwords(str_replace(['-', '_'], ' ', $slug));

        // Flat pages (e.g. /my-applications, /properties) get their title and
        // X-Page-Summary from NavigationConfig when a match exists, so the
        // header shows a summary on SPA partial loads too, not just hard
        // refreshes (the layout header does its own matching for those).
        $matchedMeta = \Src\Config\NavigationConfig::resolveMetaForPath($path);
        if ($matchedMeta) {
            $title = $matchedMeta['title'];
            $GLOBALS['pageSummary'] = $matchedMeta['summary'];
        }
    }

    return [$pageFile, $title];
}

/**
 * Resource-specific title/summary lookups for detail-route pages, consumed
 * by resolvePageRoute() before the layout header renders.
 *
 * @return array{title: string, summary: string}|null
 */
function resolveDynamicPageMeta(string $resource, string $id): ?array
{
    if ($resource === 'league-details' && in_array($id, ['ball-hockey', 'ice-hockey'], true)) {
        $sportTitle = $id === 'ball-hockey' ? 'Ball Hockey' : 'Ice Hockey';
        return [
            'title' => $sportTitle,
            'summary' => "Leagues, divisions, and current pricing for {$sportTitle}.",
        ];
    }

    // No other detail-route resource currently defines dynamic meta -- add a
    // branch here when a page under resources/views/pages/{resource}/detail.php
    // needs one (see resolvePageRoute() above for how this hook is called).
    return null;
}

/**
 * Recursively delete a directory and its contents.
 */
function rrmdir(string $dir): bool
{
    if (!is_dir($dir)) return false;

    $items = scandir($dir);
    if ($items === false) return false;

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            if (!rrmdir($path)) return false;
        } else {
            if (!unlink($path)) return false;
        }
    }
    return rmdir($dir);
}

/**
 * Convert an absolute file path to a format suitable for mPDF image source.
 */
function pdfImageSrc(string $absolute): ?string
{
    if (!file_exists($absolute)) return null;
    return str_replace('\\', '/', $absolute);
}

/**
 * Render a PHP view file with provided variables and return the output as a string.
 */
function renderView(string $path, array $vars = []): string
{
    if (!file_exists($path)) return "<p>View not found: {$path}</p>";

    ob_start();
    extract($vars);
    include $path;
    return ob_get_clean();
}
