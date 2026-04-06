<?php
$__title = 'Transferências e Remanejamentos';
ob_start();
?>
<style>
    .transfer-status {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.125rem 0.625rem;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.25rem;
        border-radius: 9999px;
    }
    .ts-concluido { background: #dcfce7; color: #166534; }
    .ts-transito { background: #dbeafe; color: #1e40af; }
    .ts-pendente { background: #f3e8ff; color: #6b21a8; }

    .row-concluido { background: rgba(220, 252, 231, 0.15); }
    .row-transito { background: rgba(219, 234, 254, 0.15); }
    .row-pendente { background: rgba(243, 232, 255, 0.15); }

    .confirm-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.15s;
    }
    .confirm-btn-primary { background: var(--navy); color: white; }
    .confirm-btn-primary:hover { background: var(--navy-dark); }

    .qty-input {
        width: 4rem;
        padding: 0.25rem 0.5rem;
        border: 1px solid var(--slate-200);
        border-radius: 0.375rem;
        font-size: 0.8rem;
        text-align: center;
        font-weight: 600;
    }
    .qty-input:focus { outline: none; border-color: var(--navy); box-shadow: 0 0 0 2px rgba(0,26,114,0.1); }

    .pedido-link {
        color: var(--navy);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .pedido-link:hover { text-decoration: underline; }

    .origin-card, .dest-card {
        border-radius: 0.75rem;
        border: 1px solid var(--slate-100);
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        padding: 1.25rem;
    }
    .origin-card .card-title { font-weight: 700; color: var(--navy); font-size: 0.875rem; border-bottom: 1px solid var(--slate-100); padding-bottom: 0.5rem; margin-bottom: 0.75rem; }
    .dest-card .card-title { font-weight: 700; color: var(--green-600); font-size: 0.875rem; border-bottom: 1px solid var(--slate-100); padding-bottom: 0.5rem; margin-bottom: 0.75rem; }
</style>
<?php $__styles = ob_get_clean();
ob_start();
?>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tomselect').forEach(el => {
        new TomSelect(el, {
            create: false,
            sortField: { field: "text", direction: "asc" }
        });
    });

    const pedidoOrigemSelect = document.getElementById('pedidoOrigemSelect');
    const itemOrigemSelect = document.getElementById('itemOrigemSelect');

    pedidoOrigemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        itemOrigemSelect.innerHTML = '<option value="">Selecione o item...</option>';

        if (!selectedOption.value) {
            itemOrigemSelect.disabled = true;
            return;
        }

        const itemsRaw = selectedOption.getAttribute('data-itens');
        if (itemsRaw) {
            const items = JSON.parse(itemsRaw);
            items.forEach(pi => {
                if(pi.item) {
                    const opt = document.createElement('option');
                    opt.value = pi.id;
                    opt.textContent = `[${pi.item.codigo}] ${pi.item.nome} (Qtd: ${pi.quantidade})`;
                    itemOrigemSelect.appendChild(opt);
                }
            });
            itemOrigemSelect.disabled = false;
        }
    });
});
</script>
<?php $__scripts = ob_get_clean();
include __DIR__ . '/../layouts/header.php';
?>
<?php 
    $usuario = session('usuario');
    $isAdminOrComprador = in_array($usuario->role, ['admin', 'comprador']);
 ?>


<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1.25rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.375rem; font-size:0.75rem; color:var(--slate-400); margin-bottom:0.5rem;">
            <a href="/" style="color:var(--slate-400); text-decoration:none;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:0.6rem;"></i>
            <span style="color:var(--slate-700); font-weight:500;">Transferências</span>
        </div>
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="width:2.5rem; height:2.5rem; border-radius:0.75rem; background:#9333ea; display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,.1); flex-shrink:0;">
                <i class="fas fa-right-left" style="color:white; font-size:1rem;"></i>
            </div>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color:var(--slate-900); margin:0;">Transferências</h1>
                <p style="font-size:0.75rem; color:var(--slate-400); margin:0.125rem 0 0;">
                    Gerencie remanejamentos de itens entre pedidos e unidades.
                </p>
            </div>
        </div>
    </div>
    <?php if ($isAdminOrComprador): ?>
        <button class="rhc-btn rhc-btn-primary" data-bs-toggle="modal" data-bs-target="#novaTransferenciaModal" style="flex-shrink:0;">
            <i class="fas fa-plus" style="margin-right:0.375rem;"></i> Nova Transferência
        </button>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../components/flash-messages.php'; ?>


