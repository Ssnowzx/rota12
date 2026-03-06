<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Auth;
use App\Core\ControllerBase;
use App\Models\PageModel;
use App\Models\BannerModel;
use App\Models\HighlightModel;
use App\Models\PartnerModel;
use App\Models\CityModel;
use App\Models\EstablishmentModel;
use App\Models\CampaignModel;
use App\Models\AuditModel;

class DashboardController extends ControllerBase
{
    /**
     * GET /administrator/dashboard
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm('core.admin.login');

        $stats = [
            'pages_count'          => PageModel::count(['status'    => 1]),
            'banners_count'        => BannerModel::count(['is_active' => 1]),
            'highlights_count'     => HighlightModel::count(['is_active' => 1]),
            'cities_count'         => CityModel::count(['is_active'   => 1]),
            'partners_count'       => PartnerModel::count(['is_active' => 1]),
            'establishments_count' => EstablishmentModel::count(['is_active' => 1]),
            'coupons_count'        => CampaignModel::countActive(),
        ];

        $recentAudit = AuditModel::recent(10);

        $this->renderAdmin('dashboard/index', compact('stats', 'recentAudit'));
    }
}
