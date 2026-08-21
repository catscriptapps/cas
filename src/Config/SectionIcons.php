<?php
// /src/Config/SectionIcons.php

declare(strict_types=1);

namespace Src\Config;

/**
 * A small curated icon set a Company Admin can pick from when creating a
 * question-bank Section, so each tab gets a recognizable icon without
 * needing a logo-style file upload for every single tab.
 */
class SectionIcons
{
    /**
     * One tinted "personality" per icon key, applied to a section tab while
     * it's NOT the active one -- shared by every tab strip that renders a
     * Section (Questions' own tabs, an inspection's fill-in tabs, and the
     * guest read-only report view, which reuses the inspection markup
     * verbatim). The selected tab always turns solid primary blue instead
     * (see ACTIVE_TAB_CLASSES), matching every other tab strip in the app,
     * so which section is active never depends on this map.
     *
     * Every class name here must appear literally (no string-building) so
     * Tailwind's content scanner picks it up -- see tailwind.config.js.
     */
    private const TAB_COLORS = [
        'roof'       => 'bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-900/50 hover:border-amber-400 hover:text-amber-800 dark:hover:text-amber-300',
        'electrical' => 'bg-yellow-50 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-900/50 hover:border-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-300',
        'plumbing'   => 'bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-900/50 hover:border-blue-400 hover:text-blue-800 dark:hover:text-blue-300',
        'hvac'       => 'bg-cyan-50 dark:bg-cyan-950/30 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-900/50 hover:border-cyan-400 hover:text-cyan-800 dark:hover:text-cyan-300',
        'exterior'   => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/50 hover:border-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300',
        'safety'     => 'bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-900/50 hover:border-rose-400 hover:text-rose-800 dark:hover:text-rose-300',
        'structure'  => 'bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-900/50 hover:border-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300',
        'documents'  => 'bg-violet-50 dark:bg-violet-950/30 text-violet-700 dark:text-violet-400 border-violet-200 dark:border-violet-900/50 hover:border-violet-400 hover:text-violet-800 dark:hover:text-violet-300',
        'general'    => 'bg-secondary-50 dark:bg-secondary-950/30 text-secondary-700 dark:text-secondary-400 border-secondary-200 dark:border-secondary-900/50 hover:border-secondary-400 hover:text-secondary-800 dark:hover:text-secondary-300',
    ];

    private const DEFAULT_TAB_COLOR = 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-800 hover:border-primary-300 hover:text-primary-600';

    public const ACTIVE_TAB_CLASSES = 'bg-primary-600 text-white border-primary-600 shadow-md shadow-primary-500/20';

    /**
     * The resting-state class string for a section tab with this icon key.
     */
    public static function tabColor(?string $key): string
    {
        return self::TAB_COLORS[$key ?? ''] ?? self::DEFAULT_TAB_COLOR;
    }

    /**
     * @return array<string, array{label: string, svg: string}>
     */
    public static function all(): array
    {
        return [
            'roof' => [
                'label' => 'Roof',
                'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"></path>',
            ],
            'electrical' => [
                'label' => 'Electrical',
                'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"></path>',
            ],
            'plumbing' => [
                'label' => 'Plumbing / Mechanical',
                'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a7.723 7.723 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z"></path>',
            ],
            'hvac' => [
                'label' => 'HVAC',
                'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"></path>',
            ],
            'exterior' => [
                'label' => 'Exterior / Grounds',
                'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>',
            ],
            'safety' => [
                'label' => 'Safety',
                'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.746 3.746 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"></path>',
            ],
            'structure' => [
                'label' => 'Structure / Foundation',
                'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h9a.75.75 0 01.75.75V21H4.5V3.75A.75.75 0 014.5 3zM13.5 21V9.75a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75V21M8.25 6.75h.008v.008H8.25V6.75zm0 3.75h.008v.008H8.25V10.5zm0 3.75h.008v.008H8.25v-.008zm3-7.5h.008v.008h-.008V6.75zm0 3.75h.008v.008h-.008V10.5zm0 3.75h.008v.008h-.008v-.008z"></path>',
            ],
            'documents' => [
                'label' => 'Documents / Storage',
                'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>',
            ],
            'general' => [
                'label' => 'General',
                'svg' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"></path>',
            ],
        ];
    }

    /**
     * Returns the full <svg>...</svg> markup for a given icon key, falling
     * back to the 'general' icon for an unknown/missing key.
     */
    public static function svg(?string $key, string $class = 'w-5 h-5'): string
    {
        $icons = self::all();
        $inner = $icons[$key ?? '']['svg'] ?? $icons['general']['svg'];

        return '<svg class="' . htmlspecialchars($class) . '" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">' . $inner . '</svg>';
    }
}
