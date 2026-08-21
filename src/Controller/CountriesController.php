<?php
// /src/Controller/CountriesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Country;

class CountriesController
{
    /**
     * Return all countries for the dropdowns
     */
    public static function list()
    {
        // Maps to: id, country, country_code, currency, currency_symbol
        return Country::orderBy('country')->get();
    }
}
