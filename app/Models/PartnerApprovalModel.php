<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\{ModelBase, DB};

/**
 * Partner Approval Model
 *
 * Encapsulates queries related to partner approval workflow:
 * - Tracking approval requests
 * - Historical approval/rejection records
 * - Statistics and reporting
 */
class PartnerApprovalModel extends ModelBase
{
    protected static string $table = 'partners';

    // ─────────────────────────────────────────────────────────────────────────
    // APPROVAL WORKFLOW QUERIES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get recent approved/rejected partners (approval history).
     *
     * @param int $limit Number of records to retrieve
     * @return array List of partners with approval details
     */
    public static function recent(int $limit = 10): array
    {
        return DB::select(
            'SELECT p.*, u.username, u.email
             FROM partners p
             LEFT JOIN users u ON u.id = p.user_id
             WHERE p.status_aprovacao IN (?, ?)
             ORDER BY p.approved_at DESC
             LIMIT ?',
            ['aprovado', 'rejeitado', $limit]
        );
    }

    /**
     * Record a new approval request (called when partner registers).
     *
     * Sets approval_requested_at timestamp for new approval requests.
     *
     * @param int $userId The user ID of the partner
     * @return bool Success status
     */
    public static function request(int $userId): bool
    {
        $result = DB::execute(
            'UPDATE partners
             SET approval_requested_at = NOW()
             WHERE user_id = ? AND approval_requested_at IS NULL',
            [$userId]
        );

        return $result > 0;
    }

    /**
     * Get approval history for a specific partner.
     *
     * @param int $userId The user ID of the partner
     * @return ?array Partner approval record, or null if not found
     */
    public static function getByUserId(int $userId): ?array
    {
        return DB::selectOne(
            'SELECT p.*, u.username, u.email
             FROM partners p
             LEFT JOIN users u ON u.id = p.user_id
             WHERE p.user_id = ?
             LIMIT 1',
            [$userId]
        );
    }

    /**
     * Get approval statistics for dashboard.
     *
     * Returns counts of partners by approval status.
     *
     * @return array{
     *     total: int,
     *     pending: int,
     *     approved: int,
     *     rejected: int,
     *     pending_days_avg: float
     * }
     */
    public static function getStats(): array
    {
        $stats = DB::selectOne(
            'SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status_aprovacao = ? THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status_aprovacao = ? THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status_aprovacao = ? THEN 1 ELSE 0 END) as rejected,
                ROUND(AVG(CASE
                    WHEN status_aprovacao = ? THEN DATEDIFF(NOW(), approval_requested_at)
                    ELSE NULL
                END), 2) as pending_days_avg
             FROM partners',
            ['pendente_aprovacao', 'aprovado', 'rejeitado', 'pendente_aprovacao']
        );

        return [
            'total'            => (int)($stats['total'] ?? 0),
            'pending'          => (int)($stats['pending'] ?? 0),
            'approved'         => (int)($stats['approved'] ?? 0),
            'rejected'         => (int)($stats['rejected'] ?? 0),
            'pending_days_avg' => (float)($stats['pending_days_avg'] ?? 0),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UTILITY QUERIES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Get approval request details with timestamps.
     *
     * Used for admin dashboard to show approval timeline.
     *
     * @param int $partnerId The partner ID
     * @return ?array Approval details with request/approval/rejection timestamps
     */
    public static function getApprovalDetails(int $partnerId): ?array
    {
        return DB::selectOne(
            'SELECT
                p.id,
                p.user_id,
                p.status_aprovacao,
                p.approval_requested_at,
                p.approved_at,
                p.approved_by,
                p.rejection_reason,
                u.username as approver_name,
                CASE
                    WHEN p.status_aprovacao = ? AND p.approval_requested_at IS NOT NULL
                        THEN DATEDIFF(NOW(), p.approval_requested_at)
                    ELSE NULL
                END as pending_days
             FROM partners p
             LEFT JOIN users u ON u.id = p.approved_by
             WHERE p.id = ?
             LIMIT 1',
            ['pendente_aprovacao', $partnerId]
        );
    }

    /**
     * Get pending partners that have been waiting longer than X days.
     *
     * Useful for admin to identify overdue approvals.
     *
     * @param int $daysSinceRequest Minimum days to filter by
     * @return array List of overdue pending partners
     */
    public static function getPendingOverdue(int $daysSinceRequest = 7): array
    {
        return DB::select(
            'SELECT p.*, u.username, u.email,
                    DATEDIFF(NOW(), p.approval_requested_at) as pending_days
             FROM partners p
             LEFT JOIN users u ON u.id = p.user_id
             WHERE p.status_aprovacao = ?
               AND p.approval_requested_at IS NOT NULL
               AND DATEDIFF(NOW(), p.approval_requested_at) >= ?
             ORDER BY p.approval_requested_at ASC',
            ['pendente_aprovacao', $daysSinceRequest]
        );
    }
}
