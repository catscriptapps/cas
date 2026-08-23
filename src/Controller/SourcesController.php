<?php
// /src/Controller/SourcesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Source;

class SourcesController
{
    /**
     * Return active "how did you hear about us" options for the registration form.
     */
    public static function list()
    {
        return Source::where('status_id', Source::STATUS_ACTIVE)
            ->orderBy('display_order')
            ->get();
    }
}
