<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::query();

        // Search by nome, codigo, or referencia
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('codigo', 'LIKE', "%{$search}%")
                  ->orWhere('referencia', 'LIKE', "%{$search}%");
            });
        }

        // Filter by tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $perPage = in_array((int) $request->input('per_page'), [25, 50, 100, 200]) ? (int) $request->input('per_page') : 50;

        $itens = $query->orderBy('nome')
            ->paginate($perPage)
            ->appends($request->query());

        // Get distinct tipos for filter dropdown
        $tipos = Item::whereNotNull('tipo')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        return view('itens.index', compact('itens', 'tipos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:255',
            'nome'   => 'required|string|max:255',
            'tipo'   => 'required|string|max:255',
        ]);

        Item::create([
            'id'         => Str::uuid()->toString(),
            'codigo'     => $request->codigo,
            'nome'       => $request->nome,
            'tipo'       => $request->tipo,
            'referencia' => $request->referencia,
        ]);

        return redirect()->back()->with('success', 'Item cadastrado com sucesso!');
    }

    public function buscar(Request $request)
    {
        $search = $request->input('q', '');

        // Return all items for Excel import matching
        if ($request->has('all')) {
            $itens = Item::orderBy('nome')->get(['id', 'codigo', 'nome', 'tipo', 'referencia']);
            return response()->json($itens);
        }

        $itens = Item::where(function ($query) use ($search) {
                $query->where('nome', 'LIKE', "%{$search}%")
                      ->orWhere('codigo', 'LIKE', "%{$search}%")
                      ->orWhere('referencia', 'LIKE', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'codigo', 'nome', 'tipo', 'referencia']);

        return response()->json($itens);
    }
}
