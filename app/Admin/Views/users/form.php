<?php
/** @var array|null $record */
/** @var array      $roles   All available roles [{id, name, key}] */
$isEdit     = !empty($record);
$action     = $isEdit ? '/administrator/users/' . (int)$record['id']  : '/administrator/users';
$title      = $isEdit ? 'Editar Usuário' : 'Novo Usuário';
$userRoles  = array_column($record['roles'] ?? [], 'id');
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><?= $title ?></h1>
        <p class="adm-breadcrumb"><a href="/administrator/users">Usuários</a> / <?= $title ?></p>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="adm-alert adm-alert-error">
    <i class="fas fa-circle-xmark"></i>
    <ul style="margin: 0; padding-left: 1rem;">
        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="adm-card" style="max-width: 720px;">
    <div class="adm-card-header"><h2 class="adm-card-title"><?= $title ?></h2></div>
    <div style="padding: 1.5rem;">
        <form method="POST" action="<?= $action ?>" class="adm-form" style="max-width: 100%;">
            <?= \App\Core\CSRF::field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nome <span style="color: var(--adm-danger);">*</span></label>
                    <input type="text" id="name" name="name" class="form-control"
                           value="<?= e($record['name'] ?? $_POST['name'] ?? '') ?>" required autocomplete="name"/>
                </div>
                <div class="form-group">
                    <label for="email">E-mail <span style="color: var(--adm-danger);">*</span></label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= e($record['email'] ?? $_POST['email'] ?? '') ?>" required autocomplete="email"/>
                </div>
            </div>

            <?php if (!$isEdit): ?>
            <div class="form-group">
                <label for="password">Senha <span style="color: var(--adm-danger);">*</span></label>
                <input type="password" id="password" name="password" class="form-control"
                       required minlength="8" autocomplete="new-password" placeholder="Mínimo 8 caracteres"/>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirmar Senha <span style="color: var(--adm-danger);">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                       required autocomplete="new-password"/>
            </div>
            <?php else: ?>
            <div class="form-group">
                <label for="password">Nova Senha <span style="color: var(--adm-muted);">(deixe em branco para não alterar)</span></label>
                <input type="password" id="password" name="password" class="form-control"
                       minlength="8" autocomplete="new-password" placeholder="••••••••"/>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Papéis (Roles)</label>
                <div class="check-group">
                    <?php foreach ($roles ?? [] as $role): ?>
                    <label class="check-item">
                        <input type="checkbox" name="roles[]" value="<?= (int)$role['id'] ?>"
                               <?= in_array($role['id'], $userRoles) ? 'checked' : '' ?>>
                        <?= e($role['name']) ?>
                        <small style="color: var(--adm-muted); font-size: 0.72rem;">(<?= e($role['key']) ?>)</small>
                    </label>
                    <?php endforeach; ?>
                    <?php if (empty($roles)): ?>
                    <span style="color: var(--adm-muted); font-size: 0.85rem;">Nenhum papel cadastrado ainda.</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="is_active">Status</label>
                <select id="is_active" name="is_active" class="form-control">
                    <option value="1" <?= !empty($record['is_active'] ?? 1) ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= empty($record['is_active'] ?? 1) ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Salvar' : 'Criar Usuário' ?></button>
                <a href="/administrator/users" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
