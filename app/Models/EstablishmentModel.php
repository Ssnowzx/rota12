<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\{ModelBase, DB};

class EstablishmentModel extends ModelBase
{
    protected static string $table = 'establishments';

    /**
     * Find an active establishment by city ID and slug, joining city info.
     */
    public static function findByCityAndSlug(int $cityId, string $slug): ?array
    {
        return DB::selectOne(
            'SELECT e.*, c.name AS city_name, c.slug AS city_slug
             FROM establishments e
             JOIN cities c ON c.id = e.city_id
             WHERE e.city_id = ? AND e.slug = ? AND e.is_active = 1
             LIMIT 1',
            [$cityId, $slug]
        );
    }

    /**
     * Return all active establishments for a given city, ordered by name ASC then name ASC.
     */
    public static function listByCity(int $cityId): array
    {
        return static::findAll(['city_id' => $cityId, "is_active" => 1], 'name ASC');
    }

    /**
     * Return all active establishments with city name and state joined.
     */
    public static function listActiveWithCity(): array
    {
        return DB::select(
            'SELECT e.*, c.name AS city_name, c.state AS city_state
             FROM establishments e
             JOIN cities c ON c.id = e.city_id
             WHERE e.is_active = 1
             ORDER BY e.name ASC',
            []
        );
    }
}
