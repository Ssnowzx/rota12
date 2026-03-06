<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\ModelBase;

class AdminPermissionModel extends ModelBase
{
    protected static string $table = 'admin_permissions';

    /**
     * Return all permissions grouped by the prefix before the first dot.
     *
     * Example: perm_key 'pages.create' goes into group 'pages'.
     * Keys with no dot go into group '_other'.
     *
     * @return array<string, list<array<string,mixed>>>
     */
    public static function grouped(): array
    {
        $all    = static::findAll([], 'perm_key ASC');
        $groups = [];

        foreach ($all as $perm) {
            $dotPos = strpos((string) $perm['perm_key'], '.');
            $prefix = $dotPos !== false
                ? substr((string) $perm['perm_key'], 0, $dotPos)
                : '_other';

            $groups[$prefix][] = $perm;
        }

        return $groups;
    }
}
