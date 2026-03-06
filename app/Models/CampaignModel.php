<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\{ModelBase, DB};

class CampaignModel extends ModelBase
{
    protected static string $table = 'coupon_campaigns';

    /**
     * List active campaigns for the public site.
     * Only shows campaigns where status=ativa, not deleted, within date range.
     */
    public static function listActive(): array
    {
        return DB::select(
            "SELECT cc.*,
                    u.username AS partner_username,
                    u.username AS partner_nome
             FROM coupon_campaigns cc
             JOIN users u ON u.id = cc.user_id
             WHERE cc.status = 'ativa'
               AND cc.deleted_at IS NULL
               AND (cc.data_inicio IS NULL OR cc.data_inicio <= CURDATE())
               AND (cc.data_fim IS NULL OR cc.data_fim >= CURDATE())
             ORDER BY cc.created_at DESC",
            []
        );
    }

    /**
     * List active campaigns with generated coupon stats (for admin).
     */
    public static function listForAdmin(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $total = (int)(DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM coupon_campaigns WHERE deleted_at IS NULL",
            []
        )['cnt'] ?? 0);

        $data = DB::select(
            "SELECT cc.*,
                    u.username AS partner_username,
                    u.email AS partner_email,
                    (SELECT COUNT(*) FROM generated_coupons g WHERE g.campaign_id = cc.id) AS total_gerados,
                    (SELECT COUNT(*) FROM generated_coupons g WHERE g.campaign_id = cc.id AND g.status = 'utilizado') AS total_resgatados
             FROM coupon_campaigns cc
             LEFT JOIN users u ON u.id = cc.user_id
             WHERE cc.deleted_at IS NULL
             ORDER BY cc.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        return [
            'data'    => $data,
            'total'   => $total,
            'pages'   => (int)ceil($total / $perPage),
            'current' => $page,
        ];
    }

    /**
     * Count active campaigns (not deleted, status=ativa).
     */
    public static function countActive(): int
    {
        $row = DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM coupon_campaigns
             WHERE status = 'ativa' AND deleted_at IS NULL",
            []
        );
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Find a campaign with its partner info.
     */
    public static function findWithPartner(int $id): ?array
    {
        return DB::selectOne(
            "SELECT cc.*,
                    u.username AS partner_username,
                    u.email AS partner_email,
                    u.username AS partner_nome
             FROM coupon_campaigns cc
             LEFT JOIN users u ON u.id = cc.user_id
             WHERE cc.id = ? AND cc.deleted_at IS NULL
             LIMIT 1",
            [$id]
        );
    }

    /**
     * Pause all active campaigns for a given user (when subscription expires).
     */
    public static function pauseAllByUser(int $userId): int
    {
        return DB::execute(
            "UPDATE coupon_campaigns SET status = 'pausada'
             WHERE user_id = ? AND status = 'ativa' AND deleted_at IS NULL",
            [$userId]
        );
    }

    /**
     * Reactivate paused campaigns for a given user (when subscription renews).
     */
    public static function reactivateByUser(int $userId): int
    {
        return DB::execute(
            "UPDATE coupon_campaigns SET status = 'ativa'
             WHERE user_id = ? AND status = 'pausada' AND deleted_at IS NULL",
            [$userId]
        );
    }
}
