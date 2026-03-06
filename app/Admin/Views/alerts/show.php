<?php
declare(strict_types=1);

/**
 * Alert Details View
 * Detalhes de um alerta específico
 *
 * @var array $alert
 * @var array $context
 * @var array $relatedByIP
 */
?>
<style>
.alert-detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
@media(max-width:768px){ .alert-detail-grid { grid-template-columns: 1fr; } }
.detail-row { display: flex; justify-content: space-between; padding: .6rem 0; border-bottom: 1px solid var(--adm-border); font-size: .9rem; }
.detail-row:last-child { border-bottom: none; }
.detail-label { color: var(--adm-muted); font-weight: 600; font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; }
.detail-value { color: var(--adm-text); }
.context-block { background: var(--adm-surface-2); border: 1px solid var(--adm-border); border-radius: 6px; padding: 1rem; overflow-x: auto; }
.context-block pre { margin: 0; font-size: .8rem; color: var(--adm-text); white-space: pre-wrap; word-break: break-all; }
.sev-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: .75rem; font-weight: 600; }
.sev-badge.critical { background: rgba(255,82,82,.15); color: var(--adm-danger); }
.sev-badge.high { background: rgba(255,215,64,.15); color: var(--adm-warning); }
.sev-badge.medium { background: rgba(66,165,245,.15); color: #42a5f5; }
.sev-badge.low { background: rgba(105,240,174,.15); color: var(--adm-success); }
.link-accent { color: var(--adm-accent); text-decoration: none; }
.link-accent:hover { text-decoration: underline; }
.timeline-item { border-left: 2px solid var(--adm-border); padding-left: 1rem; padding-bottom: 1rem; margin-bottom: .5rem; }
.timeline-item.triggered { border-left-color: #42a5f5; }
.timeline-item.acknowledged { border-left-color: var(--adm-success); }
.timeline-item.pending { border-left-color: var(--adm-warning); }
.related-item { display: flex; align-items: center; justify-content: space-between; padding: .6rem .75rem; border: 1px solid var(--adm-border); border-radius: 6px; margin-bottom: .5rem; text-decoration: none; color: var(--adm-text); transition: background .15s; }
.related-item:hover { background: var(--adm-surface-2); }
</style>

<div class="adm-page-header">
    <div>
        <a href="/administrator/alerts" class="link-accent" style="font-size:.85rem;">&#8592; Voltar aos Alertas</a>
        <h1 class="adm-page-title" style="margin-top:.5rem;">Detalhes do Alerta</h1>
        <p class="adm-breadcrumb">ID: #<?= (int)$alert['id'] ?></p>
    </div>
</div>

<div class="alert-detail-grid">
    <!-- Coluna Principal -->
    <div>
        <!-- Informações do Alerta -->
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header">
                <h2 class="adm-card-title"><?= e($alert['event']) ?></h2>
                <span class="sev-badge <?= e($alert['severity'] ?? 'medium') ?>"><?= e($alert['severity']) ?></span>
            </div>
            <div style="padding:1rem;">
                <div class="detail-row">
                    <span class="detail-label">IP de Origem</span>
                    <span class="detail-value" style="font-family:monospace;">
                        <a href="/administrator/alerts/by-ip/<?= e($alert['ip_address']) ?>" class="link-accent">
                            <?= e($alert['ip_address'] ?? 'unknown') ?>
                        </a>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Usuário Admin</span>
                    <span class="detail-value">
                        <?php if (!empty($alert['admin_name'])): ?>
                            <a href="/administrator/alerts/by-user/<?= (int)$alert['admin_user_id'] ?>" class="link-accent">
                                <?= e($alert['admin_name']) ?> (ID: <?= (int)$alert['admin_user_id'] ?>)
                            </a>
                        <?php else: ?>
                            <span style="color:var(--adm-muted);">-</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tipo de Evento</span>
                    <span class="detail-value">
                        <a href="/administrator/alerts/by-event/<?= e($alert['event']) ?>" class="link-accent">
                            <?= e($alert['event']) ?>
                        </a>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Data/Hora</span>
                    <span class="detail-value"><?= date('d/m/Y H:i:s', strtotime((string)($alert['triggered_at'] ?? ''))) ?></span>
                </div>
            </div>
        </div>

        <!-- Contexto -->
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header">
                <h2 class="adm-card-title">Contexto do Evento</h2>
            </div>
            <div style="padding:1rem;">
                <?php if (empty($context)): ?>
                    <p style="color:var(--adm-muted);">Nenhum contexto adicional disponível</p>
                <?php else: ?>
                    <div class="context-block">
                        <pre><?= htmlspecialchars(json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8') ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ações -->
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header">
                <h2 class="adm-card-title">Ações</h2>
            </div>
            <div style="padding:1rem;display:flex;flex-wrap:wrap;gap:.5rem;">
                <?php if (!empty($alert['ip_address']) && $alert['ip_address'] !== 'unknown'): ?>
                    <form method="POST" action="/administrator/alerts/<?= (int)$alert['id'] ?>/block-ip"
                          style="display:inline;" onsubmit="return confirm('Bloquear este IP?');">
                        <button type="submit" class="btn btn-danger btn-sm">Bloquear IP</button>
                    </form>
                <?php endif; ?>
                <?php if (!empty($alert['admin_user_id'])): ?>
                    <form method="POST" action="/administrator/alerts/<?= (int)$alert['id'] ?>/disable-user"
                          style="display:inline;" onsubmit="return confirm('Desativar esta conta de usuário?');">
                        <button type="submit" class="btn btn-danger btn-sm">Desativar Usuário</button>
                    </form>
                <?php endif; ?>
                <a href="/administrator/alerts" class="btn btn-secondary btn-sm">Voltar</a>
            </div>
        </div>

        <!-- Alertas Relacionados por IP -->
        <?php if (!empty($relatedByIP)): ?>
        <div class="adm-card">
            <div class="adm-card-header">
                <h2 class="adm-card-title">Outros Alertas de <?= e($alert['ip_address']) ?></h2>
            </div>
            <div style="padding:.75rem;">
                <?php foreach ($relatedByIP as $related): ?>
                    <a href="/administrator/alerts/<?= (int)$related['id'] ?>" class="related-item">
                        <div>
                            <div style="font-weight:600;font-size:.9rem;"><?= e($related['event']) ?></div>
                            <div style="font-size:.8rem;color:var(--adm-muted);"><?= date('d/m/Y H:i:s', strtotime((string)($related['triggered_at'] ?? ''))) ?></div>
                        </div>
                        <span class="sev-badge <?= e($related['severity'] ?? 'medium') ?>"><?= e($related['severity']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Timeline -->
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header">
                <h2 class="adm-card-title">Timeline</h2>
            </div>
            <div style="padding:1rem;">
                <div class="timeline-item triggered">
                    <div style="font-size:.8rem;color:var(--adm-muted);">Disparado</div>
                    <div style="font-family:monospace;font-size:.85rem;">
                        <?= date('d/m/Y H:i:s', strtotime((string)($alert['triggered_at'] ?? ''))) ?>
                    </div>
                </div>
                <?php if (!empty($alert['acknowledged_at'])): ?>
                    <div class="timeline-item acknowledged">
                        <div style="font-size:.8rem;color:var(--adm-muted);">Reconhecido</div>
                        <div style="font-family:monospace;font-size:.85rem;">
                            <?= date('d/m/Y H:i:s', strtotime((string)($alert['acknowledged_at'] ?? ''))) ?>
                        </div>
                        <?php if (!empty($alert['acknowledged_by'])): ?>
                            <div style="font-size:.75rem;color:var(--adm-muted);margin-top:.25rem;">por User ID <?= (int)$alert['acknowledged_by'] ?></div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="timeline-item pending">
                        <div style="font-size:.8rem;color:var(--adm-warning);font-weight:600;">Não reconhecido</div>
                        <div style="font-size:.75rem;color:var(--adm-muted);">Pendente de revisão</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info do IP -->
        <?php if (!empty($alert['ip_address'])): ?>
        <div class="adm-card" style="margin-bottom:1.5rem;">
            <div class="adm-card-header">
                <h2 class="adm-card-title">Informações do IP</h2>
            </div>
            <div style="padding:1rem;">
                <div class="detail-row">
                    <span class="detail-label">Endereço</span>
                    <span class="detail-value" style="font-family:monospace;"><?= e($alert['ip_address']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tipo</span>
                    <span class="detail-value">
                        <?php
                        if (filter_var($alert['ip_address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                            echo 'IPv4';
                        } elseif (filter_var($alert['ip_address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                            echo 'IPv6';
                        } else {
                            echo 'Desconhecido';
                        }
                        ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Alertas</span>
                    <span class="detail-value"><?= count($relatedByIP) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Classificação -->
        <div class="adm-card">
            <div class="adm-card-header">
                <h2 class="adm-card-title">Classificação</h2>
            </div>
            <div style="padding:1rem;">
                <div class="detail-row">
                    <span class="detail-label">Evento</span>
                    <span class="detail-value"><?= e($alert['event']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Severidade</span>
                    <span class="sev-badge <?= e($alert['severity'] ?? 'medium') ?>"><?= e($alert['severity']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
