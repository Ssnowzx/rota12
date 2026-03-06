<?php
/** @var array|null $record */
/** @var array      $establishments List [{id, name}] */
$isEdit = !empty($record);
$action = $isEdit ? '/administrator/coupons/' . (int)$record['id']  : '/administrator/coupons';
$title  = $isEdit ? 'Editar Cupom' : 'Novo Cupom';
?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title"><?= $title ?></h1>
        <p class="adm-breadcrumb"><a href="/administrator/coupons">Cupons</a> / <?= $title ?></p>
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
        <form method="POST" action="<?= $action ?>" class="adm-form" style="max-width: 100%;">
            <?= \App\Core\CSRF::field() ?>

            <div class="form-group">
                <label for="title">Título <span style="color: var(--adm-danger);">*</span></label>
                <input type="text" id="title" name="title" class="form-control"
                       value="<?= e($record['title'] ?? $_POST['title'] ?? '') ?>" required
                       placeholder="Ex: 15% OFF no Almoço"/>
            </div>

            <div class="form-group">
                <label for="description">Descrição</label>
                <textarea id="description" name="description" class="form-control" rows="4"
                          placeholder="Detalhes do cupom..."><?= e($record['description'] ?? $_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="code">Código do Cupom</label>
                    <input type="text" id="code" name="code" class="form-control"
                           value="<?= e($record['code'] ?? $_POST['code'] ?? '') ?>"
                           placeholder="ROTA12-2025" style="font-family: monospace; text-transform: uppercase;"/>
                </div>
                <div class="form-group">
                    <label for="establishment_id">Estabelecimento</label>
                    <select id="establishment_id" name="establishment_id" class="form-control">
                        <option value="">— Selecione —</option>
                        <?php foreach ($establishments ?? [] as $est): ?>
                        <option value="<?= (int)$est['id'] ?>"
                            <?= ($record['establishment_id'] ?? $_POST['establishment_id'] ?? '') == $est['id'] ? 'selected' : '' ?>>
                            <?= e($est['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="discount_type">Tipo de Desconto <span style="color: var(--adm-danger);">*</span></label>
                    <select id="discount_type" name="discount_type" class="form-control" required>
                        <option value="percentage" <?= ($record['discount_type'] ?? 'percentage') === 'percentage' ? 'selected' : '' ?>>Porcentagem (%)</option>
                        <option value="fixed"      <?= ($record['discount_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Valor fixo (R$)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="discount_value">Valor do Desconto <span style="color: var(--adm-danger);">*</span></label>
                    <input type="number" id="discount_value" name="discount_value" class="form-control"
                           value="<?= e($record['discount_value'] ?? $_POST['discount_value'] ?? '') ?>"
                           min="0" step="0.01" required placeholder="15"/>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="valid_from">Válido de</label>
                    <input type="date" id="valid_from" name="valid_from" class="form-control"
                           value="<?= e(isset($record['valid_from']) ? date('Y-m-d', strtotime($record['valid_from'])) : ($_POST['valid_from'] ?? '')) ?>"/>
                </div>
                <div class="form-group">
                    <label for="valid_until">Válido até</label>
                    <input type="date" id="valid_until" name="valid_until" class="form-control"
                           value="<?= e(isset($record['valid_until']) ? date('Y-m-d', strtotime($record['valid_until'])) : ($_POST['valid_until'] ?? '')) ?>"/>
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
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Salvar' : 'Criar' ?></button>
                <a href="/administrator/coupons" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
