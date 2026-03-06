<?php
/**
 * Partner Dashboard View
 * @var int    $campanhas_ativas
 * @var int    $total_gerados
 * @var int    $total_resgatados
 * @var float  $taxa_conversao
 * @var array  $historico
 * @var string $grafico_evolucao_json
 * @var string $grafico_status_json
 */
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Metric Cards -->
<div class="pn-metrics">
    <div class="pn-metric">
        <div class="pn-metric-icon"><i class="fas fa-tags"></i></div>
        <div class="pn-metric-label">Campanhas Ativas</div>
        <div class="pn-metric-value"><?= (int)$campanhas_ativas ?></div>
        <div class="pn-metric-sub"><a href="/parceiro/campanhas" style="color:var(--accent);font-size:0.78rem;">Ver campanhas →</a></div>
    </div>
    <div class="pn-metric">
        <div class="pn-metric-icon"><i class="fas fa-ticket-alt"></i></div>
        <div class="pn-metric-label">Cupons Gerados</div>
        <div class="pn-metric-value"><?= (int)$total_gerados ?></div>
        <div class="pn-metric-sub">Total acumulado</div>
    </div>
    <div class="pn-metric">
        <div class="pn-metric-icon"><i class="fas fa-check-circle"></i></div>
        <div class="pn-metric-label">Resgates</div>
        <div class="pn-metric-value"><?= (int)$total_resgatados ?></div>
        <div class="pn-metric-sub">Cupons utilizados</div>
    </div>
    <div class="pn-metric">
        <div class="pn-metric-icon"><i class="fas fa-percentage"></i></div>
        <div class="pn-metric-label">Taxa de Conversão</div>
        <div class="pn-metric-value"><?= number_format((float)$taxa_conversao, 1) ?>%</div>
        <div class="pn-metric-sub">Gerados → Resgatados</div>
    </div>
</div>

<!-- Charts Row -->
<div class="pn-grid-2">
    <!-- Evolution Chart -->
    <div class="pn-card">
        <div class="pn-card-header">
            <span class="pn-card-title"><i class="fas fa-chart-area" style="color:var(--accent);margin-right:0.5rem;"></i>Evolução (7 dias)</span>
        </div>
        <div class="pn-card-body pn-chart-container">
            <canvas id="chartEvolucao"></canvas>
        </div>
    </div>

    <!-- Status Donut Chart -->
    <div class="pn-card">
        <div class="pn-card-header">
            <span class="pn-card-title"><i class="fas fa-chart-pie" style="color:var(--accent);margin-right:0.5rem;"></i>Status dos Cupons</span>
        </div>
        <div class="pn-card-body pn-chart-container" style="height: 150px; min-height: 150px;">
            <canvas id="chartStatus"></canvas>
        </div>
    </div>
</div>

<!-- Bottom Row: Validator + History -->
<div class="pn-grid-2">
    <!-- Coupon Validator -->
    <div class="pn-card">
        <div class="pn-card-header">
            <span class="pn-card-title"><i class="fas fa-qrcode" style="color:var(--accent);margin-right:0.5rem;"></i>Validador Rápido</span>
        </div>
        <div class="pn-card-body">
            <p style="color:var(--muted);font-size:0.85rem;margin-bottom:1rem;">Insira o código do cupom para validar no ato.</p>
            <div style="display:flex;gap:0.75rem;">
                <input type="text" id="codigoInput" class="pn-form-control" placeholder="Ex: ABC12345" style="flex:1;text-transform:uppercase;letter-spacing:0.1em;"/>
                <button onclick="validarCupom()" class="pn-btn pn-btn-primary" id="btnValidar">
                    <i class="fas fa-check"></i> Validar
                </button>
            </div>
            <div id="validadorResult" style="margin-top:1rem;display:none;"></div>
        </div>
    </div>

    <!-- Redemption History -->
    <div class="pn-card">
        <div class="pn-card-header">
            <span class="pn-card-title"><i class="fas fa-history" style="color:var(--accent);margin-right:0.5rem;"></i>Últimos Resgates</span>
        </div>
        <?php if (empty($historico)): ?>
        <div class="pn-card-body" style="text-align:center;color:var(--muted);padding:2rem;">
            <i class="fas fa-inbox" style="font-size:2rem;margin-bottom:0.75rem;"></i>
            <p>Nenhum resgate ainda.</p>
        </div>
        <?php else: ?>
        <div class="pn-table-wrapper">
            <table class="pn-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Campanha</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historico as $h): ?>
                    <tr>
                        <td><code style="font-family:monospace;color:var(--accent);letter-spacing:0.05em;"><?= e($h['codigo_masked'] ?? $h['codigo']) ?></code></td>
                        <td style="color:var(--muted);font-size:0.82rem;"><?= e($h['titulo'] ?? '') ?></td>
                        <td style="color:var(--muted);font-size:0.82rem;">
                            <?= $h['utilizado_em'] ? date('d/m H:i', strtotime($h['utilizado_em'])) : '—' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const graficoEvolucao = <?= $grafico_evolucao_json ?>;
