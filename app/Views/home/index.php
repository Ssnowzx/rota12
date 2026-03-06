<?php
/** @var array $banners     [{title, subtitle, image_path, link_url, button_text}] */
/** @var array $highlights  [{title, subtitle, image_path, link_url}] */
/** @var array $cities      [{id, name, slug, image_path}] */
/** @var array $topCoupons  [{title, discount_value, discount_type, establishment_name, category}] */
?>

<!-- HERO -->
<section id="hero" class="hero-section">
    <div class="vertical-text">
        <span>BEM VINDO A UMA NOVA</span>
        <span>EXPERIÊNCIA EM VIAGENS</span>
    </div>
    <div class="hero-content">
        <h1 class="display-title">Aventura <span class="ampersand">&amp;</span> Gastronomia</h1>
        <p class="hero-subtitle">O melhor da serra catarinense no seu turismo de aventura com excelentes descontos para que sua experiência de viagem seja a melhor possível!</p>
        <div class="hero-actions">
            <?php if (!empty($banners) && !empty($banners[0]['link_url'])): ?>
                <a class="cta-button primary" href="<?= e($banners[0]['link_url']) ?>"><?= e($banners[0]['button_text'] ?? 'Saiba Mais') ?></a>
            <?php else: ?>
                <a class="cta-button primary" href="/cupons">Ver Cupons</a>
                <a class="cta-button secondary" href="/#planos">Nossos Planos</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<section class="categories-section">
    <a class="category-card" href="#restaurantes">
        <div class="card-bg" style="background-image:url('/assets/images/cat_restaurante.png')"></div>
        <div class="overlay"></div>
        <div class="card-content"><h3>Deliciosos Restaurantes</h3><p>Descontos de dar água na boca</p></div>
    </a>
    <a class="category-card" href="#hospedagem">
        <div class="card-bg" style="background-image:url('/assets/images/cat_hospedagem.png')"></div>
        <div class="overlay"></div>
        <div class="card-content"><h3>Hospede-se por menos!</h3><p>Descontos imperdíveis</p></div>
    </a>
    <a class="category-card" href="#bebidas">
        <div class="card-bg" style="background-image:url('/assets/images/cat_bebidas.png')"></div>
        <div class="overlay"></div>
        <div class="card-content"><h3>Bebidas Locais e Mundiais</h3><p>Saiba onde ir economizando</p></div>
    </a>
</section>

<!-- CTA PROMO -->
<section class="cta-promo-section">
    <div class="promo-content">
        <h2 class="promo-title">Assine agora e economize todos os dias em sua viagem!</h2>
        <div class="promo-details">
            <h3>Na Hospedagem, no Almoço, na Janta, no Cafézinho...</h3>
            <p>Descontos para todos os momentos em sua Rota 12</p>
        </div>
    </div>
    <a class="promo-arrow" href="#planos">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M7 17L17 7M17 7H7M17 7V17" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </a>
</section>

<!-- HIGHLIGHTS / CUPONS MENU -->
<section id="cupons" class="menu-section">
    <?php if (!empty($highlights)): ?>
        <div class="menu-category">
            <h2 class="category-title">Destaques</h2>
            <div class="category-divider"></div>
            <div class="menu-grid highlights-grid">
                <?php foreach ($highlights as $h): ?>
                <div class="menu-item highlight-card">
                    <div class="item-image">
                        <?php
                            $hlImg = $h['image_path'] ?? '';
                            if ($hlImg === '') {
                                $hlImg = '/assets/images/marquee_1.png';
                            } elseif (!str_starts_with($hlImg, '/')) {
                                $hlImg = '/uploads/' . $hlImg;
                            }
                        ?>
                        <img src="<?= e($hlImg) ?>"
                             alt="<?= e($h['title']) ?>" loading="lazy"/>
                    </div>
                    <div class="item-info">
                        <h3><?= e($h['title']) ?></h3>
                        <?php if (!empty($h['subtitle'])): ?>
                        <p><?= e($h['subtitle']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($h['link_url'])): ?>
                    <a class="item-link" href="<?= e($h['link_url']) ?>">Ver mais</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<style>
/* Highlights Grid - Cards padronizados */
.highlights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.highlight-card {
    display: flex;
    flex-direction: column;
    background: var(--color-surface);
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    padding: 0;
}

.highlight-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
}

.highlight-card .item-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.highlight-card .item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.highlight-card:hover .item-image img {
    transform: scale(1.05);
}

.highlight-card .item-info {
    padding: 1.25rem 1.5rem;
}

.highlight-card .item-info h3 {
    font-family: var(--font-heading);
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.3rem;
}

.highlight-card .item-info p {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
    line-height: 1.4;
}

.highlight-card .item-link {
    display: inline-block;
    padding: 0.6rem 1.5rem;
    margin: 0 1.5rem 1.25rem;
    font-family: var(--font-heading);
    font-size: 0.82rem;
    font-weight: 700;
    color: #000;
    background: var(--color-accent);
    border-radius: 8px;
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.highlight-card .item-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(223,255,0,0.3);
}

