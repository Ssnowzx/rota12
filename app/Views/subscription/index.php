<?php /** @var array $plans @var string $selectedPlan @var array|null $user @var array|null $subscription */ ?>
<style>
.checkout-wrap { max-width: 960px; margin: 0 auto; padding: 7rem 1.5rem 4rem; }
.checkout-title { font-size: 2rem; font-weight: 900; color: #fff; text-align: center; margin-bottom: .5rem; }
.checkout-sub { color: rgba(255,255,255,.5); text-align: center; margin-bottom: 3rem; font-size: 1rem; }
.plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
.plan-card { background: #1a1a1a; border: 2px solid rgba(255,255,255,.08); border-radius: 16px; padding: 2rem; cursor: pointer; transition: border-color .2s, transform .2s; position: relative; }
.plan-card:hover { transform: translateY(-4px); }
.plan-card.selected { border-color: #dfff00; }
.plan-card.popular::before { content: 'Mais Popular'; position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #dfff00; color: #111; font-size: .7rem; font-weight: 700; padding: 3px 12px; border-radius: 20px; white-space: nowrap; }
.plan-name { font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: .5rem; }
.plan-price { font-size: 2.2rem; font-weight: 900; color: #dfff00; line-height: 1; }
.plan-price span { font-size: 1rem; color: rgba(255,255,255,.4); font-weight: 400; }
.plan-features { margin-top: 1.5rem; list-style: none; padding: 0; }
.plan-features li { color: rgba(255,255,255,.7); font-size: .9rem; padding: .35rem 0; display: flex; align-items: center; gap: .5rem; }
.plan-features li::before { content: '✓'; color: #dfff00; font-weight: 700; flex-shrink: 0; }
.checkout-box { background: #1a1a1a; border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: 2rem; max-width: 480px; margin: 0 auto; }
.checkout-box h2 { font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 1.5rem; }
.summary-row { display: flex; justify-content: space-between; color: rgba(255,255,255,.6); font-size: .9rem; margin-bottom: .75rem; }
.summary-row.total { color: #fff; font-weight: 700; font-size: 1.1rem; border-top: 1px solid rgba(255,255,255,.1); padding-top: .75rem; margin-top: .5rem; }
.checkout-cta { width: 100%; background: #dfff00; color: #111; border: none; border-radius: 10px; padding: 1rem; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: 1.5rem; transition: background .2s; }
.checkout-cta:hover { background: #c8e600; }
.checkout-note { text-align: center; color: rgba(255,255,255,.35); font-size: .78rem; margin-top: 1rem; }
.active-badge { background: rgba(223,255,0,.1); border: 1px solid rgba(223,255,0,.3); color: #dfff00; border-radius: 8px; padding: .75rem 1rem; text-align: center; font-size: .9rem; margin-bottom: 1.5rem; }
</style>

<div class="checkout-wrap">
  <h1 class="checkout-title">Escolha seu Plano</h1>
  <p class="checkout-sub">Acesse cupons exclusivos para motociclistas em toda a Serra Catarinense</p>

  <?php if ($subscription && $subscription['status'] === 'active'): ?>
  <div class="active-badge">
    ✅ Você já possui uma assinatura <strong><?= e(ucfirst($subscription['plan_type'])) ?></strong> ativa até <?= e(date('d/m/Y', strtotime($subscription['end_date'] ?? 'now'))) ?>.
  </div>
  <?php endif; ?>

  <div class="plans-grid">
    <?php foreach ($plans as $key => $plan): ?>
    <div class="plan-card <?= $key === $selectedPlan ? 'selected' : '' ?> <?= $key === 'monthly' ? 'popular' : '' ?>"
         onclick="selectPlan('<?= e($key) ?>')">
      <div class="plan-name"><?= e($plan['name']) ?></div>
      <div class="plan-price"><?= e($plan['price_label']) ?><span><?= e($plan['period']) ?></span></div>
      <ul class="plan-features">
        <?php foreach ($plan['features'] as $f): ?>
        <li><?= e($f) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if ($selectedPlan !== 'free'): ?>
  <div class="checkout-box">
    <h2>Resumo do Pedido</h2>
    <div class="summary-row">
      <span>Plano</span>
      <span><?= e($plans[$selectedPlan]['name']) ?></span>
    </div>
    <div class="summary-row">
      <span>Período</span>
      <span><?= $selectedPlan === 'yearly' ? 'Anual' : 'Mensal' ?></span>
    </div>
    <div class="summary-row total">
      <span>Total</span>
      <span><?= e($plans[$selectedPlan]['price_label']) ?><?= e($plans[$selectedPlan]['period']) ?></span>
    </div>

    <?php if (!$user): ?>
    <p style="color:rgba(255,255,255,.5);font-size:.85rem;text-align:center;margin:.75rem 0;">
      Faça login ou cadastre-se para assinar.
    </p>
    <a href="/login" class="checkout-cta" style="display:block;text-align:center;text-decoration:none;">
      Entrar e Assinar
    </a>
    <?php else: ?>
    <button class="checkout-cta" onclick="alert('Integração de pagamento em breve! Entre em contato pelo WhatsApp para assinar.')">
      Assinar Agora
    </button>
    <?php endif; ?>

    <p class="checkout-note">🔒 Pagamento seguro · Cancele quando quiser</p>
  </div>
  <?php else: ?>
  <div style="text-align:center;">
    <?php if (!$user): ?>
    <a href="/cadastro" style="display:inline-block;background:#dfff00;color:#111;font-weight:700;padding:.9rem 2.5rem;border-radius:10px;text-decoration:none;">
      Criar Conta Grátis
    </a>
    <?php else: ?>
    <a href="/" style="display:inline-block;background:#dfff00;color:#111;font-weight:700;padding:.9rem 2.5rem;border-radius:10px;text-decoration:none;">
      Explorar Cupons
    </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<script>
function selectPlan(key) {
  document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
  event.currentTarget.classList.add('selected');
  const url = new URL(window.location.href);
  url.searchParams.set('plan', key);
  window.history.replaceState({}, '', url);
  // Update summary
  setTimeout(() => location.reload(), 100);
}
</script>
