<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\{ModelBase, DB};

class AuditModel extends ModelBase
{
    protected static string $table = 'admin_audit_log';

    /**
     * Log an admin action to the audit log.
     *
     * @param array<string, mixed> $detail Will be JSON-encoded into detail_json; NULL if empty.
     */
    public static function log(
        ?int    $adminUserId,
        string  $action,
        ?string $entity,
        ?int    $entityId,
        array   $detail    = [],
        string  $ip        = '',
        string  $userAgent = ''
    ): void {
        $detailJson = !empty($detail) ? json_encode($detail, JSON_UNESCAPED_UNICODE) : null;

        DB::execute(
            'INSERT INTO admin_audit_log
                 (admin_user_id, action, entity, entity_id, detail_json, ip, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $adminUserId,
                $action,
                $entity,
                $entityId,
                $detailJson,
                $ip !== '' ? $ip : null,
                $userAgent !== '' ? $userAgent : null,
            ]
        );
    }

    /**
     * Return the N most recent audit log entries.
     */
    public static function recent(int $limit = 10): array
    {
        return DB::select(
            'SELECT al.*, au.name AS admin_name
             FROM admin_audit_log al
             LEFT JOIN admin_users au ON au.id = al.admin_user_id
             ORDER BY al.created_at DESC
             LIMIT ?',
            [$limit]
        );
    }

    /**
     * Paginated audit log with admin user name joined.
     *
     * @return array{data: list<array<string,mixed>>, total: int, pages: int, current: int}
     */
    public static function listPaginated(int $page = 1, int $perPage = 30): array
    {
        $page    = max(1, $page);
        $perPage = max(1, $perPage);
        $offset  = ($page - 1) * $perPage;

        $totalRow = DB::selectOne('SELECT COUNT(*) AS cnt FROM admin_audit_log', []);
        $total    = (int) ($totalRow['cnt'] ?? 0);
        $pages    = (int) ceil($total / $perPage);

        $data = DB::select(
            'SELECT al.*, au.name AS admin_name
             FROM admin_audit_log al
             LEFT JOIN admin_users au ON au.id = al.admin_user_id
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?',
            [$perPage, $offset]
        );

        return [
            'data'    => $data,
            'total'   => $total,
            'pages'   => $pages,
            'current' => $page,
        ];
    }
}
