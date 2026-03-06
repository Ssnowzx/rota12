<?php
/** @var array $campaigns Campaign list from CampaignModel::listActive() */
?>

<div class="catalogo-page">
    <!-- CSRF Token para requisições AJAX -->
    <?= \App\Core\CSRF::field() ?>

    <div class="catalogo-header">
        <h1>Cupons de Desconto</h1>
        <p>Encontre os melhores descontos para sua viagem pela Serra Catarinense.</p>
        <input type="search" id="couponSearch" placeholder="Buscar por nome, categoria ou parceiro..."
               aria-label="Buscar cupons" class="catalogo-busca"/>
    </div>

    <div class="catalogo-grid" id="couponGrid">
        <?php if (!empty($campaigns)): ?>
            <?php foreach ($campaigns as $c): ?>
            <article class="campanha-card" data-title="<?= e(strtolower($c['titulo'] . ' ' . ($c['categoria'] ?? '') . ' ' . ($c['partner_nome'] ?? $c['partner_username'] ?? ''))) ?>">
                <?php if (!empty($c['image_path'])): ?>
                <div class="card-image">
                    <img src="/uploads/<?= e($c['image_path']) ?>" alt="<?= e($c['titulo']) ?>"/>
                </div>
                <?php else: ?>
                <div class="card-image card-image-empty">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <?php endif; ?>

                <div class="card-body-content">
                    <div class="card-header">
                        <h3 class="card-title"><?= e($c['titulo']) ?></h3>
                        <span class="discount-badge-card">
                            <?php if ($c['tipo_desconto'] === 'percentual'): ?>
                                <?= e($c['valor_desconto']) ?>% OFF
                            <?php else: ?>
                                R$ <?= e(number_format((float)$c['valor_desconto'], 2, ',', '.')) ?> OFF
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if (!empty($c['partner_nome']) || !empty($c['partner_username'])): ?>
                    <p class="card-partner"><i class="fas fa-store"></i> <?= e($c['partner_nome'] ?: $c['partner_username']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($c['descricao'])): ?>
                    <p class="card-desc"><?= e($c['descricao']) ?></p>
                    <?php endif; ?>
                    <div class="card-meta">
                        <?php if (!empty($c['categoria'])): ?>
                        <span class="card-tag"><i class="fas fa-tag"></i> <?= e($c['categoria']) ?></span>
                        <?php endif; ?>
                        <?php if ($c['exige_assinatura']): ?>
                        <span class="card-tag card-tag-premium"><i class="fas fa-crown"></i> Assinantes</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <?php if (!empty($c['data_fim'])): ?>
                    <span class="coupon-validity">
                        <i class="fas fa-calendar-alt"></i>
                        Válido até <?= e(date('d/m/Y', strtotime($c['data_fim']))) ?>
                    </span>
                    <?php endif; ?>
                    <button type="button" class="btn-pegar-cupom" data-campaign-id="<?= e($c['id']) ?>" data-campaign-title="<?= e($c['titulo']) ?>">
                        <i class="fas fa-ticket-alt"></i> Pegar Cupom
                    </button>
                </div>
            </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-ticket-alt"></i>
                <h3>Nenhum cupom disponível</h3>
                <p>Novos cupons são adicionados regularmente. Volte em breve!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de Cupom -->
    <div id="couponModal" class="coupon-modal" style="display: none;">
        <div class="coupon-modal-content">
            <button type="button" class="coupon-modal-close" id="closeModal">&times;</button>
            <div class="coupon-modal-body">
                <div class="coupon-success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Cupom Gerado com Sucesso!</h2>
                <p class="coupon-modal-campaign" id="campaignTitle"></p>
                <div class="coupon-code-container">
                    <p class="coupon-code-label">Seu código:</p>
                    <div class="coupon-code-box">
                        <code id="couponCode"></code>
                        <button type="button" class="coupon-copy-btn" id="copyCouponBtn" title="Copiar código">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <p class="coupon-modal-instructions">
                    📌 Apresente este código ao parceiro para validar seu benefício.
                </p>
                <button type="button" class="coupon-modal-btn" id="closeModalBtn">Fechar</button>
            </div>
        </div>
    </div>
</div>

