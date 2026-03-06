<?php /** @var array $records */ ?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Usuários</h1>
        <p class="adm-breadcrumb">Gerenciar usuários administradores</p>
    </div>
    <a href="/administrator/users/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Novo Usuário
    </a>
</div>

<div class="adm-card">
    <div class="adm-card-header">
        <h2 class="adm-card-title">Todos os Usuários (<?= (int)($total ?? count($records ?? [])) ?>)</h2>
    </div>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr><th>#</th><th>Nome</th><th>E-mail</th><th>Papéis</th><th>Status</th><th>Criado em</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php if (!empty($records)): ?>
                    <?php foreach ($records as $row): ?>
                    <tr>
                        <td style="color: var(--adm-muted);"><?= (int)$row['id'] ?></td>
                        <td><strong><?= e($row['name']) ?></strong></td>
                        <td style="color: var(--adm-muted); font-size: 0.85rem;"><?= e($row['email']) ?></td>
                        <td>
                            <?php foreach ($row['roles'] ?? [] as $role): ?>
                            <span class="badge badge-warning"><?= e($role['name'] ?? $role) ?></span>
                            <?php endforeach; ?>
                            <?php if (empty($row['roles'])): ?>
                            <span style="color: var(--adm-muted); font-size: 0.8rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= ($row['is_active'] ?? 0) ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-muted">Inativo</span>' ?></td>
                        <td style="font-size: 0.8rem; color: var(--adm-muted);"><?= e($row['created_at'] ? date('d/m/Y', strtotime($row['created_at'])) : '—') ?></td>
                        <td>
                            <div style="display: flex; gap: 0.4rem;">
                                <a href="/administrator/users/<?= (int)$row['id'] ?>/edit" class="btn btn-secondary btn-sm"><i class="fas fa-pencil"></i></a>
                                <form method="POST" action="/administrator/users/<?= (int)$row['id'] ?>/delete" style="margin: 0;">
                                    <?= \App\Core\CSRF::field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" data-confirm="Excluir este usuário?"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="7" class="adm-empty"><i class="fas fa-users"></i><p>Nenhum usuário cadastrado.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
