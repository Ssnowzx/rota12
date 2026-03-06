<?php /** @var array $records */ ?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Campanhas de Cupom</h1>
        <p class="adm-breadcrumb">Gerenciar campanhas de desconto criadas pelos parceiros</p>
    </div>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <h2 class="adm-card-title">Todas as Campanhas (<?= (int)($total ?? count($records ?? [])) ?>)</h2>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Parceiro</th>
                    <th>Categoria</th>
                    <th>Desconto</th>
                    <th>Gerados</th>
                    <th>Resgatados</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $row): ?>
                    <tr>
                        <td style="color: var(--adm-muted);"><?= (int)$row['id'] ?></td>
                        <td>
                            <strong><?= e($row['titulo']) ?></strong>
                            <?php if ($row['exige_assinatura']): ?>
                            <span class="badge badge-warning" style="font-size:0.65rem;">Assinantes</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 0.85rem; color: var(--adm-muted);">
                            <?= e($row['partner_username'] ?? '—') ?>
                        </td>
                        <td style="font-size: 0.85rem;"><?= e($row['categoria'] ?? '—') ?></td>
                        <td>
                            <span class="badge badge-success">
                                <?php if ($row['tipo_desconto'] === 'percentual'): ?>
                                    <?= e($row['valor_desconto']) ?>%
                                <?php else: ?>
                                    R$ <?= e(number_format((float)$row['valor_desconto'], 2, ',', '.')) ?>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td style="text-align:center;"><?= (int)($row['total_gerados'] ?? 0) ?></td>
                        <td style="text-align:center;"><?= (int)($row['total_resgatados'] ?? 0) ?></td>
                        <td>
                            <?php
                            $statusColors = ['ativa' => 'success', 'pausada' => 'warning', 'encerrada' => 'danger', 'rascunho' => 'muted'];
                            $badge = $statusColors[$row['status']] ?? 'muted';
                            ?>
                            <span class="badge badge-<?= $badge ?>"><?= e(ucfirst($row['status'])) ?></span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;">
                                <a href="/administrator/coupons/<?= (int)$row['id'] ?>/edit" class="btn btn-secondary btn-sm"><i class="fas fa-pencil"></i></a>
                                <form method="POST" action="/administrator/coupons/<?= (int)$row['id'] ?>/delete" style="margin: 0;">
                                    <?= \App\Core\CSRF::field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Excluir esta campanha?"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="9" class="adm-empty"><i class="fas fa-ticket-alt"></i><p>Nenhuma campanha cadastrada.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