<div class="rhc-card" style="overflow:hidden;">
    <div class="rhc-table-wrap">
        <table class="rhc-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Código</th>
                    <th style="text-align:center;">Qtd</th>
                    <th>De (Envia)</th>
                    <th>Para (Recebe)</th>
                    <th style="text-align:center;">Qtd Recebida</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($remanejamentos) > 0): ?><?php foreach ($remanejamentos as $rem): ?>
                    <?php 
                        $qtdRecebida = $rem->quantidade_recebida ?? 0;
                        $isConcluido = $qtdRecebida >= $rem->quantidade;
                        $isTransito = $qtdRecebida > 0 && $qtdRecebida < $rem->quantidade;
                        $isPendente = $qtdRecebida == 0;

                        $rowClass = $isConcluido ? 'row-concluido' : ($isTransito ? 'row-transito' : 'row-pendente');
                        $statusClass = $isConcluido ? 'ts-concluido' : ($isTransito ? 'ts-transito' : 'ts-pendente');
                        $statusLabel = $isConcluido ? 'Concluído' : ($isTransito ? 'Em trânsito' : 'Pendente');
                     ?>
                    <tr class="<?= e($rowClass) ?>">
                        <td style="font-size:0.875rem; font-weight:500;"><?= e($rem->item->nome ?? 'Item Deletado') ?></td>
                        <td style="font-family:ui-monospace,monospace; font-size:0.8rem; color:var(--slate-600);"><?= e($rem->item->codigo ?? '-') ?></td>
                        <td style="text-align:center; font-weight:700; font-size:0.875rem;"><?= e($rem->quantidade) ?></td>
                        <td>
                            <?php if ($rem->pedidoItemOrigem && $rem->pedidoItemOrigem->pedido): ?>
                                <a href="<?= e(route('pedidos.show', $rem->pedidoItemOrigem->pedido->id)) ?>" class="pedido-link">
                                    #<?= e($rem->pedidoItemOrigem->pedido->numero_pedido ?? 'S/N') ?>
                                </a>
                                <div style="font-size:0.7rem; color:var(--slate-400); margin-top:0.125rem;">
                                    <i class="fas fa-building" style="font-size:0.6rem; margin-right:0.25rem;"></i><?= e($rem->pedidoItemOrigem->pedido->unidade->nome ?? '-') ?>
                                </div>
                            <?php else: ?>
                                <span style="color:var(--slate-400); font-size:0.8rem;">Desconhecido</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($rem->pedidoDestino): ?>
                                <a href="<?= e(route('pedidos.show', $rem->pedidoDestino->id)) ?>" class="pedido-link">
                                    #<?= e($rem->pedidoDestino->numero_pedido ?? 'S/N') ?>
                                </a>
                                <div style="font-size:0.7rem; color:var(--slate-400); margin-top:0.125rem;">
                                    <i class="fas fa-building" style="font-size:0.6rem; margin-right:0.25rem;"></i><?= e($rem->pedidoDestino->unidade->nome ?? '-') ?>
                                </div>
                            <?php else: ?>
                                <span style="color:var(--slate-400); font-size:0.8rem;">Desconhecido</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if (!$isConcluido && $isAdminOrComprador): ?>
                                <form action="<?= e(route('transferencias.confirmar', $rem->id)) ?>" method="POST" style="display:inline-flex; align-items:center; gap:0.375rem;">
                                    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                    <input type="number" name="quantidade_recebida" class="qty-input" min="0" max="<?= e($rem->quantidade) ?>" value="<?= e($qtdRecebida) ?>">
                                    <button type="submit" class="confirm-btn confirm-btn-primary">
                                        <i class="fas fa-check" style="font-size:0.6rem;"></i> Confirmar
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="font-weight:700; font-size:0.875rem; color:<?= e($isConcluido ? '#166534' : 'var(--slate-600)') ?>;"><?= e($qtdRecebida) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="transfer-status <?= e($statusClass) ?>">
                                <i class="fas <?= e($isConcluido ? 'fa-circle-check' : ($isTransito ? 'fa-truck' : 'fa-hourglass-half')) ?>" style="font-size:0.6rem;"></i>
                                <?= e($statusLabel) ?>
                            </span>
                        </td>
                        <td style="font-size:0.75rem; color:var(--slate-400);"><?= e($rem->created_at->format('d/m/Y H:i')) ?></td>
                    </tr>
                <?php endforeach; ?><?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="fas fa-right-left"></i>
                                Nenhuma transferência registrada no sistema.
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div class="modal fade" id="novaTransferenciaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?= e(route('transferencias.store')) ?>" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <div class="modal-content" style="border:0; border-radius:1rem; overflow:hidden;">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid var(--slate-100);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--slate-800); margin:0;">
                        <i class="fas fa-right-left" style="margin-right:0.5rem; color:var(--navy);"></i>Registrar Nova Transferência
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div style="padding:1.25rem; background:var(--slate-50);">
                    <div style="display:grid; grid-template-columns:5fr 1fr 5fr; gap:0.75rem; align-items:start;">
                        
                        <div class="origin-card" style="background:white;">
                            <div class="card-title"><i class="fas fa-arrow-right-from-bracket" style="margin-right:0.375rem;"></i> Origem</div>
                            <div style="margin-bottom:0.75rem;">
                                <label class="rhc-label">Pedido de Origem</label>
                                <select id="pedidoOrigemSelect" class="rhc-select tomselect" style="width:100%;" required>
                                    <option value="">Selecione o pedido...</option>
                                    <?php foreach ($pedidosOrigem as $p): ?>
                                        <option value="<?= e($p->id) ?>" data-itens="<?= e(json_encode($p->itens)) ?>">
                                            #<?= e($p->numero_pedido ?? 'N/A') ?> - <?= e($p->unidade->nome ?? '-') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="margin-bottom:0.75rem;">
                                <label class="rhc-label">Item a Transferir</label>
                                <select id="itemOrigemSelect" name="pedido_item_origem_id" class="rhc-select" style="width:100%;" required disabled>
                                    <option value="">Aguardando pedido de origem...</option>
                                </select>
                            </div>
                            <div>
                                <label class="rhc-label">Qtd a Transferir</label>
                                <input type="number" name="quantidade" class="rhc-input" min="1" required>
                            </div>
                        </div>

                        
                        <div style="display:flex; align-items:center; justify-content:center; padding-top:3rem;">
                            <i class="fas fa-arrow-right" style="font-size:1.5rem; color:var(--slate-300);"></i>
                        </div>

                        
                        <div class="dest-card" style="background:white;">
                            <div class="card-title"><i class="fas fa-arrow-right-to-bracket" style="margin-right:0.375rem;"></i> Destino</div>
                            <div style="margin-bottom:0.75rem;">
                                <label class="rhc-label">Pedido de Destino</label>
                                <select name="pedido_destino_id" class="rhc-select tomselect" style="width:100%;" required>
                                    <option value="">Selecione o pedido...</option>
                                    <?php foreach ($pedidosDestino as $p): ?>
                                        <option value="<?= e($p->id) ?>">
                                            #<?= e($p->numero_pedido ?? 'N/A') ?> - <?= e($p->unidade->nome ?? '-') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <p style="font-size:0.75rem; color:var(--slate-400); margin:0;">
                                <i class="fas fa-info-circle" style="margin-right:0.25rem;"></i>
                                Um novo item com essa quantidade será adicionado ao pedido destino.
                            </p>
                        </div>
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:0.5rem; padding:1rem 1.25rem; border-top:1px solid var(--slate-100);">
                    <button type="button" class="rhc-btn rhc-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="rhc-btn rhc-btn-primary">
                        <i class="fas fa-right-left" style="margin-right:0.25rem;"></i> Confirmar Transferência
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
