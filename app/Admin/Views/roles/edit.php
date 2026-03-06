<?php
/** @var array|null $record */
$isEdit = !empty($record);
$action = $isEdit ? '/administrator/roles/' . (int)$record['id']  : '/administrator/roles';
$title  = $isEdit ? 'Editar Papel' : 'Novo Papel';

// All available permissions in the system
$allPermissions = [
    'pages.view', 'pages.create', 'pages.edit', 'pages.delete',
    'banners.view', 'banners.create', 'banners.edit', 'banners.delete',
    'highlights.view', 'highlights.create', 'highlights.edit', 'highlights.delete',
    'cities.view', 'cities.create', 'cities.edit', 'cities.delete',
    'partners.view', 'partners.create', 'partners.edit', 'partners.delete',
    'establishments.view', 'establishments.create', 'establishments.edit', 'establishments.delete',
    'coupons.view', 'coupons.create', 'coupons.edit', 'coupons.delete',
    'users.view', 'users.create', 'users.edit', 'users.delete',
    'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
    'audit.view',
];

$existingPerms = [];
if ($isEdit) {
    $raw = $record['permissions'] ?? [];
    $existingPerms = is_array($raw) ? $raw : json_decode($raw, true) ?? [];
}
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><?= $title ?></h1>
        <p class="adm-breadcrumb"><a href="/administrator/roles">Papéis</a> / <?= $title ?></p>
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
                           value="<?= e($record['name'] ?? $_POST['name'] ?? '') ?>" required
                           placeholder="Administrador"/>
                </div>
                <div class="form-group">
                    <label for="key">Chave (key) <span style="color: var(--adm-danger);">*</span></label>
                    <input type="text" id="key" name="key" class="form-control"
                           value="<?= e($record['key'] ?? $_POST['key'] ?? '') ?>" required
                           placeholder="admin" style="font-family: monospace;"/>
                    <p class="form-hint">Identificador único, ex: admin, editor, moderator</p>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Descrição</label>
                <textarea id="description" name="description" class="form-control" rows="3"
                          placeholder="Descreva as responsabilidades deste papel..."><?= e($record['description'] ?? $_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Permissões</label>
                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <button type="button" onclick="setAllPerms(true)" class="btn btn-secondary btn-sm">Selecionar tudo</button>
                    <button type="button" onclick="setAllPerms(false)" class="btn btn-secondary btn-sm">Limpar tudo</button>
                </div>
                <?php
                // Group permissions by entity
                $grouped = [];
                foreach ($allPermissions as $perm) {
                    [$entity] = explode('.', $perm);
                    $grouped[$entity][] = $perm;
                }
                ?>
                <?php foreach ($grouped as $entity => $perms): ?>
                <div style="margin-bottom: 1rem;">
                    <div style="font-size: 0.72rem; font-weight: 700; color: var(--adm-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.4rem;">
                        <?= e(ucfirst($entity)) ?>
                    </div>
                    <div class="check-group">
                        <?php foreach ($perms as $perm): ?>
                        <label class="check-item">
                            <input type="checkbox" name="permissions[]" value="<?= e($perm) ?>"
                                   class="perm-checkbox"
                                   <?= in_array($perm, $existingPerms) ? 'checked' : '' ?>>
                            <?= e(explode('.', $perm)[1]) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Salvar' : 'Criar Papel' ?></button>
                <a href="/administrator/roles" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
function setAllPerms(checked) {
    document.querySelectorAll('.perm-checkbox').forEach(function(cb) {
        cb.checked = checked;
    });
}
</script>
