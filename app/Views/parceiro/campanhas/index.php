<?php
/**
 * Partner Campaigns View
 * @var array  $campanhas
 * @var string $filterStatus
 * @var string $csrf
 */
?>
<style>
.camp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; margin-top: 1.5rem; }
.camp-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; position: relative; }
.camp-card-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem; }
.camp-title { font-size: 1rem; font-weight: 700; line-height: 1.3; }
.camp-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin: 1rem 0; }
.camp-meta-item { font-size: 0.78rem; color: var(--muted); }
.camp-meta-item strong { display: block; color: var(--text); font-size: 0.88rem; }
.camp-actions { display: flex; gap: 0.5rem; margin-top: 1.25rem; border-top: 1px solid var(--border); padding-top: 1rem; }
.camp-discount { font-size: 1.5rem; font-weight: 900; color: var(--accent); }
.camp-discount-type { font-size: 0.72rem; color: var(--muted); }
.filter-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
.filter-tab { padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.82rem; font-weight: 600; text-decoration: none; color: var(--muted); background: rgba(255,255,255,0.05); border: 1px solid var(--border); transition: all 0.15s; }
.filter-tab:hover, .filter-tab.active { background: var(--accent); color: #111; border-color: var(--accent); }
.page-actions { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-actions h2 { font-size: 1.1rem; font-weight: 700; }
</style>

<!-- Page Header -->
<div class="page-actions">
    <h2>Minhas Campanhas</h2>
    <button onclick="document.getElementById('modalCriar').classList.add('open')" class="pn-btn pn-btn-primary">
        <i class="fas fa-plus"></i> Nova Campanha
    </button>
</div>

<!-- Filter Tabs -->
<div class="filter-tabs">
    <?php
    $tabs = ['todas' => 'Todas', 'ativa' => 'Ativa', 'pausada' => 'Pausada', 'encerrada' => 'Encerrada'];
    foreach ($tabs as $val => $label):
        $active = ($filterStatus === $val) ? 'active' : '';
    ?>
    <a href="/parceiro/campanhas?status=<?= e($val) ?>" class="filter-tab <?= $active ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>

<!-- Campaigns Grid -->
<?php if (empty($campanhas)): ?>
<div style="text-align:center;padding:4rem 2rem;color:var(--muted);">
    <i class="fas fa-tags" style="font-size:3rem;margin-bottom:1rem;display:block;"></i>
    <p style="font-size:1.1rem;margin-bottom:0.5rem;">Nenhuma campanha encontrada.</p>
    <p style="font-size:0.85rem;">Crie sua primeira campanha para começar a oferecer descontos.</p>
</div>
<?php else: ?>
<div class="camp-grid">
    <?php foreach ($campanhas as $c): ?>
    <div class="camp-card">
        <?php if (!empty($c['image_path'])): ?>
        <div style="margin:-1.5rem -1.5rem 1rem -1.5rem;border-radius:12px 12px 0 0;overflow:hidden;height:140px;background:#f0f0f0;">
            <img src="/uploads/<?= e($c['image_path']) ?>" alt="<?= e($c['titulo']) ?>" style="width:100%;height:100%;object-fit:cover;"/>
        </div>
        <?php endif; ?>

        <div class="camp-card-header">
            <div>
                <div class="camp-title"><?= e($c['titulo']) ?></div>
                <?php if ($c['categoria']): ?>
                <div style="font-size:0.75rem;color:var(--muted);margin-top:0.25rem;"><?= e($c['categoria']) ?></div>
                <?php endif; ?>
            </div>
            <?php
            $badgeCls = match ($c['status']) {
                'ativa'     => 'pn-badge-green',
                'pausada'   => 'pn-badge-yellow',
                'encerrada' => 'pn-badge-red',
                default     => 'pn-badge-gray',
            };
            $statusLabel = match ($c['status']) {
                'ativa'     => 'Ativa',
                'pausada'   => 'Pausada',
                'encerrada' => 'Encerrada',
                'rascunho'  => 'Rascunho',
                default     => ucfirst($c['status']),
            };
            ?>
            <span class="pn-badge <?= $badgeCls ?>"><?= $statusLabel ?></span>
        </div>

        <!-- Discount -->
        <div style="display:flex;align-items:baseline;gap:0.3rem;">
            <span class="camp-discount">
                <?= $c['tipo_desconto'] === 'percentual' ? number_format((float)$c['valor_desconto'], 0) . '%' : 'R$ ' . number_format((float)$c['valor_desconto'], 2, ',', '.') ?>
            </span>
            <span class="camp-discount-type"><?= $c['tipo_desconto'] === 'percentual' ? 'de desconto' : 'de desconto fixo' ?></span>
        </div>

        <!-- Meta -->
        <div class="camp-meta">
            <div class="camp-meta-item">
                <strong><?= (int)$c['total_gerados'] ?></strong>
                cupons gerados
            </div>
            <div class="camp-meta-item">
                <strong><?= (int)$c['total_resgatados'] ?></strong>
                resgates
            </div>
            <div class="camp-meta-item">
                <strong><?= $c['limite_total'] !== null ? (int)$c['limite_total'] : '∞' ?></strong>
                limite total
            </div>
            <div class="camp-meta-item">
                <strong><?= date('d/m/Y', strtotime($c['data_inicio'])) ?></strong>
                início
            </div>
        </div>

        <!-- Actions -->
        <div class="camp-actions">
            <form method="POST" action="/parceiro/campanhas/<?= (int)$c['id'] ?>/status" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"/>
                <button type="submit" class="pn-btn pn-btn-ghost pn-btn-sm">
                    <?php if ($c['status'] === 'ativa'): ?>
                        <i class="fas fa-pause"></i> Pausar
                    <?php else: ?>
                        <i class="fas fa-play"></i> Ativar
                    <?php endif; ?>
                </button>
            </form>
            <form method="POST" action="/parceiro/campanhas/<?= (int)$c['id'] ?>/delete" style="margin:0;"
                  onsubmit="return confirm('Encerrar esta campanha?')">
                <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"/>
                <button type="submit" class="pn-btn pn-btn-danger pn-btn-sm">
                    <i class="fas fa-trash"></i> Excluir
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Create Campaign Modal -->
<div id="modalCriar" class="pn-modal-overlay">
    <div class="pn-modal">
        <button class="pn-modal-close" onclick="document.getElementById('modalCriar').classList.remove('open')">
            <i class="fas fa-times"></i>
        </button>
        <div class="pn-modal-title"><i class="fas fa-plus" style="color:var(--accent);margin-right:0.5rem;"></i>Nova Campanha</div>

        <form method="POST" action="/parceiro/campanhas" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"/>

            <div class="pn-form-group">
                <label>Título da Campanha *</label>
                <input type="text" name="titulo" class="pn-form-control" placeholder="Ex: 20% Off no Almoço" required/>
            </div>

            <div class="pn-form-group">
                <label>Descrição</label>
                <textarea name="descricao" class="pn-form-control" rows="2" placeholder="Descreva o benefício..."></textarea>
            </div>

            <div class="pn-form-group">
                <label>Imagem/Banner da Campanha</label>
                <input type="file" name="image" class="pn-form-control" accept=".jpg,.jpeg,.png,.gif,.webp" id="imageInput"/>
                <small style="color:var(--muted);font-size:0.75rem;">Formatos: JPG, PNG, GIF, WebP. Tamanho máximo: 3 MB.</small>
                <div id="imageError" style="display:none;margin-top:0.5rem;padding:0.5rem 0.75rem;background:rgba(255,60,60,0.12);border:1px solid rgba(255,60,60,0.3);border-radius:8px;color:#ff4444;font-size:0.82rem;"></div>
                <div id="imagePreview" style="margin-top:0.75rem;"></div>
            </div>

            <div class="pn-form-row">
                <div class="pn-form-group">
                    <label>Categoria</label>
                    <select name="categoria" class="pn-form-control">
                        <option value="">Selecione...</option>
                        <option>Restaurante</option>
                        <option>Hospedagem</option>
                        <option>Bebidas</option>
                        <option>Aventura</option>
                        <option>Artesanato</option>
                        <option>Outro</option>
                    </select>
                </div>
                <div class="pn-form-group">
                    <label>Tipo de Desconto</label>
                    <select name="tipo_desconto" class="pn-form-control" id="tipoDesconto">
                        <option value="percentual">Percentual (%)</option>
                        <option value="valor_fixo">Valor Fixo (R$)</option>
                    </select>
                </div>
            </div>

            <div class="pn-form-row">
                <div class="pn-form-group">
                    <label>Valor do Desconto *</label>
                    <input type="number" name="valor_desconto" class="pn-form-control" placeholder="15" min="0" step="0.01" required/>
                </div>
                <div class="pn-form-group">
                    <label>Limite Total de Usos</label>
                    <input type="number" name="limite_total" class="pn-form-control" placeholder="100 (vazio = ilimitado)" min="1"/>
                </div>
            </div>

            <div class="pn-form-row">
                <div class="pn-form-group">
                    <label>Data de Início *</label>
                    <input type="date" name="data_inicio" class="pn-form-control" value="<?= date('Y-m-d') ?>" required/>
                </div>
                <div class="pn-form-group">
                    <label>Data de Fim</label>
                    <input type="date" name="data_fim" class="pn-form-control"/>
                </div>
            </div>

            <div class="pn-form-row">
                <div class="pn-form-group">
                    <label>Usos por Usuário</label>
                    <input type="number" name="max_uses_per_user" class="pn-form-control" value="1" min="1"/>
                </div>
                <div class="pn-form-group">
                    <label>Exige Assinatura</label>
                    <select name="exige_assinatura" class="pn-form-control">
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" class="pn-btn pn-btn-ghost"
                        onclick="document.getElementById('modalCriar').classList.remove('open')">
                    Cancelar
                </button>
                <button type="submit" class="pn-btn pn-btn-primary">
                    <i class="fas fa-rocket"></i> Criar Campanha
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Close modal on overlay click
document.getElementById('modalCriar').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});

