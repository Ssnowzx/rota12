<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Access Control List — column names match actual DB schema:
 *   admin_user_roles     (admin_user_id, admin_role_id)
 *   admin_role_permissions (admin_role_id, admin_permission_id)
 *   admin_permissions    (id, `key`, name)
 *   admin_roles          (id, `key`, name)
 */
final class ACL
{
    private static array $permCache = [];
    private static array $roleCache = [];

    public static function can(string $permKey): bool
    {
        $userId = Auth::adminId();
        if ($userId === null) {
            return false;
        }
        // Super-admin bypasses all checks
        if ($permKey !== 'core.super' && self::isSuperAdmin($userId)) {
            return true;
        }
        $cacheKey = "{$userId}:{$permKey}";
        if (array_key_exists($cacheKey, self::$permCache)) {
            return self::$permCache[$cacheKey];
        }
        $row = DB::selectOne(
            'SELECT COUNT(*) AS cnt
               FROM admin_user_roles       aur
               JOIN admin_role_permissions arp ON arp.admin_role_id       = aur.admin_role_id
               JOIN admin_permissions      ap  ON ap.id                   = arp.admin_permission_id
              WHERE aur.admin_user_id = ? AND ap.`key` = ?',
            [$userId, $permKey]
        );
        $result = (int)($row['cnt'] ?? 0) > 0;
        return self::$permCache[$cacheKey] = $result;
    }

    public static function hasRole(string $roleKey): bool
    {
        $userId = Auth::adminId();
        if ($userId === null) {
            return false;
        }
        $cacheKey = "{$userId}:{$roleKey}";
        if (array_key_exists($cacheKey, self::$roleCache)) {
            return self::$roleCache[$cacheKey];
        }
        $row = DB::selectOne(
            'SELECT COUNT(*) AS cnt
               FROM admin_user_roles aur
               JOIN admin_roles      ar  ON ar.id = aur.admin_role_id
              WHERE aur.admin_user_id = ? AND ar.`key` = ?',
            [$userId, $roleKey]
        );
        $result = (int)($row['cnt'] ?? 0) > 0;
        return self::$roleCache[$cacheKey] = $result;
    }

    public static function userPermissions(int $userId): array
    {
        $rows = DB::select(
            'SELECT DISTINCT ap.`key`
               FROM admin_user_roles       aur
               JOIN admin_role_permissions arp ON arp.admin_role_id       = aur.admin_role_id
               JOIN admin_permissions      ap  ON ap.id                   = arp.admin_permission_id
              WHERE aur.admin_user_id = ?',
            [$userId]
        );
        return array_column($rows, 'key');
    }

    public static function clearCache(): void
    {
        self::$permCache = [];
        self::$roleCache = [];
    }

    private static function isSuperAdmin(int $userId): bool
    {
        $cacheKey = "{$userId}:core.super";
        if (array_key_exists($cacheKey, self::$permCache)) {
            return self::$permCache[$cacheKey];
        }
        $row = DB::selectOne(
            'SELECT COUNT(*) AS cnt
               FROM admin_user_roles       aur
               JOIN admin_role_permissions arp ON arp.admin_role_id       = aur.admin_role_id
               JOIN admin_permissions      ap  ON ap.id                   = arp.admin_permission_id
              WHERE aur.admin_user_id = ? AND ap.`key` = ?',
            [$userId, 'core.super']
        );
        $result = (int)($row['cnt'] ?? 0) > 0;
        return self::$permCache[$cacheKey] = $result;
    }
}
