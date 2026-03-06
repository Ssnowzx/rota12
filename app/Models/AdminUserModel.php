<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\{ModelBase, DB};

class AdminUserModel extends ModelBase
{
    protected static string $table = 'admin_users';

    /**
     * Find a user by email address.
     */
    public static function findByEmail(string $email): ?array
    {
        return DB::selectOne(
            'SELECT * FROM admin_users WHERE email = ? LIMIT 1',
            [$email]
        );
    }

    /**
     * Get a user row plus an array of their assigned roles under key 'roles'.
     */
    public static function withRoles(int $userId): ?array
    {
        $user = static::find($userId);
        if ($user === null) {
            return null;
        }
        $user['roles'] = static::getRoles($userId);
        return $user;
    }

    /**
     * Assign a role to a user (idempotent).
     */
    public static function assignRole(int $userId, int $roleId): void
    {
        DB::execute(
            'INSERT INTO admin_user_roles (admin_user_id, admin_role_id) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE admin_user_id = admin_user_id',
            [$userId, $roleId]
        );
    }

    /**
     * Remove a role from a user.
     */
    public static function removeRole(int $userId, int $roleId): void
    {
        DB::execute(
            'DELETE FROM admin_user_roles WHERE admin_user_id = ? AND admin_role_id = ?',
            [$userId, $roleId]
        );
    }

    /**
     * Get all roles assigned to a user.
     */
    public static function getRoles(int $userId): array
    {
        return DB::select(
            'SELECT r.* FROM admin_roles r
             JOIN admin_user_roles aur ON aur.admin_role_id = r.id
             WHERE aur.admin_user_id = ?',
            [$userId]
        );
    }

    /**
     * Update last_login_at to the current timestamp.
     */
    public static function touchLogin(int $userId): void
    {
        DB::execute(
            'UPDATE admin_users SET last_login_at = NOW() WHERE id = ?',
            [$userId]
        );
    }

    /**
     * Paginated user list with role data.
     *
     * @return array{data: list<array<string,mixed>>, total: int, pages: int, current: int}
     */
    public static function listWithRoles(int $page = 1, int $perPage = 20): array
    {
        $page    = max(1, $page);
        $perPage = max(1, $perPage);
        $offset  = ($page - 1) * $perPage;

        $totalRow = DB::selectOne('SELECT COUNT(*) AS cnt FROM admin_users', []);
        $total    = (int) ($totalRow['cnt'] ?? 0);
        $pages    = (int) ceil($total / $perPage);

        $data = DB::select(
            'SELECT u.*
             FROM admin_users u
             ORDER BY u.id ASC
             LIMIT ? OFFSET ?',
            [$perPage, $offset]
        );

        // Hydrate each user with their roles
        foreach ($data as &$user) {
            $user['roles'] = static::getRoles((int)$user['id']);
        }

        return [
            'data'    => $data,
            'total'   => $total,
            'pages'   => $pages,
            'current' => $page,
        ];
    }
}
