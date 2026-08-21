<?php
// /resources/views/components/ui/breadcrumbs.php

declare(strict_types=1);

/**
 * @var array $breadcrumbs An array of ['label' => 'url'] pairs. The last item is the current page.
 * @var string $baseUrl
 * @var array{label: string, url: string, title?: string, summary?: string}|null $breadcrumbHome
 *   Optional override for the leading crumb (defaults to Home). Lets pages like the
 *   tenant apply flow lead with "Dashboard" instead.
 */

if (!isset($breadcrumbs) || empty($breadcrumbs)) {
    return;
}

$homeCrumb = $breadcrumbHome ?? ['label' => 'Home', 'url' => $baseUrl, 'title' => 'Home', 'summary' => 'Centralized Landlord Infrastructure'];
$lastItemLabel = array_key_last($breadcrumbs);

/**
 * Every page in the app passes plain root-relative paths into $breadcrumbs
 * (e.g. '/inspections'), with no APP_BASE_PATH prefix -- fine when the app
 * is hosted at the domain root (dev), but wrong the moment it's deployed
 * under a subpath in production (e.g. '/home/'), where the link needs to be
 * '/home/inspections'. Resolving it here, once, fixes every page's
 * breadcrumbs at the source instead of requiring each page to remember to
 * prefix its own $breadcrumbs array.
 */
$resolveCrumbUrl = function (?string $url) use ($baseUrl): string {
    if ($url === null || $url === '') return $baseUrl;
    if (preg_match('#^https?://#i', $url)) return $url;
    return rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
};
?>

<nav class="flex items-center gap-2 mb-10 text-sm font-black uppercase tracking-wider text-slate-400 dark:text-slate-500" aria-label="Breadcrumb" data-aos="fade-down">
    <a href="<?= htmlspecialchars($homeCrumb['url']) ?>" data-partial data-title="<?= htmlspecialchars($homeCrumb['title'] ?? $homeCrumb['label']) ?>" data-summary="<?= htmlspecialchars($homeCrumb['summary'] ?? '') ?>" class="px-1.5 py-1 -mx-1.5 -my-1 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"><?= htmlspecialchars($homeCrumb['label']) ?></a>

    <?php foreach ($breadcrumbs as $label => $url) : ?>
        <i class="fa-solid fa-angle-right text-[10px] text-slate-300 dark:text-slate-700 stroke-[3]"></i>
        <?php if ($label === $lastItemLabel) : ?>
            <span class="text-slate-900 dark:text-white font-black" aria-current="page"><?= htmlspecialchars($label) ?></span>
        <?php else : ?>
            <a href="<?= htmlspecialchars($resolveCrumbUrl($url)) ?>" data-partial class="px-1.5 py-1 -mx-1.5 -my-1 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"><?= htmlspecialchars($label) ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>