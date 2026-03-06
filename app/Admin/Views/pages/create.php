<?php
/** @var array|null $record  Existing record for edit (null on create) */
$isEdit  = !empty($record);
$action  = $isEdit ? '/administrator/pages/' . (int)$record['id']  : '/administrator/pages';
$title   = $isEdit ? 'Editar Página' : 'Nova Página';
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><?= $title ?></h1>
        <p class="adm-breadcrumb"><a href="/administrator/pages">Páginas</a> / <?= $title ?></p>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="adm-alert adm-alert-error">
    <i class="fas fa-circle-xmark"></i>
    <ul style="margin: 0; padding-left: 1rem;">
        <?php foreach ($errors as $err): ?>
        <li><?= e($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="adm-card" style="max-width: 860px;">
    <div class="adm-card-header">
        <h2 class="adm-card-title"><?= $title ?></h2>
    </div>
    <div style="padding: 1.5rem;">
        <form method="POST" action="<?= $action ?>" class="adm-form" style="max-width: 100%;">
            <?= \App\Core\CSRF::field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label for="title">Título <span style="color: var(--adm-danger);">*</span></label>
                    <input type="text" id="title" name="title" class="form-control"
                           value="<?= e($record['title'] ?? $_POST['title'] ?? '') ?>"
                           required placeholder="Título da página"/>
                </div>
                <div class="form-group">
                    <label for="slug">Slug <span style="color: var(--adm-danger);">*</span></label>
                    <input type="text" id="slug" name="slug" class="form-control"
                           value="<?= e($record['slug'] ?? $_POST['slug'] ?? '') ?>"
                           required placeholder="minha-pagina"/>
                    <p class="form-hint">URL amigável, ex: sobre-nos</p>
                </div>
            </div>

            <div class="form-group">
                <label for="content_html">Conteúdo</label>
                <textarea id="content_html" name="content_html" class="form-control"
                          rows="14" placeholder="Conteúdo HTML da página..."><?= e($record['content_html'] ?? $_POST['content_html'] ?? '') ?></textarea>
                <p class="form-hint">HTML permitido. O conteúdo será sanitizado ao salvar.</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="meta_title">Meta Título</label>
                    <input type="text" id="meta_title" name="meta_title" class="form-control"
                           value="<?= e($record['meta_title'] ?? $_POST['meta_title'] ?? '') ?>"
                           placeholder="SEO title"/>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="0" <?= empty($record['status'] ?? 1) ? 'selected' : '' ?>>Rascunho</option>
                        <option value="1" <?= !empty($record['status'] ?? 0) ? 'selected' : '' ?>>Ativo</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="meta_description">Meta Descrição</label>
                <textarea id="meta_description" name="meta_description" class="form-control"
                          rows="3" placeholder="Descrição para SEO..."><?= e($record['meta_description'] ?? $_POST['meta_description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= $isEdit ? 'Salvar Alterações' : 'Criar Página' ?>
                </button>
                <a href="/administrator/pages" class="btn btn-secondary">Cancelar</a>
                <?php if ($isEdit): ?>
                <a href="/<?= e($record['slug']) ?>" target="_blank" class="btn btn-secondary" style="margin-left: auto;">
                    <i class="fas fa-arrow-up-right-from-square"></i> Ver no Site
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
