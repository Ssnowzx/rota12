<?php
declare(strict_types=1);

namespace App\Controllers\Partner;

use App\Core\ControllerBase;
use App\Core\DB;
use App\Core\PartnerAuth;
use App\Models\CampaignDailyViewModel;

class PartnerDashboardController extends ControllerBase
{
    public function redirectToDashboard(): void
    {
        $this->redirect('/parceiro/dashboard');
    }

    /**
     * GET /parceiro/dashboard/stats (AJAX)
     * Returns current dashboard statistics in JSON format for real-time updates.
     */
    public function stats(): void
    {
        PartnerAuth::requirePartner();
        $userId = PartnerAuth::userId();

        // Total generated (all statuses)
        $totalGerados = (int)(DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM generated_coupons WHERE partner_id = ?',
            [$userId]
        )['cnt'] ?? 0);

        // Total redeemed
        $totalResgatados = (int)(DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM generated_coupons WHERE partner_id = ? AND status = "utilizado"',
            [$userId]
        )['cnt'] ?? 0);

        // Conversion rate
        $taxaConversao = $totalGerados > 0 ? round(($totalResgatados / $totalGerados) * 100, 1) : 0;

        // Recent redemptions (last 20)
        $historico = DB::select(
            'SELECT g.codigo, c.titulo, g.utilizado_em, g.status
             FROM generated_coupons g
             JOIN coupon_campaigns c ON c.id = g.campaign_id
             WHERE g.partner_id = ? AND g.status = "utilizado"
             ORDER BY g.utilizado_em DESC
             LIMIT 20',
            [$userId]
        );

        // Mask codes: keep first 3 + asterisks
        foreach ($historico as &$h) {
            $code = $h['codigo'];
            $h['codigo_masked'] = strlen($code) > 3
                ? substr($code, 0, 3) . str_repeat('*', strlen($code) - 3)
                : $code;
        }
        unset($h);

        // 7-day evolution chart data
        $views7d = CampaignDailyViewModel::last7Days($userId);

        // Build 7-day labels and datasets
        $labels    = [];
        $viewsMap  = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[]         = date('d/m', strtotime($date));
            $viewsMap[$date]  = 0;
        }
        foreach ($views7d as $row) {
            $viewsMap[$row['view_date']] = (int)$row['total_views'];
        }

        // Coupons generated per day (last 7)
        $coupons7d = DB::select(
            'SELECT DATE(gerado_em) AS gen_date, COUNT(*) AS cnt
             FROM generated_coupons
             WHERE partner_id = ? AND gerado_em >= CURDATE() - INTERVAL 6 DAY
             GROUP BY gen_date ORDER BY gen_date ASC',
            [$userId]
        );
        $couponsMap = [];
        foreach ($labels as $i => $lbl) {
            $date = date('Y-m-d', strtotime("-" . (6 - $i) . " days"));
            $couponsMap[$date] = 0;
        }
        foreach ($coupons7d as $row) {
            $couponsMap[$row['gen_date']] = (int)$row['cnt'];
        }

        $graficoEvolucao = [
            'labels'   => array_values($labels),
            'views'    => array_values($viewsMap),
            'coupons'  => array_values($couponsMap),
        ];

        // Donut status chart
        $disponivel = (int)(DB::selectOne('SELECT COUNT(*) AS cnt FROM generated_coupons WHERE partner_id = ? AND status="disponivel"', [$userId])['cnt'] ?? 0);
        $utilizado  = (int)(DB::selectOne('SELECT COUNT(*) AS cnt FROM generated_coupons WHERE partner_id = ? AND status="utilizado"', [$userId])['cnt'] ?? 0);
        $expirado   = (int)(DB::selectOne('SELECT COUNT(*) AS cnt FROM generated_coupons WHERE partner_id = ? AND status="expirado"', [$userId])['cnt'] ?? 0);

        $graficoStatus = [
            'labels' => ['Disponíveis', 'Resgatados', 'Expirados'],
            'data'   => [$disponivel, $utilizado, $expirado],
        ];

        $this->json([
            'success'              => true,
            'total_gerados'        => $totalGerados,
            'total_resgatados'     => $totalResgatados,
            'taxa_conversao'       => $taxaConversao,
            'historico'            => $historico,
            'grafico_evolucao'     => $graficoEvolucao,
            'grafico_status'       => $graficoStatus,
        ]);
    }

    public function index(): void
    {
        PartnerAuth::requirePartner();
        $userId = PartnerAuth::userId();

        // Check if partner is pending approval
        if (PartnerAuth::isPendingApproval()) {
            $this->renderPartner('parceiro/dashboard/pending', [
                'pageTitle' => 'Aguardando Aprovação',
            ]);
            return;
        }

        // Active campaigns count
        $campanhasAtivas = (int)(DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM coupon_campaigns WHERE user_id = ? AND status = "ativa" AND deleted_at IS NULL',
            [$userId]
        )['cnt'] ?? 0);

        // Total generated (all statuses)
        $totalGerados = (int)(DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM generated_coupons WHERE partner_id = ?',
            [$userId]
        )['cnt'] ?? 0);

        // Total redeemed
        $totalResgatados = (int)(DB::selectOne(
            'SELECT COUNT(*) AS cnt FROM generated_coupons WHERE partner_id = ? AND status = "utilizado"',
            [$userId]
        )['cnt'] ?? 0);

        // Conversion rate
        $taxaConversao = $totalGerados > 0 ? round(($totalResgatados / $totalGerados) * 100, 1) : 0;

        // Recent redemptions (last 20)
        $historico = DB::select(
            'SELECT g.codigo, c.titulo, g.utilizado_em, g.status
             FROM generated_coupons g
             JOIN coupon_campaigns c ON c.id = g.campaign_id
             WHERE g.partner_id = ? AND g.status = "utilizado"
             ORDER BY g.utilizado_em DESC
             LIMIT 20',
            [$userId]
        );

        // Mask codes: keep first 3 + asterisks
        foreach ($historico as &$h) {
            $code = $h['codigo'];
            $h['codigo_masked'] = strlen($code) > 3
                ? substr($code, 0, 3) . str_repeat('*', strlen($code) - 3)
                : $code;
        }
        unset($h);

        // 7-day evolution chart data
        $views7d = CampaignDailyViewModel::last7Days($userId);

        // Build 7-day labels and datasets
        $labels    = [];
        $viewsMap  = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[]         = date('d/m', strtotime($date));
            $viewsMap[$date]  = 0;
        }
        foreach ($views7d as $row) {
            $viewsMap[$row['view_date']] = (int)$row['total_views'];
        }

        // Coupons generated per day (last 7)
        $coupons7d = DB::select(
            'SELECT DATE(gerado_em) AS gen_date, COUNT(*) AS cnt
             FROM generated_coupons
             WHERE partner_id = ? AND gerado_em >= CURDATE() - INTERVAL 6 DAY
             GROUP BY gen_date ORDER BY gen_date ASC',
            [$userId]
        );
        $couponsMap = [];
        foreach ($labels as $i => $lbl) {
            $date = date('Y-m-d', strtotime("-" . (6 - $i) . " days"));
            $couponsMap[$date] = 0;
        }
        foreach ($coupons7d as $row) {
            $couponsMap[$row['gen_date']] = (int)$row['cnt'];
        }

        $graficoEvolucao = [
            'labels'   => array_values($labels),
            'views'    => array_values($viewsMap),
            'coupons'  => array_values($couponsMap),
        ];

        // Donut status chart
        $disponivel = (int)(DB::selectOne('SELECT COUNT(*) AS cnt FROM generated_coupons WHERE partner_id = ? AND status="disponivel"', [$userId])['cnt'] ?? 0);
        $utilizado  = (int)(DB::selectOne('SELECT COUNT(*) AS cnt FROM generated_coupons WHERE partner_id = ? AND status="utilizado"', [$userId])['cnt'] ?? 0);
        $expirado   = (int)(DB::selectOne('SELECT COUNT(*) AS cnt FROM generated_coupons WHERE partner_id = ? AND status="expirado"', [$userId])['cnt'] ?? 0);

        $graficoStatus = [
            'labels' => ['Disponíveis', 'Resgatados', 'Expirados'],
            'data'   => [$disponivel, $utilizado, $expirado],
        ];

        $this->renderPartner('parceiro/dashboard/index', [
            'pageTitle'            => 'Dashboard',
            'user'                 => PartnerAuth::user(),
            'campanhas_ativas'     => $campanhasAtivas,
            'total_gerados'        => $totalGerados,
            'total_resgatados'     => $totalResgatados,
            'taxa_conversao'       => $taxaConversao,
            'historico'            => $historico,
            'grafico_evolucao_json'=> json_encode($graficoEvolucao, JSON_THROW_ON_ERROR),
            'grafico_status_json'  => json_encode($graficoStatus, JSON_THROW_ON_ERROR),
        ]);
    }
}
