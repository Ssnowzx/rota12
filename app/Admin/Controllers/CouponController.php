<?php declare(strict_types=1);

namespace App\Admin\Controllers;

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ControllerBase;
use App\Core\Router;
use App\Models\CampaignModel;
use App\Models\AuditModel;

class CouponController extends ControllerBase
{
    public function index(): void
    {
        $this->requireAuth();
        $this->requirePerm('coupons.view');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $pager   = CampaignModel::listForAdmin($page, $perPage);
        $this->renderAdmin('coupons/index', [
            'records' => $pager['data'],
            'total'   => $pager['total'],
            'pages'   => $pager['pages'],
            'page'    => $pager['current'],
            'perPage' => $perPage,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->requirePerm('coupons.create');
        $this->renderAdmin('coupons/create');
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requirePerm('coupons.create');
        CSRF::check();

        $titulo           = trim($_POST['titulo'] ?? '');
        $descricao        = trim($_POST['descricao'] ?? '') ?: null;
        $categoria        = trim($_POST['categoria'] ?? '') ?: null;
        $tipo_desconto    = $_POST['tipo_desconto'] ?? 'percentual';
        $valor_desconto   = (float)($_POST['valor_desconto'] ?? 0);
        $exige_assinatura = (int)($_POST['exige_assinatura'] ?? 0);
        $data_fim         = trim($_POST['data_fim'] ?? '') ?: null;
        $status           = 'rascunho';
        $errors           = [];

        if ($titulo === '') { $errors[] = 'O título é obrigatório.'; }
        elseif (mb_strlen($titulo) > 255) { $errors[] = 'O título deve ter no máximo 255 caracteres.'; }
        if ($valor_desconto < 0) { $errors[] = 'O valor do desconto não pode ser negativo.'; }
        if (!in_array($tipo_desconto, ['percentual', 'fixo'], true)) { $tipo_desconto = 'percentual'; }

        if (!empty($errors)) {
            foreach ($errors as $msg) { $this->flash('error', $msg); }
            $this->redirect('/administrator/coupons/create');
        }

        $newId = CampaignModel::insert([
            'titulo'           => $titulo,
            'descricao'        => $descricao,
            'categoria'        => $categoria,
            'tipo_desconto'    => $tipo_desconto,
            'valor_desconto'   => $valor_desconto,
            'exige_assinatura' => $exige_assinatura,
            'data_fim'         => $data_fim,
            'status'           => $status,
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        AuditModel::log(Auth::adminId(), 'create', 'coupon_campaigns', $newId,
            ['titulo' => $titulo], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Campanha criada com sucesso.');
        $this->redirect('/administrator/coupons');
    }

    public function edit(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('coupons.edit');
        $id       = (int)($params['id'] ?? 0);
        $campaign = CampaignModel::findWithPartner($id);
        if (!$campaign) { Router::triggerError(404); }
        $this->renderAdmin('coupons/edit', ['record' => $campaign]);
    }

    public function update(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('coupons.edit');
        CSRF::check();
        $id       = (int)($params['id'] ?? 0);
        $campaign = CampaignModel::find($id);
        if (!$campaign) { Router::triggerError(404); }

        $status = $_POST['status'] ?? $campaign['status'];
        if (!in_array($status, ['ativa', 'pausada', 'encerrada', 'rascunho'], true)) {
            $status = $campaign['status'];
        }

        CampaignModel::update($id, [
            'titulo'          => trim($_POST['titulo'] ?? $campaign['titulo']),
            'descricao'       => trim($_POST['descricao'] ?? '') ?: null,
            'categoria'       => trim($_POST['categoria'] ?? '') ?: null,
            'tipo_desconto'   => $_POST['tipo_desconto'] ?? $campaign['tipo_desconto'],
            'valor_desconto'  => (float)($_POST['valor_desconto'] ?? $campaign['valor_desconto']),
            'exige_assinatura'=> (int)($_POST['exige_assinatura'] ?? $campaign['exige_assinatura']),
            'data_fim'        => trim($_POST['data_fim'] ?? '') ?: null,
            'status'          => $status,
        ]);

        AuditModel::log(Auth::adminId(), 'update', 'coupon_campaigns', $id,
            ['titulo' => trim($_POST['titulo'] ?? '')], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Campanha atualizada com sucesso.');
        $this->redirect('/administrator/coupons');
    }

    public function destroy(array $params): void
    {
        $this->requireAuth();
        $this->requirePerm('coupons.delete');
        CSRF::check();
        $id       = (int)($params['id'] ?? 0);
        $campaign = CampaignModel::find($id);
        if (!$campaign) { Router::triggerError(404); }

        CampaignModel::update($id, [
            'deleted_at' => date('Y-m-d H:i:s'),
            'status'     => 'encerrada',
        ]);

        AuditModel::log(Auth::adminId(), 'delete', 'coupon_campaigns', $id,
            ['titulo' => $campaign['titulo']], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');
        $this->flash('success', 'Campanha excluída com sucesso.');
        $this->redirect('/administrator/coupons');
    }
}
