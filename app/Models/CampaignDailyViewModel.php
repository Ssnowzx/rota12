<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\ModelBase;

class CampaignDailyViewModel extends ModelBase
{
    protected static string $table = 'campaign_daily_views';

    /**
     * Get daily views for a partner over the last N days.
     *
     * @return array<int, array{view_date: string, total_views: int}>
     */
    public static function last7Days(int $partnerId): array
    {
        return static::raw(
            'SELECT view_date, SUM(views_count) AS total_views
             FROM campaign_daily_views
             WHERE partner_id = ? AND view_date >= CURDATE() - INTERVAL 6 DAY
             GROUP BY view_date ORDER BY view_date ASC',
            [$partnerId]
        );
    }
}
