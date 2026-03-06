<?php
/** @var array $city           {id, name, slug, state, image_path} */
/** @var array $establishments [{id, name, slug, description, logo_path}] */
?>

<div class="city-show-page" style="padding-top: 80px;">

    <!-- City Hero -->
    <section class="city-hero" style="position: relative; height: 350px; overflow: hidden; display: flex; align-items: flex-end;">
        <?php if (!empty($city['image_path'])): ?>
        <img src="<?= e($city['image_path']) ?>" alt="<?= e($city['name']) ?>"
             style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;"/>
        <?php endif; ?>
        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.2) 100%); z-index: 1;"></div>
        <div style="position: relative; z-index: 2; padding: 2rem 2rem 3rem; max-width: 1200px; margin: 0 auto; width: 100%;">
            <a href="/cidades" style="color: var(--color-accent); font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 1rem;">
                <i class="fas fa-arrow-left"></i> Todas as Cidades
            </a>
            <h1 style="font-size: 3rem; margin: 0;"><?= e($city['name']) ?></h1>
            <?php if (!empty($city['state'])): ?>
            <p style="color: var(--color-text-muted); margin: 0.5rem 0 0;"><?= e($city['state']) ?></p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Establishments -->
    <div style="max-width: 1200px; margin: 0 auto; padding: 3rem 2rem;">
        <h2 style="margin-bottom: 2rem;">Estabelecimentos em <?= e($city['name']) ?></h2>

        <?php if (!empty($establishments)): ?>
        <div class="catalogo-grid">
            <?php foreach ($establishments as $est): ?>
            <a href="/estabelecimentos/<?= e($est['slug']) ?>" class="campanha-card" style="text-decoration: none; color: inherit;">
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                    <?php if (!empty($est['logo_path'])): ?>
                    <img src="<?= e($est['logo_path']) ?>" alt="<?= e($est['name']) ?>"
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-sm); flex-shrink: 0;"/>
                    <?php else: ?>
                    <div style="width: 60px; height: 60px; background: var(--color-surface-hover); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-store" style="opacity: 0.4;"></i>
                    </div>
                    <?php endif; ?>
                    <h3 style="margin: 0; font-size: 1.05rem;"><?= e($est['name']) ?></h3>
                </div>
                <?php if (!empty($est['description'])): ?>
                <p style="color: var(--color-text-muted); font-size: 0.9rem; margin: 0;"><?= e(mb_substr($est['description'], 0, 120)) ?>...</p>
                <?php endif; ?>
                <span style="margin-top: 1rem; color: var(--color-accent); font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.3rem;">
                    Ver cupons <i class="fas fa-arrow-right"></i>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 4rem; color: var(--color-text-muted);">
            <i class="fas fa-store-slash" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
            <p>Nenhum estabelecimento cadastrado nesta cidade ainda.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.catalogo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
.campanha-card { background: var(--color-surface); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-md); padding: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem; transition: transform 0.2s, border-color 0.2s; }
.campanha-card:hover { transform: translateY(-4px); border-color: var(--color-accent); }
</style>
