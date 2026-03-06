<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\ControllerBase;
use App\Models\CampaignModel;

class CouponController extends ControllerBase
{
    public function index(): void
    {
        $campaigns = CampaignModel::listActive();
        $this->render('coupons/index', ['campaigns' => $campaigns], 'main');
    }
}
