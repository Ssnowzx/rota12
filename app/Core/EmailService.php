<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Email Service
 *
 * Handles sending emails for partner approval system.
 * Integrates with PHP mail() function or SMTP if configured.
 */
class EmailService
{
    private static function fromEmail(): string
    {
        return getenv('MAIL_FROM_EMAIL') ?: 'noreply@rota12.local';
    }

    private static function fromName(): string
    {
        return getenv('MAIL_FROM_NAME') ?: 'Rota 12';
    }

    private static function supportEmail(): string
    {
        return getenv('MAIL_SUPPORT_EMAIL') ?: self::fromEmail();
    }

    private static function appUrl(): string
    {
        return rtrim(defined('APP_URL') ? APP_URL : 'http://localhost', '/');
    }

    /**
     * Send approval confirmation email to partner
     */
    public static function sendApprovalEmail(string $email, string $partnerName, array $adminNotes = []): bool
    {
        $subject = '[Rota 12] Sua solicitação de parceria foi aprovada!';
        $appUrl = self::appUrl();
        $supportEmail = self::supportEmail();

        $notes = '';
        if (!empty($adminNotes)) {
            $notes = '<p><strong>Observações do administrador:</strong></p>';
            $notes .= '<p>' . nl2br(e($adminNotes['notes'] ?? '')) . '</p>';
        }

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 8px; }
        h1 { color: #333; }
        .success-message { background: #c8e6c9; border-left: 4px solid #4caf50; padding: 1rem; margin: 1.5rem 0; }
        .next-steps { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 1rem; margin: 1.5rem 0; }
        .footer { color: #999; font-size: 0.9rem; margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Parabéns, {$partnerName}!</h1>

        <div class="success-message">
            <strong>Sua solicitação de cadastro como parceiro na Rota 12 foi aprovada!</strong>
        </div>

        <p>Você agora tem acesso total ao painel de parceiro e pode começar a criar campanhas de cupons para seus clientes.</p>

        <div class="next-steps">
            <strong>Próximos passos:</strong>
            <ol>
                <li>Acesse seu painel em <a href="{$appUrl}/parceiro/dashboard">{$appUrl}/parceiro/dashboard</a></li>
                <li>Configure seus dados de negócio</li>
                <li>Crie sua primeira campanha de cupons</li>
            </ol>
        </div>

        {$notes}

        <p>Qualquer dúvida, entre em contato com nosso suporte através de <a href="mailto:{$supportEmail}">{$supportEmail}</a></p>

        <div class="footer">
            <p>&copy; 2026 Rota 12. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
HTML;

        return self::send($email, $partnerName, $subject, $html);
    }

    /**
     * Send rejection email to partner
     */
    public static function sendRejectionEmail(string $email, string $partnerName, string $rejectionReason): bool
    {
        $subject = '[Rota 12] Solicitação de parceria — Informação Importante';
        $supportEmail = self::supportEmail();

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 8px; }
        h1 { color: #333; }
        .warning-message { background: #fff3cd; border-left: 4px solid #ff9800; padding: 1rem; margin: 1.5rem 0; }
        .rejection-reason { background: #ffebee; border-left: 4px solid #f44336; padding: 1rem; margin: 1.5rem 0; color: #c62828; }
        .reapply { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 1rem; margin: 1.5rem 0; }
        .footer { color: #999; font-size: 0.9rem; margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Solicitação de Parceria — Revisão Necessária</h1>

        <div class="warning-message">
            <strong>Sua solicitação de cadastro como parceiro foi revisada pelo nosso time.</strong>
        </div>

        <p>Infelizmente, no momento não podemos aprovar seu cadastro como parceiro na Rota 12.</p>

        <div class="rejection-reason">
            <strong>Motivo:</strong>
            <p>{$rejectionReason}</p>
        </div>

        <div class="reapply">
            <strong>O que fazer agora?</strong>
            <p>Você pode resolver os pontos mencionados acima e enviar uma nova solicitação quando estiver pronto. Nosso time estará disponível para ajudar.</p>
        </div>

        <p>Para mais informações, entre em contato com <a href="mailto:{$supportEmail}">{$supportEmail}</a></p>

        <div class="footer">
            <p>&copy; 2026 Rota 12. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
HTML;

        return self::send($email, $partnerName, $subject, $html);
    }

    /**
     * Send admin notification about new partner request
     */
    public static function sendAdminNotification(
        string $adminEmail,
        string $partnerName,
        string $partnerEmail,
        int $partnerId
    ): bool {
        $subject = '[Rota 12] Nova solicitação de parceria — Ação Requerida';
        $approvalUrl = self::appUrl() . '/administrator/approval/partners/' . $partnerId;

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 8px; }
        h1 { color: #333; }
        .notification { background: #e8f5e9; border-left: 4px solid #4caf50; padding: 1rem; margin: 1.5rem 0; }
        .details { background: #f5f5f5; padding: 1rem; border-radius: 4px; margin: 1rem 0; }
        .action-button { display: inline-block; background: #2196f3; color: #fff; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px; margin-top: 1rem; }
        .footer { color: #999; font-size: 0.9rem; margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Nova Solicitação de Parceria</h1>

        <div class="notification">
            <strong>Um novo parceiro submeteu uma solicitação de cadastro na Rota 12.</strong>
        </div>

        <div class="details">
            <p><strong>Nome de usuário:</strong> {$partnerName}</p>
            <p><strong>E-mail:</strong> {$partnerEmail}</p>
            <p><strong>ID do Parceiro:</strong> {$partnerId}</p>
        </div>

        <p>Analise os dados da solicitação e aprove ou rejeite através do painel administrativo.</p>

        <a href="{$approvalUrl}" class="action-button">Revisar Solicitação</a>

        <div class="footer">
            <p>Esta é uma mensagem automática. Não responda este e-mail.</p>
            <p>&copy; 2026 Rota 12. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
HTML;

        return self::send($adminEmail, 'Admin', $subject, $html);
    }

    /**
     * Generic send email method
     */
    private static function send(string $to, string $toName, string $subject, string $html): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . self::fromName() . " <" . self::fromEmail() . ">\r\n";
        $headers .= "Reply-To: " . self::fromEmail() . "\r\n";

        return mail($to, $subject, $html, $headers);
    }
}
