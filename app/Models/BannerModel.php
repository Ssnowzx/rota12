<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\ModelBase;

class BannerModel extends ModelBase
{
    protected static string $table = 'banners';

    /**
     * Return all active banners for a given position that are within their schedule.
     */
    public static function activeByPosition(string $positionKey): array
    {
        return static::raw(
            'SELECT * FROM banners
             WHERE is_active = 1
               AND position = ?
             ORDER BY sort_order ASC',
            [$positionKey]
        );
    }
}
