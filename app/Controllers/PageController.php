<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{ControllerBase, Router};
use App\Models\PageModel;

class PageController extends ControllerBase
{
    /**
     * Show a published page by slug.
     *
     * @param array<string, string> $params Route params, expects 'slug'.
     */
    public function show(array $params): void
    {
        $page = PageModel::findBySlug($params['slug'] ?? '');

        if ($page === null) {
            Router::triggerError(404);
        }

        $this->render('pages/show', ['page' => $page], 'main');
    }
}
