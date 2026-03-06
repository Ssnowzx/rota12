<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{ControllerBase, Router};
use App\Models\{CityModel, EstablishmentModel, CouponModel};

class EstablishmentController extends ControllerBase
{
    /**
     * Show a single establishment page.
     *
     * Route must supply both 'city_slug' and 'slug' params.
     *
     * @param array<string, string> $params
     */
    public function show(array $params): void
    {
        // Resolve city first.
        $city = CityModel::findBySlug($params['city_slug'] ?? '');

        if ($city === null) {
            Router::triggerError(404);
        }

        // Resolve establishment within that city.
        $establishment = EstablishmentModel::findByCityAndSlug(
            (int) $city['id'],
            $params['slug'] ?? ''
        );

        if ($establishment === null) {
            Router::triggerError(404);
        }

        // Fetch valid coupons belonging to this establishment.
        $allValid = CouponModel::listValid();
        $coupons  = array_filter(
            $allValid,
            static fn(array $c): bool =>
                isset($c['establishment_id']) &&
                (int) $c['establishment_id'] === (int) $establishment['id']
        );
        $coupons = array_values($coupons);

        $this->render('establishments/show', [
            'city'          => $city,
            'establishment' => $establishment,
            'coupons'       => $coupons,
        ], 'main');
    }
}
