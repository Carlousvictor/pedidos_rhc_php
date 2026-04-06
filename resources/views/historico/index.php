<?php 
$__title = 'Histórico de Pedidos';
ob_start();
 ?>
<style>
    .status-tabs {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        overflow-x: auto;
        padding-bottom: 0.125rem;
    }
    .status-tab {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.875rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        white-space: nowrap;
        background: transparent;
        border: none;
        cursor: pointer;
        text-decoration: none;
        color: var(--slate-600);
        transition: background 0.15s, color 0.15s;
    }
    .status-tab:hover { background: var(--slate-100); color: var(--slate-700); }
    .status-tab.active { background: var(--navy); color: white; }
    .status-tab .tab-count {
        font-size: 0.75rem;
        padding: 0.125rem 0.375rem;
        border-radius: 9999px;
        font-weight: 600;
    }
    .status-tab .tab-count { background: var(--slate-100); color: var(--slate-500); }
    .status-tab.active .tab-count { background: rgba(255,255,255,0.2); color: white; }
    .status-tab .tab-count.pendente-alert { background: #ffedd5; color: #9a3412; }

    .accent-row-yellow { border-left: 4px solid #facc15; }
    .accent-row-orange { border-left: 4px solid #fb923c; }
    .accent-row-navy { border-left: 4px solid #001A72; }
    .accent-row-green { border-left: 4px solid #22c55e; }

    .status-badge-hist {
        display: inline-flex;
        padding: 0.125rem 0.625rem;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.25rem;
        border-radius: 9999px;
    }
    .sb-yellow { background: #fef9c3; color: #854d0e; }
    .sb-orange { background: #ffedd5; color: #9a3412; }
    .sb-blue { background: #dbeafe; color: #001A72; }
    .sb-green { background: #dcfce7; color: #166534; }
    .sb-default { background: #f1f5f9; color: #334155; }

    .view-action {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.15s;
    }
    .view-action-default { color: var(--navy); background: #eff6ff; }
    .view-action-default:hover { background: #dbeafe; }
    .view-action-process { color: white; background: var(--navy); }
    .view-action-process:hover { background: var(--navy-dark); }
    .delete-action {
        padding: 0.375rem;
        color: #f87171;
        background: transparent;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.15s;
    }
    .delete-action:hover { color: #dc2626; background: #fef2f2; }
</style>
<?php $__styles = ob_get_clean();
include __DIR__ . '/../layouts/header.php';
 ?>
<?php 
    $usuario = session('usuario');
    $isAdminOrComprador = in_array($usuario->role, ['admin', 'comprador']);
    $currentStatus = request('status', '');

    $statusBadgeClasses = [
        'Aguardando Aprovação' => 'sb-yellow',
        'Pendente' => 'sb-orange',
        'Em Cotação' => 'sb-orange',
        'Realizado' => 'sb-blue',
        'Recebido' => 'sb-green',
    ];

    $accentClasses = [
        'Aguardando Aprovação' => 'accent-row-yellow',
        'Pendente' => 'accent-row-orange',
        'Em Cotação' => 'accent-row-orange',
        'Realizado' => 'accent-row-navy',
        'Recebido' => 'accent-row-green',
    ];

    $statusTabs = [
        '' => 'Todos',
        'Aguardando Aprovação' => 'Aguardando Aprovação',
        'Pendente' => 'Pendente',
        'Realizado' => 'Realizado',
        'Recebido' => 'Recebido',
    ];
 ?>


<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1.25rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.375rem; font-size:0.75rem; color:var(--slate-400); margin-bottom:0.5rem;">
            <a href="/" style="color:var(--slate-400); text-decoration:none;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:0.6rem;"></i>
            <span style="color:var(--slate-700); font-weight:500;">Histórico</span>
        </div>
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="width:2.5rem; height:2.5rem; border-radius:0.75rem; background:var(--slate-700); display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,.1); flex-shrink:0;">
                <i class="fas fa-clock-rotate-left" style="color:white; font-size:1rem;"></i>
            </div>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color:var(--slate-900); margin:0;">Histórico de Pedidos</h1>
                <p style="font-size:0.75rem; color:var(--slate-400); margin:0.125rem 0 0;">
                    <?php if ($isAdminOrComprador): ?>
                        Todos os pedidos do sistema — visualize e acompanhe o recebimento.
                    <?php else: ?>
                        Acompanhe suas solicitações e confirme o recebimento.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    <?php if ($isAdminOrComprador && ($statusCounts['Pendente'] ?? 0) > 0): ?>
        <div style="display:flex; align-items:center; gap:0.5rem; background:#fff7ed; border:1px solid #fed7aa; color:#c2410c; padding:0.625rem 1rem; border-radius:0.5rem; font-size:0.875rem; font-weight:500; flex-shrink:0;">
            <i class="fas fa-clock"></i>
            <?= e($statusCounts['Pendente']) ?> pedido<?= e(($statusCounts['Pendente'] ?? 0) > 1 ? 's' : '') ?> aguardando
        </div>
    <?php endif; ?>
</div>


<div class="rhc-card" style="overflow:hidden;">
    
    <div style="padding:1.25rem; border-bottom:1px solid var(--slate-100);">
        <div style="position:relative; width:100%; max-width:24rem; margin-bottom:1rem;">
            <i class="fas fa-search" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--slate-400); font-size:0.75rem;"></i>
            <form method="GET" action="<?= e(route('historico')) ?>" id="historicoForm">
                <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
                <?php if (request('unidade_id')): ?>
                    <input type="hidden" name="unidade_id" value="<?= e(request('unidade_id')) ?>">
                <?php endif; ?>
                <input type="text" name="search" class="rhc-input" placeholder="Buscar por Nº do Pedido ou Unidade..."
                       value="<?= e(request('search')) ?>" style="padding-left:2.25rem; width:100%;">
            </form>
        </div>

        <div class="status-tabs">
            <?php foreach ($statusTabs as $value => $label): ?>
                <?php 
                    $count = $value === '' ? $pedidos->total() : ($statusCounts[$value] ?? 0);
                    $isActive = $currentStatus === $value;
                    $isPendenteAlert = $value === 'Pendente' && $count > 0 && $isAdminOrComprador;
                 ?>
                <a href="<?= e(route('historico', array_merge(request()->except('status', 'page'), ['status' => $value]))) ?>"
                   class="status-tab <?= e($isActive ? 'active' : '') ?>">
                    <?= e($label) ?>
                    <span class="tab-count <?= e($isPendenteAlert && !$isActive ? 'pendente-alert' : '') ?>"><?= e($count) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    
    <div class="rhc-table-wrap">
        <table class="rhc-table">
            <thead>
                <tr>
                    <th>Nº Pedido</th>
                    <th>Unidade</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th style="text-align:right;">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pedidos) > 0): foreach ($pedidos as $pedido): ?>
                    <tr class="<?= e($accentClasses[$pedido->status] ?? '') ?>">
                        <td style="font-weight:500; font-size:0.875rem; color:var(--slate-900);">
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <i class="fas fa-file-alt" style="color:var(--slate-400); font-size:0.8rem;"></i>
                                #<?= e($pedido->numero_pedido ?? '(sem número)') ?>
                            </div>
                        </td>
                        <td style="font-size:0.875rem; color:var(--slate-600);"><?= e($pedido->unidade->nome ?? '—') ?></td>
                        <td style="font-size:0.875rem; color:var(--slate-500);"><?= e($pedido->created_at->format('d/m/Y H:i')) ?></td>
                        <td>
                            <span class="status-badge-hist <?= e($statusBadgeClasses[$pedido->status] ?? 'sb-default') ?>">
                                <?= e($pedido->status) ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.5rem;">
                                <?php  $isPendente = strtolower($pedido->status) === 'pendente' && $isAdminOrComprador;  ?>
                                <a href="<?= e(route('pedidos.show', $pedido->id)) ?>"
                                   class="view-action <?= e($isPendente ? 'view-action-process' : 'view-action-default') ?>">
                                    <?= e($isPendente ? 'Processar' : 'Visualizar') ?>
                                    <i class="fas fa-arrow-right" style="font-size:0.7rem;"></i>
                                </a>
                                <?php if ($isAdminOrComprador): ?>
                                    <form action="<?= e(route('pedidos.destroy', $pedido->id)) ?>" method="POST" class="d-inline"
                                          onsubmit="return confirm('O pedido #<?= e($pedido->numero_pedido) ?> será excluído permanentemente. Esta ação não pode ser desfeita.');">
                                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="delete-action" title="Excluir pedido">
                                            <i class="fas fa-trash" style="font-size:0.8rem;"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-search"></i>
                                Nenhum pedido encontrado.
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pedidos->count() > 0): ?>
        <div style="padding:0.75rem 1.5rem; border-top:1px solid var(--slate-50); font-size:0.75rem; color:var(--slate-400);">
            <?= e($pedidos->total()) ?> pedido<?= e($pedidos->total() !== 1 ? 's' : '') ?> exibido<?= e($pedidos->total() !== 1 ? 's' : '') ?>
        </div>
    <?php endif; ?>

    <?php if ($pedidos->hasPages()): ?>
        <div style="padding:0.75rem 1.5rem; border-top:1px solid var(--slate-100); display:flex; justify-content:center;">
            <?= e($pedidos->withQueryString()->links('pagination::bootstrap-5')) ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
