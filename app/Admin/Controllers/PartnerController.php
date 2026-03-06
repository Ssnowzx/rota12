<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ControllerBase;
use App\Core\Router;
use App\Core\UploadHandler;
use App\Core\Validator;
use App\Models\PartnerModel;
use App\Models\AuditModel;

class PartnerController extends ControllerBase
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm('partners.view');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pager   = PartnerModel::paginate($page, $perPage);
        $this->renderAdmin('partners/index', [
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
        $this->requirePerm('partners.create');
        $this->renderAdmin('partners/create');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePerm('partners.create');
        CSRF::check();

        $data = [
            'name'      => trim($_POST['name'] ?? ''),
            'slug'      => trim($_POST['slug'] ?? ''),
            'is_active' => (int)($_POST['is_active'] ?? 1),
        ];

        $v = (new Validator($data))->required('name')->max('name', 190);
        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/partners/create');
        }

        $slug = $data['slug'] !== '' ? slugify($data['slug']) : slugify($data['name']);

        $logoPath = null;
        if (!empty($_FILES['logo_path']['tmp_name'])) {
            $logoPath = UploadHandler::uploadImage($_FILES['logo_path'], 'partners');
        }

        $newId = PartnerModel::insert([
            'name'        => $data['name'],
            'slug'        => $slug,
            'logo_path'   => $logoPath,
            'website_url' => trim($_POST['website_url'] ?? '') ?: null,
            'description' => trim($_POST['description'] ?? '') ?: null,
            'is_active'   => $data['is_active'],
        ]);

        AuditModel::log(Auth::adminId(), 'create', 'partners', $newId,
            ['name' => $data['name']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Parceiro criado com sucesso.');
        $this->redirect('/administrator/partners');
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('partners.edit');
        $id      = (int)($params['id'] ?? 0);
        $partner = PartnerModel::find($id);
        if (!$partner) { Router::triggerError(404); }
        $this->renderAdmin('partners/edit', ['record' => $partner]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('partners.edit');
        CSRF::check();
        $id      = (int)($params['id'] ?? 0);
        $partner = PartnerModel::find($id);
        if (!$partner) { Router::triggerError(404); }

        $data = [
            'name'      => trim($_POST['name'] ?? ''),
            'slug'      => trim($_POST['slug'] ?? ''),
            'is_active' => (int)($_POST['is_active'] ?? 1),
        ];

        $v = (new Validator($data))->required('name')->max('name', 190);
        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/partners/' . $id . '/edit');
        }

        $slug = $data['slug'] !== '' ? slugify($data['slug']) : slugify($data['name']);
        $updateData = [
            'name'        => $data['name'],
            'slug'        => $slug,
            'website_url' => trim($_POST['website_url'] ?? '') ?: null,
            'description' => trim($_POST['description'] ?? '') ?: null,
            'is_active'   => $data['is_active'],
        ];

        if (!empty($_FILES['logo_path']['tmp_name'])) {
            $newPath = UploadHandler::uploadImage($_FILES['logo_path'], 'partners');
            if ($newPath !== null) {
                if ($partner['logo_path']) { UploadHandler::delete($partner['logo_path']); }
                $updateData['logo_path'] = $newPath;
            }
        }

        PartnerModel::update($id, $updateData);
        AuditModel::log(Auth::adminId(), 'update', 'partners', $id,
            ['name' => $data['name']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Parceiro atualizado com sucesso.');
        $this->redirect('/administrator/partners');
    }

    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('partners.delete');
        CSRF::check();
        $id      = (int)($params['id'] ?? 0);
        $partner = PartnerModel::find($id);
        if (!$partner) { Router::triggerError(404); }
        if ($partner['logo_path']) { UploadHandler::delete($partner['logo_path']); }
        PartnerModel::delete($id);
        AuditModel::log(Auth::adminId(), 'delete', 'partners', $id,
            ['name' => $partner['name']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Parceiro excluído com sucesso.');
        $this->redirect('/administrator/partners');
    }
}
