<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\ModelBase;

class CityModel extends ModelBase
{
    protected static string $table = 'cities';

    /**
     * Find an active city by its slug.
     */
    public static function findBySlug(string $slug): ?array
    {
        return static::rawOne(
            'SELECT * FROM cities WHERE slug = ? AND is_active = 1 LIMIT 1',
            [$slug]
        );
    }

    /**
     * Return all active cities ordered by sort_order ASC, then name ASC.
     */
    public static function listActive(): array
    {
        return static::findAll(["is_active" => 1], 'name ASC');
    }
}
