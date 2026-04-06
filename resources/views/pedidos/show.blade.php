@extends('layouts.app')

@section('title', 'Pedido #' . ($pedido->numero_pedido ?? 'Novo'))

@section('styles')
<style>
    .stepper-circle {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border: 2px solid #e2e8f0;
        background: #fff;
        color: #94a3b8;
    }
    .stepper-circle.active, .stepper-circle.done {
        border-color: #001A72;
        background: #001A72;
        color: #fff;
    }
    .stepper-circle.done {
        background: #16a34a;
        border-color: #16a34a;
    }
    .stepper-line {
        flex-grow: 1;
        height: 2px;
        background: #e2e8f0;
        margin: 0 10px;
    }
    .stepper-line.active {
        background: #16a34a;
    }
    .timeline-item {
        position: relative;
        padding-left: 20px;
        border-left: 2px solid #e2e8f0;
        padding-bottom: 15px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #94a3b8;
    }
</style>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Pedidos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pedido {{ $pedido->numero_pedido ? '#' . $pedido->numero_pedido : '(sem número)' }}</li>
            </ol>
        </nav>
    </div>
    <div class="col-auto">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
        @if(in_array(session('usuario')->role, ['admin', 'aprovador']))
            <a href="{{ route('pedidos.edit', $pedido->id) }}" class="btn btn-outline-primary btn-sm ms-2">
                <i class="fas fa-edit me-1"></i> Editar Pedido
            </a>
        @endif
    </div>
</div>

@include('components.flash-messages')

{{-- Resumo do Pedido --}}
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <p class="text-muted mb-1 small">Nº do Pedido</p>
                <h5 class="mb-0 fw-bold">{{ $pedido->numero_pedido ?? 'Não gerado' }}</h5>
            </div>
            <div class="col-md-3">
                <p class="text-muted mb-1 small">Status Atual</p>
                <h5><span class="badge bg-primary">{{ $pedido->status }}</span></h5>
            </div>
            <div class="col-md-3">
                <p class="text-muted mb-1 small">Unidade Solicitante</p>
                <h5 class="mb-0">{{ $pedido->unidade->nome ?? 'N/D' }}</h5>
            </div>
            <div class="col-md-3">
                <p class="text-muted mb-1 small">Valor Total Estimado</p>
                <h5 class="mb-0 text-success">R$ {{ number_format($valorTotal, 2, ',', '.') }}</h5>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-3">
                <p class="text-muted mb-1 small">Solicitante</p>
                <p class="mb-0"><i class="fas fa-user me-1"></i> {{ $pedido->usuario->nome ?? 'N/D' }}</p>
            </div>
            <div class="col-md-3">
                <p class="text-muted mb-1 small">Criado em</p>
                <p class="mb-0"><i class="fas fa-calendar me-1"></i> {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="col-md-3">
                <p class="text-muted mb-1 small">Atendido por</p>
                <p class="mb-0"><i class="fas fa-user-cog me-1"></i> {{ $pedido->atendidoPor->nome ?? 'Ainda não atendido' }}</p>
            </div>
            <div class="col-md-3">
                <p class="text-muted mb-1 small">Aprovações ({{ $approvalCount }}/1)</p>
                <div class="d-flex flex-wrap gap-1">
                    @forelse($pedido->aprovacoes as $ap)
                        <span class="badge bg-success" title="Aprovado por {{ $ap->usuario->nome }} em {{ $ap->created_at->format('d/m/Y H:i') }}">
                            <i class="fas fa-check"></i> {{ explode(' ', $ap->usuario->nome)[0] }}
                        </span>
                    @empty
                        <span class="text-muted small">Nenhuma</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stepper de Status --}}
