<?php
/** @var string $pageTitle */
?>
<div class="pending-approval-container">
    <div class="pending-card">
        <div class="pending-icon">⏳</div>

        <h1><?= e($pageTitle) ?></h1>

        <div class="pending-message">
            <p>Sua solicitação de cadastro como parceiro na <strong>Rota 12</strong> foi recebida com sucesso!</p>

            <p>No momento, seu perfil está <strong>aguardando aprovação</strong> do nosso time administrativo.</p>
        </div>

        <div class="pending-status">
            <div class="status-item">
                <span class="status-label">Status:</span>
                <span class="status-value pending">Pendente de Aprovação</span>
            </div>

            <div class="status-item">
                <span class="status-label">Próximo passo:</span>
                <span class="status-value">Você receberá um e-mail com a decisão em breve</span>
            </div>
        </div>

        <div class="pending-timeline">
            <div class="timeline-step">
                <div class="timeline-number">1</div>
                <div class="timeline-text">
                    <strong>Solicitação Enviada ✅</strong>
                    <p>Seu cadastro foi recebido e está aguardando análise</p>
                </div>
            </div>

            <div class="timeline-arrow">↓</div>

            <div class="timeline-step">
                <div class="timeline-number">2</div>
                <div class="timeline-text">
                    <strong>Análise em Andamento ⏳</strong>
                    <p>Nosso time está revisando seus dados</p>
                </div>
            </div>

            <div class="timeline-arrow">↓</div>

            <div class="timeline-step">
                <div class="timeline-number">3</div>
                <div class="timeline-text">
                    <strong>Decisão 🎉</strong>
                    <p>Você receberá um e-mail com a aprovação ou instruções para corrigir os dados</p>
                </div>
            </div>
        </div>

        <div class="pending-info">
            <h3>O que você pode fazer?</h3>
            <ul>
                <li>✉️ Verifique seu e-mail regularmente para atualizações</li>
                <li>📧 Se tiver dúvidas, entre em contato: <a href="mailto:suporte@rota12.com">suporte@rota12.com</a></li>
                <li>⏱️ Normalmente análise leva entre 1-3 dias úteis</li>
            </ul>
        </div>

        <div class="pending-actions">
            <a href="/minha-conta" class="btn btn-secondary">← Voltar para Conta</a>
            <a href="/" class="btn btn-primary">Início</a>
        </div>
    </div>
</div>

<style>
.pending-approval-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem 1rem;
}

.pending-card {
    background: #fff;
    border-radius: 12px;
    padding: 3rem 2rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    max-width: 600px;
    width: 100%;
    text-align: center;
}

.pending-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.pending-card h1 {
    color: #333;
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
}

.pending-message {
    color: #666;
    line-height: 1.8;
    margin-bottom: 2rem;
    text-align: left;
}

.pending-message strong {
    color: #667eea;
}

.pending-status {
    background: #f8f9fa;
    border-left: 4px solid #667eea;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border-radius: 8px;
    text-align: left;
}

.status-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.status-item:last-child {
    margin-bottom: 0;
}

.status-label {
    font-weight: 600;
    color: #333;
}

.status-value {
    color: #666;
}

.status-value.pending {
    color: #ff9800;
    font-weight: 600;
}

.pending-timeline {
    margin: 2rem 0;
    text-align: left;
}

.timeline-step {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.timeline-number {
    background: #667eea;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.timeline-text strong {
    color: #333;
    display: block;
    margin-bottom: 0.25rem;
}

.timeline-text p {
    color: #999;
    margin: 0;
    font-size: 0.9rem;
}

.timeline-arrow {
    text-align: center;
    color: #ddd;
    font-size: 1.5rem;
    margin: -0.5rem 0;
}

.pending-info {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    text-align: left;
}

.pending-info h3 {
    color: #1976d2;
    margin-top: 0;
    margin-bottom: 1rem;
}

.pending-info ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pending-info li {
    color: #555;
    margin-bottom: 0.75rem;
    line-height: 1.6;
}

.pending-info a {
    color: #1976d2;
    text-decoration: none;
}

.pending-info a:hover {
    text-decoration: underline;
}

.pending-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
}

.btn-secondary:hover {
    background: #e0e0e0;
    transform: translateY(-2px);
}

@media (max-width: 600px) {
    .pending-card {
        padding: 2rem 1.5rem;
    }

    .pending-card h1 {
        font-size: 1.5rem;
    }

    .pending-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}
</style>
