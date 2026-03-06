<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ACL;
use App\Core\ControllerBase;
use App\Core\Router;
use App\Core\Validator;
use App\Models\AdminRoleModel;
use App\Models\AdminPermissionModel;
use App\Models\AuditModel;

class RoleController extends ControllerBase
{
    private const PERM = 'core.users.manage';

    /**
     * GET /administrator/roles
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $records = AdminRoleModel::findAll();
        $total   = count($records);

        $this->renderAdmin('roles/index', compact('records', 'total'));
    }

    /**
     * GET /administrator/roles/create
     */
    public function create(): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $groupedPermissions = AdminPermissionModel::grouped();

        $this->renderAdmin('roles/create', compact('groupedPermissions'));
    }

    /**
     * POST /administrator/roles/create
     */
    public function store(): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);
        CSRF::check();

        $data = [
            'name'     => trim($_POST['name'] ?? ''),
            'role_key' => trim($_POST['role_key'] ?? ''),
        ];

        $v = (new Validator($data))
            ->required('name')->max('name', 80)
            ->required('role_key')->max('role_key', 80)->unique('role_key', 'admin_roles', 'role_key');

        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) {
                $this->flash('error', $msg);
            }
            $this->redirect('/administrator/roles/create');
        }

        $newId = AdminRoleModel::insert([
            'name'     => $data['name'],
            'role_key' => $data['role_key'],
        ]);

        $permissionIds = array_map('intval', $_POST['permissions'] ?? []);
        AdminRoleModel::syncPermissions($newId, $permissionIds);

        AuditModel::log(
            Auth::adminId(),
            'grant',
            'admin_roles',
            $newId,
            ['name' => $data['name'], 'role_key' => $data['role_key']],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        $this->flash('success', 'Papel criado com sucesso.');
        $this->redirect('/administrator/roles');
    }

    /**
     * GET /administrator/roles/{id}/edit
     */
    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);

        $id   = (int)($params['id'] ?? 0);
        $role = AdminRoleModel::withPermissions($id);

        if (!$role) {
            Router::triggerError(404);
        }

        $groupedPermissions = AdminPermissionModel::grouped();
        $assignedIds        = array_column($role['permissions'] ?? [], 'id');

        $this->renderAdmin('roles/edit', compact('role', 'groupedPermissions', 'assignedIds'));
    }

    /**
     * POST /administrator/roles/{id}/edit
     */
    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);
        CSRF::check();

        $id   = (int)($params['id'] ?? 0);
        $role = AdminRoleModel::find($id);

        if (!$role) {
            Router::triggerError(404);
        }

        $data = [
            'name'     => trim($_POST['name'] ?? ''),
            'role_key' => trim($_POST['role_key'] ?? ''),
        ];

        $v = (new Validator($data))
            ->required('name')->max('name', 80)
            ->required('role_key')->max('role_key', 80)->unique('role_key', 'admin_roles', 'role_key', $id);

        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) {
                $this->flash('error', $msg);
            }
            $this->redirect('/administrator/roles/' . $id . '/edit');
        }

        AdminRoleModel::update($id, [
            'name'     => $data['name'],
            'role_key' => $data['role_key'],
        ]);

        $permissionIds = array_map('intval', $_POST['permissions'] ?? []);
        AdminRoleModel::syncPermissions($id, $permissionIds);

        ACL::clearCache();

        AuditModel::log(
            Auth::adminId(),
            'grant',
            'admin_roles',
            $id,
            ['name' => $data['name'], 'role_key' => $data['role_key']],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        $this->flash('success', 'Papel atualizado com sucesso.');
        $this->redirect('/administrator/roles');
    }

    /**
     * POST /administrator/roles/{id}/delete
     */
    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm(self::PERM);
        CSRF::check();

        $id   = (int)($params['id'] ?? 0);
        $role = AdminRoleModel::find($id);

        if (!$role) {
            Router::triggerError(404);
        }

        if (($role['role_key'] ?? '') === 'super_admin') {
            $this->flash('error', 'O papel super_admin não pode ser excluído.');
            $this->redirect('/administrator/roles');
        }

        AdminRoleModel::delete($id);

        AuditModel::log(
            Auth::adminId(),
            'revoke',
            'admin_roles',
            $id,
            ['name' => $role['name'], 'role_key' => $role['role_key']],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        $this->flash('success', 'Papel excluído com sucesso.');
        $this->redirect('/administrator/roles');
    }
}
