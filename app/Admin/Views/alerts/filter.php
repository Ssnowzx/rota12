<?php
declare(strict_types=1);

/**
 * Alert Dashboard - Advanced Filter View
 * Filtro avançado de alertas de segurança
 *
 * @var array $alerts
 * @var int $total
 * @var int $pages
 * @var int $page
 * @var array $filters
 */
?>
<style>
.filter-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
@media(max-width:640px){ .filter-grid { grid-template-columns: 1fr; } }
.filter-group label { display: block; font-size: .75rem; font-weight: 600; color: var(--adm-muted); text-transform: uppercase; letter-spacing: .05em; margin-bottom: .35rem; }
.result-count { padding: .75rem 1rem; background: rgba(66,165,245,.1); border: 1px solid rgba(66,165,245,.25); border-radius: var(--adm-radius); margin-bottom: 1.5rem; color: #42a5f5; font-size: .9rem; }
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
        <h1 class="adm-page-title">Filtro Avançado de Alertas</h1>
        <p class="adm-breadcrumb">Pesquise e filtre alertas de segurança com critérios detalhados</p>
    </div>
</div>

<!-- Formulário de Filtro -->
<div class="adm-card" style="margin-bottom:1.5rem;">
    <div class="adm-card-header">
        <h2 class="adm-card-title">Critérios de Filtro</h2>
    </div>
    <div style="padding:1rem;">
        <form method="GET" action="/administrator/alerts/filter">
            <div class="filter-grid">
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
                <div class="filter-group">
                    <label for="user_id">ID do Usuário</label>
                    <input type="number" name="user_id" id="user_id" placeholder="ID"
                           value="<?= (int)($filters['user_id'] ?? 0) ?: '' ?>" class="form-control">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:.75rem;">
                <button type="submit" class="btn btn-primary btn-sm">Aplicar Filtros</button>
                <a href="/administrator/alerts" class="btn btn-secondary btn-sm">Limpar Tudo</a>
            </div>
        </form>
    </div>
</div>

<!-- Contagem -->
<div class="result-count">
    Encontrados <strong><?= (int)$total ?></strong> alerta(s)
</div>

<!-- Tabela -->
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
                            Nenhum alerta encontrado com esses critérios
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
        <a href="?page=1<?= !empty($filters['event']) ? '&event=' . e($filters['event']) : '' ?>">Primeira</a>
        <a href="?page=<?= $page - 1 ?><?= !empty($filters['event']) ? '&event=' . e($filters['event']) : '' ?>">Anterior</a>
    <?php endif; ?>
    <span>Página <?= $page ?> de <?= $pages ?></span>
    <?php if ($page < $pages): ?>
        <a href="?page=<?= $page + 1 ?><?= !empty($filters['event']) ? '&event=' . e($filters['event']) : '' ?>">Próxima</a>
        <a href="?page=<?= $pages ?><?= !empty($filters['event']) ? '&event=' . e($filters['event']) : '' ?>">Última</a>
    <?php endif; ?>
</div>
<?php endif; ?>
