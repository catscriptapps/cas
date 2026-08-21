<?php
// /src/Controller/SlideshowController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Traits\RecentActivityLogger;

/**
 * Thin controller solely to give the slideshow-* procedural API endpoints a
 * consistent activity logger, matching how the other procedural endpoints in
 * this app (e.g. cover-pages-*.php) borrow a controller's logActivity()
 * without needing a full resource controller. The slideshow endpoints don't
 * need list search/pagination like a CRUD resource, so there's no
 * index()/save() here -- see server/api/slideshow-*.php for the actual logic.
 */
class SlideshowController
{
    use RecentActivityLogger;
}
