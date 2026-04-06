@extends('layouts.app')

@section('title', 'Bionexo - Conversor')

@section('content')
<div class="row mb-3">
    <div class="col">
        <h2 class="fw-bold"><i class="fas fa-truck text-primary me-2"></i>Bionexo</h2>
        <p class="text-muted">Faça upload de PDFs da Bionexo para convertê-los em planilhas Excel organizadas.</p>
    </div>
</div>

@include('components.flash-messages')

<div class="card shadow-sm border-0">
    <div class="card-body p-5 text-center">
        
        <div id="upload-zone" class="border rounded p-5 mb-4" style="border: 2px dashed #cbd5e1 !important; background-color: #f8fafc; cursor: pointer;">
            <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
            <h5>Arraste e solte o PDF da Bionexo aqui</h5>
            <p class="text-muted mb-0">ou clique para selecionar</p>
            <input type="file" id="pdf-input" class="d-none" accept="application/pdf">
        </div>

        <div id="processing" class="d-none my-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Processando...</span>
            </div>
            <p class="mt-2 text-muted" id="processing-text">Lendo arquivo e contatando servidor de processamento...</p>
        </div>

        <div id="result-zone" class="d-none text-start mt-4">
            <h5 class="fw-bold text-success mb-3"><i class="fas fa-check-circle me-2"></i>Processamento Concluído</h5>
            
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Quantidade</th>
                            <th>Fornecedor</th>
                            <th>Valor Unitário</th>
                        </tr>
                    </thead>
                    <tbody id="result-tbody">
                        {{-- Filled by JS --}}
                    </tbody>
                </table>
            </div>

            <div class="text-center mt-4">
                <button type="button" class="btn btn-primary" id="btn-export">
                    <i class="fas fa-file-excel me-2"></i>Exportar para Excel (.xlsx)
                </button>
                <button type="button" class="btn btn-outline-secondary ms-2" id="btn-reset">
                    <i class="fas fa-redo me-2"></i>Fazer novo upload
                </button>
            </div>
        </div>

        <div id="error-zone" class="d-none alert alert-danger mt-3 text-start">
            <i class="fas fa-exclamation-triangle me-2"></i> <span id="error-text">Ocorreu um erro no processamento.</span>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('upload-zone');
    const pdfInput = document.getElementById('pdf-input');
    const processing = document.getElementById('processing');
    const resultZone = document.getElementById('result-zone');
    const resultTbody = document.getElementById('result-tbody');
    const errorZone = document.getElementById('error-zone');
    const errorText = document.getElementById('error-text');
    const btnExport = document.getElementById('btn-export');
    const btnReset = document.getElementById('btn-reset');
    
    let currentData = null;

    uploadZone.addEventListener('click', () => pdfInput.click());
    
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.style.backgroundColor = '#e2e8f0';
    });
    
    uploadZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        uploadZone.style.backgroundColor = '#f8fafc';
    });
    
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.style.backgroundColor = '#f8fafc';
        if (e.dataTransfer.files.length) {
            handleFile(e.dataTransfer.files[0]);
        }
    });

    pdfInput.addEventListener('change', function() {
        if (this.files.length) {
            handleFile(this.files[0]);
        }
    });

    btnReset.addEventListener('click', () => {
        uploadZone.classList.remove('d-none');
        resultZone.classList.add('d-none');
        errorZone.classList.add('d-none');
        pdfInput.value = '';
        currentData = null;
    });

    async function handleFile(file) {
        if (file.type !== 'application/pdf') {
            showError('Por favor, selecione um arquivo PDF.');
            return;
        }

        uploadZone.classList.add('d-none');
        errorZone.classList.add('d-none');
        processing.classList.remove('d-none');

        const formData = new FormData();
        formData.append('file', file);

        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch('{{ route("bionexo.convert") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                body: formData
            });

            const data = await res.json();
            
            if (!res.ok) throw new Error(data.error || 'Erro no processamento');
            
            currentData = data;
            renderResults(data.itens || []);
            
        } catch (err) {
            showError(err.message);
        } finally {
            processing.classList.add('d-none');
        }
    }

    function renderResults(itens) {
        resultTbody.innerHTML = '';
        itens.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.codigo || '-'}</td>
                <td>${item.quantidade || 0}</td>
                <td>${item.fornecedor || '-'}</td>
                <td>R$ ${Number(item.valor_unitario || 0).toFixed(2).replace('.', ',')}</td>
            `;
            resultTbody.appendChild(tr);
        });
        resultZone.classList.remove('d-none');
    }

    btnExport.addEventListener('click', async () => {
        if (!currentData) return;
        
        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const res = await fetch('{{ route("bionexo.export") }}', {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ data: currentData })
            });

            if (!res.ok) throw new Error('Erro ao gerar Excel');
            
            const blob = await res.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'bionexo_exportado.xlsx';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        } catch (err) {
            showError(err.message);
        }
    });

    function showError(msg) {
        uploadZone.classList.remove('d-none');
        processing.classList.add('d-none');
        resultZone.classList.add('d-none');
        errorZone.classList.remove('d-none');
        errorText.textContent = msg;
    }
});
</script>
@endsection
