<?php
// /src/Controller/HomePageTextController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\HomePageText;
use App\Traits\RecentActivityLogger;
use HTMLPurifier;
use HTMLPurifier_Config;
use Src\Service\AuthService;

/**
 * The single "Our Mission" admin-editable HTML block shown on the home
 * page. Ported from legacy essahockey_live's essa_home_page_text feature
 * (entry_id 1 of what's actually a 4-row, hardcoded-by-ID table there --
 * only this one row was in scope here). Unlike legacy, which echoes the
 * saved HTML completely raw with zero sanitization anywhere in the save or
 * render pipeline, this purifies on save: it's admin-only, but the output
 * still lands on the public home page, so a compromised admin session
 * shouldn't be able to plant a stored-XSS payload there.
 */
class HomePageTextController
{
    use RecentActivityLogger;

    /**
     * Renders on the public home page, so a DB error here (most notably: the
     * home_page_text table not existing yet on an environment that hasn't
     * had the corresponding reset/migration run) must never surface as an
     * uncaught exception -- that would fatal mid-render and white-screen the
     * entire home page, since this is called near the top of home.php
     * before most of the page has been output. Falls back to the same copy
     * the reset script seeds so the page still reads correctly either way.
     */
    public function getOurMissionHtml(): string
    {
        $fallback = "<p>Canadian All Star Sports is dedicated to providing a safe and enjoyable environment "
            . "for kids and adults of all ages and skill levels to participate in, and enjoy the game of hockey.</p>"
            . "<p>We believe hockey should be affordable and accessible to everyone. We strive to contribute to "
            . "each individual's personal growth and skill development by promoting self-confidence, team work, "
            . "fair play, and sportsmanship. We do it for the love of the sport!</p>";

        try {
            $row = HomePageText::find(HomePageText::OUR_MISSION);
            return $row->text_content ?? $fallback;
        } catch (\Throwable) {
            return $fallback;
        }
    }

    public function save(array $data): array
    {
        try {
            if (!AuthService::isAdmin()) {
                throw new \Exception("You don't have permission to do that.");
            }

            $rawHtml = (string)($data['text_content'] ?? '');
            $cleanHtml = $this->purify($rawHtml);

            $row = HomePageText::find(HomePageText::OUR_MISSION);
            $oldHtml = $row->text_content ?? '';
            if (!$row) {
                $row = new HomePageText();
                $row->entry_id = HomePageText::OUR_MISSION;
            }
            $row->text_content = $cleanHtml;
            $row->timestamp = date('Y-m-d H:i:s');
            $row->save();

            $this->deleteOrphanedImages($oldHtml, $cleanHtml);

            static::logActivity('Updated the home page "Our Mission" content block', 'Home Page');

            return [
                'success' => true,
                'html' => $cleanHtml,
                'timestamp' => $row->timestamp->format('M j, Y g:i A'),
                'messages' => ['Home page content updated.'],
            ];
        } catch (\Throwable $e) {
            static::logActivity('Home page text save error: ' . $e->getMessage(), 'Home Page');

            // A missing/un-deployed HTMLPurifier vendor package throws a raw
            // "Class ... not found" error -- accurate, but meaningless to an
            // admin clicking Save. Surface something actionable instead.
            $message = str_contains($e->getMessage(), 'HTMLPurifier')
                ? 'This server is missing a required library (HTMLPurifier). Run "composer install" on this environment, then try saving again.'
                : $e->getMessage();

            return ['success' => false, 'messages' => [$message]];
        }
    }

    /**
     * Allow-lists a plain-text-formatting subset (matches what the Quill
     * "snow" toolbar this feature ships can actually produce) -- strips
     * scripts, event handlers, iframes, forms, etc. regardless of what a
     * WYSIWYG paste or a hand-crafted request body contains. `img` is
     * allowed since pasted/uploaded screenshots land here already uploaded
     * to our own server (see home-page-image-upload.php) with the resulting
     * <img src> pointing back at our own /images/uploads/home-page/ path --
     * this never needs to accept arbitrary remote image URLs.
     *
     * Every inline text tag also needs `[style]`, not just span/p: when a
     * color/size/background format and bold/italic/etc. both apply to the
     * same run, Quill sometimes collapses them onto ONE element (e.g.
     * `<strong style="color:...">`) instead of nesting a separate `<span>`
     * inside the `<strong>`. Without `[style]` on `strong` (and friends),
     * HTMLPurifier silently stripped the whole style attribute whenever
     * Quill happened to emit that combined form -- bold text kept its bold,
     * but any color/size/background applied alongside it vanished on save.
     */
    private function purify(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p[style],br,strong[style],b[style],em[style],i[style],u[style],s[style],ul,ol,li,a[href|target|rel|style],h1[style],h2[style],h3[style],h4[style],blockquote[style],span[style],img[src|alt|width|height]');
        $config->set('CSS.AllowedProperties', ['color', 'background-color', 'text-align', 'font-style', 'font-weight', 'font-size', 'text-decoration']);
        $config->set('AutoFormat.RemoveEmpty', true);

        // No definition cache: HTMLPurifier normally serializes its parsed
        // HTML/CSS definition to disk (keyed by the config above) and reuses
        // it on later requests. That's a real perf win for high-traffic
        // purifying, but this only runs on an admin's Save click, and a
        // stale definition surviving an allow-list change (exactly what
        // happened here: font-size/img were added, but a previously-cached
        // definition kept getting reused) silently reverts *all* CSS/HTML
        // filtering to the old rules, not just the new property. Rebuilding
        // the definition fresh on every call costs microseconds and removes
        // this whole class of environment-specific staleness for good.
        $config->set('Cache.DefinitionImpl', null);

        return (new HTMLPurifier($config))->purify($html);
    }

    /**
     * Diffs the old vs. new saved HTML for <img> tags pointing at our own
     * upload folder and deletes any that no longer appear in the new
     * content -- otherwise every image an admin pastes in and later removes
     * (or replaces) sits on disk forever with nothing left referencing it.
     */
    private function deleteOrphanedImages(string $oldHtml, string $newHtml): void
    {
        $extractUrls = function (string $html): array {
            preg_match_all('/<img[^>]+src=["\']([^"\']*images\/uploads\/home-page\/[^"\']+)["\']/i', $html, $matches);
            return $matches[1] ?? [];
        };

        $removedUrls = array_diff($extractUrls($oldHtml), $extractUrls($newHtml));
        if (empty($removedUrls)) {
            return;
        }

        $uploadDir = realpath(__DIR__ . '/../../public/images/uploads/home-page');
        if (!$uploadDir) {
            return;
        }

        foreach ($removedUrls as $url) {
            $filename = basename((string)parse_url($url, PHP_URL_PATH));
            $path = realpath($uploadDir . '/' . $filename);

            if ($path && str_starts_with($path, $uploadDir) && file_exists($path)) {
                @unlink($path);
            }
        }
    }
}
