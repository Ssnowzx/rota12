<?php /** @var array $records */ ?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Cidades</h1>
        <p class="adm-breadcrumb">Gerenciar cidades parceiras</p>
    </div>
    <a href="/administrator/cities/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nova Cidade
    </a>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <h2 class="adm-card-title">Todas as Cidades (<?= (int)($total ?? count($records ?? [])) ?>)</h2>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr><th>#</th><th>Imagem</th><th>Nome</th><th>Estado</th><th>Slug</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $row): ?>
                    <tr>
                        <td style="color: var(--adm-muted);"><?= (int)$row['id'] ?></td>
                        <td>
                            <?php if (!empty($row['image_path'])): ?>
                            <?php
                                $imgSrc = $row['image_path'];
                                if (!str_starts_with($imgSrc, '/')) {
                                    $imgSrc = '/uploads/' . $imgSrc;
                                }
                            ?>
                            <img src="<?= e($imgSrc) ?>" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;"/>
                            <?php else: ?><span style="color: var(--adm-muted);">—</span><?php endif; ?>
                        </td>
                        <td><strong><?= e($row['name']) ?></strong></td>
                        <td><?= e($row['state'] ?? '—') ?></td>
                        <td style="font-family: monospace; font-size: 0.8rem; color: var(--adm-muted);"><?= e($row['slug']) ?></td>
                        <td><?= ($row['is_active'] ?? 0) ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-muted">Inativo</span>' ?></td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;">
                                <a href="/administrator/cities/<?= (int)$row['id'] ?>/edit" class="btn btn-secondary btn-sm"><i class="fas fa-pencil"></i></a>
                                <form method="POST" action="/administrator/cities/<?= (int)$row['id'] ?>/delete" style="margin: 0;">
                                    <?= \App\Core\CSRF::field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Excluir esta cidade?"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="7" class="adm-empty"><i class="fas fa-city"></i><p>Nenhuma cidade cadastrada.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
