<?php
/** @var array|null $record */
$isEdit = !empty($record);
$action = $isEdit ? '/administrator/highlights/' . (int)$record['id']  : '/administrator/highlights';
$title  = $isEdit ? 'Editar Destaque' : 'Novo Destaque';
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><?= $title ?></h1>
        <p class="adm-breadcrumb"><a href="/administrator/highlights">Destaques</a> / <?= $title ?></p>
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
                    <label for="title">Título <span style="color: var(--adm-danger);">*</span></label>
                    <input type="text" id="title" name="title" class="form-control"
                           value="<?= e($record['title'] ?? $_POST['title'] ?? '') ?>" required/>
                </div>
                <div class="form-group">
                    <label for="subtitle">Subtítulo</label>
                    <input type="text" id="subtitle" name="subtitle" class="form-control"
                           value="<?= e($record['subtitle'] ?? $_POST['subtitle'] ?? '') ?>"/>
                </div>
            </div>

            <div class="form-group">
                <label for="image_path">Imagem</label>
                <?php if ($isEdit && !empty($record['image_path'])): ?>
                <div style="margin-bottom: 0.75rem;">
                    <img src="/uploads/<?= e($record['image_path']) ?>" alt="Destaque atual"
                         style="height: 80px; border-radius: 4px; object-fit: cover;"/>
                </div>
                <?php endif; ?>
                <input type="file" id="image_path" name="image_path" class="form-control" accept="image/*"/>
            </div>

            <div class="form-group">
                <label for="link_url">URL do Link</label>
                <input type="text" id="link_url" name="link_url" class="form-control"
                       value="<?= e($record['link_url'] ?? $_POST['link_url'] ?? '') ?>" placeholder="/cupons"/>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="sort_order">Ordem de Exibição</label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control" min="0"
                           value="<?= (int)($record['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>"/>
                </div>
                <div class="form-group">
                    <label for="is_active">Status</label>
                    <select id="is_active" name="is_active" class="form-control">
                        <option value="1" <?= !empty($record['is_active'] ?? 1) ? 'selected' : '' ?>>Ativo</option>
                        <option value="0" <?= empty($record['is_active'] ?? 1) ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Salvar' : 'Criar' ?></button>
                <a href="/administrator/highlights" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
