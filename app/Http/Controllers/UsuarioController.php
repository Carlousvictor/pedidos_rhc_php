<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = Usuario::with('unidade')->orderBy('nome')->get();
        $unidades = Unidade::orderBy('nome')->get();

        return view('usuarios.index', compact('usuarios', 'unidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:usuarios,username',
            'nome'     => 'required|string|max:255',
            'password' => 'required|string|min:4',
            'role'     => 'required|string|in:admin,comprador,aprovador,solicitante',
        ]);

        $permissoes = $this->buildPermissoes($request);

        Usuario::create([
            'id'            => Str::uuid()->toString(),
            'username'      => $request->username,
            'password_hash' => $request->password,
            'nome'          => $request->nome,
            'role'          => $request->role,
            'unidade_id'    => $request->unidade_id,
            'permissoes'    => $permissoes,
        ]);

        return redirect()->back()->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $request->validate([
            'username' => 'required|string|max:255|unique:usuarios,username,' . $id,
            'nome'     => 'required|string|max:255',
            'role'     => 'required|string|in:admin,comprador,aprovador,solicitante',
        ]);

        $data = [
            'username'   => $request->username,
            'nome'       => $request->nome,
            'role'       => $request->role,
            'unidade_id' => $request->unidade_id,
            'permissoes' => $this->buildPermissoes($request),
        ];

        if ($request->filled('password')) {
            $data['password_hash'] = $request->password;
        }

        $usuario->update($data);

        return redirect()->back()->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();

        return redirect()->back()->with('success', 'Usuário removido com sucesso!');
    }

    private function buildPermissoes(Request $request): array
    {
        // If form provides modulos and scope, use them (new UI)
        if ($request->has('modulos')) {
            $modulos = [];
            foreach ($request->input('modulos', []) as $key => $value) {
                $modulos[$key] = (bool) $value;
            }

            return [
                'scope'   => $request->input('scope', 'operador'),
                'modulos' => $modulos,
            ];
        }

        // Fallback: generate defaults based on role
        $role = $request->input('role', 'solicitante');
        switch ($role) {
            case 'admin':
                return [
                    'scope'   => 'admin',
                    'modulos' => [
                        'criar_pedido' => true, 'pedidos' => true, 'historico' => true,
                        'relatorios' => true, 'transferencias' => true, 'usuarios' => true, 'itens' => true,
                    ],
                ];
            case 'comprador':
                return [
                    'scope'   => 'admin',
                    'modulos' => [
                        'criar_pedido' => true, 'pedidos' => true, 'historico' => true,
                        'relatorios' => true, 'transferencias' => true, 'itens' => true,
                    ],
                ];
            case 'aprovador':
                return [
                    'scope'   => 'admin',
                    'modulos' => ['pedidos' => true, 'historico' => true],
                ];
            default:
                return [
                    'scope'   => 'operador',
                    'modulos' => [
                        'criar_pedido' => true, 'pedidos' => true, 'historico' => true, 'transferencias' => true,
                    ],
                ];
        }
    }
}
