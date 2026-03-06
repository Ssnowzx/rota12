<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ControllerBase;
use App\Core\Router;
use App\Core\UploadHandler;
use App\Core\Validator;
use App\Models\HighlightModel;
use App\Models\AuditModel;

class HighlightController extends ControllerBase
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm('highlights.view');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pager   = HighlightModel::paginate($page, $perPage);
        $this->renderAdmin('highlights/index', [
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
        $this->requirePerm('highlights.create');
        $this->renderAdmin('highlights/create');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePerm('highlights.create');
        CSRF::check();

        $data = [
            'title'      => trim($_POST['title'] ?? ''),
            'subtitle'   => trim($_POST['subtitle'] ?? '') ?: null,
            'link_url'   => trim($_POST['link_url'] ?? '') ?: null,
            'is_active'  => (int)($_POST['is_active'] ?? 1),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        $v = (new Validator($data))->required('title')->max('title', 190);
        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/highlights/create');
        }

        $imagePath = null;
        if (!empty($_FILES['image_path']['tmp_name'])) {
            try {
                $imagePath = UploadHandler::uploadImage($_FILES['image_path'], 'highlights');
            } catch (\RuntimeException $e) {
                $this->flash('error', 'Erro no upload da imagem: ' . $e->getMessage());
                $this->redirect('/administrator/highlights/create');
                return;
            }
        }

        $insertImagePath = null;
        if ($imagePath !== null) {
            $insertImagePath = '/uploads/' . $imagePath;
        }

        $newId = HighlightModel::insert([
            'title'      => $data['title'],
            'subtitle'   => $data['subtitle'],
            'image_path' => $insertImagePath,
            'link_url'   => $data['link_url'],
            'is_active'  => $data['is_active'],
            'sort_order' => $data['sort_order'],
        ]);

        AuditModel::log(Auth::adminId(), 'create', 'highlights', $newId,
            ['title' => $data['title']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');

        $this->flash('success', 'Destaque criado com sucesso.');
        $this->redirect('/administrator/highlights');
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('highlights.edit');
        $id        = (int)($params['id'] ?? 0);
        $highlight = HighlightModel::find($id);
        if (!$highlight) { Router::triggerError(404); }
        $this->renderAdmin('highlights/edit', ['record' => $highlight]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('highlights.edit');
        CSRF::check();
        $id        = (int)($params['id'] ?? 0);
        $highlight = HighlightModel::find($id);
        if (!$highlight) { Router::triggerError(404); }

        $data = [
            'title'      => trim($_POST['title'] ?? ''),
            'subtitle'   => trim($_POST['subtitle'] ?? '') ?: null,
            'link_url'   => trim($_POST['link_url'] ?? '') ?: null,
            'is_active'  => (int)($_POST['is_active'] ?? 1),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];

        $v = (new Validator(['title' => $data['title']]))->required('title')->max('title', 190);
        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/highlights/' . $id . '/edit');
        }

        if (!empty($_FILES['image_path']['tmp_name'])) {
            try {
                $newPath = UploadHandler::uploadImage($_FILES['image_path'], 'highlights');
                if ($newPath !== null) {
                    $oldImage = $highlight['image_path'] ?? '';
                    if ($oldImage !== '' && str_starts_with($oldImage, '/uploads/')) {
                        UploadHandler::delete(substr($oldImage, strlen('/uploads/')));
                    }
                    $data['image_path'] = '/uploads/' . $newPath;
                }
            } catch (\RuntimeException $e) {
                $this->flash('error', 'Erro no upload da imagem: ' . $e->getMessage());
                $this->redirect('/administrator/highlights/' . $id . '/edit');
                return;
            }
        }

        HighlightModel::update($id, $data);
        AuditModel::log(Auth::adminId(), 'update', 'highlights', $id,
            ['title' => $data['title']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');

        $this->flash('success', 'Destaque atualizado com sucesso.');
        $this->redirect('/administrator/highlights');
    }

    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('highlights.delete');
        CSRF::check();
        $id        = (int)($params['id'] ?? 0);
        $highlight = HighlightModel::find($id);
        if (!$highlight) { Router::triggerError(404); }
        $oldImage = $highlight['image_path'] ?? '';
        if ($oldImage !== '' && str_starts_with($oldImage, '/uploads/')) {
            UploadHandler::delete(substr($oldImage, strlen('/uploads/')));
        }
        HighlightModel::delete($id);
        AuditModel::log(Auth::adminId(), 'delete', 'highlights', $id,
            ['title' => $highlight['title']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Destaque excluído com sucesso.');
        $this->redirect('/administrator/highlights');
    }
}
