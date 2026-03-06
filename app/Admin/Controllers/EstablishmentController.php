<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ControllerBase;
use App\Core\DB;
use App\Core\Router;
use App\Core\UploadHandler;
use App\Core\Validator;
use App\Models\EstablishmentModel;
use App\Models\CityModel;
use App\Models\AuditModel;

class EstablishmentController extends ControllerBase
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm('establishments.view');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pager   = EstablishmentModel::paginate($page, $perPage);
        $this->renderAdmin('establishments/index', [
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
        $this->requirePerm('establishments.create');
        $cities = CityModel::findAll([], 'name ASC');
        $this->renderAdmin('establishments/create', compact('cities'));
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePerm('establishments.create');
        CSRF::check();

        $name      = trim($_POST['name'] ?? '');
        $cityId    = (int)($_POST['city_id'] ?? 0);
        $slug      = trim($_POST['slug'] ?? '');
        $is_active = (int)($_POST['is_active'] ?? 1);
        $errors    = [];

        if ($cityId <= 0) { $errors[] = 'Selecione uma cidade.'; }
        if ($name === '') { $errors[] = 'O nome é obrigatório.'; }
        elseif (mb_strlen($name) > 190) { $errors[] = 'O nome deve ter no máximo 190 caracteres.'; }
        if ($slug === '') { $slug = slugify($name); } else { $slug = slugify($slug); }

        if ($cityId > 0 && $slug !== '') {
            $count = DB::selectOne('SELECT COUNT(*) AS cnt FROM establishments WHERE city_id = ? AND slug = ?', [$cityId, $slug]);
            if (($count['cnt'] ?? 0) > 0) { $errors[] = 'Já existe um estabelecimento com este slug nesta cidade.'; }
        }

        if (!empty($errors)) {
            foreach ($errors as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/establishments/create');
        }

        $logoPath  = null;
        $coverPath = null;
        if (!empty($_FILES['logo_path']['tmp_name'])) { $logoPath = UploadHandler::uploadImage($_FILES['logo_path'], 'establishments'); }
        if (!empty($_FILES['cover_path']['tmp_name'])) { $coverPath = UploadHandler::uploadImage($_FILES['cover_path'], 'establishments'); }

        $newId = EstablishmentModel::insert([
            'city_id'     => $cityId,
            'name'        => $name,
            'slug'        => $slug,
            'address'     => trim($_POST['address'] ?? '') ?: null,
            'description' => trim($_POST['description'] ?? '') ?: null,
            'logo_path'   => $logoPath,
            'cover_path'  => $coverPath,
            'is_active'   => $is_active,
        ]);

        AuditModel::log(Auth::adminId(), 'create', 'establishments', $newId,
            ['name' => $name, 'city_id' => $cityId], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Estabelecimento criado com sucesso.');
        $this->redirect('/administrator/establishments');
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('establishments.edit');
        $id            = (int)($params['id'] ?? 0);
        $establishment = EstablishmentModel::find($id);
        if (!$establishment) { Router::triggerError(404); }
        $cities = CityModel::findAll([], 'name ASC');
        $this->renderAdmin('establishments/edit', ['record' => $establishment, 'cities' => $cities]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('establishments.edit');
        CSRF::check();
        $id            = (int)($params['id'] ?? 0);
        $establishment = EstablishmentModel::find($id);
        if (!$establishment) { Router::triggerError(404); }

        $name      = trim($_POST['name'] ?? '');
        $cityId    = (int)($_POST['city_id'] ?? 0);
        $slug      = trim($_POST['slug'] ?? '');
        $is_active = (int)($_POST['is_active'] ?? 1);
        $errors    = [];

        if ($cityId <= 0) { $errors[] = 'Selecione uma cidade.'; }
        if ($name === '') { $errors[] = 'O nome é obrigatório.'; }
        elseif (mb_strlen($name) > 190) { $errors[] = 'O nome deve ter no máximo 190 caracteres.'; }
        if ($slug === '') { $slug = slugify($name); } else { $slug = slugify($slug); }

        if ($cityId > 0 && $slug !== '') {
            $count = DB::selectOne('SELECT COUNT(*) AS cnt FROM establishments WHERE city_id = ? AND slug = ? AND id != ?', [$cityId, $slug, $id]);
            if (($count['cnt'] ?? 0) > 0) { $errors[] = 'Já existe um estabelecimento com este slug nesta cidade.'; }
        }

        if (!empty($errors)) {
            foreach ($errors as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/establishments/' . $id . '/edit');
        }

        $updateData = [
            'city_id'     => $cityId,
            'name'        => $name,
            'slug'        => $slug,
            'address'     => trim($_POST['address'] ?? '') ?: null,
            'description' => trim($_POST['description'] ?? '') ?: null,
            'is_active'   => $is_active,
        ];

        if (!empty($_FILES['logo_path']['tmp_name'])) {
            $newPath = UploadHandler::uploadImage($_FILES['logo_path'], 'establishments');
            if ($newPath !== null) {
                if ($establishment['logo_path']) { UploadHandler::delete($establishment['logo_path']); }
                $updateData['logo_path'] = $newPath;
            }
        }
        if (!empty($_FILES['cover_path']['tmp_name'])) {
            $newPath = UploadHandler::uploadImage($_FILES['cover_path'], 'establishments');
            if ($newPath !== null) {
                if ($establishment['cover_path']) { UploadHandler::delete($establishment['cover_path']); }
                $updateData['cover_path'] = $newPath;
            }
        }

        EstablishmentModel::update($id, $updateData);
        AuditModel::log(Auth::adminId(), 'update', 'establishments', $id,
            ['name' => $name], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Estabelecimento atualizado com sucesso.');
        $this->redirect('/administrator/establishments');
    }

    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('establishments.delete');
        CSRF::check();
        $id            = (int)($params['id'] ?? 0);
        $establishment = EstablishmentModel::find($id);
        if (!$establishment) { Router::triggerError(404); }
        if ($establishment['logo_path']) { UploadHandler::delete($establishment['logo_path']); }
        if ($establishment['cover_path']) { UploadHandler::delete($establishment['cover_path']); }
        EstablishmentModel::delete($id);
        AuditModel::log(Auth::adminId(), 'delete', 'establishments', $id,
            ['name' => $establishment['name']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Estabelecimento excluído com sucesso.');
        $this->redirect('/administrator/establishments');
    }
}
