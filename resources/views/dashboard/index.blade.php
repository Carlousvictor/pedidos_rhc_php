@extends('layouts.app')

@section('title', 'Pedidos - RHC Pedidos')

@section('styles')
<style>
    /* Stat cards */
    .stat-cards {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 1024px) {
        .stat-cards { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 640px) {
        .stat-cards { grid-template-columns: repeat(2, 1fr); }
    }
    .stat-card {
        background: var(--white);
        border-radius: 0.75rem;
        border: 1px solid var(--slate-100);
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        padding: 1.25rem;
        cursor: pointer;
        transition: border-color 0.15s, box-shadow 0.15s;
        text-decoration: none;
        display: block;
    }
    .stat-card:hover {
        border-color: rgba(0,26,114,0.3);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.1);
    }
    .stat-card.active-stat {
        border-color: var(--navy);
        box-shadow: 0 0 0 2px rgba(0,26,114,0.15);
    }
    .stat-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        margin-bottom: 0.75rem;
    }
    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--slate-900);
        line-height: 1;
    }
    .stat-label {
        font-size: 0.75rem;
        color: var(--slate-500);
        margin-top: 0.25rem;
    }
    .stat-bar {
        height: 0.375rem;
        border-radius: 9999px;
        margin-top: 0.75rem;
        background: var(--slate-100);
        overflow: hidden;
    }
    .stat-bar-fill {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.3s;
    }

    /* Module cards */
    .module-cards {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 1024px) {
        .module-cards { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 640px) {
        .module-cards { grid-template-columns: repeat(2, 1fr); }
    }
    .module-card {
        background: var(--white);
        border: 1px solid var(--slate-100);
        border-radius: 0.75rem;
        padding: 1.25rem;
        text-decoration: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.5rem;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .module-card:hover {
        border-color: rgba(0,26,114,0.4);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,.1);
    }
    .module-card:hover .module-icon {
        background-color: var(--navy);
        color: var(--white);
    }
    .module-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        transition: background-color 0.15s, color 0.15s;
    }
    .module-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--slate-700);
    }

    /* Search bar */
    .search-bar {
        display: flex;
        gap: 0.75rem;
        align-items: end;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .search-input-wrap {
        position: relative;
        flex: 1;
        min-width: 200px;
    }
    .search-input-wrap .fa-search {
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--slate-400);
        font-size: 0.8rem;
    }
    .search-input-wrap .rhc-input {
        padding-left: 2.5rem;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--slate-400);
    }
    .empty-state i {
        font-size: 2.5rem;
        color: var(--slate-200);
        margin-bottom: 0.75rem;
        display: block;
    }

    /* Order link */
    .order-link {
        color: var(--navy);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .order-link:hover {
        text-decoration: underline;
    }

    /* View button */
    .view-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--slate-400);
        text-decoration: none;
        transition: color 0.15s, background-color 0.15s;
    }
    .view-btn:hover {
        color: var(--navy);
        background: #eff6ff;
    }
</style>
@endsection

@section('content')
@php
    $usuario = session('usuario');
    $modulos = $usuario->permissoes['modulos'] ?? [];
    $isAdminOrComprador = in_array($usuario->role, ['admin', 'comprador']);

    $statusConfig = [
        'Aguardando Aprovação' => ['color' => '#eab308', 'bg' => '#fef9c3', 'text' => '#854d0e', 'icon' => 'fa-clock'],
        'Pendente'             => ['color' => '#f97316', 'bg' => '#ffedd5', 'text' => '#9a3412', 'icon' => 'fa-hourglass-half'],
        'Em Cotação'           => ['color' => '#f59e0b', 'bg' => '#fef3c7', 'text' => '#92400e', 'icon' => 'fa-tags'],
        'Realizado'            => ['color' => '#001A72', 'bg' => '#dbeafe', 'text' => '#001A72', 'icon' => 'fa-check'],
        'Recebido'             => ['color' => '#16a34a', 'bg' => '#dcfce7', 'text' => '#166534', 'icon' => 'fa-check-double'],
    ];

    $allStatuses = array_keys($statusConfig);
    $totalCount = collect($statusCounts)->sum();
