<?php
/** @var array $establishment {name, logo_path, cover_path, description, address, city_id} */
/** @var array $city          {name, slug} */
/** @var array $coupons       [{title, description, discount_value, discount_type, code, valid_until}] */
?>

<div style="padding-top: 80px;">

    <!-- Cover Image -->
    <section style="position: relative; height: 350px; overflow: hidden; display: flex; align-items: flex-end; background: var(--color-surface);">
        <?php if (!empty($establishment['cover_path'])): ?>
        <img src="<?= e($establishment['cover_path']) ?>" alt="<?= e($establishment['name']) ?>"
             style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;"/>
        <?php endif; ?>
        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.2) 100%); z-index: 1;"></div>
        <div style="position: relative; z-index: 2; padding: 2rem 2rem 3rem; max-width: 1200px; margin: 0 auto; width: 100%; display: flex; align-items: flex-end; gap: 1.5rem;">
            <?php if (!empty($establishment['logo_path'])): ?>
            <img src="<?= e($establishment['logo_path']) ?>" alt="<?= e($establishment['name']) ?>"
                 style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-md); border: 2px solid rgba(255,255,255,0.2); flex-shrink: 0;"/>
            <?php endif; ?>
            <div>
                <?php if (!empty($city)): ?>
                <a href="/cidades/<?= e($city['slug'] ?? '') ?>" style="color: var(--color-accent); font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
                    <i class="fas fa-map-marker-alt"></i> <?= e($city['name']) ?>
                </a>
                <?php endif; ?>
                <h1 style="font-size: 2.5rem; margin: 0;"><?= e($establishment['name']) ?></h1>
                <?php if (!empty($establishment['address'])): ?>
                <p style="color: var(--color-text-muted); margin: 0.5rem 0 0; font-size: 0.9rem;">
                    <i class="fas fa-location-dot"></i> <?= e($establishment['address']) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <div style="max-width: 1200px; margin: 0 auto; padding: 3rem 2rem; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">

        <!-- Description -->
        <div>
            <h2 style="margin: 0 0 1.5rem;">Sobre</h2>
            <?php if (!empty($establishment['description'])): ?>
            <div style="color: var(--color-text-muted); line-height: 1.8; font-size: 1rem;">
                <?= nl2br(e($establishment['description'])) ?>
            </div>
            <?php else: ?>
            <p style="color: var(--color-text-muted);">Informações em breve.</p>
            <?php endif; ?>
        </div>

        <!-- Coupons -->
        <div>
            <h2 style="margin: 0 0 1.5rem;">Cupons Disponíveis</h2>
            <?php if (!empty($coupons)): ?>
                <?php foreach ($coupons as $coupon): ?>
                <div style="background: var(--color-surface); border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.75rem;">
                        <h3 style="margin: 0; font-size: 1rem;"><?= e($coupon['title']) ?></h3>
                        <span style="background: var(--color-accent); color: #111; font-weight: 900; padding: 0.2rem 0.5rem; border-radius: 50px; font-size: 0.8rem; white-space: nowrap;">
                            <?php if ($coupon['discount_type'] === 'percentage'): ?>
                                <?= e($coupon['discount_value']) ?>% OFF
                            <?php else: ?>
                                R$ <?= e($coupon['discount_value']) ?> OFF
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if (!empty($coupon['description'])): ?>
                    <p style="color: var(--color-text-muted); font-size: 0.85rem; margin: 0 0 0.75rem;"><?= e($coupon['description']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($coupon['code'])): ?>
                    <div style="display: flex; align-items: center; gap: 0.5rem; background: rgba(0,0,0,0.3); border-radius: var(--radius-sm); padding: 0.5rem 0.75rem;">
                        <span style="font-size: 0.75rem; color: var(--color-text-muted);">Código:</span>
                        <code style="font-family: monospace; font-weight: 700; color: var(--color-accent); letter-spacing: 0.1em;"><?= e($coupon['code']) ?></code>
                        <button onclick="navigator.clipboard.writeText('<?= e($coupon['code']) ?>')"
                                style="background: none; border: none; color: var(--color-text-muted); cursor: pointer; margin-left: auto;"
                                title="Copiar código">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($coupon['valid_until'])): ?>
                    <p style="font-size: 0.78rem; color: var(--color-text-muted); margin: 0.5rem 0 0;">
                        <i class="fas fa-calendar-alt"></i> Válido até <?= e(date('d/m/Y', strtotime($coupon['valid_until']))) ?>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: var(--color-text-muted); background: var(--color-surface); border-radius: var(--radius-md);">
                <i class="fas fa-ticket-alt" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.75rem;"></i>
                <p style="margin: 0;">Nenhum cupom disponível no momento.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] { grid-template-columns: 1fr !important; }
}
</style>
