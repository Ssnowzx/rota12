<?php
/** @var array|null $record */
$isEdit = !empty($record);
$action = $isEdit ? '/administrator/cities/' . (int)$record['id']  : '/administrator/cities';
$title  = $isEdit ? 'Editar Cidade' : 'Nova Cidade';
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><?= $title ?></h1>
        <p class="adm-breadcrumb"><a href="/administrator/cities">Cidades</a> / <?= $title ?></p>
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
                           value="<?= e($record['name'] ?? $_POST['name'] ?? '') ?>" required placeholder="São Joaquim"/>
                </div>
                <div class="form-group">
                    <label for="state">Estado</label>
                    <input type="text" id="state" name="state" class="form-control"
                           value="<?= e($record['state'] ?? $_POST['state'] ?? '') ?>" placeholder="SC"/>
                </div>
            </div>

            <div class="form-group">
                <label for="slug">Slug <span style="color: var(--adm-danger);">*</span></label>
                <input type="text" id="slug" name="slug" class="form-control"
                       value="<?= e($record['slug'] ?? $_POST['slug'] ?? '') ?>" required placeholder="sao-joaquim"/>
                <p class="form-hint">Usado na URL: /cidades/sao-joaquim</p>
            </div>

            <div class="form-group">
                <label for="image_path">Imagem da Cidade</label>
                <?php if ($isEdit && !empty($record['image_path'])): ?>
                <div style="margin-bottom: 0.75rem;">
                    <img src="<?= e($record['image_path']) ?>" alt="Cidade atual"
                         style="height: 80px; border-radius: 4px; object-fit: cover;"/>
                </div>
                <?php endif; ?>
                <input type="file" id="image_path" name="image_path" class="form-control" accept="image/*"/>
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
                <a href="/administrator/cities" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
