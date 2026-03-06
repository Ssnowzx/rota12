<div class="adm-login-page">
    <div class="adm-login-card">
        <div class="adm-login-logo">
            <h1>ROTA 12</h1>
            <p>Painel Administrativo</p>
        </div>

        <?php
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        foreach ($flash as $f): ?>
        <div class="adm-alert adm-alert-<?= e($f['type'] ?? 'error') ?>">
            <i class="fas fa-circle-xmark"></i>
            <?= e($f['message'] ?? '') ?>
        </div>
        <?php endforeach; ?>

        <?php if (!empty($error)): ?>
        <div class="adm-alert adm-alert-error">
            <i class="fas fa-circle-xmark"></i>
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/administrator/login">
            <?= \App\Core\CSRF::field() ?>

            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= e($_POST['email'] ?? '') ?>"
                       placeholder="admin@rota12.com.br"
                       required autofocus autocomplete="username"/>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="••••••••"
                       required autocomplete="current-password"/>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.7rem; font-size: 0.95rem; margin-top: 0.5rem;">
                <i class="fas fa-right-to-bracket"></i> Entrar
            </button>
        </form>
    </div>
</div>
