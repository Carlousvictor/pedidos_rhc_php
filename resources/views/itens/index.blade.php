@extends('layouts.app')

@section('title', 'Catálogo de Itens')

@section('styles')
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
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--slate-100);
        font-size: 0.875rem;
        color: var(--slate-500);
    }
    .pagination-modern .page-btns {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .pagination-modern .page-btn {
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        border: 1px solid var(--slate-200);
        background: white;
        color: var(--slate-700);
        text-decoration: none;
        font-size: 0.875rem;
        transition: background 0.15s;
    }
    .pagination-modern .page-btn:hover { background: var(--slate-50); }
    .pagination-modern .page-btn.disabled { opacity: 0.4; pointer-events: none; }
    .filter-count {
        font-size: 0.75rem;
        background: var(--slate-100);
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-weight: 500;
        color: var(--slate-500);
    }
</style>
@endsection

@section('content')
@php
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
@endphp

{{-- Header --}}
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
                    {{ number_format($itens->total(), 0, ',', '.') }} itens cadastrados
                </p>
            </div>
        </div>
    </div>
    <div style="display:flex; align-items:center; gap:0.75rem; flex-shrink:0;">
        @if(request('search') || request('tipo'))
            <span class="filter-count">
                {{ $itens->total() }} encontrado{{ $itens->total() !== 1 ? 's' : '' }}
            </span>
        @endif
        @if($isAdminOrComprador)
            <button class="rhc-btn rhc-btn-primary" data-bs-toggle="modal" data-bs-target="#novoItemModal">
                <i class="fas fa-plus" style="margin-right:0.375rem;"></i> Novo Item
            </button>
        @endif
    </div>
</div>

{{-- Main Card --}}
<div class="rhc-card" style="overflow:hidden;">
    {{-- Filters inside card --}}
    <div style="padding:1.5rem; border-bottom:1px solid var(--slate-100); display:flex; gap:1rem; flex-wrap:wrap;">
        <form action="{{ route('itens.index') }}" method="GET" style="display:flex; gap:1rem; flex-wrap:wrap; width:100%; align-items:center;">
            <div style="position:relative; width:100%; max-width:24rem;">
                <i class="fas fa-search" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--slate-400); font-size:0.8rem;"></i>
                <input type="text" name="search" class="rhc-input" placeholder="Buscar por descrição, código ou referência..."
                       value="{{ request('search') }}" style="padding-left:2.5rem; width:100%;">
            </div>
            <select name="tipo" class="rhc-select" style="min-width:180px;" onchange="this.form.submit()">
                <option value="">Todos os tipos</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                @endforeach
            </select>
            @if(request('search') || request('tipo'))
                <a href="{{ route('itens.index') }}" class="rhc-btn rhc-btn-ghost rhc-btn-sm">
                    <i class="fas fa-times"></i> Limpar
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
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
                @forelse($itens as $item)
                    <tr>
                        <td style="font-family:ui-monospace,monospace; font-size:0.875rem; color:var(--slate-700);">{{ $item->codigo }}</td>
                        <td>
                            <div class="item-icon-cell">
                                <div class="item-icon-box">
                                    <i class="fas fa-box"></i>
                                </div>
                                <span style="font-size:0.875rem; color:var(--slate-900);">{{ $item->nome }}</span>
                            </div>
                        </td>
                        <td style="font-family:ui-monospace,monospace; font-size:0.875rem; color:var(--slate-500);">{{ $item->referencia ?? '' }}</td>
                        <td>
                            @if($item->tipo)
                                <span class="tipo-badge {{ $tipoClasses[$item->tipo] ?? 'tipo-default' }}">{{ $item->tipo }}</span>
                            @else
                                <span style="color:var(--slate-400); font-size:0.875rem;">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                Nenhum item encontrado.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($itens->hasPages())
        <div class="pagination-modern">
            <span>
                Exibindo {{ $itens->firstItem() }}–{{ $itens->lastItem() }} de {{ number_format($itens->total(), 0, ',', '.') }}
            </span>
            <div class="page-btns">
                @if($itens->onFirstPage())
                    <span class="page-btn disabled">Anterior</span>
                @else
                    <a href="{{ $itens->previousPageUrl() }}" class="page-btn">Anterior</a>
                @endif
                <span style="padding:0 0.5rem;">{{ $itens->currentPage() }} / {{ $itens->lastPage() }}</span>
                @if($itens->hasMorePages())
                    <a href="{{ $itens->nextPageUrl() }}" class="page-btn">Próxima</a>
                @else
                    <span class="page-btn disabled">Próxima</span>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- Modal Novo Item --}}
@if($isAdminOrComprador)
<div class="modal fade" id="novoItemModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('itens.store') }}" method="POST">
            @csrf
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
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endforeach
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
@endif
@endsection
