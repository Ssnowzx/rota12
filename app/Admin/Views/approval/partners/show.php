<?php
/** @var array $partner */
/** @var array $approvalDetails */
/** @var string $pageTitle */
?>
<div class="admin-header">
    <h1><?= e($pageTitle) ?></h1>
    <a href="/administrator/approval/partners" class="btn btn-secondary" style="float: right;">← Voltar</a>
</div>

<div class="detail-grid">
    <!-- Partner Information -->
    <div class="admin-card">
        <h2>Informações do Parceiro</h2>

        <div class="detail-row">
            <label>ID:</label>
            <span><?= e($partner['id']) ?></span>
        </div>

        <div class="detail-row">
            <label>Nome de Usuário:</label>
            <span><?= e($partner['username']) ?></span>
        </div>

        <div class="detail-row">
            <label>E-mail:</label>
            <span><?= e($partner['email']) ?></span>
        </div>

        <div class="detail-row">
            <label>Status da Conta:</label>
            <span class="badge <?= $partner['is_active'] ? 'badge-success' : 'badge-warning' ?>">
                <?= $partner['is_active'] ? 'Ativa' : 'Bloqueada (Aprovação Pendente)' ?>
            </span>
        </div>

        <div class="detail-row">
            <label>Data de Cadastro:</label>
            <span><?= date('d/m/Y H:i', strtotime($partner['created_at'])) ?></span>
        </div>
    </div>

    <!-- Approval Timeline -->
    <div class="admin-card">
        <h2>Timeline de Aprovação</h2>

        <div class="timeline">
            <!-- Requested -->
            <div class="timeline-item">
                <div class="timeline-marker">📋</div>
                <div class="timeline-content">
                    <strong>Solicitação Criada</strong>
                    <p><?= $approvalDetails['approval_requested_at'] ? date('d/m/Y H:i', strtotime($approvalDetails['approval_requested_at'])) : 'N/A' ?></p>
                </div>
            </div>

            <!-- Pending Days -->
            <?php if ($approvalDetails['status_aprovacao'] === 'pendente_aprovacao'): ?>
            <div class="timeline-item">
                <div class="timeline-marker">⏳</div>
                <div class="timeline-content">
                    <strong>Pendente por</strong>
                    <p><?= e($approvalDetails['pending_days']) ?> dia(s)</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Approved -->
            <?php if ($approvalDetails['status_aprovacao'] === 'aprovado'): ?>
            <div class="timeline-item">
                <div class="timeline-marker">✅</div>
                <div class="timeline-content">
                    <strong>Aprovado</strong>
                    <p><?= $approvalDetails['approved_em'] ? date('d/m/Y H:i', strtotime($approvalDetails['approved_em'])) : 'N/A' ?></p>
                    <p class="text-muted">Por: <?= e($approvalDetails['approver_name'] ?? 'Admin System') ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Rejected -->
            <?php if ($approvalDetails['status_aprovacao'] === 'rejeitado'): ?>
            <div class="timeline-item">
                <div class="timeline-marker">❌</div>
                <div class="timeline-content">
                    <strong>Rejeitado</strong>
                    <p><?= $approvalDetails['approved_em'] ? date('d/m/Y H:i', strtotime($approvalDetails['approved_em'])) : 'N/A' ?></p>
                    <p class="text-muted">Por: <?= e($approvalDetails['approver_name'] ?? 'Admin System') ?></p>
                    <?php if ($approvalDetails['rejection_reason']): ?>
                    <p class="rejection-reason"><strong>Motivo:</strong> <?= e($approvalDetails['rejection_reason']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions -->
    <?php if ($approvalDetails['status_aprovacao'] === 'pendente_aprovacao'): ?>
    <div class="admin-card">
        <h2>Ações</h2>

        <div style="display: flex; gap: 1rem;">
            <button class="btn btn-success" onclick="openApproveModal(<?= e($partner['id']) ?>, '<?= e($partner['username']) ?>')">
                ✅ Aprovar Parceiro
            </button>
            <button class="btn btn-danger" onclick="openRejectModal(<?= e($partner['id']) ?>, '<?= e($partner['username']) ?>')">
                ❌ Rejeitar Parceiro
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.detail-grid {
    display: grid;
    gap: 2rem;
    max-width: 100%;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.detail-row label {
    font-weight: 600;
    color: rgba(255,255,255,0.7);
    min-width: 150px;
}

.detail-row span {
    color: #fff;
}

.badge {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-success {
    background: rgba(76, 175, 80, 0.2);
    color: #4caf50;
    border: 1px solid rgba(76, 175, 80, 0.3);
}

.badge-warning {
    background: rgba(255, 152, 0, 0.2);
    color: #ff9800;
    border: 1px solid rgba(255, 152, 0, 0.3);
}

.timeline {
    position: relative;
    padding: 2rem 0;
}

.timeline-item {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 2rem;
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 1.35rem;
    top: 4rem;
    width: 2px;
    height: calc(100% + 0.5rem);
    background: rgba(255,255,255,0.1);
}

.timeline-marker {
    font-size: 1.5rem;
    min-width: 3rem;
    text-align: center;
}

.timeline-content {
    flex: 1;
}

.timeline-content strong {
    display: block;
    color: #dfff00;
    margin-bottom: 0.5rem;
}

.timeline-content p {
    color: rgba(255,255,255,0.7);
    margin: 0.25rem 0;
}

.rejection-reason {
    background: rgba(255, 80, 80, 0.1);
    border-left: 3px solid #ff5050;
    padding: 0.75rem;
    border-radius: 4px;
    margin-top: 1rem;
    color: #ff8080 !important;
}

.text-muted {
    color: rgba(255,255,255,0.5) !important;
    font-size: 0.9rem;
}

.admin-header {
    position: relative;
    margin-bottom: 2rem;
}

.admin-header .btn {
    margin: 0;
}
</style>

<!-- Modals (reuse from index) -->
<div id="approveModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Aprovar Parceiro</h2>
        <p id="approvePartnerName"></p>

        <form onsubmit="submitApprove(event)">
            <div class="form-group">
                <label for="approveNotes">Notas (opcional):</label>
                <textarea id="approveNotes" name="notes" placeholder="Ex: Documentação completa, parceiro verificado..." rows="3"></textarea>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-success">Aprovar Parceiro</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('approveModal')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div id="rejectModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Rejeitar Parceiro</h2>
        <p id="rejectPartnerName"></p>

        <form onsubmit="submitReject(event)">
            <div class="form-group">
                <label for="rejectReason">Motivo da Rejeição:</label>
                <textarea id="rejectReason" name="reason" placeholder="Ex: Documentação incompleta, dados inconsistentes..." rows="3" required></textarea>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-danger">Rejeitar Parceiro</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('rejectModal')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #1a1a1a;
    margin: 5% auto;
    padding: 2rem;
    border: 1px solid rgba(255,255,255,0.1);
    width: 90%;
    max-width: 500px;
    border-radius: 8px;
    color: #fff;
}

.modal-actions {
    margin-top: 1.5rem;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    color: rgba(255,255,255,0.7);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-group textarea {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    color: #fff;
    padding: 0.75rem;
    border-radius: 4px;
    font-family: inherit;
    resize: vertical;
}

.form-group textarea:focus {
    outline: none;
    border-color: #dfff00;
}
</style>

<script>
let currentPartnerId = null;

function openApproveModal(partnerId, partnerName) {
    currentPartnerId = partnerId;
    document.getElementById('approvePartnerName').textContent = `Parceiro: ${partnerName}`;
    document.getElementById('approveModal').style.display = 'block';
}

function openRejectModal(partnerId, partnerName) {
    currentPartnerId = partnerId;
    document.getElementById('rejectPartnerName').textContent = `Parceiro: ${partnerName}`;
    document.getElementById('rejectModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    currentPartnerId = null;
}

function submitApprove(event) {
    event.preventDefault();

    const notes = document.getElementById('approveNotes').value;
    const formData = new FormData();
    formData.append('notes', notes);

    fetch(`/administrator/approval/partners/${currentPartnerId}/approve`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ Erro: ' + (data.error || 'Falha ao aprovar'));
        }
    })
    .catch(err => {
        alert('❌ Erro na requisição: ' + err.message);
    });

    closeModal('approveModal');
}

function submitReject(event) {
    event.preventDefault();

    const reason = document.getElementById('rejectReason').value;
    if (!reason.trim()) {
        alert('❌ Motivo da rejeição é obrigatório');
        return;
    }

    const formData = new FormData();
    formData.append('reason', reason);

    fetch(`/administrator/approval/partners/${currentPartnerId}/reject`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ Erro: ' + (data.error || 'Falha ao rejeitar'));
        }
    })
    .catch(err => {
        alert('❌ Erro na requisição: ' + err.message);
    });

    closeModal('rejectModal');
}

window.onclick = function(event) {
    const approveModal = document.getElementById('approveModal');
    const rejectModal = document.getElementById('rejectModal');

    if (event.target === approveModal) {
        approveModal.style.display = 'none';
    }
    if (event.target === rejectModal) {
        rejectModal.style.display = 'none';
    }
}
</script>
