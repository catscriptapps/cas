<?php
// /server/models/Inspection.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use DateTime;

/**
 * Inspection Model
 * Table: inspections
 *
 * The header record for a single property inspection. Its lifecycle isn't
 * tracked with a status enum (status_id is only kept for schema parity) --
 * it's driven entirely by dates: date_expires is null until the report is
 * finalized, then the list page's row coloring keys off how close that date
 * is to expiring, exactly mirroring the legacy app's fx_backgd_for_due_date().
 */
class Inspection extends Model
{
    protected $table = 'inspections';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'company_id',
        'orig_user_id',
        'property_address',
        'city',
        'country_id',
        'region_id',
        'postal_code',
        'access_code',
        'initials',
        'inspection_summary',
        'status_id',
        'file_name',
        'date_posted',
        'date_expires',
        'date_created',
        'timestamp',
    ];

    protected $casts = [
        'id'            => 'integer',
        'company_id'    => 'integer',
        'orig_user_id'  => 'integer',
        'country_id'    => 'integer',
        'region_id'     => 'integer',
        'status_id'     => 'integer',
        'date_posted'   => 'datetime',
        'date_expires'  => 'datetime',
        'date_created'  => 'datetime',
        'timestamp'     => 'datetime',
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    // Row-color thresholds, ported 1:1 from the legacy app's fx_backgd_for_due_date().
    const COLOR_EXPIRED  = '#FFB6B6'; // date_expires is in the past
    const COLOR_IMMINENT = '#FF7272'; // 0-2 days left
    const COLOR_SOON     = '#FFF78E'; // 3-7 days left
    const COLOR_HEALTHY  = '#AEFFAE'; // 8+ days left
    const COLOR_NONE     = '#FFFFFF'; // no report generated yet

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'orig_user_id', 'id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(InspectionQuestion::class, 'inspection_id', 'id');
    }

    public function sectionComments(): HasMany
    {
        return $this->hasMany(InspectionSectionComment::class, 'inspection_id', 'id');
    }

    public function pictures(): HasMany
    {
        return $this->hasMany(InspectionPicture::class, 'inspection_id', 'id');
    }

    /**
     * Whether a report has ever been finalized for this inspection.
     */
    public function hasReport(): bool
    {
        return !empty($this->date_expires);
    }

    /**
     * Whether the finalized report's access window has lapsed.
     */
    public function isExpired(): bool
    {
        return $this->hasReport() && $this->date_expires->lt(new \DateTime());
    }

    /**
     * The inline background-color style for this inspection's list row,
     * matching legacy's fx_backgd_for_due_date() thresholds exactly:
     * expired -> imminent (0-2 days) -> soon (3-7 days) -> healthy (8+ days).
     * A blank date_expires (no report finalized yet) renders plain white.
     */
    public function rowColor(): string
    {
        return self::colorForExpiry($this->date_expires?->format('Y-m-d H:i:s'));
    }

    /**
     * Pure function version of the same rule, usable without a hydrated
     * model (e.g. from a raw query row) -- accepts a date string or null.
     */
    public static function colorForExpiry(?string $dateExpires): string
    {
        if (empty($dateExpires)) {
            return self::COLOR_NONE;
        }

        $given = new DateTime($dateExpires);
        $now = new DateTime();

        if ($given < $now) {
            return self::COLOR_EXPIRED;
        }

        $days = (int)ceil(($given->getTimestamp() - $now->getTimestamp()) / 86400);

        if ($days >= 0 && $days <= 2) {
            return self::COLOR_IMMINENT;
        }

        if ($days > 2 && $days < 8) {
            return self::COLOR_SOON;
        }

        return self::COLOR_HEALTHY;
    }

    /**
     * Human-readable label for the same state, used as a small badge next to
     * the Expiry column so the color isn't the only signal (accessibility).
     */
    public static function labelForExpiry(?string $dateExpires): string
    {
        if (empty($dateExpires)) {
            return 'No Report Yet';
        }

        $color = self::colorForExpiry($dateExpires);

        return match ($color) {
            self::COLOR_EXPIRED  => 'Expired',
            self::COLOR_IMMINENT => 'Expiring Imminently',
            self::COLOR_SOON     => 'Expiring Soon',
            default               => 'Healthy',
        };
    }
}
