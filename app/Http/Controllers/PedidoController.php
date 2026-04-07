<?php

namespace App\Http\Controllers;

use App\Models\Aprovacao;
use App\Models\Item;
use App\Models\Pedido;
use App\Models\PedidoAlteracao;
use App\Models\PedidoItem;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

class PedidoController extends Controller
{
    const REQUIRED_APPROVALS = 1;

    public function create()
    {
        $unidades = Unidade::orderBy('nome')->get();
        $items = Item::orderBy('nome')->get();

        return view('pedidos.create', compact('unidades', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'unidade_id' => 'required|exists:unidades,id',
            'itens' => 'required|array|min:1',
            'itens.*.item_id' => 'required|exists:itens,id',
            'itens.*.quantidade' => 'required|integer|min:1',
        ]);

        $usuario = session('usuario');

        if (!$usuario) {
            return redirect('/login');
        }

        try {
            DB::beginTransaction();

            $pedidoId = (string) Str::uuid();

            // Generate next numero_pedido
            $lastNumber = Pedido::max('numero_pedido');
            $nextNumber = $lastNumber ? str_pad((int) $lastNumber + 1, strlen($lastNumber), '0', STR_PAD_LEFT) : '0001';

            $pedido = Pedido::create([
                'id' => $pedidoId,
                'numero_pedido' => $nextNumber,
                'status' => 'Aguardando Aprovação',
                'unidade_id' => $request->unidade_id,
                'usuario_id' => $usuario->id,
            ]);

            foreach ($request->itens as $itemData) {
                PedidoItem::create([
                    'id' => (string) Str::uuid(),
                    'pedido_id' => $pedidoId,
                    'item_id' => $itemData['item_id'],
                    'quantidade' => $itemData['quantidade'],
                    'quantidade_atendida' => 0,
                    'quantidade_recebida' => 0,
                ]);
            }

            PedidoAlteracao::create([
                'id' => (string) Str::uuid(),
                'pedido_id' => $pedidoId,
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'tipo' => 'pedido_criado',
            ]);

            DB::commit();

            return redirect()->route('pedidos.show', $pedidoId)
                ->with('success', 'Pedido criado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao criar pedido: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $usuario = session('usuario');

        if (!$usuario) {
            return redirect('/login');
        }

        $pedido = Pedido::with([
            'unidade',
            'usuario',
            'atendidoPor',
            'itens.item',
            'itens.itemRecebido',
            'aprovacoes.usuario',
            'alteracoes' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'notasFiscais.itens',
            'remanejamentosDestino.item',
            'remanejamentosDestino.pedidoItemOrigem.pedido',
        ])->findOrFail($id);

        // Calculate totals
        $itensCount = $pedido->itens->count();
        $valorTotal = $pedido->itens->sum(function ($item) {
            return $item->valor_unitario * $item->quantidade_atendida;
        });

        // Approval logic
        $approvalCount = $pedido->aprovacoes->count();
        $hasAlreadyApproved = $pedido->aprovacoes->contains('usuario_id', $usuario->id);
        $canApprove = in_array($usuario->role, ['aprovador', 'admin'])
            && !$hasAlreadyApproved
            && $pedido->status === 'Aguardando Aprovação';

        // Permission checks
        $canEditStatus = in_array($usuario->role, ['comprador', 'admin']);
        $canReceiveItems = $usuario->role === 'solicitante' && $pedido->status === 'Realizado';

        // Load pedidos list for remanejamento destination selector
        $pedidosParaRemanejamento = Pedido::select('id', 'numero_pedido')
            ->where('id', '!=', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Load all items for adding items
        $items = Item::orderBy('nome')->get();

        return view('pedidos.show', compact(
            'pedido',
            'itensCount',
            'valorTotal',
            'approvalCount',
            'canApprove',
            'canEditStatus',
            'canReceiveItems',
            'pedidosParaRemanejamento',
            'items'
        ));
    }

    public function edit($id)
    {
        $usuario = session('usuario');

        if (!$usuario) {
            return redirect('/login');
        }

        if (!in_array($usuario->role, ['admin', 'aprovador'])) {
            return redirect()->back()->withErrors(['error' => 'Sem permissão para editar este pedido.']);
        }

        $pedido = Pedido::with('itens.item')->findOrFail($id);
        $items = Item::orderBy('nome')->get();

        return view('pedidos.edit', compact('pedido', 'items'));
    }

    public function update(Request $request, $id)
    {
        $usuario = session('usuario');

        if (!$usuario) {
            return redirect('/login');
        }

        $pedido = Pedido::with('itens.item')->findOrFail($id);

        try {
            DB::beginTransaction();

            if ($request->has('itens')) {
                foreach ($request->itens as $pedidoItemId => $data) {
                    $pedidoItem = PedidoItem::where('id', $pedidoItemId)
                        ->where('pedido_id', $id)
                        ->first();

                    if (!$pedidoItem) {
                        continue;
                    }

                    $oldQuantidade = $pedidoItem->quantidade;
                    $newQuantidade = (int) ($data['quantidade'] ?? $oldQuantidade);

                    if ($oldQuantidade !== $newQuantidade) {
                        $pedidoItem->update(['quantidade' => $newQuantidade]);

                        PedidoAlteracao::create([
                            'id' => (string) Str::uuid(),
                            'pedido_id' => $id,
                            'usuario_id' => $usuario->id,
                            'usuario_nome' => $usuario->nome,
                            'tipo' => 'quantidade_alterada',
                            'item_nome' => $pedidoItem->item?->nome,
                            'item_codigo' => $pedidoItem->item?->codigo,
                            'valor_anterior' => (string) $oldQuantidade,
                            'valor_novo' => (string) $newQuantidade,
                        ]);
                    }
                }
            }

            // Handle new items added during edit
            if ($request->has('novos_itens')) {
                foreach ($request->novos_itens as $novoItem) {
                    if (empty($novoItem['item_id']) || empty($novoItem['quantidade'])) {
                        continue;
                    }

                    $item = Item::find($novoItem['item_id']);

                    PedidoItem::create([
                        'id' => (string) Str::uuid(),
                        'pedido_id' => $id,
                        'item_id' => $novoItem['item_id'],
                        'quantidade' => (int) $novoItem['quantidade'],
                        'quantidade_atendida' => 0,
                        'quantidade_recebida' => 0,
                    ]);

                    PedidoAlteracao::create([
                        'id' => (string) Str::uuid(),
                        'pedido_id' => $id,
                        'usuario_id' => $usuario->id,
                        'usuario_nome' => $usuario->nome,
                        'tipo' => 'item_adicionado',
                        'item_nome' => $item?->nome,
                        'item_codigo' => $item?->codigo,
                        'valor_novo' => (string) $novoItem['quantidade'],
                    ]);
                }
            }

            // Handle removed items
            if ($request->has('remover_itens')) {
                foreach ($request->remover_itens as $pedidoItemId) {
                    $pedidoItem = PedidoItem::with('item')
                        ->where('id', $pedidoItemId)
                        ->where('pedido_id', $id)
                        ->first();

                    if ($pedidoItem) {
                        PedidoAlteracao::create([
                            'id' => (string) Str::uuid(),
                            'pedido_id' => $id,
                            'usuario_id' => $usuario->id,
                            'usuario_nome' => $usuario->nome,
                            'tipo' => 'item_removido',
                            'item_nome' => $pedidoItem->item?->nome,
                            'item_codigo' => $pedidoItem->item?->codigo,
                        ]);

                        $pedidoItem->delete();
                    }
                }
            }

            DB::commit();

            return redirect()->route('pedidos.show', $id)
                ->with('success', 'Pedido atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao atualizar pedido: ' . $e->getMessage()]);
        }
    }

    public function aprovar($id)
    {
        $usuario = session('usuario');

        if (!$usuario) {
            return redirect('/login');
        }

        $pedido = Pedido::with('aprovacoes')->findOrFail($id);

        // Check if user already approved
        if ($pedido->aprovacoes->contains('usuario_id', $usuario->id)) {
            return redirect()->back()->withErrors(['error' => 'Você já aprovou este pedido.']);
        }

        // Check if user has permission
        if (!in_array($usuario->role, ['aprovador', 'admin'])) {
            return redirect()->back()->withErrors(['error' => 'Sem permissão para aprovar pedidos.']);
        }

        try {
            DB::beginTransaction();

            Aprovacao::create([
                'id' => (string) Str::uuid(),
                'pedido_id' => $id,
                'usuario_id' => $usuario->id,
            ]);

            $approvalCount = $pedido->aprovacoes->count() + 1;

            if ($approvalCount >= self::REQUIRED_APPROVALS) {
                $pedido->update(['status' => 'Pendente']);

                PedidoAlteracao::create([
                    'id' => (string) Str::uuid(),
                    'pedido_id' => $id,
                    'usuario_id' => $usuario->id,
                    'usuario_nome' => $usuario->nome,
                    'tipo' => 'status_alterado',
                    'valor_anterior' => 'Aguardando Aprovação',
                    'valor_novo' => 'Pendente',
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Pedido aprovado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Erro ao aprovar pedido: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $usuario = session('usuario');

        if (!$usuario) {
            return redirect('/login');
        }

        if (!in_array($usuario->role, ['comprador', 'admin'])) {
            return redirect()->back()->withErrors(['error' => 'Sem permissão para alterar status.']);
        }

        $request->validate([
            'status' => 'required|string',
        ]);

        $pedido = Pedido::findOrFail($id);

        try {
            DB::beginTransaction();

            $oldStatus = $pedido->status;
            $updateData = ['status' => $request->status];

            if (in_array($request->status, ['Em Cotação', 'Realizado'])) {
                $updateData['atendido_por'] = $usuario->id;
            }

            if ($request->filled('numero_pedido')) {
                $updateData['numero_pedido'] = $request->numero_pedido;
            }

            if ($request->filled('fornecedor')) {
                $updateData['fornecedor'] = $request->fornecedor;
            }

            $pedido->update($updateData);

            PedidoAlteracao::create([
                'id' => (string) Str::uuid(),
                'pedido_id' => $id,
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'tipo' => 'status_alterado',
                'valor_anterior' => $oldStatus,
                'valor_novo' => $request->status,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Status atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Erro ao atualizar status: ' . $e->getMessage()]);
        }
    }

    public function addItem(Request $request, $id)
    {
        $usuario = session('usuario');

        if (!$usuario) {
            return redirect('/login');
        }

        $request->validate([
            'item_id' => 'required|exists:itens,id',
            'quantidade' => 'required|integer|min:1',
        ]);

        $pedido = Pedido::findOrFail($id);
        $item = Item::findOrFail($request->item_id);

        try {
            DB::beginTransaction();

            PedidoItem::create([
                'id' => (string) Str::uuid(),
                'pedido_id' => $id,
                'item_id' => $request->item_id,
                'quantidade' => $request->quantidade,
                'quantidade_atendida' => 0,
                'quantidade_recebida' => 0,
            ]);

            PedidoAlteracao::create([
                'id' => (string) Str::uuid(),
                'pedido_id' => $id,
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'tipo' => 'item_adicionado',
                'item_nome' => $item->nome,
                'item_codigo' => $item->codigo,
                'valor_novo' => (string) $request->quantidade,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Item adicionado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Erro ao adicionar item: ' . $e->getMessage()]);
        }
    }

    public function removeItem($pedidoId, $itemId)
    {
        $usuario = session('usuario');

        if (!$usuario) {
            return redirect('/login');
        }

        $pedidoItem = PedidoItem::with('item')
            ->where('id', $itemId)
            ->where('pedido_id', $pedidoId)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            PedidoAlteracao::create([
                'id' => (string) Str::uuid(),
                'pedido_id' => $pedidoId,
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'tipo' => 'item_removido',
                'item_nome' => $pedidoItem->item?->nome,
                'item_codigo' => $pedidoItem->item?->codigo,
            ]);

            $pedidoItem->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Item removido com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['error' => 'Erro ao remover item: ' . $e->getMessage()]);
        }
    }

    public function atenderItem(Request $request, $pedidoId, $itemId)
    {
        $usuario = session('usuario');

        if (!$usuario) {
            return redirect('/login');
        }

        $request->validate([
            'quantidade_atendida' => 'required|integer|min:0',
            'valor_unitario' => 'required|numeric|min:0',
            'fornecedor' => 'nullable|string|max:255',
        ]);

        $pedidoItem = PedidoItem::where('id', $itemId)
            ->where('pedido_id', $pedidoId)
            ->firstOrFail();

        $pedidoItem->update([
            'quantidade_atendida' => $request->quantidade_atendida,
            'valor_unitario' => $request->valor_unitario,
            'fornecedor' => $request->fornecedor,
        ]);

        return redirect()->back()->with('success', 'Item atendido com sucesso!');
    }

    public function receberItem(Request $request, $pedidoId, $itemId)
    {
        $usuario = session('usuario');

        if (!$usuario) {
            return redirect('/login');
        }

        $request->validate([
            'quantidade_recebida' => 'required|integer|min:0',
            'observacao_recebimento' => 'nullable|string',
            'item_recebido_id' => 'nullable|exists:itens,id',
        ]);

        $pedidoItem = PedidoItem::where('id', $itemId)
            ->where('pedido_id', $pedidoId)
            ->firstOrFail();

        $pedidoItem->update([
            'quantidade_recebida' => $request->quantidade_recebida,
            'observacao_recebimento' => $request->observacao_recebimento,
            'item_recebido_id' => $request->item_recebido_id,
        ]);

        // Check if all items have been received to auto-update pedido status
        $pedido = Pedido::with('itens')->findOrFail($pedidoId);
        $allReceived = $pedido->itens->every(function ($item) {
            return $item->quantidade_recebida > 0 && $item->quantidade_recebida >= $item->quantidade_atendida;
        });

        if ($allReceived && $pedido->itens->count() > 0) {
            $pedido->update(['status' => 'Recebido']);

            PedidoAlteracao::create([
                'id' => (string) Str::uuid(),
                'pedido_id' => $pedidoId,
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'tipo' => 'status_alterado',
                'valor_anterior' => $pedido->getOriginal('status') ?? 'Realizado',
                'valor_novo' => 'Recebido',
            ]);
        }

        return redirect()->back()->with('success', 'Recebimento registrado com sucesso!');
    }

    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);

        // Delete related items first
        PedidoItem::where('pedido_id', $pedido->id)->delete();

        $pedido->delete();

        return redirect()->route('historico')->with('success', 'Pedido excluído com sucesso.');
    }

    public function confirmarEspelho(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);
        $usuario = session('usuario');

        $comparisonData = json_decode($request->input('comparison_data'), true);

        if (!$comparisonData || !is_array($comparisonData)) {
            return redirect()->back()->withErrors(['error' => 'Dados de comparação inválidos.']);
        }

        DB::beginTransaction();
        try {
            foreach ($comparisonData as $item) {
                PedidoItem::where('id', $item['pedido_item_id'])->update([
                    'quantidade_atendida' => $item['quantidade_atendida'] ?? 0,
                    'fornecedor' => $item['fornecedor'] ?? null,
                    'valor_unitario' => $item['valor_unitario'] ?? 0,
                ]);
            }

            $pedido->update([
                'status' => 'Realizado',
                'atendido_por' => $usuario->id,
            ]);

            PedidoAlteracao::create([
                'id' => (string) Str::uuid(),
                'pedido_id' => $pedido->id,
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'tipo' => 'status_alterado',
                'valor_anterior' => $pedido->getOriginal('status') ?? 'Pendente',
                'valor_novo' => 'Realizado',
            ]);

            DB::commit();

            return redirect()->route('pedidos.show', $pedido->id)
                ->with('success', 'Pedido confirmado como Realizado! Aguardando confirmação de recebimento pelo solicitante.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Erro ao confirmar: ' . $e->getMessage()]);
        }
    }

    public function parsePdf(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($request->file('file')->getRealPath());
            $text = $pdf->getText();

            // Get all known item codes from the database (indexed by codigo)
            $allItems = Item::pluck('codigo')->toArray();
            $codigoSet = array_flip(array_map('trim', $allItems));

            $itens = [];
            $fornecedor = '';
            $lines = preg_split('/\r?\n/', $text);

            // Try to detect fornecedor from text
            foreach ($lines as $line) {
                if (preg_match('/fornecedor[:\s]+(.+)/i', $line, $m)) {
                    $fornecedor = trim($m[1]);
                    break;
                }
            }

            // Detect MV2000/MVSoul format: "Relatório de Solicitação de Compras" or "MV2000"
            $isMV = false;
            foreach ($lines as $line) {
                if (preg_match('/MV2000|MVSoul|Solicita[çc][aã]o de Compras/i', $line)) {
                    $isMV = true;
                    break;
                }
            }

            if ($isMV) {
                // MV2000/MVSoul PDF parsing strategy
                // The PDF parser extracts table columns vertically per page:
                // First a block of product CODES (one per line, 4-5 digit numbers),
                // then descriptions, then QUANTITIES (Brazilian format like "500,00"),
                // then units, then sequence numbers, etc.
                // We need to pair codes with quantities by their positional order.

                // Process each page separately to maintain code-quantity pairing
                $pages = $pdf->getPages();

                foreach ($pages as $page) {
                    $pageText = $page->getText();
                    $pageLines = preg_split('/\r?\n/', $pageText);

                    // Phase 1: Find the block of product codes
                    // Codes are standalone lines with 4-5 digit numbers
                    // Accept any 4-5 digit number to avoid breaking the block detection
                    $pageCodes = [];
                    $codeBlockStarted = false;

                    foreach ($pageLines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;

                        if (preg_match('/^\d{4,5}$/', $line)) {
                            $pageCodes[] = $line;
                            $codeBlockStarted = true;
                        } elseif ($codeBlockStarted && count($pageCodes) > 0) {
                            break;
                        }
                    }

                    if (empty($pageCodes)) continue;

                    // Phase 2: Find the first block of Brazilian decimal numbers (quantities)
                    // These appear as lines like " 500,00" or " 3.000,00"
                    $pageQtys = [];
                    $qtyBlockStarted = false;

                    foreach ($pageLines as $line) {
                        $trimmed = trim($line);
                        if (empty($trimmed)) continue;

                        // Skip code lines
                        if (preg_match('/^\d{4,5}$/', $trimmed)) continue;

                        if (preg_match('/^\s*\d{1,3}(?:\.\d{3})*,\d{2}\s*$/', $line)) {
                            $val = (float) str_replace(['.', ','], ['', '.'], $trimmed);
                            $pageQtys[] = $val;
                            $qtyBlockStarted = true;
                        } elseif ($qtyBlockStarted && count($pageQtys) > 0) {
                            if (count($pageQtys) >= count($pageCodes)) {
                                break;
                            }
                            // Block interrupted before having enough quantities — reset and keep looking
                            $qtyBlockStarted = false;
                            $pageQtys = [];
                        }
                    }

                    // Phase 3: Pair codes with quantities by position (only known codes)
                    $pairCount = min(count($pageCodes), count($pageQtys));
                    for ($i = 0; $i < $pairCount; $i++) {
                        $code = $pageCodes[$i];
                        $qty = (int) $pageQtys[$i];

                        if ($qty > 0 && isset($codigoSet[$code]) && !isset($itens[$code])) {
                            $itens[$code] = [
                                'codigo' => $code,
                                'quantidade' => $qty,
                                'fornecedor' => $fornecedor,
                                'valor_unitario' => 0,
                            ];
                        }
                    }
                }
            }

            // Generic strategy (non-MV or if MV found nothing)
            if (empty($itens)) {
                // Strategy 1: Look for lines that contain a known codigo followed by a number (quantity)
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    foreach ($codigoSet as $codigo => $_) {
                        $codigoStr = (string) $codigo;
                        if (empty($codigoStr)) continue;

                        if (strpos($line, $codigoStr) !== false) {
                            $pattern = '/' . preg_quote($codigoStr, '/') . '.*?(\d+)(?:\s|$)/';
                            if (preg_match($pattern, $line, $qm)) {
                                $qty = (int) $qm[1];
                                if ($qty > 0 && $qty < 100000) {
                                    $vu = 0;
                                    if (preg_match('/R\$\s*([\d.]+,[\d]+)/', $line, $vm)) {
                                        $vu = (float) str_replace(['.', ','], ['', '.'], $vm[1]);
                                    }

                                    if (!isset($itens[$codigoStr])) {
                                        $itens[$codigoStr] = [
                                            'codigo' => $codigoStr,
                                            'quantidade' => $qty,
                                            'fornecedor' => $fornecedor,
                                            'valor_unitario' => $vu,
                                        ];
                                    } else {
                                        $itens[$codigoStr]['quantidade'] += $qty;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Strategy 2: If still nothing, try broader pattern matching
            if (empty($itens)) {
                $fullText = implode(' ', $lines);
                foreach ($codigoSet as $codigo => $_) {
                    $codigoStr = (string) $codigo;
                    if (empty($codigoStr) || strlen($codigoStr) < 2) continue;

                    $offset = 0;
                    while (($pos = strpos($fullText, $codigoStr, $offset)) !== false) {
                        $context = substr($fullText, $pos, 200);
                        if (preg_match('/^' . preg_quote($codigoStr, '/') . '\D+?(\d{1,5})/', $context, $cm)) {
                            $qty = (int) $cm[1];
                            if ($qty > 0 && $qty < 100000) {
                                $vu = 0;
                                if (preg_match('/R\$\s*([\d.]+,[\d]+)/', $context, $vm)) {
                                    $vu = (float) str_replace(['.', ','], ['', '.'], $vm[1]);
                                }
                                $itens[$codigoStr] = [
                                    'codigo' => $codigoStr,
                                    'quantidade' => $qty,
                                    'fornecedor' => $fornecedor,
                                    'valor_unitario' => $vu,
                                ];
                                break;
                            }
                        }
                        $offset = $pos + strlen($codigoStr);
                    }
                }
            }

            return response()->json([
                'itens' => array_values($itens),
                'fornecedor' => $fornecedor,
                'text_preview' => mb_substr($text, 0, 500),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao processar PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadEspelho(Request $request, $id)
    {
        $request->validate([
            'espelho' => 'required|file|mimes:pdf|max:10240',
        ]);

        $pedido = Pedido::findOrFail($id);

        $path = $request->file('espelho')->store('espelhos', 'public');
        $pedido->update(['espelho_path' => $path]);

        return redirect()->back()->with('success', 'Espelho do pedido anexado com sucesso!');
    }
}