@endphp

{{-- Page Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.25rem; font-weight:700; color:var(--slate-900); margin:0;">
            <i class="fas fa-clipboard-list" style="margin-right:0.5rem; color:var(--navy);"></i>Pedidos
        </h1>
        <p style="font-size:0.75rem; color:var(--slate-400); margin:0.25rem 0 0;">
            @if($usuario->unidade_nome)
                {{ $usuario->unidade_nome }}
            @else
                Todas as unidades
            @endif
        </p>
    </div>
    @if($modulos['criar_pedido'] ?? false)
        <a href="/pedidos/novo" class="rhc-btn rhc-btn-primary">
            <i class="fas fa-plus-circle"></i> Novo Pedido
        </a>
    @endif
</div>

{{-- Status Cards --}}
<div class="stat-cards">
    {{-- Total --}}
    <a href="?{{ http_build_query(array_merge($filters, ['status' => ''])) }}"
       class="stat-card {{ empty($filters['status']) ? 'active-stat' : '' }}">
        <div class="stat-icon" style="background:#f1f5f9; color:var(--slate-600);">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-number">{{ $totalCount }}</div>
        <div class="stat-label">Todos</div>
        <div class="stat-bar">
            <div class="stat-bar-fill" style="width:100%; background:var(--navy);"></div>
        </div>
    </a>
    @foreach($allStatuses as $st)
        @php $cfg = $statusConfig[$st]; $count = $statusCounts[$st] ?? 0; $pct = $totalCount > 0 ? ($count / $totalCount * 100) : 0; @endphp
        <a href="?{{ http_build_query(array_merge($filters, ['status' => $st])) }}"
           class="stat-card {{ ($filters['status'] ?? '') === $st ? 'active-stat' : '' }}">
            <div class="stat-icon" style="background:{{ $cfg['bg'] }}; color:{{ $cfg['text'] }};">
                <i class="fas {{ $cfg['icon'] }}"></i>
            </div>
            <div class="stat-number">{{ $count }}</div>
            <div class="stat-label">{{ $st }}</div>
            <div class="stat-bar">
                <div class="stat-bar-fill" style="width:{{ $pct }}%; background:{{ $cfg['color'] }};"></div>
            </div>
        </a>
    @endforeach
</div>

{{-- Quick Access Modules --}}
<div class="module-cards">
    @if($modulos['criar_pedido'] ?? false)
    <a href="/pedidos/novo" class="module-card">
        <div class="module-icon" style="background:#dbeafe; color:var(--navy);">
            <i class="fas fa-plus-circle"></i>
        </div>
        <span class="module-label">Novo Pedido</span>
    </a>
    @endif
    @if($modulos['itens'] ?? false)
    <a href="/itens" class="module-card">
        <div class="module-icon" style="background:#ffedd5; color:#9a3412;">
            <i class="fas fa-boxes-stacked"></i>
        </div>
        <span class="module-label">Itens</span>
    </a>
    @endif
    @if($modulos['transferencias'] ?? false)
    <a href="/transferencias" class="module-card">
        <div class="module-icon" style="background:#f3e8ff; color:#7c3aed;">
            <i class="fas fa-right-left"></i>
        </div>
        <span class="module-label">Transferências</span>
    </a>
    @endif
    @if($modulos['relatorios'] ?? false)
    <a href="/relatorios" class="module-card">
        <div class="module-icon" style="background:#dcfce7; color:#166534;">
            <i class="fas fa-chart-bar"></i>
        </div>
        <span class="module-label">Relatórios</span>
    </a>
    @endif
    @if($modulos['usuarios'] ?? false)
    <a href="/usuarios" class="module-card">
        <div class="module-icon" style="background:#ede9fe; color:#6d28d9;">
            <i class="fas fa-users-gear"></i>
        </div>
        <span class="module-label">Usuários</span>
    </a>
    @endif
</div>

{{-- Search & Filters --}}
<form method="GET" action="/" class="search-bar">
    @if(!empty($filters['status']))
        <input type="hidden" name="status" value="{{ $filters['status'] }}">
    @endif
    <div class="search-input-wrap">
        <i class="fas fa-search"></i>
        <input type="text" name="search" class="rhc-input" placeholder="Buscar por nº do pedido..."
               value="{{ $filters['search'] ?? '' }}">
    </div>
    @if($isAdminOrComprador)
    <div style="min-width:220px;">
        <select name="unidade_id" class="rhc-select">
            <option value="">Todas as unidades</option>
            @foreach($unidades as $unidade)
                <option value="{{ $unidade->id }}" {{ ($filters['unidade_id'] ?? '') == $unidade->id ? 'selected' : '' }}>
                    {{ $unidade->nome }}
                </option>
            @endforeach
        </select>
    </div>
    @endif
    <button type="submit" class="rhc-btn rhc-btn-outline rhc-btn-sm">
        <i class="fas fa-search"></i> Filtrar
    </button>
    @if(!empty($filters['search']) || !empty($filters['unidade_id']))
    <a href="/?{{ !empty($filters['status']) ? 'status=' . urlencode($filters['status']) : '' }}" class="rhc-btn rhc-btn-ghost rhc-btn-sm">
        <i class="fas fa-times"></i> Limpar
    </a>
    @endif
</form>

{{-- Orders Table --}}
<div class="rhc-card" style="overflow:hidden;">
    <div class="rhc-table-wrap">
        <table class="rhc-table">
            <thead>
                <tr>
                    <th>Nº Pedido</th>
                    <th>Status</th>
                    <th>Unidade</th>
                    <th>Solicitante</th>
                    <th style="text-align:center;">Itens</th>
                    <th style="text-align:right;">Valor Total</th>
                    <th>Data</th>
                    <th style="text-align:center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                @php
                    $stCfg = $statusConfig[$pedido->status] ?? ['bg' => '#f1f5f9', 'text' => '#64748b'];
                @endphp
                <tr>
                    <td>
                        <a href="/pedidos/{{ $pedido->id }}" class="order-link">
                            {{ $pedido->numero_pedido ?? '(sem número)' }}
                        </a>
                    </td>
                    <td>
                        <span class="rhc-status" style="background:{{ $stCfg['bg'] }}; color:{{ $stCfg['text'] }};">
                            {{ $pedido->status }}
                        </span>
                    </td>
                    <td style="font-size:0.8rem;">{{ $pedido->unidade->nome ?? '-' }}</td>
                    <td style="font-size:0.8rem;">{{ $pedido->usuario->nome ?? '-' }}</td>
                    <td style="text-align:center; font-weight:600;">{{ $pedido->itens_count }}</td>
                    <td style="text-align:right; font-weight:600; font-family:ui-monospace,monospace; font-size:0.8rem;">
                        R$ {{ number_format($pedido->valor_total ?? 0, 2, ',', '.') }}
                    </td>
                    <td style="font-size:0.75rem; color:var(--slate-400);">
                        {{ $pedido->created_at ? $pedido->created_at->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td style="text-align:center;">
                        <a href="/pedidos/{{ $pedido->id }}" class="view-btn">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            Nenhum pedido encontrado.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
@if($pedidos->hasPages())
<div class="rhc-pagination">
    @if($pedidos->onFirstPage())
        <span class="disabled-page"><i class="fas fa-chevron-left fa-xs"></i></span>
    @else
        <a href="{{ $pedidos->previousPageUrl() }}"><i class="fas fa-chevron-left fa-xs"></i></a>
    @endif

    @foreach($pedidos->getUrlRange(1, $pedidos->lastPage()) as $page => $url)
        @if($page == $pedidos->currentPage())
            <span class="active-page">{{ $page }}</span>
        @else
            <a href="{{ $url }}">{{ $page }}</a>
        @endif
    @endforeach

    @if($pedidos->hasMorePages())
        <a href="{{ $pedidos->nextPageUrl() }}"><i class="fas fa-chevron-right fa-xs"></i></a>
    @else
        <span class="disabled-page"><i class="fas fa-chevron-right fa-xs"></i></span>
    @endif
</div>
@endif
@endsection
