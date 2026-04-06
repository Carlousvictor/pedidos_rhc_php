<?php
$__title = 'Novo Pedido';
ob_start();
?>
<style>
    .search-results {
        position: absolute;
        z-index: 1050;
        width: 100%;
        max-height: 300px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid var(--slate-200);
        border-top: 0;
        border-radius: 0 0 0.5rem 0.5rem;
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        display: none;
    }
    .search-results .search-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid var(--slate-100);
        transition: background-color 0.15s;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .search-results .search-item:hover {
        background-color: var(--slate-50);
    }
    .search-results .search-item:last-child {
        border-bottom: none;
    }
    .search-item .si-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        background: var(--slate-100);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .search-item .si-icon i { color: var(--slate-500); font-size: 0.75rem; }
    .search-item .si-code {
        font-weight: 600;
        color: var(--navy);
        font-size: 0.8rem;
        font-family: ui-monospace, monospace;
    }
    .search-item .si-name {
        font-size: 0.875rem;
        color: var(--slate-800);
    }
    .search-item .si-type {
        font-size: 0.7rem;
        color: var(--slate-400);
    }

    /* Item Add Modal */
    .item-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .item-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
    }
    .item-modal-card {
        position: relative;
        background: white;
        border-radius: 0.75rem;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        border: 1px solid var(--slate-100);
        width: 100%;
        max-width: 28rem;
        margin: 1rem;
        overflow: hidden;
    }
    .item-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 1.5rem;
        border-bottom: 1px solid var(--slate-100);
    }
    .item-modal-header .im-info {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        flex: 1;
        min-width: 0;
        margin-right: 0.75rem;
    }
    .item-modal-header .im-icon {
        padding: 0.5rem;
        background: #eff6ff;
        color: var(--navy);
        border-radius: 0.5rem;
        flex-shrink: 0;
        margin-top: 0.125rem;
    }
    .item-modal-header .im-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--slate-800);
        line-height: 1.3;
    }
    .item-modal-header .im-close {
        padding: 0.25rem;
        color: var(--slate-400);
        background: none;
        border: none;
        cursor: pointer;
        flex-shrink: 0;
    }
    .item-modal-header .im-close:hover { color: var(--slate-600); }

    .im-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        padding: 1.25rem 1.5rem 1rem;
    }
    .im-detail-box {
        background: var(--slate-50);
        border-radius: 0.5rem;
        padding: 0.75rem;
    }
    .im-detail-label { font-size: 0.75rem; color: var(--slate-400); margin-bottom: 0.125rem; }
    .im-detail-value { font-size: 0.875rem; font-weight: 600; color: var(--slate-800); }

    .im-qty-section { padding: 0 1.5rem 1.5rem; }
    .im-qty-label { font-size: 0.875rem; font-weight: 500; color: var(--slate-700); margin-bottom: 0.75rem; }
    .im-qty-control {
        display: flex;
        align-items: center;
        border: 1px solid var(--slate-200);
        border-radius: 0.5rem;
        overflow: hidden;
        width: fit-content;
    }
    .im-qty-btn {
        padding: 0.75rem 1.25rem;
        background: var(--slate-50);
        color: var(--slate-600);
        border: none;
        cursor: pointer;
        font-size: 1.125rem;
        font-weight: 700;
        transition: background 0.15s;
    }
    .im-qty-btn:hover { background: var(--slate-100); }
    .im-qty-input {
        width: 5rem;
        padding: 0.75rem 0;
        font-size: 1.25rem;
        font-weight: 700;
        text-align: center;
        border: none;
        border-left: 1px solid var(--slate-200);
        border-right: 1px solid var(--slate-200);
        outline: none;
    }
    .im-qty-input:focus { background: #eff6ff; }

    .im-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 0 1.5rem 1.5rem;
    }

    .tipo-badge-sm {
        display: inline-block;
        margin-top: 0.25rem;
        padding: 0.125rem 0.375rem;
        font-size: 0.625rem;
        font-weight: 600;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .tipo-bbraun { background: #dbeafe; color: #1e40af; }
    .tipo-fraldas { background: #dcfce7; color: #166534; }
    .tipo-lifetex { background: #ffedd5; color: #9a3412; }
    .tipo-mathospitalar { background: #f1f5f9; color: #334155; }
    .tipo-medonco { background: #fee2e2; color: #991b1b; }
    .tipo-medoncolibbs { background: #f3e8ff; color: #6b21a8; }
    .tipo-medicamentos { background: #ccfbf1; color: #115e59; }
    .tipo-default { background: #f1f5f9; color: #334155; }

    .selected-items-table .item-icon-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .selected-items-table .item-icon-box {
        width: 1.75rem;
        height: 1.75rem;
        background: var(--slate-100);
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .selected-items-table .item-icon-box i { color: var(--slate-500); font-size: 0.7rem; }
</style>
<?php $__styles = ob_get_clean();
ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const searchInput = document.getElementById('itemSearch');
    const searchResults = document.getElementById('searchResults');
    const itensBody = document.getElementById('itensBody');
    const emptyRow = document.getElementById('emptyRow');
    const btnSubmit = document.getElementById('btnSubmit');
    const itemCountEl = document.getElementById('itemCount');

    let itemIndex = 0;
    let addedItems = {};
    let debounceTimer = null;
    let pendingItem = null; // item waiting for quantity confirmation
    let editingItemId = null; // if editing quantity of existing item

    const tipoClasses = {
        'B.BRAUN': 'tipo-bbraun',
        'FRALDAS': 'tipo-fraldas',
        'LIFETEX-SURGITEXTIL': 'tipo-lifetex',
        'MAT. MED. HOSPITALAR': 'tipo-mathospitalar',
        'MED. ONCO': 'tipo-medonco',
        'MED. ONCO CONTR. LIBBS.': 'tipo-medoncolibbs',
        'MEDICAMENTOS': 'tipo-medicamentos',
    };

    // Debounced search
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(function() {
            fetch('/itens/buscar?q=' + encodeURIComponent(query), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                searchResults.innerHTML = '';

                if (data.length === 0) {
                    searchResults.innerHTML = '<div class="search-item" style="color:var(--slate-400); justify-content:center;"><i class="fas fa-search" style="margin-right:0.5rem;"></i> Nenhum item encontrado.</div>';
                    searchResults.style.display = 'block';
                    return;
                }

                data.forEach(function(item) {
                    var div = document.createElement('div');
                    div.className = 'search-item';
                    div.innerHTML = '<div class="si-icon"><i class="fas fa-box"></i></div>'
                        + '<div style="min-width:0;">'
                        + '<div><span class="si-code">[' + escapeHtml(item.codigo || '') + ']</span> '
                        + '<span class="si-name">' + escapeHtml(item.nome) + '</span></div>'
                        + (item.tipo ? '<div class="si-type">' + escapeHtml(item.tipo) + '</div>' : '')
                        + '</div>';
                    div.addEventListener('click', function() {
                        openItemModal(item, 'add');
                    });
                    searchResults.appendChild(div);
                });

                searchResults.style.display = 'block';
            })
            .catch(function(err) {
                console.error('Erro na busca:', err);
            });
        }, 300);
    });

    // Close search results on outside click
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    // ── Modal functions ──

    window.openItemModal = function(item, mode, currentQty) {
        pendingItem = item;
        editingItemId = (mode === 'edit') ? item.id : null;

        document.getElementById('modalItemName').textContent = item.nome;
        document.getElementById('modalItemCode').textContent = item.codigo || '-';

        // Tipo badge
        var tipoEl = document.getElementById('modalItemTipo');
        if (item.tipo) {
            var cls = tipoClasses[item.tipo] || 'tipo-default';
            tipoEl.innerHTML = '<span class="tipo-badge-sm ' + cls + '">' + escapeHtml(item.tipo) + '</span>';
        } else {
            tipoEl.innerHTML = '';
        }

        // Reference
        var refBox = document.getElementById('modalRefBox');
        if (item.referencia) {
            document.getElementById('modalItemRef').textContent = item.referencia;
            refBox.style.display = '';
        } else {
            refBox.style.display = 'none';
        }

        // Quantity
        document.getElementById('modalQtyInput').value = currentQty || 1;

        // Button text
        var confirmBtn = document.getElementById('modalConfirmBtn');
        if (mode === 'edit') {
            confirmBtn.innerHTML = '<i class="fas fa-save" style="margin-right:0.25rem;"></i> Salvar Quantidade';
        } else {
            confirmBtn.innerHTML = '<i class="fas fa-plus" style="margin-right:0.25rem;"></i> Adicionar ao Pedido';
        }

        document.getElementById('itemAddModal').style.display = '';
        document.getElementById('modalQtyInput').focus();
        document.getElementById('modalQtyInput').select();
    };

    window.closeItemModal = function() {
        document.getElementById('itemAddModal').style.display = 'none';
        pendingItem = null;
        editingItemId = null;
    };

    window.changeQty = function(delta) {
        var input = document.getElementById('modalQtyInput');
        var val = Math.max(1, (parseInt(input.value) || 1) + delta);
        input.value = val;
    };

    // Handle Enter key in qty input
    document.getElementById('modalQtyInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            confirmAddItem();
        }
    });

    window.confirmAddItem = function() {
        if (!pendingItem) return;

        var qty = Math.max(1, parseInt(document.getElementById('modalQtyInput').value) || 1);

        if (editingItemId) {
            // Editing existing item quantity
            var qtyInput = document.querySelector('#itemRow-' + editingItemId + ' .qty-display');
            var hiddenInput = document.querySelector('#itemRow-' + editingItemId + ' input[name$="[quantidade]"]');
            if (qtyInput) qtyInput.textContent = qty;
            if (hiddenInput) hiddenInput.value = qty;
            closeItemModal();
            return;
        }

        // Adding new item
        if (addedItems[pendingItem.id]) {
            alert('Este item já foi adicionado ao pedido.');
            closeItemModal();
            return;
        }

        addedItems[pendingItem.id] = pendingItem;
        emptyRow.style.display = 'none';

        var idx = itemIndex++;
        var tipoCls = tipoClasses[pendingItem.tipo] || 'tipo-default';
        var tr = document.createElement('tr');
        tr.id = 'itemRow-' + pendingItem.id;
        tr.innerHTML = ''
            + '<td style="font-family:ui-monospace,monospace; font-size:0.8rem; color:var(--slate-600);">' + escapeHtml(pendingItem.codigo || '') + '</td>'
            + '<td>'
            + '  <div class="item-icon-cell">'
            + '    <div class="item-icon-box"><i class="fas fa-box"></i></div>'
            + '    <span style="font-size:0.875rem; color:var(--slate-900);">' + escapeHtml(pendingItem.nome) + '</span>'
            + '  </div>'
            + '</td>'
            + '<td>'
            + (pendingItem.tipo ? '<span class="tipo-badge-sm ' + tipoCls + '">' + escapeHtml(pendingItem.tipo) + '</span>' : '<span style="color:var(--slate-400);">—</span>')
            + '</td>'
            + '<td style="text-align:center;">'
            + '  <span class="qty-display" style="font-weight:700; font-size:1rem; color:var(--slate-900); cursor:pointer;" '
            + '    onclick="openItemModal(addedItems[\'' + pendingItem.id + '\'], \'edit\', ' + qty + ')" title="Clique para alterar">'
            + qty
            + '  </span>'
            + '  <input type="hidden" name="itens[' + idx + '][quantidade]" value="' + qty + '">'
            + '  <input type="hidden" name="itens[' + idx + '][item_id]" value="' + pendingItem.id + '">'
            + '</td>'
            + '<td style="text-align:center;">'
            + '  <button type="button" class="btn-remove" data-item-id="' + pendingItem.id + '" '
            + '    style="padding:0.375rem; color:var(--red-500); background:none; border:none; cursor:pointer; border-radius:0.375rem; transition:all 0.15s;"'
            + '    onmouseover="this.style.background=\'#fef2f2\'" onmouseout="this.style.background=\'none\'">'
            + '    <i class="fas fa-trash" style="font-size:0.75rem;"></i>'
            + '  </button>'
            + '</td>';

        itensBody.appendChild(tr);
        updateSubmitButton();
        closeItemModal();

        // Clear search
        searchInput.value = '';
        searchResults.style.display = 'none';
    };

    // Make addedItems accessible from inline onclick
    window.addedItems = addedItems;

    // Remove item (event delegation)
    itensBody.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-remove');
        if (!btn) return;

        var itemId = btn.getAttribute('data-item-id');
        var row = document.getElementById('itemRow-' + itemId);
        if (row) row.remove();
        delete addedItems[itemId];
        delete window.addedItems[itemId];

        updateSubmitButton();
    });

    function updateSubmitButton() {
        var count = Object.keys(addedItems).length;
        btnSubmit.disabled = count === 0;
        var csvBtn = document.getElementById('btnDownloadCsv');
        if (csvBtn) csvBtn.disabled = count === 0;
        if (count === 0) {
            emptyRow.style.display = '';
            itemCountEl.style.display = 'none';
        } else {
            itemCountEl.style.display = '';
            itemCountEl.textContent = count + ' ite' + (count === 1 ? 'm adicionado' : 'ns adicionados');
        }
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // ── Excel Import ──
    document.getElementById('excelImport').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;

        var reader = new FileReader();
        reader.onload = function(evt) {
            try {
                var data = new Uint8Array(evt.target.result);
                var workbook = XLSX.read(data, { type: 'array' });
                var sheet = workbook.Sheets[workbook.SheetNames[0]];
                var rows = XLSX.utils.sheet_to_json(sheet, { defval: '' });

                var foundCount = 0;
                var notFound = [];

                // We need to fetch all items to match by codigo
                fetch('/itens/buscar?q=&all=1', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                })
                .then(function(r) { return r.json(); })
                .then(function(allItems) {
                    rows.forEach(function(row) {
                        var codigo = String(row['Codigo'] || row['codigo'] || row['CODIGO'] || row['Código'] || row['código'] || '').trim();
                        var quantidade = parseInt(row['Quantidade'] || row['quantidade'] || row['QUANTIDADE'] || row['Qtd'] || row['qtd'] || '1') || 1;

                        if (!codigo) return;

                        var matched = allItems.find(function(i) { return String(i.codigo).trim() === codigo; });
                        if (matched && !addedItems[matched.id]) {
                            // Add directly (skip modal for bulk import)
                            addedItems[matched.id] = matched;
                            window.addedItems[matched.id] = matched;
                            emptyRow.style.display = 'none';

                            var idx = itemIndex++;
                            var tipoCls = tipoClasses[matched.tipo] || 'tipo-default';
                            var tr = document.createElement('tr');
                            tr.id = 'itemRow-' + matched.id;
                            tr.innerHTML = ''
                                + '<td style="font-family:ui-monospace,monospace; font-size:0.8rem; color:var(--slate-600);">' + escapeHtml(matched.codigo || '') + '</td>'
                                + '<td><div class="item-icon-cell"><div class="item-icon-box"><i class="fas fa-box"></i></div>'
                                + '<span style="font-size:0.875rem; color:var(--slate-900);">' + escapeHtml(matched.nome) + '</span></div></td>'
                                + '<td>' + (matched.tipo ? '<span class="tipo-badge-sm ' + tipoCls + '">' + escapeHtml(matched.tipo) + '</span>' : '<span style="color:var(--slate-400);">—</span>') + '</td>'
                                + '<td style="text-align:center;">'
                                + '  <span class="qty-display" style="font-weight:700; font-size:1rem; color:var(--slate-900); cursor:pointer;"'
                                + '    onclick="openItemModal(addedItems[\'' + matched.id + '\'], \'edit\', ' + quantidade + ')" title="Clique para alterar">' + quantidade + '</span>'
                                + '  <input type="hidden" name="itens[' + idx + '][quantidade]" value="' + quantidade + '">'
                                + '  <input type="hidden" name="itens[' + idx + '][item_id]" value="' + matched.id + '">'
                                + '</td>'
                                + '<td style="text-align:center;">'
                                + '  <button type="button" class="btn-remove" data-item-id="' + matched.id + '"'
                                + '    style="padding:0.375rem; color:var(--red-500); background:none; border:none; cursor:pointer; border-radius:0.375rem;"'
                                + '    onmouseover="this.style.background=\'#fef2f2\'" onmouseout="this.style.background=\'none\'">'
                                + '    <i class="fas fa-trash" style="font-size:0.75rem;"></i></button></td>';
                            itensBody.appendChild(tr);
                            foundCount++;
                        } else if (!matched) {
                            notFound.push(codigo);
                        }
                    });

                    updateSubmitButton();

                    // Show result
                    var resultEl = document.getElementById('importResult');
                    var resultText = document.getElementById('importResultText');
                    var msg = foundCount + ' ite' + (foundCount === 1 ? 'm importado' : 'ns importados') + ' com sucesso.';
                    if (notFound.length > 0) {
                        msg += ' ' + notFound.length + ' código(s) não encontrado(s): ' + notFound.join(', ');
                        resultEl.style.background = '#fffbeb';
                        resultEl.style.borderColor = '#fde68a';
                        resultEl.style.color = '#92400e';
                    } else {
                        resultEl.style.background = '#ecfdf5';
                        resultEl.style.borderColor = '#a7f3d0';
                        resultEl.style.color = '#065f46';
                    }
                    resultText.textContent = msg;
                    resultEl.style.display = '';
                });
            } catch (err) {
                alert('Erro ao ler o arquivo. Verifique se o formato está correto.');
            }
        };
        reader.readAsArrayBuffer(file);
        // Reset input so same file can be re-imported
        e.target.value = '';
    });

    // ── CSV Download ──
    window.downloadCsv = function() {
        var items = Object.values(addedItems);
        if (items.length === 0) return;

        var unidadeSelect = document.getElementById('unidade_id');
        var unidadeNome = unidadeSelect.options[unidadeSelect.selectedIndex]?.text || 'Unidade';
        var num = 'NOVO';

        var header = 'Numero_Pedido;Unidade;Data;Tipo;Codigo;Referencia;Descricao;Quantidade';
        var rows = [];
        var dataHoje = new Date().toLocaleDateString('pt-BR');

        // Get quantities from hidden inputs
        items.forEach(function(item) {
            var qtyInput = document.querySelector('#itemRow-' + item.id + ' input[name$="[quantidade]"]');
            var qty = qtyInput ? qtyInput.value : '1';
            rows.push([
                num,
                unidadeNome,
                dataHoje,
                item.tipo || '',
                item.codigo || '',
                item.referencia || '',
                item.nome || '',
                qty
            ].map(function(v) {
                // Escape semicolons and quotes in CSV
                var s = String(v);
                if (s.indexOf(';') !== -1 || s.indexOf('"') !== -1) {
                    s = '"' + s.replace(/"/g, '""') + '"';
                }
                return s;
            }).join(';'));
        });

        var csv = '\ufeff' + header + '\n' + rows.join('\n');
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'Pedido_' + num + '_' + unidadeNome.replace(/\s+/g, '_') + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };
});
</script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<?php $__scripts = ob_get_clean();
include __DIR__ . '/../layouts/header.php';
?>
<?php 
    $usuario = session('usuario');
 ?>


