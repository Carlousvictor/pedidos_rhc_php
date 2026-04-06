<?php
$__title = 'Editar Pedido #' . $pedido->numero_pedido;
ob_start();
?>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    
    function initTomSelect(el) {
        new TomSelect(el, {
            create: false,
            sortField: { field: "text", direction: "asc" },
            placeholder: "-- Selecione um item --"
        });
    }

    document.querySelectorAll('.item-select').forEach(initTomSelect);

    document.getElementById('btn-add-item').addEventListener('click', function() {
        const container = document.getElementById('new-items-container');
        const firstRow = container.querySelector('.new-item-row');
        const clone = firstRow.cloneNode(true);
        
        // Remove tom-select classes and wrappers from clone
        const selectWrap = clone.querySelector('.ts-wrapper');
        if (selectWrap) selectWrap.remove();
        
        const originalSelect = firstRow.querySelector('select').cloneNode(true);
        originalSelect.className = 'form-select item-select mt-0';
        originalSelect.name = `novos_itens[${rowIndex}][item_id]`;
        
        const colMd8 = clone.querySelector('.col-md-8');
        colMd8.appendChild(originalSelect);
        
        const qtyInput = clone.querySelector('input[type="number"]');
        qtyInput.name = `novos_itens[${rowIndex}][quantidade]`;
        qtyInput.value = 1;

        const removeBtn = clone.querySelector('.btn-remove-row');
        removeBtn.style.display = 'inline-block';
        removeBtn.addEventListener('click', function() {
            clone.remove();
        });

        container.appendChild(clone);
        initTomSelect(originalSelect);
        rowIndex++;
    });
});
</script>
<?php $__scripts = ob_get_clean();
include __DIR__ . '/../layouts/header.php';
?>
<div class="row mb-3">
    <div class="col">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= e(route('dashboard')) ?>">Pedidos</a></li>
                <li class="breadcrumb-item"><a href="<?= e(route('pedidos.show', $pedido->id)) ?>">Pedido #<?= e($pedido->numero_pedido) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
        <h2 class="fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Editar Pedido</h2>
    </div>
</div>

<?php include __DIR__ . '/../components/flash-messages.php'; ?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="<?= e(route('pedidos.update', $pedido->id)) ?>" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="_method" value="PUT">

            <h5 class="fw-bold border-bottom pb-2 mb-3">Itens Atuais</h5>
            
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Produto</th>
                            <th style="width: 150px">Qtd Solicitada</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedido->itens as $item): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= e($item->item->codigo) ?></span></td>
                            <td><?= e($item->item->nome) ?></td>
                            <td>
                                <input type="number" class="form-control" name="itens[<?= e($item->id) ?>][quantidade]" value="<?= e($item->quantidade) ?>" min="1" required>
                            </td>
                            <td class="text-end">
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input text-danger" type="checkbox" name="remover_itens[]" value="<?= e($item->id) ?>" id="remover_<?= e($item->id) ?>">
                                    <label class="form-check-label text-danger" for="remover_<?= e($item->id) ?>">Remover</label>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <h5 class="fw-bold border-bottom pb-2 mb-3 mt-4">Adicionar Novos Itens (Opcional)</h5>
            
            <div id="new-items-container">
                <div class="row new-item-row mb-3 align-items-end border p-3 rounded bg-light">
                    <div class="col-md-8">
                        <label class="form-label">Selecionar Item</label>
                        <select class="form-select item-select" name="novos_itens[0][item_id]">
                            <option value="">-- Selecione --</option>
                            <?php foreach ($items as $catItem): ?>
                                <option value="<?= e($catItem->id) ?>">[<?= e($catItem->codigo) ?>] <?= e($catItem->nome) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quantidade</label>
                        <input type="number" class="form-control" name="novos_itens[0][quantidade]" min="1" value="1">
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-outline-danger btn-remove-row" style="display:none;"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <button type="button" class="btn btn-sm btn-outline-success" id="btn-add-item">
                    <i class="fas fa-plus me-1"></i> Adicionar Mais Itens
                </button>
            </div>

            <hr>
            
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="<?= e(route('pedidos.show', $pedido->id)) ?>" class="btn btn-secondary px-4">Cancelar</a>
                <button type="submit" class="btn btn-primary px-5"><i class="fas fa-save me-2"></i> Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
