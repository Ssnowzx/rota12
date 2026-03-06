<?php
/** @var array $partners */
/** @var int $total */
/** @var int $pages */
/** @var int $current */
/** @var string $pageTitle */
?>
<div class="admin-header">
    <h1><?= e($pageTitle) ?></h1>
    <p class="text-muted">Solicitações de cadastro de parceiros aguardando análise</p>
</div>

<?php if (empty($partners)): ?>
<div class="alert alert-success">
    ✅ Nenhuma solicitação pendente no momento. Parabéns!
</div>
<?php else: ?>

<div class="admin-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome de Usuário</th>
                <th>E-mail</th>
                <th>Solicitado em</th>
                <th>Dias Pendente</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($partners as $partner): ?>
            <tr>
                <td><?= e($partner['id']) ?></td>
                <td><?= e($partner['username']) ?></td>
                <td><?= e($partner['email']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($partner['approval_requested_at'])) ?></td>
                <td>
                    <?php
                    $days = (new DateTime())->diff(new DateTime($partner['approval_requested_at']))->days;
                    echo $days . ' dia' . ($days !== 1 ? 's' : '');
                    ?>
                </td>
                <td>
                    <a href="/administrator/approval/partners/<?= e($partner['id']) ?>" class="btn btn-sm btn-info">
                        Visualizar
                    </a>
                    <button class="btn btn-sm btn-success" onclick="approvePartner(<?= e($partner['id']) ?>, '<?= e($partner['username']) ?>')">
                        Aprovar
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="rejectPartner(<?= e($partner['id']) ?>, '<?= e($partner['username']) ?>')">
                        Rejeitar
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div class="admin-pagination">
    <?php if ($current > 1): ?>
        <a href="?page=1" class="btn btn-sm">Primeira</a>
        <a href="?page=<?= $current - 1 ?>" class="btn btn-sm">Anterior</a>
    <?php endif; ?>

    <span class="text-muted">Página <?= $current ?> de <?= $pages ?></span>

    <?php if ($current < $pages): ?>
        <a href="?page=<?= $current + 1 ?>" class="btn btn-sm">Próxima</a>
        <a href="?page=<?= $pages ?>" class="btn btn-sm">Última</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<!-- Modal: Approve Partner -->
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

<!-- Modal: Reject Partner -->
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

.admin-pagination {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
    align-items: center;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
}

.btn-info {
    background: #0066cc;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-info:hover {
    background: #0052a3;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background: rgba(76, 175, 80, 0.1);
    border: 1px solid rgba(76, 175, 80, 0.3);
    color: #4caf50;
}
</style>

<script>
let currentPartnerId = null;

function approvePartner(partnerId, partnerName) {
    currentPartnerId = partnerId;
    document.getElementById('approvePartnerName').textContent = `Parceiro: ${partnerName}`;
    document.getElementById('approveModal').style.display = 'block';
}

function rejectPartner(partnerId, partnerName) {
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

// Close modals when clicking outside
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
