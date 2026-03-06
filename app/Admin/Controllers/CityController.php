<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ControllerBase;
use App\Core\DB;
use App\Core\Router;
use App\Core\UploadHandler;
use App\Core\Validator;
use App\Models\CityModel;
use App\Models\AuditModel;

class CityController extends ControllerBase
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm('cities.view');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pager   = CityModel::paginate($page, $perPage);
        $this->renderAdmin('cities/index', [
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
        $this->requirePerm('cities.create');
        $this->renderAdmin('cities/create');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePerm('cities.create');
        CSRF::check();

        $name      = trim($_POST['name'] ?? '');
        $state     = strtoupper(trim($_POST['state'] ?? ''));
        $slug      = trim($_POST['slug'] ?? '');
        $is_active = (int)($_POST['is_active'] ?? 1);
        $errors    = [];

        if ($name === '') { $errors[] = 'O campo nome é obrigatório.'; }
        elseif (mb_strlen($name) > 120) { $errors[] = 'O nome deve ter no máximo 120 caracteres.'; }
        if ($state === '' || strlen($state) !== 2) { $errors[] = 'O estado deve ter exatamente 2 caracteres.'; }
        if ($slug === '') { $slug = slugify($name); } else { $slug = slugify($slug); }

        if ($state !== '' && $slug !== '') {
            $count = DB::selectOne('SELECT COUNT(*) AS cnt FROM cities WHERE state = ? AND slug = ?', [$state, $slug]);
            if (($count['cnt'] ?? 0) > 0) { $errors[] = 'Já existe uma cidade com este slug e estado.'; }
        }

        if (!empty($errors)) {
            foreach ($errors as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/cities/create');
            return;
        }

        // Upload da imagem com tratamento de erro robusto
        $imagePath = null;
        try {
            $imagePath = UploadHandler::uploadImage($_FILES['image_path'] ?? [], 'cities');
        } catch (\RuntimeException $e) {
            $this->flash('error', 'Erro no upload da imagem: ' . $e->getMessage());
            $this->redirect('/administrator/cities/create');
            return;
        }

        $insertData = ['name' => $name, 'state' => $state, 'slug' => $slug, 'is_active' => $is_active];
        if ($imagePath !== null) {
            $insertData['image_path'] = '/uploads/' . $imagePath;
        }

        $newId = CityModel::insert($insertData);
        AuditModel::log(Auth::adminId(), 'create', 'cities', $newId,
            ['name' => $name, 'state' => $state], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Cidade criada com sucesso.');
        $this->redirect('/administrator/cities');
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('cities.edit');
        $id   = (int)($params['id'] ?? 0);
        $city = CityModel::find($id);
        if (!$city) { Router::triggerError(404); }
        $this->renderAdmin('cities/edit', ['record' => $city]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('cities.edit');
        CSRF::check();
        $id        = (int)($params['id'] ?? 0);
        $city      = CityModel::find($id);
        if (!$city) { Router::triggerError(404); }

        $name      = trim($_POST['name'] ?? '');
        $state     = strtoupper(trim($_POST['state'] ?? ''));
        $slug      = trim($_POST['slug'] ?? '');
        $is_active = (int)($_POST['is_active'] ?? 1);
        $errors    = [];

        if ($name === '') { $errors[] = 'O campo nome é obrigatório.'; }
        elseif (mb_strlen($name) > 120) { $errors[] = 'O nome deve ter no máximo 120 caracteres.'; }
        if ($state === '' || strlen($state) !== 2) { $errors[] = 'O estado deve ter exatamente 2 caracteres.'; }
        if ($slug === '') { $slug = slugify($name); } else { $slug = slugify($slug); }

        if ($state !== '' && $slug !== '') {
            $count = DB::selectOne('SELECT COUNT(*) AS cnt FROM cities WHERE state = ? AND slug = ? AND id != ?', [$state, $slug, $id]);
            if (($count['cnt'] ?? 0) > 0) { $errors[] = 'Já existe uma cidade com este slug e estado.'; }
        }

        if (!empty($errors)) {
            foreach ($errors as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/cities/' . $id . '/edit');
            return;
        }

        $updateData = ['name' => $name, 'state' => $state, 'slug' => $slug, 'is_active' => $is_active];

        // Upload da imagem com tratamento de erro robusto
        if (!empty($_FILES['image_path']['tmp_name'])) {
            try {
                $newPath = UploadHandler::uploadImage($_FILES['image_path'], 'cities');
                if ($newPath !== null) {
                    $oldImage = $city['image_path'] ?? '';
                    if ($oldImage !== '' && str_starts_with($oldImage, '/uploads/')) {
                        UploadHandler::delete(substr($oldImage, strlen('/uploads/')));
                    }
                    $updateData['image_path'] = '/uploads/' . $newPath;
                }
            } catch (\RuntimeException $e) {
                $this->flash('error', 'Erro no upload da imagem: ' . $e->getMessage());
                $this->redirect('/administrator/cities/' . $id . '/edit');
                return;
            }
        }

        CityModel::update($id, $updateData);
        AuditModel::log(Auth::adminId(), 'update', 'cities', $id,
            ['name' => $name], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Cidade atualizada com sucesso.');
        $this->redirect('/administrator/cities');
    }

    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('cities.delete');
        CSRF::check();
        $id   = (int)($params['id'] ?? 0);
        $city = CityModel::find($id);
        if (!$city) { Router::triggerError(404); }
        $oldImage = $city['image_path'] ?? '';
        if ($oldImage !== '' && str_starts_with($oldImage, '/uploads/')) {
            UploadHandler::delete(substr($oldImage, strlen('/uploads/')));
        }
        CityModel::delete($id);
        AuditModel::log(Auth::adminId(), 'delete', 'cities', $id,
            ['name' => $city['name']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Cidade excluída com sucesso.');
        $this->redirect('/administrator/cities');
    }
}