<div class="card mb-4 shadow-sm border-0 py-3">
    <div class="card-body d-flex align-items-center justify-content-between px-md-5">
        @php
            $steps = ['Aguardando Aprovação', 'Pendente', 'Em Cotação', 'Realizado', 'Recebido'];
            $currentIdx = array_search($pedido->status, $steps);
            if ($currentIdx === false) $currentIdx = 0;
        @endphp

        @foreach($steps as $idx => $step)
            <div class="d-flex flex-column align-items-center text-center" style="width: 100px;">
                <div class="stepper-circle {{ $idx < $currentIdx ? 'done' : ($idx === $currentIdx ? 'active' : '') }} mb-2 shadow-sm">
                    @if($idx < $currentIdx)
                        <i class="fas fa-check"></i>
                    @else
                        {{ $idx + 1 }}
                    @endif
                </div>
                <small class="{{ $idx <= $currentIdx ? 'fw-bold text-dark' : 'text-muted' }}">{{ $step }}</small>
            </div>
            @if($idx < count($steps) - 1)
                <div class="stepper-line {{ $idx < $currentIdx ? 'active' : '' }}"></div>
            @endif
        @endforeach
    </div>
</div>

{{-- Ações Rápidas --}}
@if($canApprove)
    <div class="alert alert-warning d-flex align-items-center mb-4">
        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-1">Aprovação Necessária</h5>
            <p class="mb-0 small">Este pedido requer aprovação para prosseguir ao setor de compras.</p>
        </div>
        <form action="{{ route('pedidos.aprovar', $pedido->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-success fw-bold">
                <i class="fas fa-check-circle me-1"></i> Aprovar Pedido
            </button>
        </form>
    </div>
@endif

