<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('usuario')) {
            return redirect('/');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = Usuario::where('username', $request->username)->first();

        if (!$user || $request->password !== $user->password_hash) {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors(['login' => 'Usuário ou senha inválidos']);
        }

        $user->load('unidade');

        $sessionData = (object) [
            'id' => $user->id,
            'nome' => $user->nome,
            'username' => $user->username,
            'role' => $user->role,
            'unidade_id' => $user->unidade_id,
            'unidade_nome' => $user->unidade?->nome,
            'permissoes' => $user->permissoes ?? [],
        ];

        session(['usuario' => $sessionData]);

        return redirect('/')->with('success', 'Login realizado com sucesso!');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();

        return redirect('/login');
    }
}