@media (max-width: 768px) {
    .highlights-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .highlight-card .item-image {
        height: 180px;
    }
}
</style>

<!-- IMAGE MARQUEE -->
<section class="image-marquee">
    <div class="marquee-track">
        <?php
        $marqueeImages = [
            ['src' => '/assets/images/marquee_1.png', 'alt' => 'Entrevero de Pinhão'],
            ['src' => '/assets/images/marquee_2.png', 'alt' => 'Fondue de Queijo'],
            ['src' => '/assets/images/marquee_3.png', 'alt' => 'Cuca Alemã'],
            ['src' => '/assets/images/marquee_4.png', 'alt' => 'Churrasco Serrano'],
            ['src' => '/assets/images/marquee_5.png', 'alt' => 'Truta Grelhada'],
            ['src' => '/assets/images/marquee_6.png', 'alt' => 'Polenta Rústica'],
            ['src' => '/assets/images/marquee_7.png', 'alt' => 'Queijo Serrano com Mel'],
        ];
        // Duplicate for infinite scroll effect
        $marqueeItems = array_merge($marqueeImages, $marqueeImages);
        foreach ($marqueeItems as $img): ?>
        <div class="marquee-item">
            <img src="<?= e($img['src']) ?>" alt="<?= e($img['alt']) ?>"/>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- LOCATIONS / CITIES -->
<section class="locations-section">
    <div class="section-header text-center">
        <span class="section-subtitle">Descubra</span>
        <h2 class="section-title">Onde Estamos</h2>
        <div class="section-divider"></div>
    </div>
    <div class="locations-grid">
        <?php if (!empty($cities)): ?>
            <?php foreach ($cities as $city): ?>
            <article class="location-card">
                <img src="<?= e(!empty($city['image_path']) ? $city['image_path'] : '/assets/images/city_bom_jardim.png') ?>"
                     alt="<?= e($city['name']) ?>"/>
                <div class="location-info">
                    <h3><?= e($city['name']) ?></h3>
                    <p>Pousadas, Hotéis, Restaurantes, e muito mais...</p>
                </div>
            </article>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback static locations -->
            <article class="location-card">
                <img src="/assets/images/city_bom_jardim.png" alt="Bom Jardim da Serra"/>
                <div class="location-info"><h3>Bom Jardim da Serra</h3><p>Pousadas, Hotéis, Restaurantes, Cafeterias, etc...</p></div>
            </article>
            <article class="location-card">
                <img src="/assets/images/city_sao_joaquim.jpg" alt="São Joaquim"/>
                <div class="location-info"><h3>São Joaquim</h3><p>Pousadas, Hotéis, Restaurantes, Vinícolas, Lojas, etc...</p></div>
            </article>
            <article class="location-card">
                <img src="/assets/images/city_urubici.jpg" alt="Urubici"/>
                <div class="location-info"><h3>Urubici</h3><p>Campings, Pousadas, Hotéis, Canions, etc...</p></div>
            </article>
        <?php endif; ?>
    </div>
</section>

<!-- PRICING -->
<section id="planos" class="pricing-section">
    <div class="pricing-header">
        <h2 class="section-title">Escolha seu Plano</h2>
    </div>
    <div class="pricing-grid">
        <div class="pricing-card free-plan">
            <h3 class="plan-name">Teste Grátis</h3>
            <div class="plan-price">
                <span class="amount">0</span>
                <span class="period">/mês</span>
            </div>
            <div class="plan-divider"></div>
            <ul class="plan-benefits">
                <li>Acesso limitado a cupons</li>
                <li>Validade de 30 dias</li>
            </ul>
            <a class="cta-button secondary full-width" href="/cadastro">COMEÇAR GRÁTIS</a>
        </div>
        <div class="pricing-card premium-plan">
            <h3 class="plan-name">Assinatura Premium</h3>
            <div class="plan-price">
                <span class="currency">R$</span>
                <span class="amount">29,90</span>
                <span class="period">/mês</span>
            </div>
            <div class="plan-divider"></div>
            <ul class="plan-benefits">
                <li>Acesso ilimitado a todos os cupons</li>
                <li>Descontos em hotéis e restaurantes</li>
                <li>Suporte prioritário</li>
            </ul>
            <a class="cta-button primary full-width" href="/checkout?plan=monthly">ASSINAR PREMIUM</a>
        </div>
    </div>
</section>

