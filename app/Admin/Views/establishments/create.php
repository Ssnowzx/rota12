<?php
/** @var array|null $record */
/** @var array      $cities  List of cities for select [{id, name}] */
$isEdit = !empty($record);
$action = $isEdit ? '/administrator/establishments/' . (int)$record['id']  : '/administrator/establishments';
$title  = $isEdit ? 'Editar Estabelecimento' : 'Novo Estabelecimento';
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><?= $title ?></h1>
        <p class="adm-breadcrumb"><a href="/administrator/establishments">Estabelecimentos</a> / <?= $title ?></p>
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

<div class="adm-card" style="max-width: 860px;">
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
                           value="<?= e($record['slug'] ?? $_POST['slug'] ?? '') ?>" required/>
                </div>
            </div>

            <div class="form-group">
                <label for="city_id">Cidade <span style="color: var(--adm-danger);">*</span></label>
                <select id="city_id" name="city_id" class="form-control" required>
                    <option value="">— Selecione uma cidade —</option>
                    <?php foreach ($cities ?? [] as $city): ?>
                    <option value="<?= (int)$city['id'] ?>"
                        <?= ($record['city_id'] ?? $_POST['city_id'] ?? '') == $city['id'] ? 'selected' : '' ?>>
                        <?= e($city['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="logo_path">Logo</label>
                    <?php if ($isEdit && !empty($record['logo_path'])): ?>
                    <div style="margin-bottom: 0.5rem;">
                        <img src="<?= e($record['logo_path']) ?>" alt="" style="height: 50px; object-fit: contain;"/>
                    </div>
                    <?php endif; ?>
                    <input type="file" id="logo_path" name="logo_path" class="form-control" accept="image/*"/>
                </div>
                <div class="form-group">
                    <label for="cover_path">Foto de Capa</label>
                    <?php if ($isEdit && !empty($record['cover_path'])): ?>
                    <div style="margin-bottom: 0.5rem;">
                        <img src="<?= e($record['cover_path']) ?>" alt="" style="height: 50px; object-fit: cover;"/>
                    </div>
                    <?php endif; ?>
                    <input type="file" id="cover_path" name="cover_path" class="form-control" accept="image/*"/>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Endereço</label>
                <input type="text" id="address" name="address" class="form-control"
                       value="<?= e($record['address'] ?? $_POST['address'] ?? '') ?>"
                       placeholder="Rua, número, bairro, cidade"/>
            </div>

            <div class="form-group">
                <label for="description">Descrição</label>
                <textarea id="description" name="description" class="form-control" rows="6"
                          placeholder="Descreva o estabelecimento..."><?= e($record['description'] ?? $_POST['description'] ?? '') ?></textarea>
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
                <a href="/administrator/establishments" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
