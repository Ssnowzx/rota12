<?php
declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\{ControllerBase, Auth, DB, EmailService, CSRF};
use App\Models\{PartnerModel, PartnerApprovalModel, AuditModel};

/**
 * Partner Approval Controller
 *
 * Manages partner approval/rejection workflow in admin dashboard.
 * - List pending partners with pagination
 * - Show approval details
 * - Approve partner (update status + activate user)
 * - Reject partner (update status + send email)
 */
class PartnerApprovalController extends ControllerBase
{
    /**
     * List pending partner approval requests
     *
     * GET /administrator/approval/partners
     */
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm('partners.approve');

        $page    = (int)($_GET['page'] ?? 1);
        $perPage = 20;

        // Get pending partners with pagination
        $result = PartnerModel::listPending($page, $perPage);

        $this->renderAdmin('approval/partners/index', [
            'pageTitle'  => 'Parceiros Pendentes de Aprovação',
            'partners'   => $result['data'],
            'total'      => $result['total'],
            'pages'      => $result['pages'],
            'current'    => $result['current'],
        ]);
    }

    /**
     * Show approval details for specific partner
     *
     * GET /administrator/approval/partners/{id}
     */
    public function show(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('partners.approve');

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            $this->renderAdmin('errors/404', ['pageTitle' => 'Parâmetro inválido']);
            return;
        }

        $partner = PartnerModel::getWithUser($id);
        if (!$partner) {
            http_response_code(404);
            $this->renderAdmin('errors/404', ['pageTitle' => 'Parceiro não encontrado']);
            return;
        }

        $approvalDetails = PartnerApprovalModel::getApprovalDetails($id);

        $this->renderAdmin('approval/partners/show', [
            'pageTitle'         => 'Detalhe da Aprovação',
            'partner'           => $partner,
            'approvalDetails'   => $approvalDetails,
        ]);
    }

    /**
     * Approve a partner
     *
     * POST /administrator/approval/partners/{id}/approve
     */
    public function approve(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('partners.approve');
        CSRF::check();

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            $this->json(['error' => 'Parâmetro inválido']);
            return;
        }

        $partner = PartnerModel::find($id);
        if (!$partner) {
            http_response_code(404);
            $this->json(['error' => 'Parceiro não encontrado']);
            return;
        }

        $notes = trim($_POST['notes'] ?? '');
        $adminId = Auth::adminId();

        if (!$adminId) {
            http_response_code(401);
            $this->json(['error' => 'Admin não autenticado']);
            return;
        }

        $success = PartnerModel::approve($id, $adminId, $notes);

        if ($success) {
            // Log approval action
            AuditModel::log(
                $adminId,
                'partner_approved',
                'partners',
                $id,
                ['partner_name' => $partner['name'], 'notes' => $notes],
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            // Send approval email to partner
            EmailService::sendApprovalEmail($partner['email'], $partner['username'], ['notes' => $notes]);

            $this->json(['success' => true, 'message' => 'Parceiro aprovado com sucesso!']);
        } else {
            http_response_code(500);
            $this->json(['error' => 'Erro ao aprovar parceiro']);
        }
    }

    /**
     * Reject a partner
     *
     * POST /administrator/approval/partners/{id}/reject
     */
    public function reject(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('partners.approve');
        CSRF::check();

        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            $this->json(['error' => 'Parâmetro inválido']);
            return;
        }

        $partner = PartnerModel::find($id);
        if (!$partner) {
            http_response_code(404);
            $this->json(['error' => 'Parceiro não encontrado']);
            return;
        }

        $reason = trim($_POST['reason'] ?? '');
        if ($reason === '') {
            http_response_code(400);
            $this->json(['error' => 'Motivo da rejeição é obrigatório']);
            return;
        }

        $adminId = Auth::adminId();
        if (!$adminId) {
            http_response_code(401);
            $this->json(['error' => 'Admin não autenticado']);
            return;
        }

        $success = PartnerModel::reject($id, $adminId, $reason);

        if ($success) {
            // Log rejection action
            AuditModel::log(
                $adminId,
                'partner_rejected',
                'partners',
                $id,
                ['partner_name' => $partner['name'], 'reason' => $reason],
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            // Send rejection email to partner with reason
            EmailService::sendRejectionEmail($partner['email'], $partner['username'], $reason);

            $this->json(['success' => true, 'message' => 'Parceiro rejeitado com sucesso!']);
        } else {
            http_response_code(500);
            $this->json(['error' => 'Erro ao rejeitar parceiro']);
        }
    }

    /**
     * Get approval statistics for dashboard card
     *
     * GET /api/admin/approval/stats (AJAX endpoint)
     */
    public static function getStats(): array
    {
        return PartnerApprovalModel::getStats();
    }
}
