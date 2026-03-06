<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\ModelBase;

class PageModel extends ModelBase
{
    protected static string $table = 'pages';

    /**
     * Find a published page by its slug.
     */
    public static function findBySlug(string $slug): ?array
    {
        return static::rawOne(
            'SELECT * FROM pages WHERE slug = ? AND status = 1 LIMIT 1',
            [$slug]
        );
    }

    /**
     * Return all published pages ordered by id ASC.
     */
    public static function listPublished(string $orderBy = 'id ASC'): array
    {
        return static::findAll(['status' => 1], $orderBy);
    }
}
