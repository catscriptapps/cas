<?php
// /src/Config/NavigationConfig.php

declare(strict_types=1);

namespace Src\Config;

use Src\Service\AuthService;
use App\Models\UserType;

/**
 * NavigationConfig handles all static data related to the application's
 * primary navigation structure, including link URLs and associated icons.
 */
class NavigationConfig
{
    /**
     * Resolve {title, summary} for a page path by matching it against
     * whichever role-specific link set applies to the signed-in account (or
     * the public link set if nobody's signed in). This is the single source
     * of truth both the full-page layout header (hard load) and the
     * X-Page-Title/X-Page-Summary response headers (SPA partial load) draw
     * from, so a page's summary doesn't go missing depending on how you
     * navigated to it.
     *
     * @return array{title: string, summary: string}|null
     */
    public static function resolveMetaForPath(string $path): ?array
    {
        $baseUrl = rtrim($_ENV['APP_BASE_PATH'] ?? '', '/');
        $normalizedPath = rtrim($path, '/') ?: '/';

        $navLinks = AuthService::isLoggedIn() ? self::getNavLinks(true) : self::publicLinks();

        foreach ($navLinks as $config) {
            $normalizedLinkUrl = rtrim($config['url'] ?? '', '/') ?: '/';
            if ($normalizedPath === $normalizedLinkUrl) {
                return ['title' => $config['title'] ?? '', 'summary' => $config['summary'] ?? ''];
            }
        }

        return null;
    }

    /**
     * Defines the icon mapping for each navigation link name.
     * @return array<string, string>
     */
    public static function getIcons(): array
    {
        return [
            'Dashboard' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z"/></svg>',
            'Slideshow' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h.01M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" /></svg>',
            'Profile'   => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
            'Users'     => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75a3 3 0 11-6 0 3 3 0 016 0zM6.75 6.75a3 3 0 116 0 a3 3 0 01-6 0zM3 21a6 6 0 0112 0M9 21a6 6 0 0112 0"></path></svg>',
            'Companies' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h9a.75.75 0 01.75.75V21H4.5V3.75A.75.75 0 014.5 3zM13.5 21V9.75a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75V21M8.25 6.75h.008v.008H8.25V6.75zm0 3.75h.008v.008H8.25V10.5zm0 3.75h.008v.008H8.25v-.008zm3-7.5h.008v.008h-.008V6.75zm0 3.75h.008v.008h-.008V10.5zm0 3.75h.008v.008h-.008v-.008z"></path></svg>',
            'Inspections' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            'Questions' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"></path></svg>',
            'Cover Pages' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path></svg>',
            'Standards' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"></path></svg>',
        ];
    }

    /**
     * Returns the navigation links for the current user.
     */
    public static function getNavLinks(bool $isLoggedIn): array
    {
        return $isLoggedIn ? self::authLinks() : self::publicLinks();
    }

    /**
     * Returns all auth-only links.
     *
     * Each signed-in role gets its own ordered tab set (see adminNavLinks()/
     * companyAdminNavLinks() below) rather than one universal list filtered
     * down -- Admin and Company Admin share some destinations (Users,
     * Companies, Profile) but want them in a different relative order, which
     * a single filtered array can't express.
     */
    public static function authLinks(bool $showAll = false): array
    {
        if ($showAll) {
            // Superset across every role -- used only by public/index.php's
            // URL -> app-name reverse lookup for the permission gate, so
            // order doesn't matter here, just full coverage.
            return self::adminNavLinks() + self::companyAdminNavLinks();
        }

        $user = AuthService::currentUser();
        if (!$user) {
            return [];
        }

        if (AuthService::isAdmin()) {
            return self::filterByAccess(self::adminNavLinks());
        }

        if ($user->isType(UserType::COMPANY_ADMIN)) {
            return self::filterByAccess(self::companyAdminNavLinks());
        }

        if ($user->isType(UserType::INSPECTOR)) {
            return self::filterByAccess(self::inspectorNavLinks());
        }

        return [];
    }

    /**
     * Filters an ordered app list down to what AuthService::hasAccess()
     * currently allows, preserving the given order.
     */
    private static function filterByAccess(array $apps): array
    {
        $visible = [];
        foreach ($apps as $name => $config) {
            if (AuthService::hasAccess($name)) {
                $visible[$name] = $config;
            }
        }
        return $visible;
    }

