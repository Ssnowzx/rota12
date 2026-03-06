<?php
/** @var array $flash */
/** @var string $csrf */
?>
<style>
.auth-wrapper { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 6rem 1rem 3rem; }
.auth-card { background: #1a1a1a; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 2.5rem; width: 100%; max-width: 420px; }
.auth-logo { text-align: center; margin-bottom: 2rem; }
.auth-logo img { height: 48px; }
.auth-title { font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 0.25rem; text-align: center; }
.auth-subtitle { color: rgba(255,255,255,0.5); font-size: 0.9rem; text-align: center; margin-bottom: 2rem; }
.auth-field { margin-bottom: 1.25rem; }
.auth-field label { display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem; margin-bottom: 0.5rem; font-weight: 500; }
.auth-field input { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 8px; color: #fff; padding: 0.75rem 1rem; font-size: 1rem; outline: none; box-sizing: border-box; }
.auth-field input:focus { border-color: #dfff00; }
.auth-submit { width: 100%; background: #dfff00; color: #111; border: none; border-radius: 8px; padding: 0.85rem; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: 0.5rem; }
.auth-submit:hover { background: #c8e600; }
.auth-footer { text-align: center; margin-top: 1.5rem; color: rgba(255,255,255,0.5); font-size: 0.9rem; }
.auth-footer a { color: #dfff00; text-decoration: none; }
.auth-alert { background: rgba(255,80,80,0.1); border: 1px solid rgba(255,80,80,0.3); border-radius: 8px; color: #ff8080; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.9rem; }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <img src="/assets/images/logo.png" alt="Rota 12"/>
        </div>
        <h1 class="auth-title">Bem-vindo de volta</h1>
        <p class="auth-subtitle">Entre na sua conta Rota 12</p>

        <?php foreach ($flash as $f): ?>
        <div class="auth-alert"><?= e($f['message']) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="/login">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"/>
            <div class="auth-field">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="seu@email.com" required autocomplete="email"/>
            </div>
            <div class="auth-field">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password"/>
            </div>
            <button type="submit" class="auth-submit">Entrar</button>
        </form>

        <div class="auth-footer">
            Não tem conta? <a href="/cadastro">Cadastre-se grátis</a>
        </div>
    </div>
</div>
