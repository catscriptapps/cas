<?php

/**
 * Project:         CatScript Web Application for Property Management Brokers (PMB)
 * Platform:        CatScript-13
 * Version:         1.1.0
 * Client:          Richard Brisson
 * Author:          Chigozie (Cat) Nduanya
 * Date-Created:    2026-06-06
 */

// /public/index.php

declare(strict_types=1);

require_once __DIR__ . '/../server/bootstrap.php';
require_once __DIR__ . '/../server/helpers.php';

use Src\Service\AuthService;
use Src\Config\NavigationConfig;

// ------------------------------------------------------------
// Config & Environment
// ------------------------------------------------------------
$basePath = trim($_ENV['APP_BASE_PATH'] ?? '', '/');
$appName = $_ENV['APP_NAME'] ?? 'CatScript Apps';
$isPartial = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

// ------------------------------------------------------------
// Main Execution
// ------------------------------------------------------------
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = normalizePath($uri, $basePath);

// Auth check
$isAdminReset = filter_var($_ENV['ADMIN_RESET'] ?? false, FILTER_VALIDATE_BOOLEAN); // Convert the string "true" from .env into a real boolean true

if ($isAdminReset === true) {
    $isLoggedIn = false;
    $currentUser = null;
} else {
    $isLoggedIn = AuthService::isLoggedIn();
    $currentUser = $isLoggedIn ? AuthService::currentUser() : null;
}

// Redirect logged-in users away from home. This only swaps which page file
// gets rendered for the request -- the browser's address bar still shows
// the original "/" (or the live deployment's base-path segment), since
// there's no actual HTTP redirect here.
$rootPageScript = null;
if ($path === '/home' && $isLoggedIn) {
    $path = '/dashboard';
}

// Serve static assets directly
$assetFile = __DIR__ . $path;
if (is_file($assetFile)) {
    return false;
}

// API route handling
resolveApiRoute($path);

$untrimmedBasePath = $_ENV['APP_BASE_PATH'] ?? '';
$baseUrl = '/' . (trim($untrimmedBasePath, '/') ? trim($untrimmedBasePath, '/') . '/' : '');
$assetBase = $baseUrl;
$baseUrl = rtrim($baseUrl, '/') . '/';
$assetBase = rtrim($assetBase, '/') . '/';

// ------------------------------------------------------------
// Protected route handling (The Security Guard)
// ------------------------------------------------------------
$protectedPaths = NavigationConfig::getProtectedPaths();
$fullPath = $untrimmedBasePath . $path;

// Exact match covers flat pages (e.g. /questions); the prefix check covers
// dynamic detail routes nested under a protected resource (e.g.
// /inspections/{encodedId}), which resolvePageRoute() below resolves to
// resources/views/pages/inspections/detail.php.
$isProtectedPath = in_array($fullPath, $protectedPaths, true) || array_reduce(
    $protectedPaths,
    fn($matched, $protected) => $matched || ($protected !== '' && str_starts_with($fullPath, $protected . '/')),
    false
);

if ($isProtectedPath) {

    // 1. If not logged in at all
    if (!$isLoggedIn) {
        [$pageFile, $title] = [__DIR__ . '/../resources/views/pages/auth-required.php', 'Not Authenticated'];
        $pageMeta = ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4.93 4.93a10 10 0 0114.14 0 10 10 0 010 14.14 10 10 0 01-14.14 0 10 10 0 010-14.14z" /></svg>'];

        if ($isPartial) {
            header('X-Page-Title: ' . $title);
            include $pageFile;
            exit;
        }
        include __DIR__ . '/../resources/views/layouts/app.php';
        exit;
    }

    // 2. Check App-specific Permission
    //
    // authLinks() is the backend Users' app menu (Dashboard, Slideshow,
    // Profile, Users) -- it exists to gate which backend modules a non-admin
    // staff account can see. Only run this check for an actual signed-in
    // backend User.
    if (AuthService::currentUser()) {
        $allApps = NavigationConfig::authLinks(true);
        $currentAppName = null;
        foreach ($allApps as $navAppName => $appConfig) {
            if (($appConfig['url'] ?? null) === $fullPath) {
                $currentAppName = $navAppName;
                break;
            }
        }

        if ($currentAppName && !AuthService::hasAccess((string)$currentAppName)) {
            header("Location: " . $baseUrl . "access-denied");
            exit;
        }
    }
}

// ------------------------------------------------------------
// Page route handling
// ------------------------------------------------------------
[$pageFile, $title] = resolvePageRoute($path);
$pageMeta = resolvePageMeta($title, $isLoggedIn);
$pageIcon = $pageMeta['icon'];

// Partial request handling (AJAX)
if ($isPartial) {
    // rawurlencode keeps header values safe (and readable by the SPA router as a
    // fallback title/summary source when a partial load has no clicked <a data-partial> trigger)
    header('X-Page-Title: ' . rawurlencode($title));
    header('X-Page-Summary: ' . rawurlencode($GLOBALS['pageSummary'] ?? ''));
    include $pageFile;
    exit;
}

// Final Step: Full layout rendering
if ($isAdminReset) {
    include __DIR__ . '/../resources/views/layouts/db-reset.php';
} else {
    include __DIR__ . '/../resources/views/layouts/app.php';
}
