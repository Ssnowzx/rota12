<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\CSRF;
use App\Core\PartnerAuth;
use App\Models\UserModel;
use App\Models\SubscriptionModel;
use App\Models\GeneratedCouponModel;
use App\Models\CampaignModel;

class AccountController extends ControllerBase
{
    public function index(): void
    {
        PartnerAuth::requireLogin('/login');

        $userId = PartnerAuth::userId();
        $user   = UserModel::getProfile($userId);

        $subscription  = SubscriptionModel::current($userId);
        $recentCoupons = GeneratedCouponModel::recentByUser($userId, 10);

        $this->render('account/index', [
            'pageTitle'     => 'Minha Conta — Rota 12',
            'user'          => $user,
            'avatars'       => UserModel::AVATARS,
            'csrf'          => CSRF::generate(),
            'subscription'  => $subscription,
            'recentCoupons' => $recentCoupons,
            'flash'         => $this->getFlash(),
        ]);
    }

    /**
     * POST /minha-conta/pegar-cupom
     * Generate a unique coupon code for the logged-in user.
     * Returns JSON for AJAX requests, redirects for form submissions.
     */
    public function grabCoupon(): void
    {
        PartnerAuth::requireLogin('/login');
        CSRF::check();

        $userId     = PartnerAuth::userId();
        $campaignId = (int)($_POST['campaign_id'] ?? 0);
        $isAjax     = !empty($_POST['ajax']);

        if ($campaignId <= 0) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Campanha inválida.'], 400);
            }
            $this->flash('error', 'Campanha inválida.');
            $this->redirect('/cupons');
        }

        // Check campaign exists and is active
        $campaign = CampaignModel::find($campaignId);
        if (!$campaign || $campaign['status'] !== 'ativa') {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Esta campanha não está disponível.'], 400);
            }
            $this->flash('error', 'Esta campanha não está disponível.');
            $this->redirect('/cupons');
        }

        // Check subscription requirement
        if ($campaign['exige_assinatura']) {
            if (!SubscriptionModel::isActive($userId)) {
                if ($isAjax) {
                    $this->json(['success' => false, 'error' => 'Este cupom é exclusivo para assinantes.', 'redirect' => '/checkout'], 403);
                }
                $this->flash('error', 'Este cupom é exclusivo para assinantes. Assine o Rota 12 para ter acesso.');
                $this->redirect('/checkout');
            }
        }

        // Get partner user_id from campaign
        $partnerId = (int)$campaign['user_id'];

        // Generate coupon
        $result = GeneratedCouponModel::generate($campaignId, $userId, $partnerId);

        if ($result === null) {
            if ($isAjax) {
                $this->json(['success' => false, 'error' => 'Não foi possível gerar o cupom. O limite pode ter sido atingido.'], 400);
            }
            $this->flash('error', 'Não foi possível gerar o cupom. O limite pode ter sido atingido.');
            $this->redirect('/cupons');
        }

        if ($isAjax) {
            $this->json([
                'success' => true,
                'codigo'  => $result['codigo'],
                'message' => 'Cupom gerado com sucesso!',
            ]);
        }

        $this->flash('success', 'Cupom gerado! Seu código: ' . $result['codigo']);
        $this->redirect('/minha-conta');
    }

    /**
     * POST /minha-conta/atualizar-avatar
     * Update the user's avatar preset.
     */
    public function updateAvatar(): void
    {
        PartnerAuth::requireLogin('/login');
        CSRF::check();

        $userId = PartnerAuth::userId();
        $avatar = trim($_POST['avatar'] ?? 'default');

        UserModel::updateAvatar($userId, $avatar);

        $this->flash('success', 'Avatar atualizado!');
        $this->redirect('/minha-conta');
    }

    /**
     * POST /minha-conta/cancelar-assinatura
     * Cancel the user's active subscription.
     */
    public function cancelSubscription(): void
    {
        PartnerAuth::requireLogin('/login');
        CSRF::check();

        $userId = PartnerAuth::userId();
        $reason = trim($_POST['motivo'] ?? '');

        $cancelled = SubscriptionModel::cancel($userId, $reason);

        if ($cancelled) {
            $this->flash('success', 'Sua assinatura foi cancelada. Sentiremos sua falta!');
        } else {
            $this->flash('error', 'Não foi possível cancelar. Você pode não ter uma assinatura ativa.');
        }

        $this->redirect('/minha-conta');
    }
}
