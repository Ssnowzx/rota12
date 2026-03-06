<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\ModelBase;
use App\Core\DB;
use App\Models\UserModel;

class PartnerModel extends ModelBase
{
    protected static string $table = 'partners';

    /**
     * Find an active partner by its slug.
     */
    public static function findBySlug(string $slug): ?array
    {
        return static::rawOne(
            'SELECT * FROM partners WHERE slug = ? AND is_active = 1 LIMIT 1',
            [$slug]
        );
    }

    /**
     * Return all active partners ordered by name ASC.
     */
    public static function listActive(): array
    {
        return static::findAll(['is_active' => 1], 'name ASC');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARTNER APPROVAL SYSTEM METHODS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * List pending partner approval requests with pagination.
     *
     * @param int $page The page number (1-based)
     * @param int $perPage Results per page
     * @return array{data: array, total: int, pages: int, current: int}
     */
    public static function listPending(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $total = (int)(DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM partners WHERE status_aprovacao = ?',
            ['pendente_aprovacao']
        )['cnt'] ?? 0);

        $data = DB::select(
            'SELECT p.*, u.username, u.email
             FROM partners p
             LEFT JOIN users u ON u.id = p.user_id
             WHERE p.status_aprovacao = ?
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?',
            ['pendente_aprovacao', $perPage, $offset]
        );

        return [
            'data'    => $data,
            'total'   => $total,
            'pages'   => (int)ceil($total / $perPage),
            'current' => $page,
        ];
    }

    /**
     * Count pending approval requests.
     *
     * @return int Number of pending partners
     */
    public static function countPending(): int
    {
        $row = DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM partners WHERE status_aprovacao = ?',
            ['pendente_aprovacao']
        );
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Approve a partner by admin.
     *
     * @param int $partnerId The partner ID to approve
     * @param int $adminId The admin user ID performing the action
     * @param string $notes Optional notes about the approval
     * @return bool Success status
     */
    public static function approve(int $partnerId, int $adminId, string $notes = ''): bool
    {
        $partner = static::find($partnerId);
        if (!$partner) {
            return false;
        }

        // Update partner approval status
        DB::execute(
            'UPDATE partners
             SET status_aprovacao = ?, approved_by = ?, approved_at = NOW()
             WHERE id = ?',
            ['aprovado', $adminId, $partnerId]
        );

        // Activate user account
        UserModel::activate($partner['user_id']);

        // Log to audit trail
        AuditModel::log(
            $adminId,
            'approve_partner',
            'partners',
            $partnerId,
            [
                'partner_name' => $partner['name'] ?? 'Unknown',
                'notes'        => $notes,
            ]
        );

        return true;
    }

    /**
     * Reject a partner approval request.
     *
     * @param int $partnerId The partner ID to reject
     * @param int $adminId The admin user ID performing the action
     * @param string $reason The reason for rejection
     * @return bool Success status
     */
    public static function reject(int $partnerId, int $adminId, string $reason = ''): bool
    {
        $partner = static::find($partnerId);
        if (!$partner) {
            return false;
        }

        // Update partner rejection status
        DB::execute(
            'UPDATE partners
             SET status_aprovacao = ?, rejection_reason = ?, approved_by = ?, approved_at = NOW()
             WHERE id = ?',
            ['rejeitado', $reason, $adminId, $partnerId]
        );

        // Keep user account inactive
        UserModel::deactivate($partner['user_id']);

        // Log to audit trail
        AuditModel::log(
            $adminId,
            'reject_partner',
            'partners',
            $partnerId,
            [
                'partner_name' => $partner['name'] ?? 'Unknown',
                'reason'       => $reason,
            ]
        );

        return true;
    }

    /**
     * Check if a partner is pending approval.
     *
     * @param int $partnerId The partner ID
     * @return bool True if pending, false otherwise
     */
    public static function isPending(int $partnerId): bool
    {
        $partner = static::find($partnerId);
        return $partner && $partner['status_aprovacao'] === 'pendente_aprovacao';
    }

    /**
     * Check if a user can access the partner dashboard.
     *
     * Conditions:
     * - User role must be 'partner'
     * - Partner status must be 'aprovado'
     * - User account must be active (is_active=TRUE)
     *
     * @param int $userId The user ID
     * @return bool True if can access, false otherwise
     */
    public static function canAccessDashboard(int $userId): bool
    {
        $result = DB::selectOne(
            'SELECT p.status_aprovacao, u.role, u.is_active
             FROM partners p
             LEFT JOIN users u ON u.id = p.user_id
             WHERE p.user_id = ?
             LIMIT 1',
            [$userId]
        );

        if (!$result) {
            return false;
        }

        return $result['status_aprovacao'] === 'aprovado'
            && $result['role'] === 'parceiro'
            && $result['is_active'] === 1;
    }

    /**
     * Get partner with associated user information.
     *
     * @param int $partnerId The partner ID
     * @return ?array Partner with user info, or null if not found
     */
    public static function getWithUser(int $partnerId): ?array
    {
        return DB::selectOne(
            'SELECT p.*, u.username, u.email, u.is_active
             FROM partners p
             LEFT JOIN users u ON u.id = p.user_id
             WHERE p.id = ?
             LIMIT 1',
            [$partnerId]
        );
    }

    /**
     * Create a new partner record for a newly registered user.
     *
     * @param int $userId The user ID to link to the partner
     * @param string $username The username for the partner slug
     * @return void
     */
    public static function createFromUser(int $userId, string $username): void
    {
        $slug = slugify($username) . '-' . $userId;
        static::insert([
            'user_id'               => $userId,
            'name'                  => $username,
            'slug'                  => $slug,
            'status_aprovacao'      => 'pendente_aprovacao',
            'approval_requested_at' => now(),
            'is_active'             => 0,
            'created_at'            => now(),
        ]);
    }

    /**
     * Get partner approval status by user ID.
     *
     * @param int $userId The user ID to look up
     * @return ?string The status_aprovacao value, or null if not found
     */
    public static function getStatusByUserId(int $userId): ?string
    {
        $row = DB::selectOne(
            'SELECT status_aprovacao FROM partners WHERE user_id = ? LIMIT 1',
            [$userId]
        );
        return $row ? (string)$row['status_aprovacao'] : null;
    }
}
