<?php
$__title = 'Relatórios e KPIs';
ob_start();
?>
<style>
    .kpi-grid { display: grid; grid-template-columns: 1fr repeat(4, 1fr); gap: 0.75rem; margin-bottom: 1.25rem; }
    @media (max-width: 1024px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 640px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }

    .kpi-total {
        grid-column: 1;
        background: var(--navy);
        color: white;
        border-radius: 0.75rem;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
    }
    @media (max-width: 1024px) { .kpi-total { grid-column: 1 / -1; } }
    .kpi-total .kpi-label { font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.6; }
    .kpi-total .kpi-number { font-size: 2.25rem; font-weight: 700; line-height: 1; margin-top: 0.75rem; }
    .kpi-total .kpi-sub { font-size: 0.75rem; opacity: 0.6; margin-top: 0.25rem; }

    .kpi-card {
        background: white;
        border: 1px solid var(--slate-100);
        border-radius: 0.75rem;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .kpi-card .kpi-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
    .kpi-card .kpi-icon { width: 2rem; height: 2rem; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; }
    .kpi-card .kpi-value { font-size: 1.5rem; font-weight: 700; }
    .kpi-card .kpi-name { font-size: 0.75rem; font-weight: 600; color: var(--slate-500); }
    .kpi-bar { height: 0.5rem; border-radius: 9999px; background: var(--slate-100); overflow: hidden; margin-top: 0.5rem; }
    .kpi-bar-fill { height: 100%; border-radius: 9999px; transition: width 0.5s; }
    .kpi-bar-pct { font-size: 0.625rem; color: var(--slate-400); text-align: right; margin-top: 0.25rem; }

    .filter-card {
        background: white;
        border: 1px solid var(--slate-200);
        border-radius: 0.75rem;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        margin-bottom: 1.25rem;
    }
    .filter-label { font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--slate-400); margin-bottom: 0.25rem; }

    .section-card {
        background: white;
        border: 1px solid var(--slate-100);
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        overflow: hidden;
    }
    .section-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1.25rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--slate-800);
    }
    .section-header i { font-size: 0.8rem; }

    .funnel-step { margin-bottom: 1rem; }
    .funnel-step:last-child { margin-bottom: 0; }
    .funnel-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem; }
    .funnel-label { display: flex; align-items: center; gap: 0.5rem; }
    .funnel-num { width: 1.25rem; height: 1.25rem; border-radius: 9999px; background: var(--slate-100); font-size: 0.625rem; font-weight: 700; color: var(--slate-500); display: flex; align-items: center; justify-content: center; }
    .funnel-name { font-size: 0.875rem; font-weight: 600; color: var(--slate-700); }
    .funnel-desc { font-size: 0.75rem; color: var(--slate-400); }
    .funnel-value { font-size: 1rem; font-weight: 700; color: var(--slate-900); }
    .funnel-bar { height: 0.75rem; border-radius: 9999px; background: var(--slate-100); overflow: hidden; }
    .funnel-bar-fill { height: 100%; border-radius: 9999px; transition: width 0.7s; }

    .rate-box { border-radius: 0.5rem; padding: 0.75rem 1rem; }
    .rate-label { font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
    .rate-value { font-size: 1.5rem; font-weight: 700; margin-top: 0.125rem; }

    .unit-bar { margin-bottom: 0.75rem; }
    .unit-bar:last-child { margin-bottom: 0; }
    .unit-row { display: flex; justify-content: space-between; margin-bottom: 0.25rem; }
    .unit-name { font-size: 0.8rem; font-weight: 500; }
    .unit-count { font-size: 0.8rem; color: var(--slate-400); }
</style>
<?php $__styles = ob_get_clean();
include __DIR__ . '/../layouts/header.php';
?>
<?php 
    $pendentes = $pedidosPorStatus['Pendente'] ?? 0;
    $emCotacao = $pedidosPorStatus['Em Cotação'] ?? 0;
    $realizados = $pedidosPorStatus['Realizado'] ?? 0;
    $recebidos = $pedidosPorStatus['Recebido'] ?? 0;

    $statusCards = [
        ['label' => 'Pendente', 'count' => $pendentes, 'color' => '#fb923c', 'bg' => '#fff7ed', 'text' => '#ea580c', 'icon' => 'fa-clock'],
        ['label' => 'Em Cotação', 'count' => $emCotacao, 'color' => '#fbbf24', 'bg' => '#fffbeb', 'text' => '#d97706', 'icon' => 'fa-chart-line'],
        ['label' => 'Realizado', 'count' => $realizados, 'color' => '#001A72', 'bg' => '#eff6ff', 'text' => '#001A72', 'icon' => 'fa-box'],
        ['label' => 'Recebido', 'count' => $recebidos, 'color' => '#10b981', 'bg' => '#ecfdf5', 'text' => '#059669', 'icon' => 'fa-circle-check'],
    ];
 ?>


<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1.25rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.375rem; font-size:0.75rem; color:var(--slate-400); margin-bottom:0.5rem;">
            <a href="/" style="color:var(--slate-400); text-decoration:none;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:0.6rem;"></i>
            <span style="color:var(--slate-700); font-weight:500;">Relatórios</span>
        </div>
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="width:2.5rem; height:2.5rem; border-radius:0.75rem; background:var(--navy); display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,.1); flex-shrink:0;">
                <i class="fas fa-chart-bar" style="color:white; font-size:1rem;"></i>
            </div>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color:var(--slate-900); margin:0;">Relatórios & KPIs</h1>
                <p style="font-size:0.75rem; color:var(--slate-400); margin:0.125rem 0 0;">
                    Análise de desempenho de pedidos e atendimentos
                </p>
            </div>
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:0.5rem;">
        <button class="rhc-btn rhc-btn-ghost rhc-btn-sm" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
</div>


<form method="GET" action="<?= e(route('relatorios')) ?>" class="filter-card">
    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
        <i class="fas fa-sliders" style="color:var(--slate-400); font-size:0.75rem;"></i>
        <span style="font-size:0.75rem; font-weight:600; color:var(--slate-500); text-transform:uppercase; letter-spacing:0.05em;">Filtros</span>
        <?php if (request('unidade_id') || request('data_inicio') || request('data_fim')): ?>
            <a href="<?= e(route('relatorios')) ?>" style="margin-left:auto; font-size:0.6875rem; color:var(--red-500); text-decoration:none; font-weight:500; display:flex; align-items:center; gap:0.25rem;">
                <i class="fas fa-times-circle"></i> Limpar filtros
            </a>
        <?php endif; ?>
    </div>
    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:0.75rem;">
        <div>
            <div class="filter-label">Unidade</div>
            <select name="unidade_id" class="rhc-select" style="width:100%;" onchange="this.form.submit()">
                <option value="">Todas as unidades</option>
                <?php foreach ($unidades as $u): ?>
                    <option value="<?= e($u->id) ?>" <?= e(request('unidade_id') == $u->id ? 'selected' : '') ?>><?= e($u->nome) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <div class="filter-label">De</div>
            <input type="date" name="data_inicio" class="rhc-input" value="<?= e(request('data_inicio')) ?>" onchange="this.form.submit()">
        </div>
        <div>
            <div class="filter-label">Até</div>
            <input type="date" name="data_fim" class="rhc-input" value="<?= e(request('data_fim')) ?>" onchange="this.form.submit()">
        </div>
    </div>
</form>


<div class="kpi-grid">
    <div class="kpi-total">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
            <i class="fas fa-shopping-cart" style="opacity:0.6;"></i>
            <span class="kpi-label">Total</span>
        </div>
        <div class="kpi-number"><?= e($totalPedidos) ?></div>
        <div class="kpi-sub">pedidos no período</div>
        <div style="display:flex; gap:0.25rem; margin-top:1rem;">
            <?php foreach ($statusCards as $sc): ?>
                <div style="flex:1; height:0.25rem; border-radius:9999px; background:rgba(255,255,255,0.2); overflow:hidden;">
                    <div style="height:100%; border-radius:9999px; background:rgba(255,255,255,0.7); width:<?= e($totalPedidos ? round(($sc['count'] / $totalPedidos) * 100) : 0) ?>%;"></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php foreach ($statusCards as $sc): ?>
        <?php  $pct = $totalPedidos > 0 ? round(($sc['count'] / $totalPedidos) * 100) : 0;  ?>
        <div class="kpi-card">
            <div class="kpi-header">
                <div class="kpi-icon" style="background:<?= e($sc['bg']) ?>; color:<?= e($sc['text']) ?>;">
                    <i class="fas <?= e($sc['icon']) ?>"></i>
                </div>
                <span class="kpi-value" style="color:<?= e($sc['text']) ?>;"><?= e($sc['count']) ?></span>
            </div>
            <div class="kpi-name"><?= e($sc['label']) ?></div>
            <div class="kpi-bar">
                <div class="kpi-bar-fill" style="width:<?= e($pct) ?>%; background:<?= e($sc['color']) ?>;"></div>
            </div>
            <div class="kpi-bar-pct"><?= e($pct) ?>% do total</div>
        </div>
    <?php endforeach; ?>
</div>


<div style="display:grid; grid-template-columns:2fr 1fr; gap:1rem; margin-bottom:1.25rem;">
    <div class="section-card">
        <div class="section-header">
            <i class="fas fa-chart-line" style="color:var(--navy);"></i> Funil de Atendimento
        </div>
        <div style="padding:0 1.25rem 1.25rem;">
            <?php 
                $funnelSteps = [
                    ['label' => 'Solicitado', 'value' => $totaisItens->total_solicitado, 'pct' => 100, 'color' => 'var(--navy)', 'desc' => 'Total de unidades pedidas'],
                    ['label' => 'Atendido', 'value' => $totaisItens->total_atendido, 'pct' => $taxa, 'color' => '#60a5fa', 'desc' => $taxa . '% do solicitado'],
                    ['label' => 'Recebido', 'value' => $totaisItens->total_recebido, 'pct' => $taxaRecebimento, 'color' => '#10b981', 'desc' => $taxaRecebimento . '% do atendido'],
                ];
             ?>
            <?php foreach ($funnelSteps as $i => $step): ?>
                <div class="funnel-step">
                    <div class="funnel-row">
                        <div class="funnel-label">
                            <div class="funnel-num"><?= e($i + 1) ?></div>
                            <span class="funnel-name"><?= e($step['label']) ?></span>
                            <span class="funnel-desc"><?= e($step['desc']) ?></span>
                        </div>
                        <span class="funnel-value"><?= e(number_format($step['value'], 0, ',', '.')) ?></span>
                    </div>
                    <div class="funnel-bar">
                        <div class="funnel-bar-fill" style="width:<?= e($step['pct']) ?>%; background:<?= e($step['color']) ?>;"></div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-top:1rem; padding-top:1rem; border-top:1px solid var(--slate-100);">
                <div class="rate-box" style="background:#eff6ff;">
                    <div class="rate-label" style="color:#3b82f6;">Taxa de Atendimento</div>
                    <div class="rate-value" style="color:var(--navy);"><?= e($taxa) ?>%</div>
                </div>
                <div class="rate-box" style="background:#ecfdf5;">
                    <div class="rate-label" style="color:#10b981;">Taxa de Recebimento</div>
                    <div class="rate-value" style="color:#059669;"><?= e($taxaRecebimento) ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card" style="display:flex; flex-direction:column; justify-content:space-between;">
        <div>
            <div class="section-header">
                <i class="fas fa-right-left" style="color:#9333ea;"></i> Remanejamentos
            </div>
            <div style="padding:0 1.25rem 1.25rem;">
                <div style="text-align:center; padding:1rem 0;">
                    <div style="font-size:2.5rem; font-weight:700; color:var(--slate-900);"><?= e($totalRemanejamentos) ?></div>
                    <div style="font-size:0.75rem; color:var(--slate-400); margin-top:0.25rem;">transferências registradas</div>
                </div>
                <div style="border-top:1px solid var(--slate-100); padding-top:1rem; text-align:center;">
                    <div style="font-size:1.5rem; font-weight:700; color:#9333ea;"><?= e(number_format($totalQuantidadeTransferida ?? 0, 0, ',', '.')) ?></div>
                    <div style="font-size:0.75rem; color:var(--slate-400); margin-top:0.25rem;">unidades remanejadas</div>
                </div>
            </div>
        </div>
        <div style="padding:0 1.25rem 1.25rem;">
            <div style="font-size:0.75rem; font-weight:600; color:var(--slate-500); margin-bottom:0.5rem;">Divergências</div>
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <i class="fas fa-triangle-exclamation" style="color:#f59e0b; font-size:0.875rem;"></i>
                <span style="font-size:1.25rem; font-weight:700; color:var(--slate-900);"><?= e($divergencias) ?></span>
                <span style="font-size:0.75rem; color:var(--slate-400);">itens com troca ou observação</span>
            </div>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
    
    <div class="section-card">
        <div class="section-header">
            <i class="fas fa-ranking-star" style="color:var(--navy);"></i> 10 Itens Mais Solicitados
        </div>
        <div class="rhc-table-wrap">
            <table class="rhc-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Produto</th>
                        <th style="text-align:center;">Qtd. Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($topItens) > 0): ?><?php foreach ($topItens as $i): ?>
                        <tr>
                            <td style="font-family:ui-monospace,monospace; font-size:0.8rem; color:var(--slate-600);"><?= e($i->codigo) ?></td>
                            <td style="font-size:0.875rem;"><?= e($i->nome) ?></td>
                            <td style="text-align:center; font-weight:700; font-size:0.875rem;"><?= e(number_format($i->total_quantidade, 0, ',', '.')) ?></td>
                        </tr>
                    <?php endforeach; ?><?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align:center; padding:2rem; color:var(--slate-400);">Nenhum dado encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="section-card">
        <div class="section-header">
            <i class="fas fa-building" style="color:var(--navy);"></i> Volume por Unidade
        </div>
        <div style="padding:0 1.25rem 1.25rem;">
            <?php if (count($pedidosPorUnidade) > 0): ?><?php foreach ($pedidosPorUnidade as $pu): ?>
                <?php 
                    $maxU = $pedidosPorUnidade->first()->total > 0 ? $pedidosPorUnidade->first()->total : 1;
                    $percentU = round(($pu->total / $maxU) * 100);
                 ?>
                <div class="unit-bar">
                    <div class="unit-row">
                        <span class="unit-name"><?= e($pu->nome) ?></span>
                        <span class="unit-count"><?= e($pu->total) ?> pedidos</span>
                    </div>
                    <div style="height:0.375rem; border-radius:9999px; background:var(--slate-100); overflow:hidden;">
                        <div style="height:100%; border-radius:9999px; background:var(--navy); width:<?= e($percentU) ?>%; transition:width 0.5s;"></div>
                    </div>
                </div>
            <?php endforeach; ?><?php else: ?>
                <p style="text-align:center; padding:2rem 0; color:var(--slate-400);">Nenhum dado encontrado.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


<div style="margin-top:1rem;">
    <div class="section-card">
        <div style="padding:1.25rem; display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="width:2.5rem; height:2.5rem; border-radius:0.5rem; background:#ecfdf5; display:flex; align-items:center; justify-content:center;">
                    <i class="fas fa-dollar-sign" style="color:#059669;"></i>
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:600; color:var(--slate-500); text-transform:uppercase;">Valor Total Movimentado</div>
                    <div style="font-size:1.5rem; font-weight:700; color:var(--slate-900);">R$ <?= e(number_format($valorTotal, 2, ',', '.')) ?></div>
                </div>
            </div>
            <span style="font-size:0.75rem; color:var(--slate-400);">em pedidos processados</span>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
