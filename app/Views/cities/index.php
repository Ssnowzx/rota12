<?php
/** @var array $cities [{id, name, slug, state, image_path}] */
?>

<div class="cities-page" style="padding: 120px 2rem 4rem; max-width: 1200px; margin: 0 auto;">
    <div class="section-header text-center" style="margin-bottom: 3rem;">
        <span class="section-subtitle">Explore</span>
        <h1 class="section-title">Cidades Parceiras</h1>
        <div class="section-divider"></div>
        <p style="color: var(--color-text-muted); margin-top: 1rem;">Descubra todos os destinos cobertos pela Rota 12.</p>
    </div>

    <div class="locations-grid">
        <?php if (!empty($cities)): ?>
            <?php foreach ($cities as $city): ?>
            <a href="/cidades/<?= e($city['slug']) ?>" class="location-card" style="text-decoration: none; display: block;">
                <?php if (!empty($city['image_path'])): ?>
                <img src="<?= e($city['image_path']) ?>" alt="<?= e($city['name']) ?>" loading="lazy"/>
                <?php else: ?>
                <div style="height: 200px; background: var(--color-surface); display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md) var(--radius-md) 0 0;">
                    <i class="fas fa-map-marker-alt" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <?php endif; ?>
                <div class="location-info">
                    <h3><?= e($city['name']) ?></h3>
                    <?php if (!empty($city['state'])): ?>
                    <p><?= e($city['state']) ?></p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 4rem; color: var(--color-text-muted);">
                <i class="fas fa-map" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                <p>Nenhuma cidade cadastrada ainda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