<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1.25rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.375rem; font-size:0.75rem; color:var(--slate-400); margin-bottom:0.5rem;">
            <a href="/" style="color:var(--slate-400); text-decoration:none;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:0.6rem;"></i>
            <span style="color:var(--slate-700); font-weight:500;">Novo Pedido</span>
        </div>
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="width:2.5rem; height:2.5rem; border-radius:0.75rem; background:var(--navy); display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,.1); flex-shrink:0;">
                <i class="fas fa-plus" style="color:white; font-size:1rem;"></i>
            </div>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color:var(--slate-900); margin:0;">Novo Pedido</h1>
                <p style="font-size:0.75rem; color:var(--slate-400); margin:0.125rem 0 0;">Crie uma nova solicitação de materiais</p>
            </div>
        </div>
    </div>
</div>

<?php if ($errors->any()): ?>
<div class="rhc-flash rhc-flash-error" style="margin-bottom:1rem;">
    <i class="fas fa-circle-exclamation"></i>
    <div>
        <?php foreach ($errors->all() as $error): ?>
            <?= e($error) ?><br>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<form method="POST" action="/pedidos" id="pedidoForm">
    <input type="hidden" name="_token" value="<?= csrf_token() ?>">

    
    <div class="rhc-card" style="overflow:hidden; margin-bottom:1rem;">
        <div style="padding:1.25rem; border-bottom:1px solid var(--slate-100);">
            <h5 style="font-size:0.875rem; font-weight:700; color:var(--slate-800); margin:0;">
                <i class="fas fa-building" style="color:var(--navy); margin-right:0.5rem;"></i>Unidade
            </h5>
        </div>
        <div style="padding:1.25rem;">
            <div style="max-width:24rem;">
                <label class="rhc-label">Unidade <span style="color:var(--red-500);">*</span></label>
                <select name="unidade_id" id="unidade_id" class="rhc-select" style="width:100%;" required>
                    <option value="">Selecione a unidade...</option>
                    <?php foreach ($unidades as $unidade): ?>
                        <option value="<?= e($unidade->id) ?>"
                            <?= e((old('unidade_id') ?? $usuario->unidade_id) == $unidade->id ? 'selected' : '') ?>>
                            <?= e($unidade->nome) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    
    <div class="rhc-card" style="overflow:hidden; margin-bottom:1rem;">
        <div style="padding:1.25rem; border-bottom:1px solid var(--slate-100); display:flex; justify-content:space-between; align-items:center;">
            <h5 style="font-size:0.875rem; font-weight:700; color:var(--slate-800); margin:0;">
                <i class="fas fa-boxes-stacked" style="color:var(--navy); margin-right:0.5rem;"></i>Itens do Pedido
            </h5>
            <span id="itemCount" style="font-size:0.75rem; color:var(--slate-400); display:none;">
                0 itens adicionados
            </span>
        </div>
        <div style="padding:1.25rem; border-bottom:1px solid var(--slate-100); display:flex; gap:0.75rem; flex-wrap:wrap; align-items:center;">
            <div style="position:relative; flex:1; min-width:16rem;">
                <i class="fas fa-search" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--slate-400); font-size:0.8rem;"></i>
                <input type="text" id="itemSearch" class="rhc-input" placeholder="Buscar item por nome, código ou referência..."
                       autocomplete="off" style="padding-left:2.5rem; width:100%;">
                <div id="searchResults" class="search-results"></div>
            </div>
            <label class="rhc-btn rhc-btn-ghost rhc-btn-sm" style="cursor:pointer; margin:0;">
                <i class="fas fa-file-excel" style="margin-right:0.25rem; color:#059669;"></i> Importar Excel
                <input type="file" id="excelImport" accept=".xlsx,.xls,.csv" style="display:none;">
            </label>
            <button type="button" class="rhc-btn rhc-btn-outline rhc-btn-sm" id="btnDownloadCsv" disabled onclick="downloadCsv()">
                <i class="fas fa-download" style="margin-right:0.25rem;"></i> Baixar CSV
            </button>
        </div>
        
        <div id="importResult" style="display:none; padding:0.75rem 1.25rem; background:#ecfdf5; border-bottom:1px solid #a7f3d0; font-size:0.8rem; color:#065f46;">
            <i class="fas fa-circle-check" style="margin-right:0.375rem;"></i>
            <span id="importResultText"></span>
        </div>

        
        <div class="rhc-table-wrap selected-items-table">
            <table class="rhc-table" id="itensTable">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descrição</th>
                        <th>Tipo</th>
                        <th style="text-align:center; width:120px;">Quantidade</th>
                        <th style="text-align:center; width:80px;">Ações</th>
                    </tr>
                </thead>
                <tbody id="itensBody">
                    <tr id="emptyRow">
                        <td colspan="5">
                            <div class="empty-state" style="padding:2rem 1rem;">
                                <i class="fas fa-boxes-stacked" style="font-size:2rem;"></i>
                                Nenhum item adicionado. Use a busca acima para adicionar itens.
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    
    <div style="display:flex; gap:0.5rem;">
        <button type="submit" class="rhc-btn rhc-btn-primary" id="btnSubmit" disabled style="padding:0.625rem 1.5rem;">
            <i class="fas fa-check" style="margin-right:0.375rem;"></i> Criar Pedido
        </button>
        <a href="/" class="rhc-btn rhc-btn-ghost" style="padding:0.625rem 1.5rem;">Cancelar</a>
    </div>
