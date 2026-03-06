<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\ControllerBase;
use App\Models\AuditModel;

class AuditController extends ControllerBase
{
    /**
     * GET /administrator/audit
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm('core.users.manage');

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;

        $result = AuditModel::listPaginated($page, $perPage);
        $logs   = $result['data'] ?? [];
        $total  = $result['total'] ?? 0;
        $pages  = $result['pages'] ?? 1;

        $this->renderAdmin('audit/index', compact('logs', 'total', 'pages', 'page'));
    }
}
