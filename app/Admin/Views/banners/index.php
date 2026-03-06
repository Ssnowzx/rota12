<?php
/** @var array $records */
/** @var int   $total */
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Banners</h1>
        <p class="adm-breadcrumb">Gerenciar banners do hero</p>
    </div>
    <a href="/administrator/banners/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Banner
    </a>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <h2 class="adm-card-title">Todos os Banners (<?= (int)($total ?? count($records ?? [])) ?>)</h2>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Imagem</th>
                    <th>Título</th>
                    <th>Posição</th>
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
                            <?php if (!empty($row['image_path'])): ?>
                            <img src="/uploads/<?= e($row['image_path']) ?>" alt="<?= e($row['title']) ?>"
                                 style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;"/>
                            <?php else: ?>
                            <span style="color: var(--adm-muted); font-size: 0.8rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= e($row['title']) ?></strong>
                            <?php if (!empty($row['subtitle'])): ?>
                            <br><small style="color: var(--adm-muted);"><?= e(mb_substr($row['subtitle'], 0, 50)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?= (int)($row['position'] ?? 0) ?></td>
                        <td>
                            <?php if ($row['is_active'] ?? 0): ?>
                                <span class="badge badge-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-muted">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;">
                                <a href="/administrator/banners/<?= (int)$row['id'] ?>/edit" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-pencil"></i>
                                </a>
                                <form method="POST" action="/administrator/banners/<?= (int)$row['id'] ?>/delete" style="margin: 0;">
                                    <?= \App\Core\CSRF::field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Excluir este banner?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="6" class="adm-empty"><i class="fas fa-images"></i><p>Nenhum banner cadastrado.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