</form>


<div id="itemAddModal" class="item-modal-overlay" style="display:none;">
    <div class="item-modal-backdrop" onclick="closeItemModal()"></div>
    <div class="item-modal-card">
        <div class="item-modal-header">
            <div class="im-info">
                <div class="im-icon">
                    <i class="fas fa-box" style="font-size:1rem;"></i>
                </div>
                <div style="min-width:0;">
                    <div class="im-name" id="modalItemName"></div>
                    <div id="modalItemTipo"></div>
                </div>
            </div>
            <button class="im-close" onclick="closeItemModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="im-details">
            <div class="im-detail-box">
                <div class="im-detail-label">Código</div>
                <div class="im-detail-value" id="modalItemCode"></div>
            </div>
            <div class="im-detail-box" id="modalRefBox">
                <div class="im-detail-label">Referência</div>
                <div class="im-detail-value" id="modalItemRef"></div>
            </div>
        </div>

        <div class="im-qty-section">
            <div class="im-qty-label">Quantidade</div>
            <div class="im-qty-control">
                <button type="button" class="im-qty-btn" onclick="changeQty(-1)">−</button>
                <input type="number" id="modalQtyInput" class="im-qty-input" min="1" value="1">
                <button type="button" class="im-qty-btn" onclick="changeQty(1)">+</button>
            </div>
        </div>

        <div class="im-actions">
            <button type="button" class="rhc-btn rhc-btn-ghost" onclick="closeItemModal()">Cancelar</button>
            <button type="button" class="rhc-btn rhc-btn-primary" id="modalConfirmBtn" onclick="confirmAddItem()">
                <i class="fas fa-plus" style="margin-right:0.25rem;"></i> Adicionar ao Pedido
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