// Image validation + preview
document.getElementById('imageInput').addEventListener('change', function() {
    var preview = document.getElementById('imagePreview');
    var errorDiv = document.getElementById('imageError');
    var submitBtn = this.closest('form').querySelector('[type="submit"]');
    var file = this.files && this.files[0];

    preview.innerHTML = '';
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';

    if (!file) return;

    var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    var maxSize = 3 * 1024 * 1024; // 3 MB

    if (allowedTypes.indexOf(file.type) === -1) {
        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Formato não suportado: <strong>' + (file.type || 'desconhecido') + '</strong>. Use JPG, PNG, GIF ou WebP.';
        errorDiv.style.display = 'block';
        this.value = '';
        return;
    }

    if (file.size > maxSize) {
        var sizeMb = (file.size / 1024 / 1024).toFixed(1);
        errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Arquivo muito grande: <strong>' + sizeMb + ' MB</strong>. O tamanho máximo é 3 MB.';
        errorDiv.style.display = 'block';
        this.value = '';
        return;
    }

    var reader = new FileReader();
    reader.onload = function(e) {
        preview.innerHTML = '<img src="' + e.target.result + '" style="max-width:100%;max-height:200px;border-radius:8px;border:1px solid var(--border);">';
    };
    reader.readAsDataURL(file);
});
</script>
