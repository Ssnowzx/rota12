<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{ControllerBase, Router};
use App\Models\{CityModel, EstablishmentModel};

class CityController extends ControllerBase
{
    /**
     * List all active cities.
     */
    public function index(): void
    {
        $cities = CityModel::listActive();

        $this->render('cities/index', ['cities' => $cities], 'main');
    }

    /**
     * Show a city and its establishments.
     *
     * @param array<string, string> $params Route params, expects 'slug'.
     */
    public function show(array $params): void
    {
        $city = CityModel::findBySlug($params['slug'] ?? '');

        if ($city === null) {
            Router::triggerError(404);
        }

        $establishments = EstablishmentModel::listByCity((int) $city['id']);

        $this->render('cities/show', [
            'city'           => $city,
            'establishments' => $establishments,
        ], 'main');
    }
}
