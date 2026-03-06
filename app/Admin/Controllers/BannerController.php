<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ControllerBase;
use App\Core\Router;
use App\Core\UploadHandler;
use App\Core\Validator;
use App\Models\BannerModel;
use App\Models\AuditModel;

class BannerController extends ControllerBase
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm('banners.view');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pager   = BannerModel::paginate($page, $perPage);
        $this->renderAdmin('banners/index', [
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
        $this->requirePerm('banners.create');
        $this->renderAdmin('banners/create');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePerm('banners.create');
        CSRF::check();

        $data = [
            'title'    => trim($_POST['title'] ?? ''),
            'position' => trim($_POST['position'] ?? 'home'),
            'link_url' => trim($_POST['link_url'] ?? '') ?: null,
            'starts_at'=> trim($_POST['starts_at'] ?? '') ?: null,
            'ends_at'  => trim($_POST['ends_at'] ?? '') ?: null,
            'is_active'=> (int)($_POST['is_active'] ?? 1),
            'sort_order'=> (int)($_POST['sort_order'] ?? 0),
        ];

        $v = (new Validator($data))->required('title')->max('title', 190);
        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/banners/create');
        }

        $imagePath = UploadHandler::uploadImage($_FILES['image_path'] ?? [], 'banners');
        if ($imagePath === null) {
            $this->flash('error', 'A imagem do banner é obrigatória.');
            $this->redirect('/administrator/banners/create');
        }

        $newId = BannerModel::insert([
            'title'      => $data['title'],
            'image_path' => $imagePath,
            'link_url'   => $data['link_url'],
            'position'   => $data['position'],
            'is_active'  => $data['is_active'],
            'sort_order' => $data['sort_order'],
        ]);

        AuditModel::log(Auth::adminId(), 'create', 'banners', $newId,
            ['title' => $data['title']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');

        $this->flash('success', 'Banner criado com sucesso.');
        $this->redirect('/administrator/banners');
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('banners.edit');
        $id     = (int)($params['id'] ?? 0);
        $banner = BannerModel::find($id);
        if (!$banner) { Router::triggerError(404); }
        $this->renderAdmin('banners/edit', ['record' => $banner]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('banners.edit');
        CSRF::check();
        $id     = (int)($params['id'] ?? 0);
        $banner = BannerModel::find($id);
        if (!$banner) { Router::triggerError(404); }

        $data = [
            'title'      => trim($_POST['title'] ?? ''),
            'position'   => trim($_POST['position'] ?? 'home'),
            'link_url'   => trim($_POST['link_url'] ?? '') ?: null,
            'is_active'  => (int)($_POST['is_active'] ?? 1),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        $v = (new Validator(['title' => $data['title']]))->required('title')->max('title', 190);
        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/banners/' . $id . '/edit');
        }

        if (!empty($_FILES['image_path']['tmp_name'])) {
            $newPath = UploadHandler::uploadImage($_FILES['image_path'], 'banners');
            if ($newPath !== null) {
                UploadHandler::delete($banner['image_path']);
                $data['image_path'] = $newPath;
            }
        }

        BannerModel::update($id, $data);
        AuditModel::log(Auth::adminId(), 'update', 'banners', $id,
            ['title' => $data['title']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');

        $this->flash('success', 'Banner atualizado com sucesso.');
        $this->redirect('/administrator/banners');
    }

    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('banners.delete');
        CSRF::check();
        $id     = (int)($params['id'] ?? 0);
        $banner = BannerModel::find($id);
        if (!$banner) { Router::triggerError(404); }
        UploadHandler::delete($banner['image_path']);
        BannerModel::delete($id);
        AuditModel::log(Auth::adminId(), 'delete', 'banners', $id,
            ['title' => $banner['title']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Banner excluído com sucesso.');
        $this->redirect('/administrator/banners');
    }
}
