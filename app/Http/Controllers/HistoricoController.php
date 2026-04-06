<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Unidade;
use Illuminate\Http\Request;

class HistoricoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::with(['unidade', 'usuario'])
            ->withCount('itens');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by unidade
        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->unidade_id);
        }

        // Search by numero_pedido or item name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_pedido', 'LIKE', "%{$search}%")
                  ->orWhereHas('itens.item', function ($itemQuery) use ($search) {
                      $itemQuery->where('nome', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $pedidos = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $unidades = Unidade::orderBy('nome')->get();

        // Status counts for tabs
        $statusCounts = Pedido::selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('historico.index', compact('pedidos', 'unidades', 'statusCounts'));
    }
}
