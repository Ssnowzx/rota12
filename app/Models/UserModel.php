<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\ModelBase;
use App\Core\DB;

class UserModel extends ModelBase
{
    protected static string $table = 'users';

    /** Available avatar presets (name => image url). */
    public const AVATARS = [
        'default'     => '',
        'moto_male'   => '/assets/images/avatars/moto_male.png',
        'moto_female' => '/assets/images/avatars/moto_female.png',
    ];

    /**
     * Find a user by email address.
     */
    public static function findByEmail(string $email): ?array
    {
        return static::rawOne(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
    }

    /**
     * Find a user by username.
     */
    public static function findByUsername(string $username): ?array
    {
        return static::rawOne(
            'SELECT * FROM users WHERE username = ? LIMIT 1',
            [$username]
        );
    }

    /**
     * Check if email or username already exists.
     */
    public static function existsByEmailOrUsername(string $email, string $username): ?array
    {
        return static::rawOne(
            'SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1',
            [$email, $username]
        );
    }

    /**
     * Create a new user with hashed password.
     *
     * @return int The new user ID.
     */
    public static function createUser(
        string $username,
        string $email,
        string $password,
        string $role = 'member',
        bool   $isActive = true
    ): int {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        return static::insert([
            'username'      => $username,
            'email'         => $email,
            'password_hash' => $hash,
            'role'          => $role,
            'is_active'     => $isActive ? 1 : 0,
        ]);
    }

    /**
     * Verify password against stored hash.
     */
    public static function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, (string)($user['password_hash'] ?? ''));
    }

    /**
     * Get user profile (safe fields, no password_hash).
     */
    public static function getProfile(int $userId): ?array
    {
        return static::rawOne(
            'SELECT id, username, email, role, avatar, is_active, created_at, updated_at FROM users WHERE id = ? LIMIT 1',
            [$userId]
        );
    }

    /**
     * Update user avatar preset.
     */
    public static function updateAvatar(int $userId, string $avatar): int
    {
        if (!isset(self::AVATARS[$avatar])) {
            $avatar = 'default';
        }
        return DB::execute('UPDATE users SET avatar = ? WHERE id = ?', [$avatar, $userId]);
    }

    /**
     * Get image URL for an avatar preset.
     */
    public static function avatarUrl(string $avatar): string
    {
        return self::AVATARS[$avatar] ?? self::AVATARS['default'];
    }

    /**
     * Activate a user account.
     */
    public static function activate(int $userId): int
    {
        return DB::execute('UPDATE users SET is_active = 1 WHERE id = ?', [$userId]);
    }

    /**
     * Deactivate a user account.
     */
    public static function deactivate(int $userId): int
    {
        return DB::execute('UPDATE users SET is_active = 0 WHERE id = ?', [$userId]);
    }

    /**
     * Update user password with bcrypt hashing.
     *
     * @param int $userId The user ID
     * @param string $newPassword Plain-text password to hash
     * @return int Number of rows affected
     */
    public static function updatePassword(int $userId, string $newPassword): int
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        return DB::execute('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $userId]);
    }

    /**
     * Update user profile (username and/or email).
     *
     * @param int $userId The user ID
     * @param array $data Key-value pairs to update (only username, email allowed)
     * @return int Number of rows affected
     */
    public static function updateProfile(int $userId, array $data): int
    {
        $allowed = ['username', 'email'];
        $payload = array_intersect_key($data, array_flip($allowed));
        return empty($payload) ? 0 : static::update($userId, $payload);
    }
}
