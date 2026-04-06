<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Remanejamento;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function index(Request $request)
    {
        $unidades = Unidade::orderBy('nome')->get();

        // Base query scoping by filters
        $pedidoQuery = Pedido::query();
        $filterUnidade = $request->input('unidade_id');
        $filterDe = $request->input('data_inicio');
        $filterAte = $request->input('data_fim');

        if ($filterUnidade) {
            $pedidoQuery->where('unidade_id', $filterUnidade);
        }
        if ($filterDe) {
            $pedidoQuery->whereDate('created_at', '>=', $filterDe);
        }
        if ($filterAte) {
            $pedidoQuery->whereDate('created_at', '<=', $filterAte);
        }

        $pedidoIds = (clone $pedidoQuery)->pluck('id');

        // Total pedidos by status
        $pedidosPorStatus = (clone $pedidoQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Total items within filtered pedidos
        $itensQuery = PedidoItem::whereIn('pedido_id', $pedidoIds);

        $totaisItens = (clone $itensQuery)->select(
            DB::raw('COALESCE(SUM(quantidade), 0) as total_solicitado'),
            DB::raw('COALESCE(SUM(quantidade_atendida), 0) as total_atendido'),
            DB::raw('COALESCE(SUM(quantidade_recebida), 0) as total_recebido')
        )->first();

        // Total value
        $valorTotal = (clone $itensQuery)
            ->select(DB::raw('COALESCE(SUM(quantidade * COALESCE(valor_unitario, 0)), 0) as valor'))
            ->value('valor');

        // Top 10 items
        $topItens = DB::table('pedidos_itens')
            ->join('itens', 'pedidos_itens.item_id', '=', 'itens.id')
            ->whereIn('pedidos_itens.pedido_id', $pedidoIds)
            ->select('itens.nome', 'itens.codigo', DB::raw('SUM(pedidos_itens.quantidade) as total_quantidade'))
            ->groupBy('itens.id', 'itens.nome', 'itens.codigo')
            ->orderByDesc('total_quantidade')
            ->limit(10)
            ->get();

        // Orders per unit
        $pedidosPorUnidade = (clone $pedidoQuery)
            ->select('unidades.nome', DB::raw('COUNT(*) as total'))
            ->join('unidades', 'pedidos.unidade_id', '=', 'unidades.id')
            ->groupBy('unidades.id', 'unidades.nome')
            ->orderByDesc('total')
            ->get();

        // Divergence count
        $divergencias = (clone $itensQuery)->where(function ($query) {
            $query->whereNotNull('item_recebido_id')
                  ->orWhere(function ($q) {
                      $q->whereNotNull('observacao_recebimento')
                        ->where('observacao_recebimento', '!=', '');
                  });
        })->count();

        // Transfer stats
        $totalRemanejamentos = Remanejamento::count();
        $totalQuantidadeTransferida = Remanejamento::sum('quantidade');

        // Taxa de atendimento
        $taxa = $totaisItens->total_solicitado > 0
            ? round(($totaisItens->total_atendido / $totaisItens->total_solicitado) * 100, 1)
            : 0;

        // Taxa de recebimento
        $taxaRecebimento = $totaisItens->total_atendido > 0
            ? round(($totaisItens->total_recebido / $totaisItens->total_atendido) * 100, 1)
            : 0;

        $totalPedidos = (clone $pedidoQuery)->count();

        return view('relatorios.index', compact(
            'pedidosPorStatus',
            'totaisItens',
            'valorTotal',
            'topItens',
            'pedidosPorUnidade',
            'divergencias',
            'totalRemanejamentos',
            'totalQuantidadeTransferida',
            'unidades',
            'taxa',
            'taxaRecebimento',
            'totalPedidos'
        ));
    }
}
