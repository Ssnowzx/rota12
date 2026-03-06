<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\ControllerBase;
use App\Core\PartnerAuth;
use App\Core\DB;

class SubscriptionController extends ControllerBase
{
    private const PLANS = [
        'free'    => ['name' => 'Grátis',    'price' => 0,      'price_label' => 'Grátis',        'period' => '',     'features' => ['Acesso a cupons básicos', 'Newsletter exclusiva', 'Acesso à comunidade']],
        'monthly' => ['name' => 'Premium',   'price' => 29.90,  'price_label' => 'R$ 29,90',       'period' => '/mês', 'features' => ['Todos os cupons premium', 'Descontos exclusivos em rotas', 'Suporte prioritário', 'App mobile (em breve)', 'Cupons ilimitados']],
        'yearly'  => ['name' => 'Anual',     'price' => 239.90, 'price_label' => 'R$ 239,90',      'period' => '/ano', 'features' => ['Tudo do Premium', '2 meses grátis', 'Acesso antecipado a novidades', 'Badge exclusivo de membro anual', 'Cupons ilimitados']],
    ];

    public function index(): void
    {
        $plan = $_GET['plan'] ?? 'monthly';
        if (!isset(self::PLANS[$plan])) {
            $plan = 'monthly';
        }

        $user = PartnerAuth::check() ? PartnerAuth::user() : null;
        $subscription = null;

        if ($user) {
            $subscription = DB::selectOne(
                "SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1",
                [$user['id']]
            );
        }

        $this->render('subscription/index', [
            'pageTitle'    => 'Assinatura Premium — Rota 12',
            'plans'        => self::PLANS,
            'selectedPlan' => $plan,
            'user'         => $user,
            'subscription' => $subscription,
        ]);
    }

    public function success(): void
    {
        $this->render('subscription/success', [
            'pageTitle' => 'Assinatura Confirmada — Rota 12',
        ]);
    }
}
