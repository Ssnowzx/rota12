<?php
/** @var array $partners [{id, name, slug, logo_path, description}] */
?>

<div style="padding: 120px 2rem 4rem; max-width: 1200px; margin: 0 auto;">
    <div class="section-header text-center" style="margin-bottom: 3rem;">
        <span class="section-subtitle">Parceiros</span>
        <h1 class="section-title">Nossos Parceiros</h1>
        <div class="section-divider"></div>
        <p style="color: var(--color-text-muted); margin-top: 1rem;">Estabelecimentos que oferecem descontos exclusivos para membros Rota 12.</p>
    </div>

    <?php if (!empty($partners)): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.5rem;">
        <?php foreach ($partners as $partner): ?>
        <a href="/parceiros/<?= e($partner['slug']) ?>"
           style="background: var(--color-surface); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-md); padding: 2rem; display: flex; flex-direction: column; align-items: center; gap: 1rem; text-decoration: none; color: inherit; transition: transform 0.2s, border-color 0.2s; text-align: center;">
            <?php if (!empty($partner['logo_path'])): ?>
            <img src="<?= e($partner['logo_path']) ?>" alt="<?= e($partner['name']) ?>"
                 style="width: 80px; height: 80px; object-fit: contain; border-radius: var(--radius-sm);"/>
            <?php else: ?>
            <div style="width: 80px; height: 80px; background: var(--color-surface-hover); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-handshake" style="font-size: 2rem; opacity: 0.4;"></i>
            </div>
            <?php endif; ?>
            <h3 style="margin: 0; font-size: 1.1rem;"><?= e($partner['name']) ?></h3>
            <?php if (!empty($partner['description'])): ?>
            <p style="color: var(--color-text-muted); font-size: 0.85rem; margin: 0; line-height: 1.5;">
                <?= e(mb_substr($partner['description'], 0, 100)) ?>...
            </p>
            <?php endif; ?>
            <span style="color: var(--color-accent); font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.3rem; margin-top: auto;">
                Ver detalhes <i class="fas fa-arrow-right"></i>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
        <i class="fas fa-handshake" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
        <p>Nenhum parceiro cadastrado ainda.</p>
    </div>
    <?php endif; ?>
</div>
