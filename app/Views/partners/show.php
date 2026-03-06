<?php
/** @var array $partner {name, logo_path, description, website_url} */
?>

<div style="padding-top: 80px;">
    <!-- Partner Hero -->
    <section style="background: var(--color-surface); border-bottom: 1px solid rgba(255,255,255,0.08); padding: 4rem 2rem;">
        <div style="max-width: 800px; margin: 0 auto; display: flex; align-items: center; gap: 2rem; flex-wrap: wrap;">
            <?php if (!empty($partner['logo_path'])): ?>
            <img src="<?= e($partner['logo_path']) ?>" alt="<?= e($partner['name']) ?>"
                 style="width: 120px; height: 120px; object-fit: contain; border-radius: var(--radius-md); flex-shrink: 0;"/>
            <?php else: ?>
            <div style="width: 120px; height: 120px; background: var(--color-surface-hover); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-store" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
            <?php endif; ?>
            <div>
                <a href="/parceiros" style="color: var(--color-accent); font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.75rem;">
                    <i class="fas fa-arrow-left"></i> Todos os Parceiros
                </a>
                <h1 style="margin: 0 0 0.5rem; font-size: 2.5rem;"><?= e($partner['name']) ?></h1>
                <?php if (!empty($partner['website_url'])): ?>
                <a href="<?= e($partner['website_url']) ?>" target="_blank" rel="noopener noreferrer"
                   style="color: var(--color-accent); font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="fas fa-external-link-alt"></i> Visitar Site
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Description -->
    <div style="max-width: 800px; margin: 0 auto; padding: 3rem 2rem;">
        <?php if (!empty($partner['description'])): ?>
        <div style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-muted);">
            <?= nl2br(e($partner['description'])) ?>
        </div>
        <?php else: ?>
        <p style="color: var(--color-text-muted);">Informações em breve.</p>
        <?php endif; ?>
    </div>
</div>
