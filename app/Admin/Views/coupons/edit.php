<?php /** @var array $record */ ?>

<div class="adm-page-header">
    <div>
        <h1 class="adm-page-title">Editar Campanha</h1>
        <p class="adm-breadcrumb">
            <a href="/administrator/coupons" style="color: var(--adm-accent); text-decoration: none;">Campanhas</a> /
            <?= e($record['titulo']) ?>
        </p>
    </div>
</div>

<div class="adm-card" style="max-width: 700px;">
    <div class="adm-card-header">
        <h2 class="adm-card-title">Detalhes da Campanha</h2>
    </div>
    <form method="POST" action="/administrator/coupons/<?= (int)$record['id'] ?>" style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
        <?= \App\Core\CSRF::field() ?>

        <div class="form-group">
            <label for="titulo">Título *</label>
            <input type="text" id="titulo" name="titulo" class="form-control" value="<?= e($record['titulo']) ?>" required/>
        </div>

        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" class="form-control" rows="3"><?= e($record['descricao'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="categoria">Categoria</label>
            <input type="text" id="categoria" name="categoria" class="form-control" value="<?= e($record['categoria'] ?? '') ?>"/>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="tipo_desconto">Tipo de Desconto</label>
                <select id="tipo_desconto" name="tipo_desconto" class="form-control">
                    <option value="percentual" <?= ($record['tipo_desconto'] === 'percentual') ? 'selected' : '' ?>>Percentual (%)</option>
                    <option value="valor_fixo" <?= ($record['tipo_desconto'] === 'valor_fixo') ? 'selected' : '' ?>>Valor Fixo (R$)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="valor_desconto">Valor do Desconto</label>
                <input type="number" id="valor_desconto" name="valor_desconto" class="form-control" min="0" step="0.01" value="<?= e($record['valor_desconto']) ?>"/>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="exige_assinatura">Exige Assinatura?</label>
                <select id="exige_assinatura" name="exige_assinatura" class="form-control">
                    <option value="1" <?= $record['exige_assinatura'] ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= !$record['exige_assinatura'] ? 'selected' : '' ?>>Não</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="ativa" <?= ($record['status'] === 'ativa') ? 'selected' : '' ?>>Ativa</option>
                    <option value="pausada" <?= ($record['status'] === 'pausada') ? 'selected' : '' ?>>Pausada</option>
                    <option value="encerrada" <?= ($record['status'] === 'encerrada') ? 'selected' : '' ?>>Encerrada</option>
                    <option value="rascunho" <?= ($record['status'] === 'rascunho') ? 'selected' : '' ?>>Rascunho</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="data_fim">Data de Término</label>
            <input type="date" id="data_fim" name="data_fim" class="form-control" value="<?= e($record['data_fim'] ?? '') ?>"/>
        </div>

        <div style="display: flex; gap: 0.75rem; padding-top: 0.5rem;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Alterações</button>
            <a href="/administrator/coupons" class="btn btn-secondary">Cancelar</a>
        </div>

        <div style="margin-top: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 8px; font-size: 0.82rem; color: var(--adm-muted);">
            <strong>Info:</strong> Criada por <?= e($record['partner_username'] ?? '—') ?> (<?= e($record['partner_email'] ?? '') ?>)
            em <?= e($record['created_at'] ? date('d/m/Y H:i', strtotime($record['created_at'])) : '—') ?>
        </div>
    </form>
</div>
