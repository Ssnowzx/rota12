<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\ModelBase;

class HighlightModel extends ModelBase
{
    protected static string $table = 'highlights';

    /**
     * Return all active highlights ordered by ordering ASC.
     */
    public static function listActive(): array
    {
        return static::findAll(['is_active' => 1], 'sort_order ASC');
    }
}
