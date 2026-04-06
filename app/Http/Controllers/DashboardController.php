<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Remanejamento;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $usuario = session('usuario');

        if (!$usuario) {
            return redirect('/login');
        }

        $query = Pedido::query()
            ->with(['unidade', 'usuario'])
            ->withCount('itens as itens_count')
            ->withSum(
                ['itens as valor_total' => function ($q) {
                    $q->select(DB::raw('COALESCE(SUM(valor_unitario * quantidade_atendida), 0)'));
                }],
                DB::raw('1') // placeholder, actual aggregation is in the closure
            );

        // Use a subquery for total value instead of withSum workaround
        $query = Pedido::query()
            ->with(['unidade', 'usuario'])
            ->withCount('itens as itens_count')
            ->addSelect([
                'pedidos.*',
                'valor_total' => PedidoItem::selectRaw('COALESCE(SUM(valor_unitario * quantidade_atendida), 0)')
                    ->whereColumn('pedidos_itens.pedido_id', 'pedidos.id'),
            ]);

        // Apply scope filtering based on user role
        switch ($usuario->role) {
            case 'admin':
                // See all orders, no filter
                break;

            case 'comprador':
                // See all orders EXCEPT status 'Aguardando Aprovação'
                $query->where('pedidos.status', '!=', 'Aguardando Aprovação');
                break;

            case 'aprovador':
                // See orders with status 'Aguardando Aprovação' + orders from their unit
                $query->where(function ($q) use ($usuario) {
                    $q->where('pedidos.status', 'Aguardando Aprovação')
                      ->orWhere('pedidos.unidade_id', $usuario->unidade_id);
                });
                break;

            default:
                // solicitante (scope=operador): own orders + orders with remanejamentos from their unit
                $query->where(function ($q) use ($usuario) {
                    $q->where('pedidos.usuario_id', $usuario->id)
                      ->orWhereIn('pedidos.id', function ($sub) use ($usuario) {
                          $sub->select('remanejamentos.pedido_destino_id')
                              ->from('remanejamentos')
                              ->join('pedidos_itens', 'pedidos_itens.id', '=', 'remanejamentos.pedido_item_origem_id')
                              ->join('pedidos as p_origem', 'p_origem.id', '=', 'pedidos_itens.pedido_id')
                              ->where('p_origem.unidade_id', $usuario->unidade_id);
                      });
                });
                break;
        }

        // Apply query param filters
        if ($request->filled('status')) {
            $query->where('pedidos.status', $request->status);
        }

        if ($request->filled('unidade_id')) {
            $query->where('pedidos.unidade_id', $request->unidade_id);
        }

        if ($request->filled('search')) {
            $query->where('pedidos.numero_pedido', 'like', '%' . $request->search . '%');
        }

        // Get status counts for filter badges (using same scope filtering)
        $statusCountsQuery = Pedido::query();

        switch ($usuario->role) {
            case 'admin':
                break;
            case 'comprador':
                $statusCountsQuery->where('status', '!=', 'Aguardando Aprovação');
                break;
            case 'aprovador':
                $statusCountsQuery->where(function ($q) use ($usuario) {
                    $q->where('status', 'Aguardando Aprovação')
                      ->orWhere('unidade_id', $usuario->unidade_id);
                });
                break;
            default:
                $statusCountsQuery->where(function ($q) use ($usuario) {
                    $q->where('usuario_id', $usuario->id)
                      ->orWhereIn('id', function ($sub) use ($usuario) {
                          $sub->select('remanejamentos.pedido_destino_id')
                              ->from('remanejamentos')
                              ->join('pedidos_itens', 'pedidos_itens.id', '=', 'remanejamentos.pedido_item_origem_id')
                              ->join('pedidos as p_origem', 'p_origem.id', '=', 'pedidos_itens.pedido_id')
                              ->where('p_origem.unidade_id', $usuario->unidade_id);
                      });
                });
                break;
        }

        $statusCounts = $statusCountsQuery
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Order and paginate
        $pedidos = $query->orderBy('pedidos.created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        // Load filter data
        $unidades = Unidade::orderBy('nome')->get();

        $filters = [
            'status' => $request->status,
            'unidade_id' => $request->unidade_id,
            'search' => $request->search,
        ];

        return view('dashboard.index', compact('pedidos', 'filters', 'statusCounts', 'unidades'));
    }
}
