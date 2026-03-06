<?php
declare(strict_types=1);

/**
 * Alert Dashboard - Main View
 * Painel de alertas de segurança em tempo real
 *
 * @var array $statsBySeverity
 * @var int $totalAlerts24h
 * @var array $topIPs
 * @var array $statsByEvent
 * @var array $alerts
 * @var int $pages
 * @var int $page
 * @var array $filters
 */
?>
<style>
.alert-stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.alert-stat { background: var(--adm-surface); border: 1px solid var(--adm-border); border-radius: var(--adm-radius); padding: 1.25rem; }
.alert-stat-label { font-size: .8rem; font-weight: 600; margin-bottom: .25rem; }
.alert-stat-value { font-size: 1.8rem; font-weight: 700; color: var(--adm-text); }
.alert-stat.critical { border-left: 3px solid var(--adm-danger); }
.alert-stat.critical .alert-stat-label { color: var(--adm-danger); }
.alert-stat.high { border-left: 3px solid var(--adm-warning); }
.alert-stat.high .alert-stat-label { color: var(--adm-warning); }
.alert-stat.medium { border-left: 3px solid #42a5f5; }
.alert-stat.medium .alert-stat-label { color: #42a5f5; }
.alert-stat.total { border-left: 3px solid var(--adm-accent); }
.alert-stat.total .alert-stat-label { color: var(--adm-accent); }
.alert-panels { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
@media(max-width:768px){ .alert-panels { grid-template-columns: 1fr; } }
.alert-list-item { display: flex; align-items: center; justify-content: space-between; padding: .65rem .75rem; border-radius: 6px; border: 1px solid var(--adm-border); margin-bottom: .5rem; transition: background .15s; text-decoration: none; color: var(--adm-text); }
.alert-list-item:hover { background: var(--adm-surface-2); }
.alert-list-item .mono { font-family: monospace; font-size: .85rem; }
.alert-count { font-size: .75rem; font-weight: 600; padding: 2px 10px; border-radius: 12px; }
.alert-count.danger { background: rgba(255,82,82,.15); color: var(--adm-danger); }
.alert-count.info { background: rgba(66,165,245,.15); color: #42a5f5; }
.filter-form { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
.filter-group label { display: block; font-size: .75rem; font-weight: 600; color: var(--adm-muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: .35rem; }
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
        <h1 class="adm-page-title">Alertas de Segurança</h1>
        <p class="adm-breadcrumb">Monitoramento de eventos críticos em tempo real</p>
    </div>
</div>

<!-- Estatísticas -->
<div class="alert-stats">
    <div class="alert-stat critical">
        <div class="alert-stat-label">Críticos</div>
        <div class="alert-stat-value"><?= (int)($statsBySeverity['critical'] ?? 0) ?></div>
    </div>
    <div class="alert-stat high">
        <div class="alert-stat-label">Alta Severidade</div>
        <div class="alert-stat-value"><?= (int)($statsBySeverity['high'] ?? 0) ?></div>
    </div>
    <div class="alert-stat medium">
        <div class="alert-stat-label">Média Severidade</div>
        <div class="alert-stat-value"><?= (int)($statsBySeverity['medium'] ?? 0) ?></div>
    </div>
    <div class="alert-stat total">
        <div class="alert-stat-label">Últimas 24h</div>
        <div class="alert-stat-value"><?= (int)($totalAlerts24h ?? 0) ?></div>
    </div>
</div>

<!-- IPs Suspeitos + Eventos -->
<div class="alert-panels">
    <div class="adm-card">
        <div class="adm-card-header">
            <h2 class="adm-card-title">IPs Mais Suspeitos</h2>
        </div>
        <?php if (empty($topIPs)): ?>
            <p style="color:var(--adm-muted);padding:1rem;">Nenhuma atividade suspeita detectada</p>
        <?php else: ?>
            <div style="padding:.75rem;">
                <?php foreach ($topIPs as $item): ?>
                    <a href="/administrator/alerts/by-ip/<?= e($item['ip']) ?>" class="alert-list-item">
                        <span class="mono"><?= e($item['ip']) ?></span>
                        <span class="alert-count danger"><?= (int)$item['count'] ?> alertas</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="adm-card">
        <div class="adm-card-header">
            <h2 class="adm-card-title">Alertas por Tipo de Evento</h2>
        </div>
        <?php if (empty($statsByEvent)): ?>
            <p style="color:var(--adm-muted);padding:1rem;">Nenhum evento registrado</p>
        <?php else: ?>
            <div style="padding:.75rem;">
                <?php foreach ($statsByEvent as $event => $count): ?>
                    <a href="/administrator/alerts/by-event/<?= e($event) ?>" class="alert-list-item">
                        <span style="font-size:.85rem;"><?= e($event) ?></span>
                        <span class="alert-count info"><?= (int)$count ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Filtros -->
<div class="adm-card" style="margin-bottom:1.5rem;">
    <div class="adm-card-header">
        <h2 class="adm-card-title">Filtros</h2>
    </div>
    <div style="padding:1rem;">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label for="event">Tipo de Evento</label>
                <select name="event" id="event" class="form-control">
                    <option value="">Todos</option>
                    <option value="login_failed" <?= ($filters['event'] === 'login_failed' ? 'selected' : '') ?>>Login Falhou</option>
                    <option value="admin_delete" <?= ($filters['event'] === 'admin_delete' ? 'selected' : '') ?>>Admin Delete</option>
                    <option value="role_change" <?= ($filters['event'] === 'role_change' ? 'selected' : '') ?>>Mudança de Role</option>
                    <option value="acl_denied" <?= ($filters['event'] === 'acl_denied' ? 'selected' : '') ?>>ACL Negado</option>
                    <option value="file_upload_suspicious" <?= ($filters['event'] === 'file_upload_suspicious' ? 'selected' : '') ?>>Upload Suspeito</option>
                    <option value="config_change" <?= ($filters['event'] === 'config_change' ? 'selected' : '') ?>>Mudança de Config</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="severity">Severidade</label>
                <select name="severity" id="severity" class="form-control">
                    <option value="">Todas</option>
                    <option value="critical" <?= ($filters['severity'] === 'critical' ? 'selected' : '') ?>>Crítica</option>
                    <option value="high" <?= ($filters['severity'] === 'high' ? 'selected' : '') ?>>Alta</option>
                    <option value="medium" <?= ($filters['severity'] === 'medium' ? 'selected' : '') ?>>Média</option>
                    <option value="low" <?= ($filters['severity'] === 'low' ? 'selected' : '') ?>>Baixa</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="ip">Endereço IP</label>
                <input type="text" name="ip" id="ip" placeholder="192.168.1.1"
                       value="<?= e($filters['ip'] ?? '') ?>" class="form-control">
            </div>
            <div class="filter-group" style="display:flex;gap:.5rem;">
                <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                <a href="/administrator/alerts" class="btn btn-secondary btn-sm">Limpar</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Alertas -->
<div class="adm-card">
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Severidade</th>
                    <th>IP</th>
                    <th>Usuário</th>
                    <th>Data</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($alerts)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:var(--adm-muted);padding:2rem;">
                            Nenhum alerta encontrado
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
                            <td>
                                <?php if (!empty($alert['admin_name'])): ?>
                                    <a href="/administrator/alerts/by-user/<?= e($alert['admin_user_id']) ?>" class="link-accent">
                                        <?= e($alert['admin_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--adm-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:.85rem;color:var(--adm-muted);">
                                <?= date('d/m/Y H:i', strtotime((string)($alert['triggered_at'] ?? ''))) ?>
                            </td>
                            <td>
                                <a href="/administrator/alerts/<?= (int)$alert['id'] ?>" class="btn btn-secondary btn-sm">
                                    Ver
                                </a>
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
        <a href="?page=1">Primeira</a>
        <a href="?page=<?= $page - 1 ?>">Anterior</a>
    <?php endif; ?>
    <span>Página <?= $page ?> de <?= $pages ?></span>
    <?php if ($page < $pages): ?>
        <a href="?page=<?= $page + 1 ?>">Próxima</a>
        <a href="?page=<?= $pages ?>">Última</a>
    <?php endif; ?>
</div>
<?php endif; ?>
