<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\{ModelBase, DB};

class CouponModel extends ModelBase
{
    protected static string $table = 'coupons';

    public static function listValid(): array
    {
        return DB::select(
            'SELECT c.*,
                    e.name AS establishment_name
             FROM coupons c
             LEFT JOIN establishments e ON e.id = c.establishment_id
             WHERE c.is_active = 1
               AND (c.valid_from  IS NULL OR c.valid_from  <= CURDATE())
               AND (c.valid_until IS NULL OR c.valid_until >= CURDATE())
             ORDER BY c.id ASC',
            []
        );
    }

    public static function findByCode(string $code): ?array
    {
        return DB::selectOne(
            'SELECT * FROM coupons WHERE code = ? AND is_active = 1 LIMIT 1',
            [$code]
        );
    }
}
