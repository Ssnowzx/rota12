<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ControllerBase;
use App\Core\Validator;
use App\Models\AuditModel;

class AuthController extends ControllerBase
{
    /**
     * GET /administrator
     * Redirect based on auth state.
     */
    public function index(): void
    {
        if (Auth::checkAdmin()) {
            $this->redirect('/administrator/dashboard');
        }
        $this->redirect('/administrator/login');
    }

    /**
     * GET /administrator/login
     */
    public function loginForm(): void
    {
        if (Auth::checkAdmin()) {
            $this->redirect('/administrator/dashboard');
        }
        $this->renderAdmin('auth/login', [], 'login');
    }

    /**
     * POST /administrator/login
     */
    public function login(): void
    {
        CSRF::check();

        $data = [
            'email'    => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
        ];

        $v = (new Validator($data))
            ->required('email')
            ->email('email')
            ->required('password');

        if (!$v->passes()) {
            foreach ($v->allErrors() as $msg) {
                $this->flash('error', $msg);
            }
            $this->redirect('/administrator/login');
        }

        $admin = Auth::loginAdmin($data['email'], $data['password']);

        if ($admin === false) {
            $this->flash('error', 'E-mail ou senha inválidos.');
            $this->redirect('/administrator/login');
        }

        AuditModel::log(
            Auth::adminId(),
            'login',
            'admin_users',
            Auth::adminId(),
            ['email' => $data['email']],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        $this->redirect('/administrator/dashboard');
    }

    /**
     * GET /administrator/logout
     */
    public function logout(): void
    {
        Auth::logoutAdmin();
        $this->redirect('/administrator/login');
    }
}
