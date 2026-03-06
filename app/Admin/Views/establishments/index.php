<?php /** @var array $records */ ?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Estabelecimentos</h1>
        <p class="adm-breadcrumb">Gerenciar estabelecimentos parceiros</p>
    </div>
    <a href="/administrator/establishments/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Estabelecimento
    </a>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <h2 class="adm-card-title">Todos os Estabelecimentos (<?= (int)($total ?? count($records ?? [])) ?>)</h2>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr><th>#</th><th>Logo</th><th>Nome</th><th>Cidade</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $row): ?>
                    <tr>
                        <td style="color: var(--adm-muted);"><?= (int)$row['id'] ?></td>
                        <td>
                            <?php if (!empty($row['logo_path'])): ?>
                            <img src="/uploads/<?= e($row['logo_path']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"/>
                            <?php else: ?><span style="color: var(--adm-muted);">—</span><?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($row['name']) ?></strong>
                            <?php if (!empty($row['address'])): ?>
                            <br><small style="color: var(--adm-muted);"><?= e(mb_substr($row['address'], 0, 40)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($row['city_name'] ?? '—') ?></td>
                        <td><?= ($row['is_active'] ?? 0) ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-muted">Inativo</span>' ?></td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;">
                                <a href="/administrator/establishments/<?= (int)$row['id'] ?>/edit" class="btn btn-secondary btn-sm"><i class="fas fa-pencil"></i></a>
                                <form method="POST" action="/administrator/establishments/<?= (int)$row['id'] ?>/delete" style="margin: 0;">
                                    <?= \App\Core\CSRF::field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Excluir este estabelecimento?"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="6" class="adm-empty"><i class="fas fa-store"></i><p>Nenhum estabelecimento cadastrado.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
