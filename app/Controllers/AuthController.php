<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\CSRF;
use App\Core\PartnerAuth;
use App\Core\EmailService;
use App\Models\PartnerApprovalModel;
use App\Models\UserModel;
use App\Models\PartnerModel;

class AuthController extends ControllerBase
{
    public function loginForm(): void
    {
        if (PartnerAuth::check()) {
            $this->redirect(PartnerAuth::isPartner() ? '/parceiro/dashboard' : '/minha-conta');
        }
        $this->render('auth/login', [
            'pageTitle' => 'Login - Rota 12',
            'csrf'      => CSRF::generate(),
            'flash'     => $this->getFlash(),
        ]);
    }

    public function login(): void
    {
        CSRF::check();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->flash('error', 'Preencha e-mail e senha.');
            $this->redirect('/login');
        }

        $user = UserModel::findByEmail($email);

        if ($user === null || !UserModel::verifyPassword($user, $password)) {
            $this->flash('error', 'E-mail ou senha inválidos.');
            $this->redirect('/login');
        }

        // Note: Partners with is_active=0 can login, but dashboard shows limited view with approval status message
        // They are not completely blocked, allowing better UX during approval process

        PartnerAuth::login($user);

        $role = $user['role'] ?? 'member';
        if ($role === 'parceiro') {
            $this->redirect('/parceiro/dashboard');
        } elseif ($role === 'admin') {
            $this->redirect('/administrator/dashboard');
        } else {
            $this->redirect('/minha-conta');
        }
    }

    public function registerForm(): void
    {
        if (PartnerAuth::check()) {
            $this->redirect('/minha-conta');
        }
        $this->render('auth/register', [
            'pageTitle' => 'Cadastro - Rota 12',
            'csrf'      => CSRF::generate(),
            'flash'     => $this->getFlash(),
        ]);
    }

    public function register(): void
    {
        CSRF::check();

        $username   = trim($_POST['username'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';
        $isPartner  = (isset($_POST['is_partner']) && $_POST['is_partner'] === '1');
        $errors     = [];

        if ($username === '') { $errors[] = 'Nome de usuário é obrigatório.'; }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'E-mail inválido.'; }
        if (strlen($password) < 6) { $errors[] = 'Senha deve ter pelo menos 6 caracteres.'; }

        if (empty($errors)) {
            $exists = UserModel::existsByEmailOrUsername($email, $username);
            if ($exists) { $errors[] = 'E-mail ou usuário já cadastrado.'; }
        }

        if (!empty($errors)) {
            foreach ($errors as $msg) { $this->flash('error', $msg); }
            $this->redirect('/cadastro');
        }

        // Determine role and is_active status based on partner flag
        $role     = $isPartner ? 'parceiro' : 'member';
        $isActive = !$isPartner;  // Partners start with is_active=FALSE (blocked during approval)

        $id = UserModel::createUser($username, $email, $password, $role, $isActive);

        // If registering as partner, create partner record and request approval
        if ($isPartner) {
            // Create partner record via Model (delegated from Controller to Model layer)
            PartnerModel::createFromUser($id, $username);
            PartnerApprovalModel::request($id);

            // Send notification email to admin about new partner request
            $adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@rota12.local';
            EmailService::sendAdminNotification($adminEmail, $username, $email, $id);

            $this->flash('success', 'Cadastro como parceiro realizado! Sua solicitação foi enviada para análise. Você receberá um e-mail com a decisão em breve.');
        } else {
            $this->flash('success', 'Conta criada com sucesso!');
        }

        $user = UserModel::find($id);
        PartnerAuth::login($user);

        // Redirect based on role
        if ($isPartner) {
            $this->redirect('/minha-conta');  // Pending partners can't access /parceiro/dashboard yet
        } else {
            $this->redirect('/minha-conta');
        }
    }

    public function logout(): void
    {
        PartnerAuth::logout();
        $this->redirect('/');
    }
}
