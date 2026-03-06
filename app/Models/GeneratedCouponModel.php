<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\{ModelBase, DB};

class GeneratedCouponModel extends ModelBase
{
    protected static string $table = 'generated_coupons';

    /**
     * Generate a unique coupon code for a client.
     * Returns the new record ID, or null if limits exceeded.
     */
    public static function generate(int $campaignId, int $userId, int $partnerId): ?array
    {
        $campaign = DB::selectOne(
            "SELECT * FROM coupon_campaigns WHERE id = ? AND status = 'ativa' AND deleted_at IS NULL LIMIT 1",
            [$campaignId]
        );

        if (!$campaign) {
            return null;
        }

        // Check total limit
        if ($campaign['limite_total'] !== null) {
            $totalGenerated = (int)(DB::selectOne(
                "SELECT COUNT(*) AS cnt FROM generated_coupons WHERE campaign_id = ?",
                [$campaignId]
            )['cnt'] ?? 0);

            if ($totalGenerated >= (int)$campaign['limite_total']) {
                return null;
            }
        }

        // Check per-user limit
        $userCount = (int)(DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM generated_coupons WHERE campaign_id = ? AND user_id = ?",
            [$campaignId, $userId]
        )['cnt'] ?? 0);

        if ($userCount >= (int)$campaign['max_uses_per_user']) {
            return null;
        }

        // Generate unique code
        $code = self::uniqueCode();

        $newId = DB::insert(
            "INSERT INTO generated_coupons (campaign_id, user_id, partner_id, codigo, status)
             VALUES (?, ?, ?, ?, 'disponivel')",
            [$campaignId, $userId, $partnerId, $code]
        );

        return [
            'id'     => $newId,
            'codigo' => $code,
        ];
    }

    /**
     * Generate a unique alphanumeric code (8 chars uppercase).
     */
    private static function uniqueCode(): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $exists = DB::selectOne(
                "SELECT id FROM generated_coupons WHERE codigo = ? LIMIT 1",
                [$code]
            );
        } while ($exists);

        return $code;
    }

    /**
     * Cancel all available coupons for a user (when subscription expires).
     */
    public static function cancelByUser(int $userId): int
    {
        return DB::execute(
            "UPDATE generated_coupons SET status = 'cancelado'
             WHERE user_id = ? AND status = 'disponivel'",
            [$userId]
        );
    }

    /**
     * Get recent coupons for a user (for "Minha Conta").
     */
    public static function recentByUser(int $userId, int $limit = 10): array
    {
        return DB::select(
            "SELECT g.codigo, g.status, g.gerado_em, c.titulo
             FROM generated_coupons g
             JOIN coupon_campaigns c ON c.id = g.campaign_id
             WHERE g.user_id = ?
             ORDER BY g.gerado_em DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}
