<?php
/** @var array $logs  Audit log rows: {id, admin_user_id, action, entity, entity_id, detail_json, ip, user_agent, created_at} */
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Log de Auditoria</h1>
        <p class="adm-breadcrumb">Registro de todas as ações administrativas</p>
    </div>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <h2 class="adm-card-title">
            <i class="fas fa-scroll" style="color: var(--adm-accent);"></i>
            Eventos Registrados (<?= (int)($total ?? count($logs ?? [])) ?>)
        </h2>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ação</th>
                    <th>Entidade</th>
                    <th>ID Entidade</th>
                    <th>Detalhes</th>
                    <th>IP</th>
                    <th>Data/Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td style="color: var(--adm-muted); font-size: 0.8rem;"><?= (int)$log['id'] ?></td>
                        <td>
                            <?php
                            $actionColors = [
                                'create' => 'success',
                                'update' => 'warning',
                                'delete' => 'danger',
                                'login'  => 'muted',
                                'logout' => 'muted',
                            ];
                            $badge = $actionColors[strtolower($log['action'] ?? '')] ?? 'muted';
                            ?>
                            <span class="badge badge-<?= $badge ?>"><?= e(strtoupper($log['action'] ?? '')) ?></span>
                        </td>
                        <td><?= e($log['entity'] ?? '—') ?></td>
                        <td style="color: var(--adm-muted); font-size: 0.82rem;"><?= e($log['entity_id'] ?? '—') ?></td>
                        <td>
                            <?php if (!empty($log['detail_json'])): ?>
                            <span class="detail-json" title="<?= e($log['detail_json']) ?>">
                                <?= e(mb_substr($log['detail_json'], 0, 60)) ?>...
                            </span>
                            <?php else: ?><span style="color: var(--adm-muted);">—</span><?php endif; ?>
                        </td>
                        <td style="font-family: monospace; font-size: 0.78rem; color: var(--adm-muted);">
                            <?= e($log['ip'] ?? '—') ?>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--adm-muted); white-space: nowrap;">
                            <?= e($log['created_at'] ? date('d/m/Y H:i:s', strtotime($log['created_at'])) : '—') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7" class="adm-empty">
                        <i class="fas fa-scroll"></i>
                        <p>Nenhum evento registrado.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<p style="color: var(--adm-muted); font-size: 0.8rem; margin-top: 0.5rem;">
    <i class="fas fa-lock"></i> Este log é somente leitura. Nenhuma ação pode ser executada aqui.
</p>
