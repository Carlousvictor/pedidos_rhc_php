<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Remanejamento;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransferenciaController extends Controller
{
    public function index(Request $request)
    {
        $usuario = session('usuario');

        $query = Remanejamento::with([
            'item',
            'pedidoItemOrigem.pedido.unidade',
            'pedidoDestino.unidade',
        ]);

        // Scope: if user is solicitante, only show transfers involving their unit
        if ($usuario->role === 'solicitante') {
            $unidadeId = $usuario->unidade_id;
            $query->where(function ($q) use ($unidadeId) {
                $q->whereHas('pedidoItemOrigem.pedido', function ($pedidoQuery) use ($unidadeId) {
                    $pedidoQuery->where('unidade_id', $unidadeId);
                })->orWhereHas('pedidoDestino', function ($pedidoQuery) use ($unidadeId) {
                    $pedidoQuery->where('unidade_id', $unidadeId);
                });
            });
        }

        $remanejamentos = $query->orderBy('created_at', 'desc')->get();

        // Available pedidos for destination dropdown (not completed or cancelled)
        $pedidosDestino = Pedido::with('unidade')
            ->whereNotIn('status', ['Recebido', 'Cancelado'])
            ->orderBy('numero_pedido')
            ->get();

        // Pedidos with items for source selection
        $pedidosOrigem = Pedido::with(['unidade', 'itens.item'])
            ->whereNotIn('status', ['Recebido', 'Cancelado'])
            ->orderBy('numero_pedido')
            ->get();

        return view('transferencias.index', compact('remanejamentos', 'pedidosDestino', 'pedidosOrigem'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pedido_item_origem_id' => 'required|exists:pedidos_itens,id',
            'pedido_destino_id'     => 'required|exists:pedidos,id',
            'quantidade'            => 'required|integer|min:1',
        ]);

        $pedidoItem = PedidoItem::findOrFail($request->pedido_item_origem_id);

        Remanejamento::create([
            'id'                    => Str::uuid()->toString(),
            'pedido_item_origem_id' => $request->pedido_item_origem_id,
            'pedido_destino_id'     => $request->pedido_destino_id,
            'item_id'               => $pedidoItem->item_id,
            'quantidade'            => $request->quantidade,
        ]);

        return redirect()->back()->with('success', 'Remanejamento criado com sucesso!');
    }

    public function confirmar(Request $request, $id)
    {
        $request->validate([
            'quantidade_recebida' => 'required|integer|min:0',
        ]);

        $remanejamento = Remanejamento::findOrFail($id);
        $remanejamento->quantidade_recebida = min($request->quantidade_recebida, $remanejamento->quantidade);
        $remanejamento->save();

        return redirect()->back()->with('success', 'Quantidade recebida atualizada com sucesso!');
    }
}
