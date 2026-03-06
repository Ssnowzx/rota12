<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ControllerBase;
use App\Core\Router;
use App\Core\Validator;
use App\Models\PageModel;
use App\Models\AuditModel;

class PageController extends ControllerBase
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm('pages.view');
        $pageNum = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pager   = PageModel::paginate($pageNum, $perPage);
        $this->renderAdmin('pages/index', [
            'records' => $pager['data'],
            'total'   => $pager['total'],
            'pages'   => $pager['pages'],
            'page'    => $pager['current'],
            'perPage' => $perPage,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requirePerm('pages.create');
        $this->renderAdmin('pages/create');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePerm('pages.create');
        CSRF::check();

        $data = [
            'title'            => trim($_POST['title'] ?? ''),
            'slug'             => trim($_POST['slug'] ?? ''),
            'content_html'     => $_POST['content_html'] ?? '',
            'meta_title'       => trim($_POST['meta_title'] ?? '') ?: null,
            'meta_description' => trim($_POST['meta_description'] ?? '') ?: null,
            'status'           => (int)($_POST['status'] ?? 1),
        ];

        $v = (new Validator($data))
            ->required('title')->max('title', 190)
            ->required('slug')->max('slug', 190)->unique('slug', 'pages', 'slug')
            ->required('content_html');

        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/pages/create');
        }

        $data['slug']         = slugify($data['slug']);
        $data['content_html'] = sanitizeHtml($data['content_html']);

        $newId = PageModel::insert($data);
        AuditModel::log(Auth::adminId(), 'create', 'pages', $newId,
            ['title' => $data['title'], 'slug' => $data['slug']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Página criada com sucesso.');
        $this->redirect('/administrator/pages');
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('pages.edit');
        $id       = (int)($params['id'] ?? 0);
        $pageData = PageModel::find($id);
        if (!$pageData) { Router::triggerError(404); }
        $this->renderAdmin('pages/edit', ['record' => $pageData]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('pages.edit');
        CSRF::check();
        $id       = (int)($params['id'] ?? 0);
        $pageData = PageModel::find($id);
        if (!$pageData) { Router::triggerError(404); }

        $data = [
            'title'            => trim($_POST['title'] ?? ''),
            'slug'             => trim($_POST['slug'] ?? ''),
            'content_html'     => $_POST['content_html'] ?? '',
            'meta_title'       => trim($_POST['meta_title'] ?? '') ?: null,
            'meta_description' => trim($_POST['meta_description'] ?? '') ?: null,
            'status'           => (int)($_POST['status'] ?? 1),
        ];

        $v = (new Validator($data))
            ->required('title')->max('title', 190)
            ->required('slug')->max('slug', 190)->unique('slug', 'pages', 'slug', $id);

        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/pages/' . $id . '/edit');
        }

        $data['slug']         = slugify($data['slug']);
        $data['content_html'] = sanitizeHtml($data['content_html']);

        PageModel::update($id, $data);
        AuditModel::log(Auth::adminId(), 'update', 'pages', $id,
            ['title' => $data['title']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Página atualizada com sucesso.');
        $this->redirect('/administrator/pages');
    }

    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('pages.delete');
        CSRF::check();
        $id = (int)($params['id'] ?? 0);
        $pageData = PageModel::find($id);
        if (!$pageData) { Router::triggerError(404); }
        PageModel::delete($id);
        AuditModel::log(Auth::adminId(), 'delete', 'pages', $id,
            ['title' => $pageData['title']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Página excluída com sucesso.');
        $this->redirect('/administrator/pages');
    }
}
