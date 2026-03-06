<?php
declare(strict_types=1);
namespace App\Core;

use App\Models\PartnerModel;

class PartnerAuth
{
    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function isPartner(): bool {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'parceiro';
    }

    public static function userId(): ?int {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function user(): array {
        return $_SESSION['user_data'] ?? [];
    }

    public static function requireLogin(string $redirect = '/login'): void {
        if (!self::check()) {
            header('Location: ' . basePath($redirect));
            exit;
        }
    }

    public static function requirePartner(): void {
        self::requireLogin('/login');
        if (!self::isPartner()) {
            header('Location: ' . basePath('/'));
            exit;
        }
        // Note: Partners with pending approval (is_active=0) can access dashboard
        // but see limited view with status message
    }

    /**
     * Check if user account is active (approved partner)
     *
     * @return bool True if partner is approved and active, false if pending/rejected
     */
    public static function isActive(): bool {
        $userId = self::userId();
        if (!$userId) {
            return false;
        }

        $user = self::user();
        return isset($user['is_active']) && $user['is_active'] == 1;
    }

    /**
     * Check if partner is pending approval
     *
     * @return bool True if partner status is 'pendente_aprovacao' in database
     */
    public static function isPendingApproval(): bool {
        $userId = self::userId();
        if (!$userId) {
            return false;
        }

        $status = PartnerModel::getStatusByUserId($userId);
        return $status === 'pendente_aprovacao';
    }

    public static function login(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_data'] = $user;
    }

    public static function logout(): void {
        $_SESSION = [];
        session_destroy();
    }
}