@if($canEditStatus)
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    <h6 class="mb-0 text-muted"><i class="fas fa-cogs me-1"></i> Alterar Status</h6>
                </div>
                <div class="col">
                    <form action="{{ route('pedidos.updateStatus', $pedido->id) }}" method="POST" class="d-flex gap-2">
                        @csrf
                        @method('PUT')
                        <select name="status" class="form-select form-select-sm" style="max-width:220px;">
                            <option value="Aguardando Aprovação" {{ $pedido->status == 'Aguardando Aprovação' ? 'selected' : '' }}>Aguardando Aprovação</option>
                            <option value="Pendente" {{ $pedido->status == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="Em Cotação" {{ $pedido->status == 'Em Cotação' ? 'selected' : '' }}>Em Cotação</option>
                            <option value="Realizado" {{ $pedido->status == 'Realizado' ? 'selected' : '' }}>Realizado</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm px-3">Atualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Fluxo de Processamento: CSV + PDF + Comparação --}}
    @if(in_array($pedido->status, ['Pendente', 'Em Cotação']))
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-tasks me-2 text-primary"></i>Processar Pedido</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                {{-- Passo 1: Baixar CSV --}}
                <div class="col-md-6">
                    <div style="border:2px solid var(--slate-200); border-radius:0.75rem; padding:1.25rem; background:var(--slate-50);">
                        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
                            <div style="width:2rem; height:2rem; border-radius:9999px; background:var(--navy); color:white; display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700;">1</div>
                            <div>
                                <p style="font-weight:600; font-size:0.875rem; color:var(--slate-800); margin:0;">Baixar CSV do Pedido</p>
                                <p style="font-size:0.75rem; color:var(--slate-500); margin:0.125rem 0 0;">Envie para a plataforma de cotação</p>
                            </div>
                        </div>
                        <button type="button" onclick="downloadPedidoCsv()" class="btn btn-primary w-100">
                            <i class="fas fa-download me-1"></i> Baixar CSV
                        </button>
                    </div>
                </div>

                {{-- Passo 2: Upload PDF do Espelho --}}
                <div class="col-md-6">
                    <div style="border:2px solid var(--slate-200); border-radius:0.75rem; padding:1.25rem; background:var(--slate-50);">
                        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
                            <div id="stepTwoCircle" style="width:2rem; height:2rem; border-radius:9999px; background:var(--slate-300); color:white; display:flex; align-items:center; justify-content:center; font-size:0.8rem; font-weight:700;">2</div>
                            <div>
                                <p style="font-weight:600; font-size:0.875rem; color:var(--slate-800); margin:0;">Anexar PDF do Espelho</p>
                                <p style="font-size:0.75rem; color:var(--slate-500); margin:0.125rem 0 0;">O PDF será processado automaticamente</p>
                            </div>
                        </div>
                        <div id="uploadedFile" style="display:none; margin-bottom:0.75rem;"></div>
                        <div id="dropZone" style="border:2px dashed var(--slate-200); border-radius:0.5rem; padding:1.25rem; text-align:center; cursor:pointer; transition:all 0.15s;"
                             onclick="if(!isProcessing) document.getElementById('espelhoInput').click();"
                             onmouseover="this.style.borderColor='var(--navy)'; this.style.background='var(--slate-100)';"
                             onmouseout="this.style.borderColor='var(--slate-200)'; this.style.background='transparent';">
                            <div id="dropContent">
                                <i class="fas fa-file-pdf" style="font-size:1.25rem; color:#dc2626; margin-bottom:0.5rem; display:block;"></i>
                                <p style="font-size:0.75rem; color:var(--slate-400); margin:0;">Clique para selecionar o PDF</p>
                            </div>
                            <div id="processingSpinner" style="display:none;">
                                <i class="fas fa-spinner fa-spin" style="font-size:1.25rem; color:var(--navy); margin-bottom:0.5rem; display:block;"></i>
                                <p style="font-size:0.75rem; color:var(--slate-500); margin:0;">Processando PDF...</p>
                            </div>
                        </div>
                        <input type="file" id="espelhoInput" accept=".pdf" style="display:none;" onchange="handlePdfFile(this.files[0]);">
                        <p id="uploadError" style="font-size:0.75rem; color:var(--red-500); margin:0.5rem 0 0; display:none;"></p>
                    </div>
                </div>
            </div>

            {{-- Prévia da Comparação --}}
            <div id="comparisonSection" style="display:none; margin-top:1.5rem;">
                <div style="border:1px solid var(--slate-200); border-radius:0.75rem; overflow:hidden;">
                    <div id="fornecedorBar" style="display:none; padding:0.75rem 1.25rem; background:#eff6ff; border-bottom:1px solid #bfdbfe;">
                        <span style="font-size:0.75rem; font-weight:600; color:var(--slate-500); text-transform:uppercase;">Fornecedor:</span>
                        <span id="fornecedorName" style="font-size:0.875rem; font-weight:700; color:var(--navy); margin-left:0.5rem;"></span>
                    </div>
                    <div style="padding:0.75rem 1.25rem; background:var(--slate-50); border-bottom:1px solid var(--slate-200); display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <p style="font-size:0.875rem; font-weight:700; color:var(--slate-800); margin:0;">Prévia da Comparação</p>
                            <p style="font-size:0.75rem; color:var(--slate-500); margin:0.125rem 0 0;">Revise antes de confirmar</p>
                        </div>
                        <div id="comparisonSummary" style="display:flex; gap:0.5rem;"></div>
                    </div>
                    <div style="overflow-x:auto; max-height:16rem;">
                        <table class="table table-sm mb-0 align-middle" style="font-size:0.8rem;">
                            <thead class="table-light" style="position:sticky; top:0;">
                                <tr>
                                    <th class="ps-3">Produto</th>
                                    <th>Código</th>
                                    <th>Fornecedor</th>
                                    <th class="text-end">Vlr Unit.</th>
                                    <th class="text-end">Pedido</th>
                                    <th class="text-end">Atendido</th>
                                    <th>Situação</th>
                                </tr>
                            </thead>
                            <tbody id="comparisonBody"></tbody>
                        </table>
                    </div>
                    <div style="padding:1rem 1.25rem; border-top:1px solid var(--slate-200); background:var(--slate-50); display:flex; justify-content:flex-end;">
                        <form action="{{ route('pedidos.confirmarEspelho', $pedido->id) }}" method="POST" id="confirmarForm">
                            @csrf
                            <input type="hidden" name="comparison_data" id="comparisonData">
                            <button type="submit" class="btn btn-primary" id="btnConfirmar">
                                <i class="fas fa-check-circle me-1"></i> Confirmar Pedido
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($pedido->status === 'Realizado')
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i> PDF processado. Aguardando confirmação de recebimento pelo solicitante.
        </div>
    @endif
@endif

{{-- Tabela de Itens --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="fas fa-boxes me-2"></i>Itens Solicitados ({{ $itensCount }})</h5>
        <div>
            {{-- Extra actions placeholder (e.g., Exportar, Upload Bionexo) --}}
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light text-secondary">
                <tr>
                    <th scope="col" class="ps-4">Código / Referência</th>
                    <th scope="col">Produto</th>
                    <th scope="col" class="text-center">Qtd Solicitada</th>
                    <th scope="col" class="text-center">Qtd Atendida</th>
                    <th scope="col" class="text-center">Qtd Recebida</th>
                    <th scope="col">Fornecedor</th>
                    <th scope="col" class="text-end pe-4">Valor Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedido->itens as $item)
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-secondary mb-1">{{ $item->item->codigo }}</span><br>
                            <small class="text-muted">{{ $item->item->referencia ?? 'S/Ref' }}</small>
                        </td>
                        <td>
                            <span class="d-block fw-semibold">{{ $item->item->nome }}</span>
                            @if($item->item_recebido_id && $item->item_recebido_id !== $item->item_id)
                                <small class="text-warning"><i class="fas fa-exchange-alt me-1"></i> Trocado por: {{ $item->itemRecebido->nome }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="fs-6">{{ $item->quantidade }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $item->quantidade_atendida > 0 ? ($item->quantidade_atendida == $item->quantidade ? 'success' : 'warning') : 'light text-dark' }}">
                                {{ $item->quantidade_atendida ?? 0 }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $item->quantidade_recebida > 0 ? ($item->quantidade_recebida >= $item->quantidade_atendida ? 'success' : 'warning') : 'light text-dark' }}">
                                {{ $item->quantidade_recebida ?? 0 }}
                            </span>
                        </td>
                        <td>
                            <small>{{ $item->fornecedor ?? '-' }}</small>
                        </td>
                        <td class="text-end pe-4">
                            R$ {{ number_format(($item->valor_unitario * $item->quantidade_atendida) ?? 0, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Nenhum item cadastrado neste pedido.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    {{-- Histórico de Alterações --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Linha do Tempo</h5>
            </div>
            <div class="card-body">
                <div class="timeline mt-3">
                    @forelse($pedido->alteracoes as $alt)
                        <div class="timeline-item">
                            <small class="text-muted d-block mb-1">{{ $alt->created_at->format('d/m/Y H:i') }} - {{ $alt->usuario_nome }}</small>
                            <span class="d-block fw-medium">
                                @if($alt->tipo == 'pedido_criado')
                                    Pedido criado.
                                @elseif($alt->tipo == 'status_alterado')
                                    Status alterado de <span class="badge bg-light text-dark border">{{ $alt->valor_anterior }}</span> para <span class="badge bg-primary">{{ $alt->valor_novo }}</span>
                                @elseif($alt->tipo == 'item_adicionado')
                                    Item <strong>{{ $alt->item_nome }}</strong> adicionado (Qtd: {{ $alt->valor_novo }}).
                                @elseif($alt->tipo == 'quantidade_alterada')
                                    Quantidade do item <strong>{{ $alt->item_nome }}</strong> alterada de {{ $alt->valor_anterior }} para {{ $alt->valor_novo }}.
                                @elseif($alt->tipo == 'item_removido')
                                    Item <strong>{{ $alt->item_nome }}</strong> removido.
                                @else
                                    {{ $alt->tipo }}
                                @endif
                            </span>
                        </div>
                    @empty
                        <p class="text-muted text-center pt-3">Nenhum evento registrado.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Espelho do Pedido (PDF) --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-file-pdf me-2"></i>Espelho do Pedido</h5>
                @if(in_array(session('usuario')->role ?? '', ['comprador', 'admin']))
                    <form action="{{ route('pedidos.uploadEspelho', $pedido->id) }}" method="POST" enctype="multipart/form-data" id="espelhoForm">
                        @csrf
                        <label class="btn btn-sm btn-outline-primary mb-0" style="cursor:pointer;">
                            <i class="fas fa-upload me-1"></i> Anexar PDF
                            <input type="file" name="espelho" accept=".pdf" style="display:none;"
                                   onchange="document.getElementById('espelhoForm').submit();">
                        </label>
                    </form>
                @endif
            </div>
            <div class="card-body">
                @if($pedido->espelho_path)
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                        <i class="fas fa-file-pdf fa-2x text-danger"></i>
                        <div class="flex-grow-1">
                            <p class="mb-0 fw-medium">Espelho anexado</p>
                            <small class="text-muted">Clique para visualizar</small>
                        </div>
                        <a href="{{ asset('storage/' . $pedido->espelho_path) }}" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i> Ver
                        </a>
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-file-pdf fa-2x mb-2 d-block" style="opacity:0.3;"></i>
                        <p class="mb-0 small">Nenhum espelho anexado.</p>
                        <p class="mb-0 small text-muted">Faça upload do PDF do espelho do pedido.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@php
    $itemsJson = $pedido->itens->map(function($pi) {
        return [
            'id' => $pi->id,
            'item_id' => $pi->item_id,
            'codigo' => $pi->item->codigo ?? '',
            'nome' => $pi->item->nome ?? '',
            'referencia' => $pi->item->referencia ?? '',
            'tipo' => $pi->item->tipo ?? '',
            'quantidade' => $pi->quantidade,
        ];
    })->values();
@endphp
<script>
var pedidoItems = @json($itemsJson);
var pedidoNumero = @json($pedido->numero_pedido ?? 'PEDIDO');
var unidadeNome = @json($pedido->unidade->nome ?? 'Unidade');
var isProcessing = false;

// ── Passo 1: Baixar CSV ──
function downloadPedidoCsv() {
    var header = 'Numero_Pedido;Unidade;Data;Tipo;Codigo;Referencia;Descricao;Quantidade';
    var dataHoje = new Date().toLocaleDateString('pt-BR');
    var rows = pedidoItems.map(function(item) {
        return [pedidoNumero, unidadeNome, dataHoje, item.tipo, item.codigo, item.referencia, item.nome, item.quantidade]
            .map(function(v) { var s = String(v||''); if (s.indexOf(';')!==-1||s.indexOf('"')!==-1) s='"'+s.replace(/"/g,'""')+'"'; return s; }).join(';');
    });
    var csv = '\ufeff' + header + '\n' + rows.join('\n');
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var a = document.createElement('a'); a.href = URL.createObjectURL(blob);
    a.download = 'Pedido_' + pedidoNumero + '_' + unidadeNome.replace(/\s+/g, '_') + '.csv';
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
}

// ── Passo 2: Upload PDF e Comparação ──
function handlePdfFile(file) {
    if (!file) return;
    var errorEl = document.getElementById('uploadError');
    var circle = document.getElementById('stepTwoCircle');
    var uploadedDiv = document.getElementById('uploadedFile');
    var dropContent = document.getElementById('dropContent');
    var spinner = document.getElementById('processingSpinner');
    errorEl.style.display = 'none';

    // Show file name
    circle.style.background = 'var(--navy)';
    uploadedDiv.style.display = '';
    uploadedDiv.innerHTML = '<div style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem 0.75rem; background:#eff6ff; border:1px solid rgba(0,26,114,0.2); border-radius:0.5rem;">'
        + '<i class="fas fa-file-pdf" style="color:#dc2626;"></i>'
        + '<span style="font-size:0.75rem; font-weight:500; color:var(--navy); flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">' + file.name + '</span>'
        + '<button type="button" onclick="clearEspelho()" style="padding:0.25rem; color:#f87171; background:none; border:none; cursor:pointer;"><i class="fas fa-times" style="font-size:0.7rem;"></i></button></div>';

    // Show spinner
    isProcessing = true;
    dropContent.style.display = 'none';
    spinner.style.display = '';

    // Send to PHP backend
    var fd = new FormData();
    fd.append('file', file);
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    fetch('/pedidos/parse-pdf', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(json) {
        isProcessing = false;
        dropContent.style.display = '';
        spinner.style.display = 'none';

        if (json.error) {
            errorEl.textContent = json.error;
            errorEl.style.display = '';
            return;
        }

        // Build map from parsed items
        var espelhoMap = {};
        var fornecedor = json.fornecedor || '';
        (json.itens || []).forEach(function(it) {
            var c = String(it.codigo || '').trim();
            if (!c) return;
            if (espelhoMap[c]) {
                espelhoMap[c].quantidade += it.quantidade || 0;
            } else {
                espelhoMap[c] = { quantidade: it.quantidade || 0, fornecedor: it.fornecedor || fornecedor, valor_unitario: it.valor_unitario || 0 };
            }
        });

        if (Object.keys(espelhoMap).length === 0) {
            errorEl.textContent = 'Nenhum item do pedido foi encontrado no PDF. Verifique se o PDF contém os códigos dos produtos.';
            errorEl.style.display = '';
            return;
        }

        renderComparison(espelhoMap, fornecedor);
    })
    .catch(function(err) {
        isProcessing = false;
        dropContent.style.display = '';
        spinner.style.display = 'none';
        errorEl.textContent = 'Erro ao processar PDF: ' + (err.message || 'erro desconhecido');
        errorEl.style.display = '';
    });

    document.getElementById('espelhoInput').value = '';
}

function clearEspelho() {
    document.getElementById('uploadedFile').style.display = 'none';
    document.getElementById('uploadedFile').innerHTML = '';
    document.getElementById('stepTwoCircle').style.background = 'var(--slate-300)';
    document.getElementById('comparisonSection').style.display = 'none';
    var dropContent = document.getElementById('dropContent');
    var spinner = document.getElementById('processingSpinner');
    if (dropContent) dropContent.style.display = '';
    if (spinner) spinner.style.display = 'none';
    isProcessing = false;
}

function renderComparison(espelhoMap, fornecedor) {
    var section = document.getElementById('comparisonSection'), body = document.getElementById('comparisonBody');
    var summary = document.getElementById('comparisonSummary'), dataInput = document.getElementById('comparisonData');
    var fornBar = document.getElementById('fornecedorBar'), fornName = document.getElementById('fornecedorName');
    if (!section) return;
    section.style.display = ''; body.innerHTML = '';
    if (fornecedor) { fornBar.style.display = ''; fornName.textContent = fornecedor; } else fornBar.style.display = 'none';

    var at = 0, pa = 0, na = 0, compData = [];
    pedidoItems.forEach(function(item) {
        var e = espelhoMap[item.codigo] || { quantidade: 0, fornecedor: '', valor_unitario: 0 };
        var sit = e.quantidade >= item.quantidade ? 'atendido' : (e.quantidade > 0 ? 'parcial' : 'nao');
        if (sit==='atendido') at++; else if (sit==='parcial') pa++; else na++;
        var bg = sit==='atendido' ? 'rgba(220,252,231,0.3)' : sit==='parcial' ? 'rgba(254,249,195,0.3)' : 'rgba(254,226,226,0.3)';
        var cl = sit==='atendido' ? '#15803d' : sit==='parcial' ? '#a16207' : '#dc2626';
        var lb = sit==='atendido' ? '✓ Atendido' : sit==='parcial' ? '~ Parcial' : '✕ Não atendido';
        body.innerHTML += '<tr style="background:'+bg+'">'
            +'<td class="ps-3" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+item.nome+'</td>'
            +'<td style="font-family:monospace;font-size:0.75rem;color:#64748b;">'+item.codigo+'</td>'
            +'<td style="font-size:0.75rem;">'+(e.fornecedor||'—')+'</td>'
            +'<td class="text-end" style="font-size:0.75rem;">'+(e.valor_unitario ? 'R$ '+e.valor_unitario.toFixed(2).replace('.',',') : '—')+'</td>'
            +'<td class="text-end fw-semibold">'+item.quantidade+'</td>'
            +'<td class="text-end fw-semibold" style="color:'+cl+'">'+e.quantidade+'</td>'
            +'<td><span style="font-size:0.7rem;font-weight:600;color:'+cl+'">'+lb+'</span></td></tr>';
        compData.push({ pedido_item_id: item.id, quantidade_atendida: e.quantidade, fornecedor: e.fornecedor, valor_unitario: e.valor_unitario });
    });

    summary.innerHTML = '<span style="padding:0.125rem 0.5rem;font-size:0.7rem;font-weight:600;background:#dcfce7;color:#15803d;border-radius:9999px;">'+at+' atendido(s)</span>';
    if (pa>0) summary.innerHTML += '<span style="padding:0.125rem 0.5rem;font-size:0.7rem;font-weight:600;background:#fef9c3;color:#a16207;border-radius:9999px;">'+pa+' parcial(is)</span>';
    if (na>0) summary.innerHTML += '<span style="padding:0.125rem 0.5rem;font-size:0.7rem;font-weight:600;background:#fee2e2;color:#dc2626;border-radius:9999px;">'+na+' não atendido(s)</span>';
    dataInput.value = JSON.stringify(compData);
}
</script>
@endsection
