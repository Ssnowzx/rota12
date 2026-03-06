<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\ControllerBase;
use App\Models\{BannerModel, HighlightModel, CityModel, CampaignModel};

class HomeController extends ControllerBase
{
    public function index(): void
    {
        $banners    = BannerModel::activeByPosition('home');
        $highlights = HighlightModel::listActive();
        $cities     = CityModel::listActive();
        $topCoupons = array_slice(CampaignModel::listActive(), 0, 10);

        $this->render('home/index', [
            'banners'    => $banners,
            'highlights' => $highlights,
            'cities'     => $cities,
            'topCoupons' => $topCoupons,
        ], 'main');
    }
}