    /**
     * The platform Admin's nav -- unchanged from before Company Admin existed.
     */
    private static function adminNavLinks(): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            'Dashboard' => [
                'url' => $base . '/dashboard',
                'title' => 'Operational Dashboard',
                'summary' => 'Real-time performance index metrics, pending verification alerts, and systemic portfolio analysis logs.'
            ],
            'Slideshow' => [
                'url' => $base . '/slideshow',
                'title' => 'Slideshow Management',
                'summary' => 'Manage the rotating background images shown in the site header.'
            ],
            'Profile' => [
                'url' => $base . '/profile',
                'title' => 'Profile Settings',
                'summary' => 'Manage account access keys, administrative security structures, and regional subscription targets.'
            ],
            'Users' => [
                'url' => $base . '/users',
                'title' => 'User Directory Management',
                'summary' => 'Control internal account scopes, assign operational roles, and check localized system access activity logs.'
            ],
            'Companies' => [
                'url' => $base . '/companies',
                'title' => 'Company Directory Management',
                'summary' => 'Onboard and manage the inspection companies subscribed to the platform.'
            ],
        ];
    }

    /**
     * A Company Admin's nav. "Users" and "Companies" point at the same URLs
     * as the Admin's, but the controllers scope those lists down to the
     * signed-in Company Admin's own company. There's no "Dashboard" tab here
     * on purpose -- Inspections is effectively their home base -- but
     * /dashboard still needs to resolve for them since that's where a fresh
     * login lands regardless of role.
     */
    private static function companyAdminNavLinks(): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            'Inspections' => [
                'url' => $base . '/inspections',
                'title' => 'Inspections',
                'summary' => 'Create and manage inspection reports for your company.'
            ],
            'Questions' => [
                'url' => $base . '/questions',
                'title' => 'Questions',
                'summary' => 'Manage the question bank used to build inspection report sections.'
            ],
            'Companies' => [
                'url' => $base . '/companies',
                'title' => 'Your Company',
                'summary' => 'View and manage your company profile and its admins.'
            ],
            'Cover Pages' => [
                'url' => $base . '/cover-pages',
                'title' => 'Cover Pages',
                'summary' => 'Manage the cover page templates used on finished reports.'
            ],
            'Standards' => [
                'url' => $base . '/standards',
                'title' => 'Standards',
                'summary' => 'Manage the inspection standards your reports reference.'
            ],
            'Users' => [
                'url' => $base . '/users',
                'title' => 'Your Team',
                'summary' => 'View and manage the users belonging to your company.'
            ],
            'Profile' => [
                'url' => $base . '/profile',
                'title' => 'Profile Settings',
                'summary' => 'Manage your account access keys and personal details.'
            ],
        ];
    }

    /**
     * An Inspector's nav -- Inspections is their entire world (they only see
     * the inspections they personally created; see
     * InspectionsController::index()'s ownership scoping), plus Profile.
     */
    private static function inspectorNavLinks(): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            'Inspections' => [
                'url' => $base . '/inspections',
                'title' => 'Inspections',
                'summary' => 'Create and manage your inspection reports.'
            ],
            'Profile' => [
                'url' => $base . '/profile',
                'title' => 'Profile Settings',
                'summary' => 'Manage your account access keys and personal details.'
            ],
        ];
    }

    /**
     * Returns all public links.
     */
    private static function publicLinks(): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            'Home' => [
                'url' => $base . '/home',
                'title' => 'Home',
                'summary' => ''
            ],
            'About' => [
                'url' => $base . '/about',
                'title' => 'About Us',
                'summary' => 'Our story, our process, and what sets our home inspection reports apart.',
                'children' => [
                    'About Us' => ['url' => $base . '/about', 'title' => 'About Us', 'summary' => 'Our story, our process, and what sets our home inspection reports apart.'],
                    'FAQs'     => ['url' => $base . '/faqs', 'title' => 'Frequently Asked Questions', 'summary' => 'Answers to common questions about the platform, the inspection report, and accessing your report.'],
                ]
            ],
            'Contact' => [
                'url' => $base . '/contact',
                'title' => 'Contact Us',
                'summary' => 'Reach out about an existing report or bringing your inspection company onto the platform -- we typically reply within one business day.'
            ],
        ];
    }

    /**
     * Returns the protected paths for route guarding.
     */
    public static function getProtectedPaths(): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            $base . '/dashboard',
            $base . '/profile',
            $base . '/users',
            $base . '/companies',
            $base . '/slideshow',
            $base . '/questions',
            $base . '/inspections',
            $base . '/cover-pages',
            $base . '/standards',
        ];
    }

    /**
     * Gets display information for the currently authenticated entity (User).
     * @return array{displayName: string, initial: string}
     */
    public static function getUserDisplayInfo(): array
    {
        $displayName = 'Account';
        $initial = 'G'; // Guest initial

        if (AuthService::isLoggedIn()) {
            $user = AuthService::currentUser();

            if ($user) {
                $parts = explode(' ', $user->full_name);
                $displayName = (count($parts) > 1)
                    ? strtoupper(substr($parts[0], 0, 1)) . '. ' . end($parts)
                    : ($parts[0] ?? 'User');
                $initial = !empty($user->full_name) ? strtoupper(substr($user->full_name, 0, 1)) : 'U';
            }
        }
        return compact('displayName', 'initial');
    }
}
