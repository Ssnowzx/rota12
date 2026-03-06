<?php
declare(strict_types=1);

/**
 * Alert Dashboard - By User View
 * Alertas de um usuário admin específico
 *
 * @var int $userId
 * @var array $alerts
 * @var int $total
 * @var int $pages
 * @var int $page
 */
?>
<style>
.severity-row { border-left: 3px solid transparent; }
.severity-row.sev-critical { border-left-color: var(--adm-danger); }
.severity-row.sev-high { border-left-color: var(--adm-warning); }
.severity-row.sev-medium { border-left-color: #42a5f5; }
.severity-row.sev-low { border-left-color: var(--adm-success); }
.sev-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: .75rem; font-weight: 600; }
.sev-badge.critical { background: rgba(255,82,82,.15); color: var(--adm-danger); }
.sev-badge.high { background: rgba(255,215,64,.15); color: var(--adm-warning); }
.sev-badge.medium { background: rgba(66,165,245,.15); color: #42a5f5; }
.sev-badge.low { background: rgba(105,240,174,.15); color: var(--adm-success); }
.code-tag { background: var(--adm-surface-2); padding: 2px 8px; border-radius: 4px; font-family: monospace; font-size: .8rem; color: var(--adm-accent); }
.link-accent { color: var(--adm-accent); text-decoration: none; }
.link-accent:hover { text-decoration: underline; }
.pagination { display: flex; justify-content: center; align-items: center; gap: .5rem; margin-top: 1.5rem; }
.pagination a { padding: .4rem .8rem; border: 1px solid var(--adm-border); border-radius: 6px; color: var(--adm-text); text-decoration: none; font-size: .85rem; }
.pagination a:hover { background: var(--adm-surface-2); }
.pagination span { padding: .4rem .8rem; color: var(--adm-muted); font-size: .85rem; }
</style>

<div class="adm-page-header">
    <div>
        <a href="/administrator/alerts" class="link-accent" style="font-size:.85rem;">&#8592; Voltar ao Dashboard</a>
        <h1 class="adm-page-title" style="margin-top:.5rem;">Alertas do Usuário: <span class="code-tag" style="font-size:1rem;">#<?= (int)$userId ?></span></h1>
        <p class="adm-breadcrumb"><?= (int)$total ?> alerta(s) deste usuário</p>
    </div>
</div>

<div class="adm-card">
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Severidade</th>
                    <th>IP</th>
                    <th>Data</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($alerts)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;color:var(--adm-muted);padding:2rem;">
                            Nenhum alerta encontrado para este usuário
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($alerts as $alert): ?>
                        <tr class="severity-row sev-<?= e($alert['severity'] ?? 'medium') ?>">
                            <td><span class="code-tag"><?= e($alert['event']) ?></span></td>
                            <td>
                                <span class="sev-badge <?= e($alert['severity'] ?? 'medium') ?>">
                                    <?= e($alert['severity']) ?>
                                </span>
                            </td>
                            <td style="font-family:monospace;">
                                <a href="/administrator/alerts/by-ip/<?= e($alert['ip_address']) ?>" class="link-accent">
                                    <?= e($alert['ip_address'] ?? 'unknown') ?>
                                </a>
                            </td>
                            <td style="font-size:.85rem;color:var(--adm-muted);">
                                <?= date('d/m/Y H:i', strtotime((string)($alert['triggered_at'] ?? ''))) ?>
                            </td>
                            <td>
                                <a href="/administrator/alerts/<?= (int)$alert['id'] ?>" class="btn btn-secondary btn-sm">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="/administrator/alerts/by-user/<?= (int)$userId ?>?page=1">Primeira</a>
        <a href="/administrator/alerts/by-user/<?= (int)$userId ?>?page=<?= $page - 1 ?>">Anterior</a>
    <?php endif; ?>
    <span>Página <?= $page ?> de <?= $pages ?></span>
    <?php if ($page < $pages): ?>
        <a href="/administrator/alerts/by-user/<?= (int)$userId ?>?page=<?= $page + 1 ?>">Próxima</a>
        <a href="/administrator/alerts/by-user/<?= (int)$userId ?>?page=<?= $pages ?>">Última</a>
    <?php endif; ?>
</div>
<?php endif; ?>
