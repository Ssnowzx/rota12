<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?= e($pageTitle ?? 'Admin') ?> — Rota 12</title>
    <link rel="icon" href="/public/favicon.ico" type="image/x-icon" sizes="16x16"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="/administrator/assets/css/admin.css"/>
</head>
<body>

<!-- ─── SIDEBAR ─────────────────────────────────────────── -->
<aside class="adm-sidebar">
    <div class="adm-sidebar-logo">
        <span>
            ROTA 12
            <span>Painel Admin</span>
        </span>
    </div>

    <?php
    $currentUri = $_SERVER['REQUEST_URI'] ?? '';
    function isActive(string $path, string $current): string {
        return (str_starts_with($current, $path)) ? ' active' : '';
    }
    ?>

    <ul class="adm-nav">
        <li class="adm-nav-section">Principal</li>
        <li><a href="/administrator/dashboard" class="<?= isActive('/administrator/dashboard', $currentUri) ?>">
            <i class="fas fa-gauge-high"></i> Dashboard
        </a></li>

        <li class="adm-nav-section">Conteúdo</li>
        <li><a href="/administrator/pages" class="<?= isActive('/administrator/pages', $currentUri) ?>">
            <i class="fas fa-file-alt"></i> Páginas
        </a></li>
        <li><a href="/administrator/banners" class="<?= isActive('/administrator/banners', $currentUri) ?>">
            <i class="fas fa-images"></i> Banners
        </a></li>
        <li><a href="/administrator/highlights" class="<?= isActive('/administrator/highlights', $currentUri) ?>">
            <i class="fas fa-star"></i> Destaques
        </a></li>

        <li class="adm-nav-section">Localização</li>
        <li><a href="/administrator/cities" class="<?= isActive('/administrator/cities', $currentUri) ?>">
            <i class="fas fa-city"></i> Cidades
        </a></li>

        <li class="adm-nav-section">Parceiros</li>
        <li><a href="/administrator/approval/partners" class="<?= isActive('/administrator/approval/partners', $currentUri) ?>">
            <i class="fas fa-clipboard-check"></i> Aprovações Pendentes
        </a></li>
        <li><a href="/administrator/partners" class="<?= isActive('/administrator/partners', $currentUri) ?>">
            <i class="fas fa-handshake"></i> Parceiros
        </a></li>
        <li><a href="/administrator/establishments" class="<?= isActive('/administrator/establishments', $currentUri) ?>">
            <i class="fas fa-store"></i> Estabelecimentos
        </a></li>
        <li><a href="/administrator/coupons" class="<?= isActive('/administrator/coupons', $currentUri) ?>">
            <i class="fas fa-ticket-alt"></i> Cupons
        </a></li>

        <li class="adm-nav-section">Sistema</li>
        <li><a href="/administrator/users" class="<?= isActive('/administrator/users', $currentUri) ?>">
            <i class="fas fa-users"></i> Usuários
        </a></li>
        <li><a href="/administrator/roles" class="<?= isActive('/administrator/roles', $currentUri) ?>">
            <i class="fas fa-shield-halved"></i> Papéis
        </a></li>
        <li><a href="/administrator/audit" class="<?= isActive('/administrator/audit', $currentUri) ?>">
            <i class="fas fa-scroll"></i> Auditoria
        </a></li>
        <li><a href="/administrator/alerts" class="<?= isActive('/administrator/alerts', $currentUri) ?>">
            <i class="fas fa-bell"></i> Alertas
        </a></li>

        <li class="adm-nav-section">Site</li>
        <li><a href="/" target="_blank">
            <i class="fas fa-arrow-up-right-from-square"></i> Ver Site
        </a></li>
    </ul>
</aside>

<!-- ─── TOP NAV ──────────────────────────────────────────── -->
<header class="adm-topnav">
    <span class="adm-topnav-brand"><?= e($pageTitle ?? 'Dashboard') ?></span>
    <div class="adm-topnav-right">
        <?php if (!empty($_SESSION['admin_user']['name'] ?? '')): ?>
        <span class="adm-topnav-user">
            <i class="fas fa-circle-user"></i>
            <?= e($_SESSION['admin_user']['name']) ?>
        </span>
        <?php endif; ?>
        <form method="POST" action="/administrator/logout" style="margin: 0;">
            <?= \App\Core\CSRF::field() ?>
            <button type="submit" class="adm-logout-btn">
                <i class="fas fa-right-from-bracket"></i> Sair
            </button>
        </form>
    </div>
</header>

<!-- ─── MAIN CONTENT ─────────────────────────────────────── -->
<main class="adm-main">
    <?php
    // Flash messages
    $flash = $_SESSION['flash'] ?? null;
    if ($flash): unset($_SESSION['flash']); ?>
    <div class="adm-alert adm-alert-<?= e($flash['type'] ?? 'success') ?>">
        <i class="fas fa-<?= $flash['type'] === 'error' ? 'circle-xmark' : ($flash['type'] === 'warning' ? 'triangle-exclamation' : 'circle-check') ?>"></i>
        <?= e($flash['message']) ?>
    </div>
    <?php endif; ?>

    <?= $content ?>
</main>

<script>
// Confirm delete dialogs
document.addEventListener('submit', function(e) {
    var form = e.target;
    var btn = form.querySelector('[data-confirm]');
    if (btn && !confirm(btn.dataset.confirm || 'Tem certeza?')) {
        e.preventDefault();
        return false;
    }
});

// Handle links that need confirmation
document.addEventListener('click', function(e) {
    if (e.target.tagName === 'A' && e.target.hasAttribute('data-confirm')) {
        if (!confirm(e.target.dataset.confirm || 'Tem certeza?')) {
            e.preventDefault();
            return false;
        }
    }
});
</script>

</body>
</html>
