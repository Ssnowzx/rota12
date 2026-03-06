<?php
/** @var array  $records  Paginated pages */
/** @var int    $total    Total record count */
/** @var int    $page     Current page */
/** @var int    $perPage  Per page count */
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Páginas</h1>
        <p class="adm-breadcrumb">Gerenciar páginas de conteúdo</p>
    </div>
    <a href="/administrator/pages/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nova Página
    </a>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <h2 class="adm-card-title">Todas as Páginas (<?= (int)($total ?? count($records ?? [])) ?>)</h2>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Título</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $row): ?>
                    <tr>
                        <td style="color: var(--adm-muted);"><?= (int)$row['id'] ?></td>
                        <td><strong><?= e($row['title']) ?></strong></td>
                        <td style="font-family: monospace; font-size: 0.8rem; color: var(--adm-muted);"><?= e($row['slug']) ?></td>
                        <td>
                            <?php if ($row['status'] ?? 0): ?>
                                <span class="badge badge-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-muted">Rascunho</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size: 0.8rem; color: var(--adm-muted);">
                            <?= e($row['created_at'] ? date('d/m/Y', strtotime($row['created_at'])) : '—') ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;">
                                <a href="/administrator/pages/<?= (int)$row['id'] ?>/edit" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-pencil"></i>
                                </a>
                                <form method="POST" action="/administrator/pages/<?= (int)$row['id'] ?>/delete" style="margin: 0;">
                                    <?= \App\Core\CSRF::field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm"
                                            data-confirm="Excluir esta página?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="6" class="adm-empty">
                        <i class="fas fa-file-alt"></i>
                        <p>Nenhuma página cadastrada.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
