<?php
// /src/Controller/LocationsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Location;

class LocationsController
{
    public static function listAll(): array
    {
        return Location::orderBy('location_desc', 'asc')->get(['location_id', 'location_desc'])->toArray();
    }
}
