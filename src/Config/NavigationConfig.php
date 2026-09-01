<?php
// /src/Config/NavigationConfig.php

declare(strict_types=1);

namespace Src\Config;

use Src\Service\AuthService;

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
        $normalizedPath = rtrim($path, '/') ?: '/';
        $isLoggedIn = AuthService::isLoggedIn();

        $primary = $isLoggedIn ? self::getNavLinks(true) : self::publicLinks();
        $found = self::findMetaIn($primary, $normalizedPath);
        if ($found) {
            return $found;
        }

        // A logged-in viewer can still land on a guest-only informational
        // page (e.g. Locations, Required Equipment -- only in publicLinks(),
        // never added to the staff nav) since nothing stops an admin from
        // visiting a guest URL directly. The page's content doesn't change
        // based on who's viewing it, so its title/summary shouldn't come up
        // empty just because the lookup only checked the staff-nav set.
        if ($isLoggedIn) {
            $found = self::findMetaIn(self::publicLinks(), $normalizedPath);
            if ($found) {
                return $found;
            }
        }

        // Pages reached only via a CTA button, never a nav link (e.g.
        // Register -- it has its own dedicated topbar button, so it can't
        // also live in publicLinks()/authLinks() without duplicating itself
        // as a plain text link in the nav). The shared hero still needs a
        // real title+summary for these, so they get a small side list of
        // their own instead.
        $pageOnly = [
            '/register' => [
                'title' => 'Register Now.',
                'summary' => 'Pick your sport, league, and division, tell us about yourself, and complete payment -- all in one place.',
            ],
            // About/Gamesheets used to live in publicLinks() too, but the
            // guest nav must match legacy's exact item list (Home, Schedules,
            // Stats+Standings, League Details, Sponsorship, Contact) -- these
            // two are still real, reachable pages (About via the footer,
            // Gamesheets via Schedules/Stats, same as legacy), they just
            // don't get their own top-level nav link. They still need a
            // title/summary for the shared hero, hence kept here instead.
            '/about' => [
                'title' => 'About Us',
                'summary' => 'Our story and what Canadian All Star Sports is building for local leagues.',
            ],
            '/gamesheets' => [
                'title' => 'Gamesheets',
                'summary' => 'Per-game player stat sheets by division and season.',
            ],
            '/my-account' => [
                'title' => 'Dashboard',
                'summary' => 'Your registration status, team, schedule, and stats, all in one place.',
            ],
        ];

        return $pageOnly[$normalizedPath] ?? null;
    }

    /**
     * @return array{title: string, summary: string}|null
     */
    private static function findMetaIn(array $navLinks, string $normalizedPath): ?array
    {
        foreach ($navLinks as $config) {
            $normalizedLinkUrl = rtrim($config['url'] ?? '', '/') ?: '/';
            if ($normalizedPath === $normalizedLinkUrl) {
                return ['title' => $config['title'] ?? '', 'summary' => $config['summary'] ?? ''];
            }

            // Dropdown entries (see 'League Details') carry their real pages
            // one level down in 'children' -- a hard load or a popstate/
            // programmatic loadPartial() (no clicked <a data-title> to read
            // from) needs those to resolve a title/summary too, not just the
            // top-level parent link.
            foreach ($config['children'] ?? [] as $childConfig) {
                $normalizedChildUrl = rtrim($childConfig['url'] ?? '', '/') ?: '/';
                if ($normalizedPath === $normalizedChildUrl) {
                    return ['title' => $childConfig['title'] ?? '', 'summary' => $childConfig['summary'] ?? ''];
                }
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
            'Profile'   => '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
            'Users'     => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75a3 3 0 11-6 0 3 3 0 016 0zM6.75 6.75a3 3 0 116 0 a3 3 0 01-6 0zM3 21a6 6 0 0112 0M9 21a6 6 0 0112 0"></path></svg>',
            'Registrations' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
            'League Management' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 3.75c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"></path></svg>',
            'Contacts' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
            'Schedules' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008z"></path></svg>',
            'Stats+Standings' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v-6.75m6.75 6.75V6.75m-3.75 10.5V13.5M3.75 21h16.5a1.5 1.5 0 001.5-1.5V4.5A1.5 1.5 0 0020.25 3H3.75A1.5 1.5 0 002.25 4.5v15A1.5 1.5 0 003.75 21z"></path></svg>',
            'Gamesheets' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"></path></svg>',
            'Incident Reports' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            'League Details' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
            'Sponsorship' => '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11.5V14m0-2.5a2.5 2.5 0 010-5H12v5H7zm5-5h4.5a2.5 2.5 0 010 5H12m0-5v5m0 0v6.5m0 0h3.5a2 2 0 002-2v-.5m-5.5 2.5H8.5a2 2 0 01-2-2v-.5"></path></svg>',
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
     * Returns the signed-in backend nav. There's currently only one backend
     * role (Admin), so this is a single flat list -- restore the old
     * per-role branching here if CAS grows a second role later.
     */
    public static function authLinks(bool $showAll = false): array
    {
        if (!$showAll && !AuthService::currentUser()) {
            return [];
        }

        $base = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            'Dashboard' => [
                'url' => $base . '/dashboard',
                'title' => 'Operational Dashboard',
                'summary' => 'Recent activity across the league, and quick links into every workspace module.'
            ],
            'Schedules' => [
                'url' => $base . '/schedules',
                'title' => 'Schedules',
                'summary' => 'Manage season divisions, team rosters, and game schedules.'
            ],
            'Stats+Standings' => [
                'url' => $base . '/stats',
                'title' => 'Stats+Standings',
                'summary' => 'Team standings and player season stats by division.'
            ],
            'Gamesheets' => [
                'url' => $base . '/gamesheets',
                'title' => 'Gamesheets',
                'summary' => 'Per-game player stat sheets by division and season.'
            ],
            'Incident Reports' => [
                'url' => $base . '/incident-reports',
                'title' => 'Incident Reports',
                'summary' => 'Official documentation of on-floor incidents, disciplinary actions, and medical assistance.'
            ],
            'Registrations' => [
                'url' => $base . '/registrations',
                'title' => 'Registrations',
                'summary' => 'Review submitted registrations and manage payment status.'
            ],
            'League Management' => [
                'url' => $base . '/league-management',
                'title' => 'League Management',
                'summary' => 'Manage the sports, leagues, and divisions registrants can sign up for.'
            ],
            'Contacts' => [
                'url' => $base . '/contacts',
                'title' => 'Contact Directory',
                'summary' => 'League officials, timekeepers, and emergency contacts.'
            ],
            'Users' => [
                'url' => $base . '/users',
                'title' => 'User Directory Management',
                'summary' => 'Manage staff accounts and their access to the backend.'
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
            'Schedules' => [
                'url' => $base . '/schedules',
                'title' => 'Schedules',
                'summary' => 'Browse season divisions, teams, and upcoming games.'
            ],
            'Stats+Standings' => [
                'url' => $base . '/stats',
                'title' => 'Stats+Standings',
                'summary' => 'Team standings and player season stats by division.'
            ],
            'League Details' => [
                'url' => $base . '/league-details',
                'title' => 'League Details',
                'summary' => 'Choose Ball Hockey or Ice Hockey to see leagues, divisions, and pricing.',
                'children' => [
                    'Locations' => [
                        'url' => $base . '/locations',
                        'title' => 'Locations',
                        'summary' => 'Rinks and venues for every ball hockey and ice hockey division.'
                    ],
                    'Suspension Matrix' => [
                        'url' => $base . '/suspension-matrix',
                        'title' => 'Suspension Matrix',
                        'summary' => 'Discipline and suspension reference for on-ice and on-floor incidents.'
                    ],
                    'Ball Hockey Rulebook' => [
                        // /public/documents (not /public/pdfs -- that folder is
                        // purged on every DB reset as transient generated-PDF
                        // output; these are permanent static assets).
                        'url' => $base . '/documents/ball_hockey_rulebook.pdf',
                        'title' => 'Ball Hockey Rulebook',
                        'summary' => '',
                        'target' => '_blank',
                    ],
                    'Ice Hockey Rulebook' => [
                        'url' => $base . '/documents/ice_hockey_rulebook.pdf',
                        'title' => 'Ice Hockey Rulebook',
                        'summary' => '',
                        'target' => '_blank',
                    ],
                    'Required Equipment' => [
                        'url' => $base . '/equipment-required',
                        'title' => 'Required Equipment',
                        'summary' => 'Mandatory and recommended gear for kids and adult ball hockey and ice hockey.'
                    ],
                    'FAQ' => [
                        'url' => $base . '/home#faqs',
                        'title' => 'Home',
                        'summary' => ''
                    ],
                ],
            ],
            'Sponsorship' => [
                'url' => $base . '/sponsorship',
                'title' => 'Sponsorship',
                'summary' => 'Become a sponsor and get your business in front of every player, family, and fan in the league.'
            ],
            'Contact' => [
                'url' => $base . '/contact',
                'title' => 'Contact Us',
                'summary' => 'Questions about a league, a schedule, or getting involved -- we typically reply within one business day.'
            ],
        ];
    }

    /**
     * Returns the protected paths for route guarding. `/schedules` is
     * deliberately excluded -- matching legacy cas-sports, guests can browse
     * season schedules and rosters without an account; only mutating them
     * (adding a game/team/player) is gated, at the UI and API level.
     */
    public static function getProtectedPaths(): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            $base . '/dashboard',
            $base . '/profile',
            $base . '/users',
            $base . '/registrations',
            $base . '/league-management',
            $base . '/contacts',
            $base . '/incident-reports',
        ];
    }

    /**
     * Paths gated to a signed-in registrant rather than staff -- a
     * completely separate guard from getProtectedPaths()/isLoggedIn() (see
     * AuthService::isRegistrant()), since a registrant session must never
     * satisfy the staff route guard or vice versa.
     */
    public static function getRegistrantProtectedPaths(): array
    {
        $base = $_ENV['APP_BASE_PATH'] ?? '';

        return [
            $base . '/my-account',
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
        } elseif (AuthService::isRegistrant()) {
            $registration = AuthService::currentRegistrations()->first();
            $fullName = $registration->full_name ?? AuthService::currentRegistrantEmail() ?? 'Registrant';

            $parts = explode(' ', $fullName);
            $displayName = (count($parts) > 1)
                ? strtoupper(substr($parts[0], 0, 1)) . '. ' . end($parts)
                : ($parts[0] ?? 'Registrant');
            $initial = strtoupper(substr($fullName, 0, 1));
        }
        return compact('displayName', 'initial');
    }
}
