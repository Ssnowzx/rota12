<?php
/** @var array|null $record */
$isEdit = !empty($record);
$action = $isEdit ? '/administrator/partners/' . (int)$record['id']  : '/administrator/partners';
$title  = $isEdit ? 'Editar Parceiro' : 'Novo Parceiro';
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><?= $title ?></h1>
        <p class="adm-breadcrumb"><a href="/administrator/partners">Parceiros</a> / <?= $title ?></p>
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
        <form method="POST" action="<?= $action ?>" enctype="multipart/form-data" class="adm-form" style="max-width: 100%;">
            <?= \App\Core\CSRF::field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nome <span style="color: var(--adm-danger);">*</span></label>
                    <input type="text" id="name" name="name" class="form-control"
                           value="<?= e($record['name'] ?? $_POST['name'] ?? '') ?>" required/>
                </div>
                <div class="form-group">
                    <label for="slug">Slug <span style="color: var(--adm-danger);">*</span></label>
                    <input type="text" id="slug" name="slug" class="form-control"
                           value="<?= e($record['slug'] ?? $_POST['slug'] ?? '') ?>" required placeholder="nome-parceiro"/>
                </div>
            </div>

            <div class="form-group">
                <label for="logo_path">Logo</label>
                <?php if ($isEdit && !empty($record['logo_path'])): ?>
                <div style="margin-bottom: 0.75rem;">
                    <img src="<?= e($record['logo_path']) ?>" alt="Logo atual"
                         style="height: 60px; border-radius: 4px; object-fit: contain;"/>
                </div>
                <?php endif; ?>
                <input type="file" id="logo_path" name="logo_path" class="form-control" accept="image/*"/>
            </div>

            <div class="form-group">
                <label for="website_url">URL do Site</label>
                <input type="url" id="website_url" name="website_url" class="form-control"
                       value="<?= e($record['website_url'] ?? $_POST['website_url'] ?? '') ?>" placeholder="https://..."/>
            </div>

            <div class="form-group">
                <label for="description">Descrição</label>
                <textarea id="description" name="description" class="form-control" rows="5"
                          placeholder="Descrição do parceiro..."><?= e($record['description'] ?? $_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="is_active">Status</label>
                <select id="is_active" name="is_active" class="form-control">
                    <option value="1" <?= !empty($record['is_active'] ?? 1) ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= empty($record['is_active'] ?? 1) ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Salvar' : 'Criar' ?></button>
                <a href="/administrator/partners" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
