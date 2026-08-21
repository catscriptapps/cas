<?php
// /server/models/Region.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modernized Region Model
 * Table: regions
 */
class Region extends Model
{
    protected $table = 'regions';

    // Standardized to 'id'
    protected $primaryKey = 'id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'region',
        'country_id'
    ];

    // Table doesn't have timestamps in the new SQL
    public $timestamps = false;

    /**
     * Relationship to the Country
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Canada/US postal abbreviations, keyed by the region table's full name --
     * used anywhere space is tight (e.g. the inspection detail page's compact
     * sticky strip) and "Barrie, ON" reads better than "Barrie, Ontario". A
     * handful of the seeded US entries (uninhabited minor outlying islands)
     * have no widely-recognized 2-letter code and are deliberately omitted;
     * getAbbreviationAttribute() falls back to the full name for those.
     */
    private const ABBREVIATIONS = [
        // Canada
        'Alberta' => 'AB',
        'British Columbia' => 'BC',
        'Manitoba' => 'MB',
        'New Brunswick' => 'NB',
        'Newfoundland and Labrador' => 'NL',
        'Northwest Territories' => 'NT',
        'Nova Scotia' => 'NS',
        'Nunavut' => 'NU',
        'Ontario' => 'ON',
        'Prince Edward Island' => 'PE',
        'Quebec' => 'QC',
        'Saskatchewan' => 'SK',
        'Yukon' => 'YT',
        // United States (+ DC and inhabited territories)
        'Alabama' => 'AL',
        'Alaska' => 'AK',
        'American Samoa' => 'AS',
        'Arizona' => 'AZ',
        'Arkansas' => 'AR',
        'California' => 'CA',
        'Colorado' => 'CO',
        'Connecticut' => 'CT',
        'Delaware' => 'DE',
        'District of Columbia' => 'DC',
        'Florida' => 'FL',
        'Georgia' => 'GA',
        'Guam' => 'GU',
        'Hawaii' => 'HI',
        'Idaho' => 'ID',
        'Illinois' => 'IL',
        'Indiana' => 'IN',
        'Iowa' => 'IA',
        'Kansas' => 'KS',
        'Kentucky' => 'KY',
        'Louisiana' => 'LA',
        'Maine' => 'ME',
        'Maryland' => 'MD',
        'Massachusetts' => 'MA',
        'Michigan' => 'MI',
        'Minnesota' => 'MN',
        'Mississippi' => 'MS',
        'Missouri' => 'MO',
        'Montana' => 'MT',
        'Nebraska' => 'NE',
        'Nevada' => 'NV',
        'New Hampshire' => 'NH',
        'New Jersey' => 'NJ',
        'New Mexico' => 'NM',
        'New York' => 'NY',
        'North Carolina' => 'NC',
        'North Dakota' => 'ND',
        'Northern Mariana Islands' => 'MP',
        'Ohio' => 'OH',
        'Oklahoma' => 'OK',
        'Oregon' => 'OR',
        'Pennsylvania' => 'PA',
        'Puerto Rico' => 'PR',
        'Rhode Island' => 'RI',
        'South Carolina' => 'SC',
        'South Dakota' => 'SD',
        'Tennessee' => 'TN',
        'Texas' => 'TX',
        'Utah' => 'UT',
        'Vermont' => 'VT',
        'Virginia' => 'VA',
        'Washington' => 'WA',
        'West Virginia' => 'WV',
        'Wisconsin' => 'WI',
        'Wyoming' => 'WY',
        'United States Virgin Islands' => 'VI',
    ];

    public function getAbbreviationAttribute(): string
    {
        return self::ABBREVIATIONS[$this->region] ?? (string)$this->region;
    }
}
