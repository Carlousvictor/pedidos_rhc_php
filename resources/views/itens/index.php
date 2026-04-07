<?php
$__title = 'Catálogo de Itens';
ob_start();
?>
<style>
    .tipo-badge {
        display: inline-flex;
        padding: 0.125rem 0.625rem;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.25rem;
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

    .item-icon-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .item-icon-box {
        background: var(--slate-100);
        padding: 0.375rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .item-icon-box i { color: var(--slate-500); font-size: 0.875rem; }

    .pagination-modern {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1.5rem;
        border-top: 1px solid var(--slate-100);
        font-size: 0.8rem;
        color: var(--slate-500);
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .pagination-modern .page-btns {
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }
    .pagination-modern .page-btn {
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        border: 1px solid var(--slate-200);
        background: white;
        color: var(--slate-700);
        text-decoration: none;
        font-size: 0.8rem;
        transition: background 0.15s;
    }
    .pagination-modern .page-btn:hover { background: var(--slate-50); }
    .pagination-modern .page-btn.disabled { opacity: 0.4; pointer-events: none; }
    .pagination-modern .page-btn.active { background: var(--navy); color: white; border-color: var(--navy); }
    .per-page-select {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.8rem;
        color: var(--slate-500);
    }
    .per-page-select select {
        padding: 0.25rem 0.5rem;
        border: 1px solid var(--slate-200);
        border-radius: 0.375rem;
        font-size: 0.8rem;
        color: var(--slate-700);
        background: white;
        cursor: pointer;
    }
    .filter-count {
        font-size: 0.75rem;
        background: var(--slate-100);
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-weight: 500;
        color: var(--slate-500);
    }
</style>
<?php $__styles = ob_get_clean();
include __DIR__ . '/../layouts/header.php';
?>
<?php 
    $usuario = session('usuario');
    $isAdminOrComprador = in_array($usuario->role, ['admin', 'comprador']);

    $tipoClasses = [
        'B.BRAUN' => 'tipo-bbraun',
        'FRALDAS' => 'tipo-fraldas',
        'LIFETEX-SURGITEXTIL' => 'tipo-lifetex',
        'MAT. MED. HOSPITALAR' => 'tipo-mathospitalar',
        'MED. ONCO' => 'tipo-medonco',
        'MED. ONCO CONTR. LIBBS.' => 'tipo-medoncolibbs',
        'MEDICAMENTOS' => 'tipo-medicamentos',
    ];
 ?>


<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1.25rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.375rem; font-size:0.75rem; color:var(--slate-400); margin-bottom:0.5rem;">
            <a href="/" style="color:var(--slate-400); text-decoration:none; transition:color 0.15s;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:0.6rem;"></i>
            <span style="color:var(--slate-700); font-weight:500;">Itens</span>
        </div>
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="width:2.5rem; height:2.5rem; border-radius:0.75rem; background:#f97316; display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,.1); flex-shrink:0;">
                <i class="fas fa-box" style="color:white; font-size:1rem;"></i>
            </div>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color:var(--slate-900); margin:0;">Catálogo de Itens</h1>
                <p style="font-size:0.75rem; color:var(--slate-400); margin:0.125rem 0 0;">
                    <?= e(number_format($itens->total(), 0, ',', '.')) ?> itens cadastrados
                </p>
            </div>
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:0.75rem; flex-shrink:0;">
        <?php if (request('search') || request('tipo')): ?>
            <span class="filter-count">
                <?= e($itens->total()) ?> encontrado<?= e($itens->total() !== 1 ? 's' : '') ?>
            </span>
        <?php endif; ?>
        <?php if ($isAdminOrComprador): ?>
            <button class="rhc-btn rhc-btn-primary" data-bs-toggle="modal" data-bs-target="#novoItemModal">
                <i class="fas fa-plus" style="margin-right:0.375rem;"></i> Novo Item
            </button>
        <?php endif; ?>
    </div>
</div>


<div class="rhc-card" style="overflow:hidden;">
    
    <div style="padding:1.5rem; border-bottom:1px solid var(--slate-100); display:flex; gap:1rem; flex-wrap:wrap;">
        <form action="<?= e(route('itens.index')) ?>" method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; width:100%; align-items:center;">
            <div style="position:relative; width:100%; max-width:24rem;">
                <i class="fas fa-search" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--slate-400); font-size:0.8rem;"></i>
                <input type="text" name="search" class="rhc-input" placeholder="Buscar por descrição, código ou referência..."
                       value="<?= e(request('search')) ?>" style="padding-left:2.5rem; width:100%;">
            </div>
            <select name="tipo" class="rhc-select" style="min-width:180px;" onchange="this.form.submit()">
                <option value="">Todos os tipos</option>
                <?php foreach ($tipos as $tipo): ?>
                    <option value="<?= e($tipo) ?>" <?= e(request('tipo') == $tipo ? 'selected' : '') ?>><?= e($tipo) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (request('search') || request('tipo')): ?>
                <a href="<?= e(route('itens.index')) ?>" class="rhc-btn rhc-btn-ghost rhc-btn-sm">
                    <i class="fas fa-times"></i> Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    
    <div class="rhc-table-wrap">
        <table class="rhc-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Referência</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($itens) > 0): ?><?php foreach ($itens as $item): ?>
                    <tr>
                        <td style="font-family:ui-monospace,monospace; font-size:0.875rem; color:var(--slate-700);"><?= e($item->codigo) ?></td>
                        <td>
                            <div class="item-icon-cell">
                                <div class="item-icon-box">
                                    <i class="fas fa-box"></i>
                                </div>
                                <span style="font-size:0.875rem; color:var(--slate-900);"><?= e($item->nome) ?></span>
                            </div>
                        </td>
                        <td style="font-family:ui-monospace,monospace; font-size:0.875rem; color:var(--slate-500);"><?= e($item->referencia ?? '') ?></td>
                        <td>
                            <?php if ($item->tipo): ?>
                                <span class="tipo-badge <?= e($tipoClasses[$item->tipo] ?? 'tipo-default') ?>"><?= e($item->tipo) ?></span>
                            <?php else: ?>
                                <span style="color:var(--slate-400); font-size:0.875rem;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?><?php else: ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                Nenhum item encontrado.
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="pagination-modern">
        <span>
            Exibindo <?= e($itens->firstItem() ?? 0) ?>–<?= e($itens->lastItem() ?? 0) ?> de <?= e(number_format($itens->total(), 0, ',', '.')) ?>
        </span>

        <div class="per-page-select">
            <span>Exibir</span>
            <select onchange="window.location.href=this.value">
                <?php foreach ([25, 50, 100, 200] as $pp): ?>
                    <?php $ppUrl = request()->fullUrlWithQuery(['per_page' => $pp, 'page' => 1]); ?>
                    <option value="<?= e($ppUrl) ?>" <?= e($itens->perPage() == $pp ? 'selected' : '') ?>><?= e($pp) ?></option>
                <?php endforeach; ?>
            </select>
            <span>por página</span>
        </div>

        <?php if ($itens->hasPages()): ?>
        <div class="page-btns">
            <?php if ($itens->onFirstPage()): ?>
                <span class="page-btn disabled"><i class="fas fa-chevron-left fa-xs"></i></span>
            <?php else: ?>
                <a href="<?= e($itens->previousPageUrl()) ?>" class="page-btn"><i class="fas fa-chevron-left fa-xs"></i></a>
            <?php endif; ?>

            <?php
                $current = $itens->currentPage();
                $last = $itens->lastPage();
                $range = [];
                // Always show first, last, and pages around current
                for ($i = 1; $i <= $last; $i++) {
                    if ($i === 1 || $i === $last || abs($i - $current) <= 2) {
                        $range[] = $i;
                    } elseif (end($range) !== '...') {
                        $range[] = '...';
                    }
                }
            ?>
            <?php foreach ($range as $page): ?>
                <?php if ($page === '...'): ?>
                    <span class="page-btn disabled" style="border:none; opacity:0.6;">…</span>
                <?php elseif ($page == $current): ?>
                    <span class="page-btn active"><?= e($page) ?></span>
                <?php else: ?>
                    <a href="<?= e($itens->url($page)) ?>" class="page-btn"><?= e($page) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($itens->hasMorePages()): ?>
                <a href="<?= e($itens->nextPageUrl()) ?>" class="page-btn"><i class="fas fa-chevron-right fa-xs"></i></a>
            <?php else: ?>
                <span class="page-btn disabled"><i class="fas fa-chevron-right fa-xs"></i></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>


<?php if ($isAdminOrComprador): ?>
<div class="modal fade" id="novoItemModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= e(route('itens.store')) ?>" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
            <div class="modal-content" style="border:0; border-radius:1rem; overflow:hidden;">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1.25rem 1.5rem; border-bottom:1px solid var(--slate-100);">
                    <h2 style="font-size:1.125rem; font-weight:700; color:var(--slate-800); margin:0;">Novo Item</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div style="padding:1.5rem; display:flex; flex-direction:column; gap:1rem;">
                    <div>
                        <label class="rhc-label">Descrição <span style="color:var(--red-500);">*</span></label>
                        <input type="text" class="rhc-input" name="nome" required placeholder="Ex: SORO FISIOLÓGICO 500ML">
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div>
                            <label class="rhc-label">Código <span style="color:var(--red-500);">*</span></label>
                            <input type="text" class="rhc-input" name="codigo" required placeholder="Ex: 12345" style="font-family:ui-monospace,monospace;">
                        </div>
                        <div>
                            <label class="rhc-label">Referência</label>
                            <input type="text" class="rhc-input" name="referencia" placeholder="Ex: 409084" style="font-family:ui-monospace,monospace;">
                        </div>
                    </div>
                    <div>
                        <label class="rhc-label">Tipo</label>
                        <select name="tipo" class="rhc-select" style="width:100%;">
                            <option value="">Selecione um tipo...</option>
                            <?php foreach ($tipos as $tipo): ?>
                                <option value="<?= e($tipo) ?>"><?= e($tipo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:0.75rem; padding:1.25rem 1.5rem; border-top:1px solid var(--slate-100);">
                    <button type="button" class="rhc-btn rhc-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="rhc-btn rhc-btn-primary">
                        <i class="fas fa-save" style="margin-right:0.375rem;"></i> Salvar Item
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