<!-- TOP 10 COUPONS -->
<section id="top-cupons" class="top-coupons-section">
    <div class="section-header text-center" style="margin-bottom: 4rem;">
        <span class="section-subtitle">Mais Populares</span>
        <h2 class="section-title">Top 10 Cupons</h2>
        <div class="section-divider" style="margin: 1.2rem auto 2rem;"></div>
        <p style="margin-top: 1.5rem; margin-bottom: 0; font-size: 1rem; color: rgba(255,255,255,0.7);">Os descontos mais utilizados pelos nossos membros na Serra Catarinense.</p>
    </div>
    <div class="ranking-grid">
        <?php if (!empty($topCoupons)): ?>
            <?php foreach ($topCoupons as $i => $coupon): ?>
            <a href="/cupons" class="ranking-card-link">
                <article class="ranking-card <?= !empty($coupon['image_path']) ? 'has-image' : '' ?>"
                         <?php if (!empty($coupon['image_path'])): ?>
                         style="background-image: url('/uploads/<?= e($coupon['image_path']) ?>')"
                         <?php endif; ?>>
                    <div class="ranking-overlay"></div>
                    <div class="ranking-badge"><?= (int)$i + 1 ?></div>
                    <div class="ranking-info">
                        <h3><?= e($coupon['titulo']) ?></h3>
                        <span class="ranking-discount">
                            <?php if ($coupon['tipo_desconto'] === 'percentual'): ?>
                                <?= (int)$coupon['valor_desconto'] ?>% OFF
                            <?php else: ?>
                                R$ <?= (int)$coupon['valor_desconto'] ?> OFF
                            <?php endif; ?>
                        </span>
                    </div>
                    <span class="ranking-category"><?= e($coupon['categoria'] ?? 'Desconto') ?></span>
                </article>
            </a>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback: show empty state -->
            <p class="text-center">Nenhum cupom disponível no momento.</p>
        <?php endif; ?>
    </div>
</section>

<style>
/* Top Coupons Link Styling */
.ranking-card-link {
    display: block;
    text-decoration: none;
    color: inherit;
    cursor: pointer;
}

.ranking-card-link:hover .ranking-card {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(223, 255, 0, 0.12);
    border-color: rgba(223,255,0,0.3);
}

.ranking-card-link:hover .ranking-card.has-image .ranking-overlay {
    background: linear-gradient(160deg, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.25) 100%);
}

/* Ranking Card */
.ranking-card {
    position: relative;
    background-color: var(--color-surface);
    background-size: cover;
    background-position: center;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    border: 1px solid rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    gap: 1rem;
    min-height: 110px;
    overflow: hidden;
    transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
}

.ranking-card.has-image {
    min-height: 140px;
    padding: 1.5rem;
}

/* Overlay */
.ranking-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(160deg, rgba(0,0,0,0.72) 0%, rgba(0,0,0,0.4) 100%);
    transition: background 0.3s ease;
    z-index: 1;
}

.ranking-card:not(.has-image) .ranking-overlay {
    background: none;
}

/* Badge de posição */
.ranking-badge {
    font-family: var(--font-heading);
    font-size: 2.2rem;
    font-weight: 900;
    color: rgba(255,255,255,0.08);
    min-width: 40px;
    text-align: center;
    line-height: 1;
    position: relative;
    z-index: 2;
    flex-shrink: 0;
}

.ranking-card-link:first-child .ranking-badge {
    color: gold;
    text-shadow: 0 0 16px rgba(255,215,0,.35);
}

.ranking-card-link:nth-child(2) .ranking-badge {
    color: silver;
    text-shadow: 0 0 12px hsla(0,0%,75%,.25);
}

.ranking-card-link:nth-child(3) .ranking-badge {
    color: #cd7f32;
    text-shadow: 0 0 12px rgba(205,127,50,.25);
}

/* Info do cupom */
.ranking-info {
    flex-grow: 1;
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.ranking-info h3 {
    font-family: var(--font-heading);
    font-size: 1.25rem;
    margin: 0;
    color: #fff;
    line-height: 1.2;
    font-weight: 800;
    letter-spacing: -0.01em;
}

.ranking-discount {
    font-family: var(--font-heading);
    font-weight: 800;
    font-size: 0.78rem;
    color: var(--color-accent);
    background: rgba(223,255,0,0.12);
    padding: 0.2rem 0.55rem;
    border-radius: 5px;
    display: inline-block;
    width: fit-content;
    margin-top: 0.1rem;
    line-height: 1.3;
}

/* Categoria à direita */
.ranking-category {
    position: relative;
    z-index: 2;
    font-size: 0.58rem;
    text-transform: uppercase;
    color: rgba(255,255,255,0.75);
    letter-spacing: 0.12em;
    font-weight: 400;
    line-height: 1;
    flex-shrink: 0;
    text-align: right;
    align-self: center;
    min-width: 55px;
}

@media (max-width: 768px) {
    .ranking-grid { grid-template-columns: 1fr; gap: 1rem; }
    .ranking-card { padding: 1rem 1.25rem; min-height: 90px; }
    .ranking-card.has-image { min-height: 120px; }
    .ranking-badge { font-size: 1.8rem; min-width: 32px; }
    .ranking-info h3 { font-size: 1.05rem; }
    .ranking-category { font-size: 0.52rem; min-width: 45px; }
}
</style>

