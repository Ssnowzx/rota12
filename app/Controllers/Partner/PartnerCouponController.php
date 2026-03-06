<?php
declare(strict_types=1);

namespace App\Controllers\Partner;

use App\Core\ControllerBase;
use App\Core\DB;
use App\Core\PartnerAuth;

class PartnerCouponController extends ControllerBase
{
    /**
     * POST /parceiro/cupons/validar
     * Validates a coupon code for this partner and marks it as used.
     */
    public function validar(): void
    {
        PartnerAuth::requirePartner();
        $userId = PartnerAuth::userId();

        // Accept both JSON body and form-encoded POST
        if (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $code = strtoupper(trim($body['codigo'] ?? ''));
        } else {
            $code = strtoupper(trim($_POST['codigo'] ?? ''));
        }

        if ($code === '') {
            $this->json(['ok' => false, 'mensagem' => 'Informe o código do cupom.']);
        }

        $coupon = DB::selectOne(
            "SELECT g.*, c.titulo AS campanha_titulo
             FROM generated_coupons g
             JOIN coupon_campaigns c ON c.id = g.campaign_id
             WHERE g.codigo = ? AND g.partner_id = ? LIMIT 1",
            [$code, $userId]
        );

        if ($coupon === null) {
            $this->json(['ok' => false, 'mensagem' => 'Cupom não encontrado ou não pertence a este parceiro.']);
        }

        if ($coupon['status'] !== 'disponivel') {
            $statusMsg = match ($coupon['status']) {
                'utilizado' => 'Este cupom já foi utilizado.',
                'expirado'  => 'Este cupom está expirado.',
                'cancelado' => 'Este cupom foi cancelado.',
                default     => 'Cupom inválido.',
            };
            $this->json(['ok' => false, 'mensagem' => $statusMsg]);
        }

        DB::execute(
            "UPDATE generated_coupons SET status = 'utilizado', utilizado_em = NOW() WHERE id = ?",
            [$coupon['id']]
        );

        $this->json([
            'ok'       => true,
            'mensagem' => 'Cupom validado com sucesso!',
            'campanha' => $coupon['campanha_titulo'],
        ]);
    }
}
