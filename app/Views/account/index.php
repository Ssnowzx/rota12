<?php /** @var array $user @var array|null $subscription @var array $recentCoupons @var array $avatars @var string $csrf @var array|null $flash */ ?>
<style>
.account-wrap { max-width: 860px; margin: 0 auto; padding: 7rem 1.5rem 4rem; }
.account-header { display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2.5rem; }
.account-avatar { width: 72px; height: 72px; border-radius: 50%; background: #dfff00; display: flex; align-items: center; justify-content: center; color: #111; flex-shrink: 0; cursor: pointer; position: relative; transition: transform .15s; overflow: hidden; padding: 0; }
.account-avatar:hover { transform: scale(1.08); }
.avatar-picker { display: none; position: absolute; top: calc(100% + .5rem); left: 0; background: #1a1a1a; border: 1px solid rgba(255,255,255,.12); border-radius: 12px; padding: .75rem; z-index: 50; width: 280px; }
.avatar-picker.open { display: grid; grid-template-columns: repeat(3, 1fr); gap: .8rem; }
.avatar-option { width: 62px; height: 62px; border-radius: 50%; background: rgba(255,255,255,.05); border: 2px solid transparent; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .15s; overflow: hidden; padding: 0; }
.avatar-option:hover { background: rgba(223,255,0,.15); border-color: rgba(223,255,0,.4); }
.avatar-option.active { border-color: #dfff00; background: rgba(223,255,0,.2); }
.avatar-option-label { font-size: .6rem; color: rgba(255,255,255,.4); text-align: center; margin-top: 2px; }
.flash-msg { padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .85rem; }
.flash-msg.success { background: rgba(100,200,100,.1); border: 1px solid rgba(100,200,100,.3); color: #6fc; }
.flash-msg.error { background: rgba(255,80,80,.1); border: 1px solid rgba(255,80,80,.3); color: #ff8080; }
.account-name { font-size: 1.5rem; font-weight: 700; color: #fff; }
.account-email { color: rgba(255,255,255,.5); font-size: .9rem; }
.account-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
@media(max-width:640px){ .account-grid { grid-template-columns: 1fr; } }
.acc-card { background: #1a1a1a; border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: 1.5rem; }
.acc-card-title { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.4); margin-bottom: 1rem; }
.plan-badge { display: inline-flex; align-items: center; gap: .5rem; background: rgba(223,255,0,.1); border: 1px solid rgba(223,255,0,.3); color: #dfff00; border-radius: 8px; padding: .5rem 1rem; font-weight: 700; font-size: .95rem; }
.plan-badge.free { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); color: rgba(255,255,255,.5); }
.upgrade-btn { display: inline-block; margin-top: 1rem; background: #dfff00; color: #111; font-weight: 700; font-size: .85rem; padding: .6rem 1.2rem; border-radius: 8px; text-decoration: none; }
.info-row { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid rgba(255,255,255,.05); font-size: .9rem; }
.info-row:last-child { border-bottom: none; }
.info-label { color: rgba(255,255,255,.4); }
.info-value { color: #fff; }
.coupon-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.coupon-table th { color: rgba(255,255,255,.4); text-align: left; padding: .4rem 0; font-weight: 600; font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid rgba(255,255,255,.08); }
.coupon-table td { padding: .6rem 0; border-bottom: 1px solid rgba(255,255,255,.04); color: rgba(255,255,255,.7); }
.coupon-table td:first-child { font-family: monospace; color: #dfff00; font-weight: 700; }
.badge-used { background: rgba(100,200,100,.1); color: #6fc; border-radius: 4px; padding: 2px 8px; font-size: .75rem; }
.badge-available { background: rgba(223,255,0,.1); color: #dfff00; border-radius: 4px; padding: 2px 8px; font-size: .75rem; }
.empty-state { color: rgba(255,255,255,.3); text-align: center; padding: 2rem 0; font-size: .9rem; }
.logout-link { display: inline-block; margin-top: 2rem; color: rgba(255,255,255,.4); font-size: .85rem; text-decoration: none; }
.logout-link:hover { color: #ff8080; }
</style>

<div class="account-wrap">
  <?php if (!empty($flash)): ?>
    <?php foreach ((array)$flash as $f): ?>
      <div class="flash-msg <?= e($f['type'] ?? 'success') ?>"><?= e($f['message'] ?? '') ?></div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="account-header">
    <div style="position:relative;">
      <div class="account-avatar" onclick="document.getElementById('avatarPicker').classList.toggle('open')" title="Clique para trocar avatar">
        <?php $currentAvatarUrl = \App\Models\UserModel::avatarUrl($user['avatar'] ?? 'default'); ?>
        <?php if ($currentAvatarUrl): ?>
          <img src="<?= e($currentAvatarUrl) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
        <?php else: ?>
          <i class="fas fa-user" style="color: #111; font-size: 2rem;"></i>
        <?php endif; ?>
      </div>
      <div id="avatarPicker" class="avatar-picker">
        <?php foreach ($avatars as $key => $url): ?>
          <form method="post" action="/minha-conta/atualizar-avatar" style="display:inline;">
            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="avatar" value="<?= e($key) ?>">
            <button type="submit" class="avatar-option <?= ($user['avatar'] ?? 'default') === $key ? 'active' : '' ?>" title="<?= e($key === 'default' ? 'Sem Avatar' : $key) ?>">
              <?php if ($url): ?>
                <img src="<?= e($url) ?>" alt="<?= e($key) ?>" style="width:100%; height:100%; object-fit:cover;">
              <?php else: ?>
                <i class="fas fa-user" style="color: rgba(255,255,255,0.5); font-size: 1.5rem;"></i>
              <?php endif; ?>
            </button>
          </form>
        <?php endforeach; ?>
      </div>
    </div>
    <div>
      <div class="account-name"><?= e($user['username'] ?? 'Usuário') ?></div>
      <div class="account-email"><?= e($user['email'] ?? '') ?></div>
    </div>
  </div>

  <div class="account-grid">
    <!-- Assinatura -->
    <div class="acc-card">
      <div class="acc-card-title">Minha Assinatura</div>
      <?php if ($subscription && $subscription['status'] === 'active'): ?>
        <div class="plan-badge">
          <i class="fas fa-star"></i>
          <?= e(ucfirst($subscription['plan_type'])) ?> Ativo
        </div>
        <?php if (!empty($subscription['end_date'])): ?>
        <p style="color:rgba(255,255,255,.4);font-size:.8rem;margin-top:.75rem;">
          Válido até <?= e(date('d/m/Y', strtotime($subscription['end_date']))) ?>
        </p>
        <?php endif; ?>
        <a href="/checkout" class="upgrade-btn" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.6);">Ver Planos</a>
      <?php else: ?>
        <div class="plan-badge free"><i class="fas fa-user"></i> Grátis</div>
        <p style="color:rgba(255,255,255,.4);font-size:.82rem;margin-top:.75rem;">
          Assine o Premium para acessar cupons exclusivos e descontos em toda a rota.
        </p>
        <a href="/checkout?plan=monthly" class="upgrade-btn">⚡ Assinar Premium</a>
      <?php endif; ?>
    </div>

    <!-- Dados -->
    <div class="acc-card">
      <div class="acc-card-title">Dados da Conta</div>
      <div class="info-row">
        <span class="info-label">Nome</span>
        <span class="info-value"><?= e($user['username'] ?? '—') ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">E-mail</span>
        <span class="info-value"><?= e($user['email'] ?? '—') ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Tipo</span>
        <span class="info-value"><?= e(ucfirst($user['role'] ?? 'member')) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Membro desde</span>
        <span class="info-value"><?= e(date('d/m/Y', strtotime($user['created_at'] ?? 'now'))) ?></span>
      </div>
    </div>

    <!-- Cupons recentes -->
    <div class="acc-card" style="grid-column: 1 / -1;">
      <div class="acc-card-title">Meus Cupons Recentes</div>
      <?php if (empty($recentCoupons)): ?>
        <div class="empty-state">
          <i class="fas fa-ticket-alt" style="font-size:2rem;display:block;margin-bottom:.75rem;opacity:.3;"></i>
          Você ainda não resgatou nenhum cupom.<br>
          <a href="/cupons" style="color:#dfff00;text-decoration:none;margin-top:.5rem;display:inline-block;">Ver cupons disponíveis →</a>
        </div>
      <?php else: ?>
        <table class="coupon-table">
          <thead>
            <tr>
              <th>Código</th>
              <th>Campanha</th>
              <th>Data</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentCoupons as $c): ?>
            <tr>
              <td><?= e($c['codigo']) ?></td>
              <td><?= e($c['titulo']) ?></td>
              <td><?= e(date('d/m/Y', strtotime($c['gerado_em']))) ?></td>
              <td>
                <?php if ($c['status'] === 'utilizado'): ?>
                  <span class="badge-used">Utilizado</span>
                <?php else: ?>
                  <span class="badge-available">Disponível</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($user['role'] === 'parceiro'): ?>
  <div style="margin-top:1.5rem;">
    <a href="/parceiro/dashboard" style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(223,255,0,.1);color:#dfff00;border:1px solid rgba(223,255,0,.3);border-radius:8px;padding:.6rem 1.2rem;text-decoration:none;font-weight:600;font-size:.9rem;">
      <i class="fas fa-store"></i> Ir para o Painel do Parceiro
    </a>
  </div>
  <?php endif; ?>

  <a href="/sair" class="logout-link"><i class="fas fa-sign-out-alt"></i> Sair da conta</a>
</div>

<script>
document.addEventListener('click', function(e) {
  const picker = document.getElementById('avatarPicker');
  if (picker && !e.target.closest('.account-avatar') && !e.target.closest('#avatarPicker')) {
    picker.classList.remove('open');
  }
});
</script>
