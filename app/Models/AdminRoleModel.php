<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\{ModelBase, DB};

class AdminRoleModel extends ModelBase
{
    protected static string $table = 'admin_roles';

    /**
     * Get a role row plus all its permissions under key 'permissions'.
     */
    public static function withPermissions(int $roleId): ?array
    {
        $role = static::find($roleId);
        if ($role === null) {
            return null;
        }
        $role['permissions'] = static::getPermissions($roleId);
        return $role;
    }

    /**
     * Replace all permissions for a role (sync).
     * Deletes existing assignments, then inserts the provided permission IDs.
     */
    public static function syncPermissions(int $roleId, array $permissionIds): void
    {
        DB::transaction(function () use ($roleId, $permissionIds): void {
            DB::execute(
                'DELETE FROM admin_role_permissions WHERE role_id = ?',
                [$roleId]
            );

            foreach ($permissionIds as $permId) {
                DB::execute(
                    'INSERT INTO admin_role_permissions (role_id, permission_id) VALUES (?, ?)',
                    [$roleId, (int) $permId]
                );
            }
        });
    }

    /**
     * Get all permissions assigned to a role.
     */
    public static function getPermissions(int $roleId): array
    {
        return DB::select(
            'SELECT p.* FROM admin_permissions p
             JOIN admin_role_permissions arp ON arp.permission_id = p.id
             WHERE arp.role_id = ?',
            [$roleId]
        );
    }

    /**
     * Find a role by its unique role_key.
     */
    public static function findByKey(string $roleKey): ?array
    {
        return DB::selectOne(
            'SELECT * FROM admin_roles WHERE role_key = ? LIMIT 1',
            [$roleKey]
        );
    }
}
