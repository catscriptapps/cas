<?php
// /src/Controller/SectionDiagramsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Traits\RecentActivityLogger;

/**
 * Thin controller solely to give the section-diagrams-* procedural API
 * endpoints a consistent activity logger, matching CoverPagesController.
 * The actual logic (company+section-scoped list/upload/delete/reorder)
 * lives in server/api/section-diagrams-*.php.
 */
class SectionDiagramsController
{
    use RecentActivityLogger;
}
