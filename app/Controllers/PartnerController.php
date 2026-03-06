<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{ControllerBase, Router};
use App\Models\PartnerModel;

class PartnerController extends ControllerBase
{
    /**
     * List all active partners.
     */
    public function index(): void
    {
        $partners = PartnerModel::listActive();

        $this->render('partners/index', ['partners' => $partners], 'main');
    }

    /**
     * Show a single partner by slug.
     *
     * @param array<string, string> $params Route params, expects 'slug'.
     */
    public function show(array $params): void
    {
        $partner = PartnerModel::findBySlug($params['slug'] ?? '');

        if ($partner === null) {
            Router::triggerError(404);
        }

        $this->render('partners/show', ['partner' => $partner], 'main');
    }
}
