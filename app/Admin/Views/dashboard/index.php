<?php
/** @var array $stats       {pages_count, banners_count, highlights_count, cities_count, partners_count, establishments_count, coupons_count} */
/** @var array $recentAudit Last 10 audit log rows */
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Dashboard</h1>
        <p class="adm-breadcrumb">Bem-vindo ao painel de controle Rota 12</p>
    </div>
</div>

<!-- Stats Grid -->
<div class="adm-stats-grid">
    <div class="adm-stat-card">
        <i class="fas fa-file-alt stat-icon"></i>
        <div class="stat-value"><?= (int)($stats['pages_count'] ?? 0) ?></div>
        <div class="stat-label">Páginas</div>
    </div>
    <div class="adm-stat-card">
        <i class="fas fa-images stat-icon"></i>
        <div class="stat-value"><?= (int)($stats['banners_count'] ?? 0) ?></div>
        <div class="stat-label">Banners</div>
    </div>
    <div class="adm-stat-card">
        <i class="fas fa-star stat-icon"></i>
        <div class="stat-value"><?= (int)($stats['highlights_count'] ?? 0) ?></div>
        <div class="stat-label">Destaques</div>
    </div>
    <div class="adm-stat-card">
        <i class="fas fa-city stat-icon"></i>
        <div class="stat-value"><?= (int)($stats['cities_count'] ?? 0) ?></div>
        <div class="stat-label">Cidades</div>
    </div>
    <div class="adm-stat-card">
        <i class="fas fa-handshake stat-icon"></i>
        <div class="stat-value"><?= (int)($stats['partners_count'] ?? 0) ?></div>
        <div class="stat-label">Parceiros</div>
    </div>
    <div class="adm-stat-card">
        <i class="fas fa-store stat-icon"></i>
        <div class="stat-value"><?= (int)($stats['establishments_count'] ?? 0) ?></div>
        <div class="stat-label">Estabelecimentos</div>
    </div>
    <div class="adm-stat-card">
        <i class="fas fa-ticket-alt stat-icon"></i>
        <div class="stat-value"><?= (int)($stats['coupons_count'] ?? 0) ?></div>
        <div class="stat-label">Cupons</div>
    </div>
</div>

<!-- Quick Links -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.75rem; margin-bottom: 2rem;">
    <?php
    $modules = [
        ['href' => '/administrator/pages/create',          'icon' => 'fa-plus', 'label' => 'Nova Página'],
        ['href' => '/administrator/banners/create',        'icon' => 'fa-plus', 'label' => 'Novo Banner'],
        ['href' => '/administrator/highlights/create',     'icon' => 'fa-plus', 'label' => 'Novo Destaque'],
        ['href' => '/administrator/coupons/create',        'icon' => 'fa-plus', 'label' => 'Novo Cupom'],
        ['href' => '/administrator/establishments/create', 'icon' => 'fa-plus', 'label' => 'Novo Estabelecimento'],
        ['href' => '/administrator/users/create',          'icon' => 'fa-plus', 'label' => 'Novo Usuário'],
    ];
    foreach ($modules as $m): ?>
    <a href="<?= e($m['href']) ?>" class="btn btn-secondary" style="justify-content: center;">
        <i class="fas <?= e($m['icon']) ?>"></i> <?= e($m['label']) ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Recent Audit Activity -->
<div class="adm-card">
    <div class="adm-card-header">
        <h2 class="adm-card-title"><i class="fas fa-scroll" style="color: var(--adm-accent);"></i> Atividade Recente</h2>
        <a href="/administrator/audit" class="btn btn-secondary btn-sm">Ver tudo</a>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Ação</th>
                    <th>Entidade</th>
                    <th>ID</th>
                    <th>IP</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recentAudit)): ?>
                    <?php foreach ($recentAudit as $log): ?>
                    <tr>
                        <td>
                            <?php
                            $actionColors = ['create' => 'success', 'update' => 'warning', 'delete' => 'danger', 'login' => 'muted'];
                            $badge = $actionColors[strtolower($log['action'] ?? '')] ?? 'muted';
                            ?>
                            <span class="badge badge-<?= $badge ?>"><?= e(strtoupper($log['action'] ?? '')) ?></span>
                        </td>
                        <td><?= e($log['entity'] ?? '—') ?></td>
                        <td><?= e($log['entity_id'] ?? '—') ?></td>
                        <td style="font-family: monospace; font-size: 0.78rem; color: var(--adm-muted);"><?= e($log['ip'] ?? '—') ?></td>
                        <td style="font-size: 0.8rem; color: var(--adm-muted);">
                            <?= e($log['created_at'] ? date('d/m/Y H:i', strtotime($log['created_at'])) : '—') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5" class="adm-empty">
                        <i class="fas fa-scroll"></i>
                        <p>Nenhuma atividade registrada.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
