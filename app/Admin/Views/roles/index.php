<?php /** @var array $records */ ?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Papéis (Roles)</h1>
        <p class="adm-breadcrumb">Gerenciar papéis e permissões</p>
    </div>
    <a href="/administrator/roles/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Papel
    </a>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <h2 class="adm-card-title">Todos os Papéis (<?= (int)($total ?? count($records ?? [])) ?>)</h2>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr><th>#</th><th>Nome</th><th>Chave</th><th>Descrição</th><th>Permissões</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $row): ?>
                    <tr>
                        <td style="color: var(--adm-muted);"><?= (int)$row['id'] ?></td>
                        <td><strong><?= e($row['name']) ?></strong></td>
                        <td><code style="font-family: monospace; color: var(--adm-accent); font-size: 0.82rem;"><?= e($row['key']) ?></code></td>
                        <td style="color: var(--adm-muted); font-size: 0.85rem; max-width: 200px;">
                            <?= e(mb_substr($row['description'] ?? '', 0, 60)) ?>
                        </td>
                        <td>
                            <?php
                            $perms = is_array($row['permissions']) ? $row['permissions'] : json_decode($row['permissions'] ?? '[]', true);
                            if (!empty($perms)):
                                foreach (array_slice($perms, 0, 3) as $p):
                            ?>
                            <span class="badge badge-muted"><?= e($p) ?></span>
                            <?php endforeach;
                            if (count($perms) > 3):
                            ?>
                            <span class="badge badge-muted">+<?= count($perms) - 3 ?></span>
                            <?php endif;
                            else: ?>
                            <span style="color: var(--adm-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;">
                                <a href="/administrator/roles/<?= (int)$row['id'] ?>/edit" class="btn btn-secondary btn-sm"><i class="fas fa-pencil"></i></a>
                                <form method="POST" action="/administrator/roles/<?= (int)$row['id'] ?>/delete" style="margin: 0;">
                                    <?= \App\Core\CSRF::field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Excluir este papel?"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="6" class="adm-empty"><i class="fas fa-shield-halved"></i><p>Nenhum papel cadastrado.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
