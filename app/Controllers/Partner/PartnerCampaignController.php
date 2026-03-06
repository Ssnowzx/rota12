<?php
declare(strict_types=1);

namespace App\Controllers\Partner;

use App\Core\ControllerBase;
use App\Core\CSRF;
use App\Core\DB;
use App\Core\PartnerAuth;
use App\Core\UploadHandler;
use App\Models\SubscriptionModel;

class PartnerCampaignController extends ControllerBase
{
    public function index(): void
    {
        PartnerAuth::requirePartner();
        $userId = PartnerAuth::userId();

        $filterStatus = $_GET['status'] ?? 'todas';

        $where  = 'WHERE c.user_id = ? AND c.deleted_at IS NULL';
        $params = [$userId];
        if (in_array($filterStatus, ['ativa', 'pausada', 'encerrada', 'rascunho'], true)) {
            $where  .= ' AND c.status = ?';
            $params[] = $filterStatus;
        }

        $campanhas = DB::select(
            "SELECT c.*,
                (SELECT COUNT(*) FROM generated_coupons g WHERE g.campaign_id = c.id) AS total_gerados,
                (SELECT COUNT(*) FROM generated_coupons g WHERE g.campaign_id = c.id AND g.status = 'utilizado') AS total_resgatados
             FROM coupon_campaigns c $where ORDER BY c.created_at DESC",
            $params
        );

        $this->renderPartner('parceiro/campanhas/index', [
            'pageTitle'    => 'Campanhas',
            'user'         => PartnerAuth::user(),
            'campanhas'    => $campanhas,
            'filterStatus' => $filterStatus,
            'csrf'         => CSRF::generate(),
        ]);
    }

    public function store(): void
    {
        PartnerAuth::requirePartner();
        CSRF::check();
        $userId = PartnerAuth::userId();

        // Check if partner is still pending approval (not approved yet)
        if (PartnerAuth::isPendingApproval()) {
            $this->flash('error', 'Sua solicitação de parceria ainda está aguardando aprovação. Você poderá criar campanhas após ser aprovado.');
            $this->redirect('/parceiro/campanhas');
        }

        $titulo          = trim($_POST['titulo'] ?? '');
        $descricao       = trim($_POST['descricao'] ?? '');
        $categoria       = trim($_POST['categoria'] ?? '');
        $tipoDesconto    = $_POST['tipo_desconto'] ?? 'percentual';
        $valorDesconto   = (float)($_POST['valor_desconto'] ?? 0);
        $exigeAssinatura = (int)($_POST['exige_assinatura'] ?? 1);
        $maxUses         = max(1, (int)($_POST['max_uses_per_user'] ?? 1));
        $limiteTotalRaw  = trim($_POST['limite_total'] ?? '');
        $limiteTotal     = $limiteTotalRaw !== '' ? (int)$limiteTotalRaw : null;
        $dataInicio      = trim($_POST['data_inicio'] ?? date('Y-m-d'));
        $dataFim         = trim($_POST['data_fim'] ?? '') ?: null;

        if ($titulo === '') {
            $this->flash('error', 'Título é obrigatório.');
            $this->redirect('/parceiro/campanhas');
        }

        if (!in_array($tipoDesconto, ['percentual', 'valor_fixo'], true)) {
            $tipoDesconto = 'percentual';
        }

        // Handle image upload
        $imagePath = null;
        if (!empty($_FILES['image'])) {
            try {
                $imagePath = UploadHandler::uploadImage($_FILES['image'], 'campaigns');
            } catch (\RuntimeException $e) {
                $this->flash('error', 'Erro ao fazer upload da imagem: ' . $e->getMessage());
                $this->redirect('/parceiro/campanhas');
            }
        }

        // Check if image_path column exists in the table
        $hasImageColumn = false;
        try {
            $cols = DB::select("SHOW COLUMNS FROM coupon_campaigns LIKE 'image_path'");
            $hasImageColumn = !empty($cols);
        } catch (\Throwable $e) {
            // Column check failed — assume it doesn't exist
        }

        if ($hasImageColumn) {
            DB::insert(
                'INSERT INTO coupon_campaigns
                 (user_id, titulo, descricao, image_path, categoria, tipo_desconto, valor_desconto, exige_assinatura, max_uses_per_user, limite_total, data_inicio, data_fim, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "ativa")',
                [$userId, $titulo, $descricao ?: null, $imagePath, $categoria ?: null, $tipoDesconto, $valorDesconto, $exigeAssinatura, $maxUses, $limiteTotal, $dataInicio, $dataFim]
            );
        } else {
            // Fallback: insert without image_path (migration not yet applied)
            DB::insert(
                'INSERT INTO coupon_campaigns
                 (user_id, titulo, descricao, categoria, tipo_desconto, valor_desconto, exige_assinatura, max_uses_per_user, limite_total, data_inicio, data_fim, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "ativa")',
                [$userId, $titulo, $descricao ?: null, $categoria ?: null, $tipoDesconto, $valorDesconto, $exigeAssinatura, $maxUses, $limiteTotal, $dataInicio, $dataFim]
            );
            // Clean up uploaded file since we can't save the path
            if ($imagePath !== null) {
                UploadHandler::delete($imagePath);
            }
        }

        $this->flash('success', 'Campanha criada com sucesso!');
        $this->redirect('/parceiro/campanhas');
    }

    public function status(array $params): void
    {
        PartnerAuth::requirePartner();
        CSRF::check();
        $userId     = PartnerAuth::userId();
        $campaignId = (int)($params['id'] ?? 0);

        $campaign = DB::selectOne(
            'SELECT * FROM coupon_campaigns WHERE id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1',
            [$campaignId, $userId]
        );

        if (!$campaign) {
            $this->json(['ok' => false, 'mensagem' => 'Campanha não encontrada.'], 404);
        }

        $newStatus = $campaign['status'] === 'ativa' ? 'pausada' : 'ativa';
        DB::execute(
            'UPDATE coupon_campaigns SET status = ? WHERE id = ? AND user_id = ?',
            [$newStatus, $campaignId, $userId]
        );

        $this->flash('success', 'Status da campanha atualizado.');
        $this->redirect('/parceiro/campanhas');
    }

    public function destroy(array $params): void
    {
        PartnerAuth::requirePartner();
        CSRF::check();
        $userId     = PartnerAuth::userId();
        $campaignId = (int)($params['id'] ?? 0);

        // Get campaign to delete its image
        $campaign = DB::selectOne(
            'SELECT * FROM coupon_campaigns WHERE id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1',
            [$campaignId, $userId]
        );

        if ($campaign && !empty($campaign['image_path'])) {
            UploadHandler::delete($campaign['image_path']);
        }

        DB::execute(
            'UPDATE coupon_campaigns SET deleted_at = NOW(), status = "encerrada" WHERE id = ? AND user_id = ?',
            [$campaignId, $userId]
        );

        $this->flash('success', 'Campanha removida.');
        $this->redirect('/parceiro/campanhas');
    }
}