<style>
.catalogo-page { padding: 120px 2rem 4rem; max-width: 1200px; margin: 0 auto; }
.catalogo-header { text-align: center; margin-bottom: 3rem; }
.catalogo-header h1 { font-size: 2.5rem; margin-bottom: 1rem; }
.catalogo-busca { width: 100%; max-width: 500px; padding: 0.75rem 1.25rem; border-radius: 50px; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.05); color: #fff; font-size: 1rem; margin-top: 1rem; outline: none; }
.catalogo-busca:focus { border-color: var(--color-accent); }
.catalogo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
.campanha-card { background: var(--color-surface); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-md); padding: 0; display: flex; flex-direction: column; overflow: hidden; transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s; }
.campanha-card:hover { transform: translateY(-4px); border-color: var(--color-accent); box-shadow: 0 12px 32px rgba(0,0,0,0.4); }
.card-image { width: 100%; height: 200px; background: linear-gradient(135deg, rgba(223,255,0,0.1), rgba(223,255,0,0.05)); overflow: hidden; }
.card-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
.campanha-card:hover .card-image img { transform: scale(1.05); }
.card-image-empty { display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.2); font-size: 3rem; }
.card-body-content { padding: 1.5rem 1.75rem; display: flex; flex-direction: column; gap: 0.75rem; flex: 1; }
.card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
.card-title { font-size: 1.15rem; font-weight: 700; margin: 0; line-height: 1.3; }
.discount-badge-card { background: var(--color-accent); color: #111; font-weight: 900; padding: 0.3rem 0.75rem; border-radius: 50px; font-size: 0.85rem; white-space: nowrap; flex-shrink: 0; }
.card-partner { color: var(--color-text-muted); font-size: 0.88rem; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.card-desc { color: var(--color-text-muted); font-size: 0.9rem; margin: 0; line-height: 1.5; }
.card-meta { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.card-tag { font-size: 0.78rem; padding: 0.25rem 0.6rem; border-radius: 50px; background: rgba(255,255,255,0.08); color: var(--color-text-muted); display: flex; align-items: center; gap: 0.3rem; }
.card-tag-premium { background: rgba(223, 255, 0, 0.15); color: var(--color-accent); }
.card-footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; padding: 1.25rem 1.75rem; border-top: 1px solid rgba(255,255,255,0.06); margin-top: auto; }
.coupon-validity { font-size: 0.82rem; color: var(--color-text-muted); display: flex; align-items: center; gap: 0.4rem; }
.btn-pegar-cupom { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 50px; background: var(--color-accent); color: #111; font-weight: 700; font-size: 0.88rem; text-decoration: none; transition: all 0.2s; box-shadow: 0 2px 8px rgba(223,255,0,0.15); }
.btn-pegar-cupom:hover { opacity: 0.9; box-shadow: 0 4px 16px rgba(223,255,0,0.25); transform: translateY(-1px); }
.empty-state { grid-column: 1/-1; text-align: center; padding: 4rem 2rem; color: var(--color-text-muted); }
.empty-state i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.4; display: block; }

/* Modal de Cupom */
.coupon-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 999; animation: fadeIn 0.3s ease-in; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.coupon-modal-content { background: var(--color-surface); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-md); padding: 2.5rem; max-width: 480px; width: 90%; position: relative; animation: slideUp 0.3s ease-out; }
.coupon-modal-close { position: absolute; top: 1.5rem; right: 1.5rem; background: none; border: none; color: var(--color-text-muted); font-size: 1.5rem; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; transition: color 0.2s; }
.coupon-modal-close:hover { color: var(--color-accent); }
.coupon-modal-body { text-align: center; }
.coupon-success-icon { font-size: 3.5rem; color: var(--color-accent); margin-bottom: 1rem; animation: scaleIn 0.5s ease-out; }
@keyframes scaleIn { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.coupon-modal-body h2 { font-size: 1.5rem; margin: 0 0 0.5rem 0; color: #fff; }
.coupon-modal-campaign { color: var(--color-text-muted); font-size: 0.9rem; margin: 0 0 1.5rem 0; }
.coupon-code-container { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-md); padding: 1.5rem; margin: 1.5rem 0; }
.coupon-code-label { color: var(--color-text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.75rem 0; }
.coupon-code-box { display: flex; align-items: center; justify-content: space-between; background: rgba(223,255,0,0.05); border: 1px dashed rgba(223,255,0,0.3); border-radius: 8px; padding: 1rem; }
#couponCode { font-family: 'Courier New', monospace; font-size: 1.75rem; font-weight: 900; color: var(--color-accent); letter-spacing: 0.1em; margin: 0; display: inline-block; }
.coupon-copy-btn { background: var(--color-accent); color: #111; border: none; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1rem; transition: opacity 0.2s; }
.coupon-copy-btn:hover { opacity: 0.85; }
.coupon-copy-btn.copied { animation: pulse 0.5s ease-out; }
@keyframes pulse { 50% { opacity: 0.7; } }
.coupon-modal-instructions { color: var(--color-text-muted); font-size: 0.9rem; margin: 1.5rem 0; line-height: 1.5; }
.coupon-modal-btn { display: inline-block; background: var(--color-accent); color: #111; border: none; border-radius: 50px; padding: 0.75rem 2rem; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: opacity 0.2s; }
.coupon-modal-btn:hover { opacity: 0.85; }
</style>

<script>
(function(){
    // --- Busca de Cupons ---
    var search = document.getElementById('couponSearch');
    var cards  = document.querySelectorAll('.campanha-card[data-title]');
    if(search){
        search.addEventListener('input', function(){
            var q = this.value.toLowerCase();
            cards.forEach(function(c){
                c.style.display = c.dataset.title.includes(q) ? '' : 'none';
            });
        });
    }

    // --- Geração de Cupom via AJAX ---
    var modal = document.getElementById('couponModal');
    var closeModalBtn = document.getElementById('closeModal');
    var closeModalBtnAlt = document.getElementById('closeModalBtn');
    var copyCouponBtn = document.getElementById('copyCouponBtn');
    var couponCodeEl = document.getElementById('couponCode');
    var campaignTitleEl = document.getElementById('campaignTitle');

    // Fechar modal ao clicar no X
    if(closeModalBtn){
        closeModalBtn.addEventListener('click', function(){
            modal.style.display = 'none';
        });
    }

    // Fechar modal ao clicar no botão Fechar
    if(closeModalBtnAlt){
        closeModalBtnAlt.addEventListener('click', function(){
            modal.style.display = 'none';
        });
    }

    // Fechar modal ao clicar fora dele
    if(modal){
        modal.addEventListener('click', function(e){
            if(e.target === modal){
                modal.style.display = 'none';
            }
        });
    }

    // Copiar código do cupom
    if(copyCouponBtn){
        copyCouponBtn.addEventListener('click', function(){
            var code = couponCodeEl.textContent;
            navigator.clipboard.writeText(code).then(function(){
                copyCouponBtn.classList.add('copied');
                setTimeout(function(){
                    copyCouponBtn.classList.remove('copied');
                }, 500);
            });
        });
    }

    // Pegar cupom - clique nos botões
    var grabBtns = document.querySelectorAll('.btn-pegar-cupom');
    grabBtns.forEach(function(btn){
        btn.addEventListener('click', function(){
            var campaignId = this.dataset.campaignId;
            var campaignTitle = this.dataset.campaignTitle;

            // Obter CSRF token da página
            var csrfEl = document.querySelector('input[name="csrf_token"]');
            var csrfToken = csrfEl ? csrfEl.value : '';

            // Fazer requisição AJAX
            var formData = new FormData();
            formData.append('campaign_id', campaignId);
            formData.append('ajax', '1');
            if(csrfToken) formData.append('csrf_token', csrfToken);

            fetch('/minha-conta/pegar-cupom', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response){
                return response.json();
            })
            .then(function(data){
                if(data.success){
                    // Exibir o cupom no modal
                    couponCodeEl.textContent = data.codigo;
                    campaignTitleEl.textContent = campaignTitle;
                    modal.style.display = 'flex';
                } else {
                    // Erro
                    var errorMsg = data.error || 'Erro ao gerar cupom.';
                    if(data.redirect){
                        alert(errorMsg);
                        window.location.href = data.redirect;
                    } else {
                        alert(errorMsg);
                    }
                }
            })
            .catch(function(error){
                console.error('Erro:', error);
                alert('Erro ao gerar cupom. Tente novamente.');
            });
        });
    });
})();
</script>
