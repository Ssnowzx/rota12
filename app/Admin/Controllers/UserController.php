<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ACL;
use App\Core\ControllerBase;
use App\Core\Router;
use App\Core\Validator;
use App\Models\AdminUserModel;
use App\Models\AdminRoleModel;
use App\Models\AuditModel;

class UserController extends ControllerBase
{
    private const PERM = 'core.users.manage';

    /**
     * GET /administrator/users
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pager = AdminUserModel::listWithRoles($page, $perPage);

        $this->renderAdmin('users/index', [
            'records' => $pager['data'],
            'total'   => $pager['total'],
            'pages'   => $pager['pages'],
            'page'    => $pager['current'],
            'perPage' => $perPage,
        ]);
    }

    /**
     * GET /administrator/users/create
     */
    public function create(): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $allRoles = AdminRoleModel::findAll();

        $this->renderAdmin('users/create', compact('allRoles'));
    }

    /**
     * POST /administrator/users/create
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);
        CSRF::check();

        $data = [
            'name'     => trim($_POST['name'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role_id'  => $_POST['role_id'] ?? '',
        ];

        $v = (new Validator($data))
            ->required('name')->max('name', 120)
            ->required('email')->email('email')->unique('email', 'admin_users', 'email')
            ->required('password')->minLength('password', 8)
            ->required('role_id');

        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) {
                $this->flash('error', $msg);
            }
            $this->redirect('/administrator/users/create');
        }

        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

        $newId = AdminUserModel::insert([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password' => $passwordHash,
            'is_active'     => 1,
        ]);

        AdminUserModel::assignRole($newId, (int)$data['role_id']);

        AuditModel::log(
            Auth::adminId(),
            'create',
            'admin_users',
            $newId,
            ['name' => $data['name'], 'email' => $data['email']],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        $this->flash('success', 'Usuário criado com sucesso.');
        $this->redirect('/administrator/users');
    }

    /**
     * GET /administrator/users/{id}/edit
     */
    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $id   = (int)($params['id'] ?? 0);
        $user = AdminUserModel::find($id);

        if (!$user) {
            Router::triggerError(404);
        }

        $userRoles = AdminUserModel::getRoles($id);
        $allRoles  = AdminRoleModel::findAll();

        $this->renderAdmin('users/edit', ['record' => $user, 'userRoles' => $userRoles, 'allRoles' => $allRoles]);
    }

    /**
     * POST /administrator/users/{id}/edit
     */
    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);
        CSRF::check();

        $id   = (int)($params['id'] ?? 0);
        $user = AdminUserModel::find($id);

        if (!$user) {
            Router::triggerError(404);
        }

        $data = [
            'name'    => trim($_POST['name'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
            'role_id' => $_POST['role_id'] ?? '',
        ];

        $v = (new Validator($data))
            ->required('name')->max('name', 120)
            ->required('email')->email('email')->unique('email', 'admin_users', 'email', $id)
            ->required('role_id');

        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) {
                $this->flash('error', $msg);
            }
            $this->redirect('/administrator/users/' . $id . '/edit');
        }

        $updateData = [
            'name'  => $data['name'],
            'email' => $data['email'],
        ];

        $rawPassword = $_POST['password'] ?? '';
        if ($rawPassword !== '') {
            if (strlen($rawPassword) < 8) {
                $this->flash('error', 'A nova senha deve ter pelo menos 8 caracteres.');
                $this->redirect('/administrator/users/' . $id . '/edit');
            }
            $updateData['password'] = password_hash($rawPassword, PASSWORD_BCRYPT);
        }

        $changedFields = [];
        if ($data['name'] !== $user['name'])   { $changedFields['name']  = $data['name']; }
        if ($data['email'] !== $user['email']) { $changedFields['email'] = $data['email']; }
        if (isset($updateData['password'])) { $changedFields['password'] = '(updated)'; }

        AdminUserModel::update($id, $updateData);

        // Re-assign role: remove old roles, assign new
        $oldRoles = AdminUserModel::getRoles($id);
        foreach ($oldRoles as $oldRole) {
            AdminUserModel::removeRole($id, (int)$oldRole['id']);
        }
        AdminUserModel::assignRole($id, (int)$data['role_id']);

        ACL::clearCache();

        AuditModel::log(
            Auth::adminId(),
            'update',
            'admin_users',
            $id,
            $changedFields,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        $this->flash('success', 'Usuário atualizado com sucesso.');
        $this->redirect('/administrator/users');
    }

    /**
     * POST /administrator/users/{id}/delete
     */
    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);
        CSRF::check();

        $id   = (int)($params['id'] ?? 0);
        $user = AdminUserModel::find($id);

        if (!$user) {
            Router::triggerError(404);
        }

        if (Auth::adminId() === $id) {
            $this->flash('error', 'Você não pode excluir sua própria conta.');
            $this->redirect('/administrator/users');
        }

        $email = $user['email'];

        AdminUserModel::delete($id);

        AuditModel::log(
            Auth::adminId(),
            'delete',
            'admin_users',
            $id,
            ['email' => $email],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        $this->flash('success', 'Usuário excluído com sucesso.');
        $this->redirect('/administrator/users');
    }
}
