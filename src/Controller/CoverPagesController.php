<?php
// /src/Controller/CoverPagesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Traits\RecentActivityLogger;

/**
 * Thin controller solely to give the cover-pages-* procedural API endpoints
 * a consistent activity logger, matching SlideshowController. The actual
 * logic (company-scoped list/upload/delete/reorder/location) lives in
 * server/api/cover-pages-*.php.
 */
class CoverPagesController
{
    use RecentActivityLogger;
}