const graficoStatus   = <?= $grafico_status_json ?>;

// Evolution Chart
const chartEvolucaoCtx = {
    instance: null,
    create: function() {
        this.instance = new Chart(document.getElementById('chartEvolucao'), {
            type: 'line',
            data: {
                labels: graficoEvolucao.labels,
                datasets: [
                    {
                        label: 'Visualizações',
                        data: graficoEvolucao.views,
                        borderColor: '#4dabf7',
                        backgroundColor: 'rgba(77,171,247,0.1)',
                        tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 5,
                    },
                    {
                        label: 'Cupons Gerados',
                        data: graficoEvolucao.coupons,
                        borderColor: '#dfff00',
                        backgroundColor: 'rgba(223,255,0,0.1)',
                        tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2.5,
                plugins: {
                    legend: {
                        labels: { color: 'rgba(255,255,255,0.6)', font: { size: 11 }, padding: 16 }
                    }
                },
                scales: {
                    x: { ticks: { color: 'rgba(255,255,255,0.4)' }, grid: { color: 'rgba(255,255,255,0.04)' } },
                    y: { ticks: { color: 'rgba(255,255,255,0.4)' }, grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true }
                }
            }
        });
    }
};
chartEvolucaoCtx.create();

// Donut Chart
const chartStatusCtx = {
    instance: null,
    create: function() {
        this.instance = new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: graficoStatus.labels,
                datasets: [{
                    data: graficoStatus.data,
                    backgroundColor: ['#dfff00', '#00c864', '#ff5050'],
                    borderWidth: 2,
                    borderColor: '#1a1a1a',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgba(255,255,255,0.6)',
                            font: { size: 11 },
                            padding: 16,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });
    }
};
chartStatusCtx.create();

// Coupon validator
async function validarCupom() {
    const btn   = document.getElementById('btnValidar');
    const input = document.getElementById('codigoInput');
    const result = document.getElementById('validadorResult');
    const code  = input.value.trim();
    if (!code) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validando...';
    result.style.display = 'none';

    const fd = new FormData();
    fd.append('codigo', code);
    // Include CSRF token if available
    const csrfInput = document.querySelector('[name="csrf_token"]');
    if (csrfInput) fd.append('csrf_token', csrfInput.value);

    try {
        const res = await fetch('/parceiro/cupons/validar', { method: 'POST', body: fd });
        const data = await res.json();
        result.style.display = 'block';
        if (data.ok) {
            result.innerHTML = '<div style="background:rgba(0,200,100,0.1);border:1px solid rgba(0,200,100,0.2);border-radius:8px;padding:0.75rem 1rem;color:#00c864;"><i class="fas fa-check-circle"></i> ' + data.mensagem + '<br><small style="opacity:0.7">Campanha: ' + (data.campanha || '') + '</small></div>';
            input.value = '';

            // Atualizar dashboard imediatamente após validação bem-sucedida
            setTimeout(() => {
                fetchDashboardStats();
            }, 500);
        } else {
            result.innerHTML = '<div style="background:rgba(255,80,80,0.1);border:1px solid rgba(255,80,80,0.2);border-radius:8px;padding:0.75rem 1rem;color:#ff5050;"><i class="fas fa-times-circle"></i> ' + data.mensagem + '</div>';
        }
    } catch (e) {
        result.style.display = 'block';
        result.innerHTML = '<div style="background:rgba(255,80,80,0.1);border-radius:8px;padding:0.75rem 1rem;color:#ff5050;">Erro de conexão.</div>';
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check"></i> Validar';
}

document.getElementById('codigoInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') validarCupom();
});

// ============================================
// Real-time Dashboard Updates
// ============================================
let updatePollingInterval = null;

/**
 * Atualizar as métricas do dashboard (cards)
 */
function updateMetrics(data) {
    const metricsDiv = document.querySelector('.pn-metrics');
    if (!metricsDiv) return;

    const metrics = metricsDiv.querySelectorAll('.pn-metric');
    if (metrics.length >= 3) {
        // Index 2: Resgates (total_resgatados)
        const resgatesCard = metrics[2];
        const valueEl = resgatesCard.querySelector('.pn-metric-value');
        if (valueEl && valueEl.textContent !== String(data.total_resgatados)) {
            valueEl.style.transition = 'transform 0.3s ease';
            valueEl.style.transform = 'scale(1.1)';
            valueEl.textContent = data.total_resgatados;
            setTimeout(() => {
                valueEl.style.transform = 'scale(1)';
            }, 150);
        }

        // Index 3: Taxa de Conversão
        const taxaCard = metrics[3];
        const taxaEl = taxaCard.querySelector('.pn-metric-value');
        if (taxaEl && parseFloat(taxaEl.textContent) !== data.taxa_conversao) {
            taxaEl.style.transition = 'transform 0.3s ease';
            taxaEl.style.transform = 'scale(1.1)';
            taxaEl.textContent = (Math.round(data.taxa_conversao * 10) / 10).toFixed(1) + '%';
            setTimeout(() => {
                taxaEl.style.transform = 'scale(1)';
            }, 150);
        }
    }
}

/**
 * Atualizar gráficos com novos dados
 */
function updateCharts(data) {
    const evolucao = data.grafico_evolucao;
    const status = data.grafico_status;

    // Atualizar gráfico de evolução
    if (chartEvolucaoCtx.instance) {
        chartEvolucaoCtx.instance.data.datasets[0].data = evolucao.views;
        chartEvolucaoCtx.instance.data.datasets[1].data = evolucao.coupons;
        chartEvolucaoCtx.instance.data.labels = evolucao.labels;
        chartEvolucaoCtx.instance.update('none');
    }

    // Atualizar gráfico de status
    if (chartStatusCtx.instance) {
        chartStatusCtx.instance.data.datasets[0].data = status.data;
        chartStatusCtx.instance.data.labels = status.labels;
        chartStatusCtx.instance.update('none');
    }
}

/**
 * Atualizar a tabela de histórico de resgates
 */
function updateHistoryTable(historico) {
    const table = document.querySelector('.pn-table tbody');
    if (!table) return;

    // Limpar linhas atuais
    table.innerHTML = '';

    // Se não há histórico
    if (historico.length === 0) {
        const emptyCard = document.querySelector('.pn-table-wrapper')?.parentElement;
        if (emptyCard) {
            emptyCard.innerHTML = '<div class="pn-card-body" style="text-align:center;color:var(--muted);padding:2rem;"><i class="fas fa-inbox" style="font-size:2rem;margin-bottom:0.75rem;"></i><p>Nenhum resgate ainda.</p></div>';
        }
        return;
    }

    // Adicionar linhas do histórico
    historico.forEach((h, index) => {
        const tr = document.createElement('tr');
        if (index === 0) tr.style.animation = 'highlightRow 1s ease';

        tr.innerHTML = `
            <td><code style="font-family:monospace;color:var(--accent);letter-spacing:0.05em;">${h.codigo_masked || h.codigo}</code></td>
            <td style="color:var(--muted);font-size:0.82rem;">${h.titulo || ''}</td>
            <td style="color:var(--muted);font-size:0.82rem;">
                ${h.utilizado_em ? new Date(h.utilizado_em).toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }).replace(',', '') : '—'}
            </td>
        `;
        table.appendChild(tr);
    });
}

/**
 * Buscar dados atualizados do servidor
 */
async function fetchDashboardStats() {
    try {
        const response = await fetch('/parceiro/dashboard/stats');
        if (!response.ok) return;

        const data = await response.json();
        if (!data.success) return;

        // Atualizar métricas
        updateMetrics(data);

        // Atualizar gráficos
        updateCharts(data);

        // Atualizar tabela de histórico
        updateHistoryTable(data.historico);
    } catch (error) {
        console.error('Erro ao buscar estatísticas:', error);
    }
}

/**
 * Iniciar polling para atualizações em tempo real
 * Atualiza a cada 5 segundos
 */
function startDashboardPolling() {
    fetchDashboardStats();
    updatePollingInterval = setInterval(fetchDashboardStats, 5000);
}

/**
 * Parar o polling
 */
function stopDashboardPolling() {
    if (updatePollingInterval) {
        clearInterval(updatePollingInterval);
    }
}

// Iniciar polling quando a página carrega
document.addEventListener('DOMContentLoaded', startDashboardPolling);

// Parar polling quando a página é descarregada
window.addEventListener('beforeunload', stopDashboardPolling);

// Adicionar animação CSS para highlight de novas linhas
const style = document.createElement('style');
style.textContent = `
    @keyframes highlightRow {
        0% { background-color: rgba(223, 255, 0, 0.2); }
        100% { background-color: transparent; }
    }
`;
document.head.appendChild(style);
</script>
