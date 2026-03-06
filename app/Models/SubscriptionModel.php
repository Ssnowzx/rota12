<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\{ModelBase, DB};

class SubscriptionModel extends ModelBase
{
    protected static string $table = 'subscriptions';

    /**
     * Get the current subscription for a user (most recent).
     */
    public static function current(int $userId): ?array
    {
        return DB::selectOne(
            "SELECT * FROM subscriptions WHERE user_id = ? ORDER BY id DESC LIMIT 1",
            [$userId]
        );
    }

    /**
     * Check if user has an active (paid) subscription.
     */
    public static function isActive(int $userId): bool
    {
        $sub = self::current($userId);
        if (!$sub) {
            return false;
        }
        return $sub['status'] === 'active'
            && ($sub['end_date'] === null || strtotime($sub['end_date']) >= time());
    }

    /**
     * Cancel a subscription by user action.
     */
    public static function cancel(int $userId, string $reason = ''): bool
    {
        $sub = self::current($userId);
        if (!$sub || $sub['status'] !== 'active') {
            return false;
        }

        DB::execute(
            "UPDATE subscriptions SET status = 'canceled', updated_at = NOW() WHERE id = ?",
            [$sub['id']]
        );

        // Pause partner campaigns if partner
        CampaignModel::pauseAllByUser($userId);

        // Cancel available generated coupons
        GeneratedCouponModel::cancelByUser($userId);

        return true;
    }

    /**
     * Expire all subscriptions past their end_date.
     * Returns the number of expired subscriptions.
     */
    public static function expireOverdue(): int
    {
        // Find subscriptions that should expire
        $expired = DB::select(
            "SELECT id, user_id FROM subscriptions
             WHERE status = 'active'
               AND end_date IS NOT NULL
               AND end_date < NOW()",
            []
        );

        if (empty($expired)) {
            return 0;
        }

        // Update status to expired
        DB::execute(
            "UPDATE subscriptions SET status = 'expired', updated_at = NOW()
             WHERE status = 'active' AND end_date IS NOT NULL AND end_date < NOW()",
            []
        );

        // Pause campaigns and cancel coupons for each expired user
        foreach ($expired as $sub) {
            CampaignModel::pauseAllByUser((int)$sub['user_id']);
            GeneratedCouponModel::cancelByUser((int)$sub['user_id']);
        }

        return count($expired);
    }

    /**
     * Count active subscriptions.
     */
    public static function countActive(): int
    {
        $row = DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM subscriptions WHERE status = 'active'",
            []
        );
        return (int)($row['cnt'] ?? 0);
    }
}
