@extends('layouts.app')

@section('title', 'Gestão de Usuários')

@section('styles')
<style>
    .user-avatar {
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .role-badge {
        display: inline-flex;
        padding: 0.125rem 0.625rem;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.25rem;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .role-admin { background: #f3e8ff; color: #6b21a8; }
    .role-comprador { background: #dbeafe; color: #001A72; }
    .role-solicitante { background: #dcfce7; color: #166534; }
    .role-aprovador { background: #fef9c3; color: #854d0e; }

    .scope-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.125rem 0.625rem;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.25rem;
        border-radius: 9999px;
    }
    .scope-admin { background: #fef3c7; color: #92400e; }
    .scope-operador { background: #f1f5f9; color: #475569; }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.375rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: background 0.15s;
    }
    .action-btn-edit { color: var(--navy); background: #eff6ff; }
    .action-btn-edit:hover { background: #dbeafe; }
    .action-btn-delete { color: #ef4444; background: #fef2f2; }
    .action-btn-delete:hover { background: #fee2e2; }

    .scope-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
    .scope-option {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.625rem 0.75rem;
        border-radius: 0.5rem;
        border: 2px solid var(--slate-200);
        background: white;
        cursor: pointer;
        transition: all 0.15s;
        text-align: left;
    }
    .scope-option:hover { border-color: var(--slate-300); }
    .scope-option.active { border-color: var(--navy); background: #eff6ff; }
    .scope-option .scope-icon { color: var(--slate-400); font-size: 0.875rem; }
    .scope-option.active .scope-icon { color: var(--navy); }
    .scope-option .scope-title { font-size: 0.875rem; font-weight: 600; color: var(--slate-700); }
    .scope-option.active .scope-title { color: var(--navy); }
    .scope-option .scope-sub { font-size: 0.6875rem; color: var(--slate-400); margin-top:0.125rem; }

    .modulo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.375rem; }
    .modulo-toggle {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid var(--slate-200);
        background: white;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--slate-500);
        transition: all 0.15s;
    }
    .modulo-toggle:hover { border-color: var(--slate-300); }
    .modulo-toggle.active { border-color: var(--navy); background: #eff6ff; color: var(--navy); }
    .modulo-checkbox {
        width: 0.875rem;
        height: 0.875rem;
        border-radius: 0.25rem;
        border: 2px solid var(--slate-300);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.15s;
    }
    .modulo-toggle.active .modulo-checkbox { border-color: var(--navy); background: var(--navy); }
</style>
@endsection

@section('content')
@php
    $usuario = session('usuario');
    $isAdmin = $usuario->role === 'admin';

    $avatarColors = ['#001A72', '#059669', '#7c3aed', '#d97706', '#e11d48', '#0e7490', '#0d9488', '#ea580c'];

    function getAvatarColor($name) {
        $hash = 0;
        for ($i = 0; $i < strlen($name); $i++) {
            $hash = ord($name[$i]) + (($hash << 5) - $hash);
        }
        $colors = ['#001A72', '#059669', '#7c3aed', '#d97706', '#e11d48', '#0e7490', '#0d9488', '#ea580c'];
        return $colors[abs($hash) % count($colors)];
    }

    function getInitials($name) {
        $parts = explode(' ', $name);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $initials;
    }

    $roleClasses = [
        'admin' => 'role-admin',
        'comprador' => 'role-comprador',
        'solicitante' => 'role-solicitante',
        'aprovador' => 'role-aprovador',
    ];

    $moduloLabels = [
        'pedidos' => 'Pedidos',
        'criar_pedido' => 'Criar Pedido',
        'historico' => 'Histórico',
        'transferencias' => 'Transferências',
        'itens' => 'Itens',
        'relatorios' => 'Relatórios',
        'usuarios' => 'Usuários',
    ];
@endphp

{{-- Header --}}
<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:1.25rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.375rem; font-size:0.75rem; color:var(--slate-400); margin-bottom:0.5rem;">
            <a href="/" style="color:var(--slate-400); text-decoration:none;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size:0.6rem;"></i>
            <span style="color:var(--slate-700); font-weight:500;">Usuários</span>
        </div>
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="width:2.5rem; height:2.5rem; border-radius:0.75rem; background:#7c3aed; display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(0,0,0,.1); flex-shrink:0;">
                <i class="fas fa-users" style="color:white; font-size:1rem;"></i>
            </div>
            <div>
                <h1 style="font-size:1.25rem; font-weight:700; color:var(--slate-900); margin:0;">Gestão de Usuários</h1>
                <p style="font-size:0.75rem; color:var(--slate-400); margin:0.125rem 0 0;">
                    {{ count($usuarios) }} usuário{{ count($usuarios) !== 1 ? 's' : '' }} no sistema
                </p>
            </div>
        </div>
    </div>
    @if($isAdmin)
        <button class="rhc-btn rhc-btn-primary" data-bs-toggle="modal" data-bs-target="#novoUsuarioModal" style="flex-shrink:0;">
            <i class="fas fa-user-plus" style="margin-right:0.375rem;"></i> Novo Usuário
        </button>
    @endif
</div>

@include('components.flash-messages')

{{-- Main Card --}}
<div class="rhc-card" style="overflow:hidden;">
    {{-- Search --}}
    <div style="padding:1.5rem; border-bottom:1px solid var(--slate-100);">
        <div style="position:relative; width:100%; max-width:24rem;">
            <i class="fas fa-search" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--slate-400); font-size:0.8rem;"></i>
            <input type="text" id="userSearch" class="rhc-input" placeholder="Buscar por nome ou usuário..."
                   style="padding-left:2.5rem; width:100%;" onkeyup="filterUsers()">
        </div>
    </div>

    {{-- Table --}}
    <div class="rhc-table-wrap">
        <table class="rhc-table" id="usersTable">
            <thead>
                <tr>
                    <th>Utilizador</th>
                    <th>Nome</th>
                    <th>Nível</th>
                    <th>Visualização</th>
                    <th>Unidade</th>
                    <th>Criação</th>
                    <th style="text-align:right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $user)
                    @php
                        $permissoes = $user->permissoes ?? [];
                        $scope = $permissoes['scope'] ?? null;
                        $modulos = $permissoes['modulos'] ?? [];
                    @endphp
                    <tr data-nome="{{ strtolower($user->nome) }}" data-username="{{ strtolower($user->username) }}">
                        <td>
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <div class="user-avatar" style="background:{{ getAvatarColor($user->nome) }};">
                                    {{ getInitials($user->nome) }}
                                </div>
                                <span style="font-weight:600; color:var(--slate-800); font-size:0.875rem;">{{ $user->username }}</span>
                            </div>
                        </td>
                        <td style="font-size:0.875rem; color:var(--slate-600);">{{ $user->nome }}</td>
                        <td>
                            <span class="role-badge {{ $roleClasses[$user->role] ?? '' }}">{{ $user->role }}</span>
                        </td>
                        <td>
                            @if($scope)
                                <span class="scope-badge {{ $scope === 'admin' ? 'scope-admin' : 'scope-operador' }}">
                                    <i class="fas {{ $scope === 'admin' ? 'fa-shield-halved' : 'fa-eye' }}" style="font-size:0.6rem;"></i>
                                    {{ $scope === 'admin' ? 'Todos' : 'Próprios' }}
                                </span>
                            @else
                                <span style="color:var(--slate-400); font-size:0.75rem;">—</span>
                            @endif
                        </td>
                        <td style="font-size:0.875rem; color:var(--slate-500);">{{ $user->unidade->nome ?? '—' }}</td>
                        <td style="font-size:0.875rem; color:var(--slate-500);">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}</td>
                        <td style="text-align:right;">
                            @if($isAdmin)
                                <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.375rem;">
                                    <button class="action-btn action-btn-edit" data-bs-toggle="modal" data-bs-target="#editarUsuarioModal{{ $user->id }}">
                                        <i class="fas fa-pencil" style="font-size:0.65rem;"></i> Editar
                                    </button>
                                    @if($user->id !== $usuario->id)
                                        <form action="{{ route('usuarios.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('O usuário &quot;{{ $user->nome }}&quot; ({{ $user->username }}) será excluído permanentemente. Esta ação não pode ser desfeita.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn action-btn-delete">
                                                <i class="fas fa-trash" style="font-size:0.65rem;"></i> Excluir
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>

                    {{-- Modal Editar Usuário --}}
                    @if($isAdmin)
                    <div class="modal fade" id="editarUsuarioModal{{ $user->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('usuarios.update', $user->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content" style="border:0; border-radius:1rem; overflow:hidden;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid var(--slate-100);">
                                        <h2 style="font-size:1rem; font-weight:700; color:var(--slate-800); margin:0;">Editar Usuário</h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div style="padding:1rem 1.25rem; display:flex; flex-direction:column; gap:1rem;">
                                        {{-- Usuário + Senha --}}
                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                                            <div>
                                                <label class="rhc-label">Usuário (login) <span style="color:var(--red-500);">*</span></label>
                                                <input type="text" name="username" class="rhc-input" value="{{ $user->username }}" required placeholder="Ex: joao.silva">
                                            </div>
                                            <div>
                                                <label class="rhc-label">Nova Senha</label>
                                                <div style="position:relative;">
                                                    <input type="password" name="password" class="rhc-input password-toggle" placeholder="Deixe vazio para manter" style="padding-right:2.25rem;">
                                                    <button type="button" class="toggle-password-btn" style="position:absolute; right:0.625rem; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--slate-400); cursor:pointer; padding:0;">
                                                        <i class="fas fa-eye" style="font-size:0.8rem;"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Nome + Unidade --}}
                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                                            <div>
                                                <label class="rhc-label">Nome Completo <span style="color:var(--red-500);">*</span></label>
                                                <input type="text" name="nome" class="rhc-input" value="{{ $user->nome }}" required placeholder="Nome do colaborador">
                                            </div>
                                            <div>
                                                <label class="rhc-label">Unidade</label>
                                                <select name="unidade_id" class="rhc-select" style="width:100%;">
                                                    <option value="">Nenhuma</option>
                                                    @foreach($unidades as $u)
                                                        <option value="{{ $u->id }}" {{ $user->unidade_id == $u->id ? 'selected' : '' }}>{{ $u->nome }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        {{-- Nível de Acesso --}}
                                        <div>
                                            <label class="rhc-label">Nível de Acesso</label>
                                            <select name="role" class="rhc-select role-select" style="width:100%;" data-user-id="{{ $user->id }}" required>
                                                <option value="solicitante" {{ $user->role == 'solicitante' ? 'selected' : '' }}>Solicitante</option>
                                                <option value="aprovador" {{ $user->role == 'aprovador' ? 'selected' : '' }}>Aprovador</option>
                                                <option value="comprador" {{ $user->role == 'comprador' ? 'selected' : '' }}>Comprador</option>
                                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrador</option>
                                            </select>
                                        </div>
                                        {{-- Visualização --}}
                                        <div>
                                            <label class="rhc-label">Visualização de Pedidos</label>
                                            <div class="scope-selector">
                                                <label class="scope-option {{ ($scope ?? 'operador') === 'operador' ? 'active' : '' }}" data-scope="operador">
                                                    <input type="radio" name="scope" value="operador" {{ ($scope ?? 'operador') === 'operador' ? 'checked' : '' }} style="display:none;">
                                                    <i class="fas fa-eye scope-icon"></i>
                                                    <div>
                                                        <div class="scope-title">Operador</div>
                                                        <div class="scope-sub">Apenas seus pedidos</div>
                                                    </div>
                                                </label>
                                                <label class="scope-option {{ ($scope ?? '') === 'admin' ? 'active' : '' }}" data-scope="admin">
                                                    <input type="radio" name="scope" value="admin" {{ ($scope ?? '') === 'admin' ? 'checked' : '' }} style="display:none;">
                                                    <i class="fas fa-shield-halved scope-icon"></i>
                                                    <div>
                                                        <div class="scope-title">Admin</div>
                                                        <div class="scope-sub">Todos os pedidos</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        {{-- Módulos --}}
                                        <div>
                                            <label class="rhc-label">Acesso aos Módulos</label>
                                            <div class="modulo-grid">
                                                @foreach($moduloLabels as $key => $label)
                                                    <label class="modulo-toggle {{ ($modulos[$key] ?? false) ? 'active' : '' }}">
                                                        <input type="hidden" name="modulos[{{ $key }}]" value="0">
                                                        <input type="checkbox" name="modulos[{{ $key }}]" value="1" {{ ($modulos[$key] ?? false) ? 'checked' : '' }} style="display:none;">
                                                        <div class="modulo-checkbox">
                                                            <svg width="8" height="6" viewBox="0 0 10 8" fill="none" style="display:{{ ($modulos[$key] ?? false) ? 'block' : 'none' }};">
                                                                <path d="M1 4l3 3 5-6" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </div>
                                                        {{ $label }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <div style="display:flex; justify-content:flex-end; gap:0.5rem; padding:1rem 1.25rem; border-top:1px solid var(--slate-100);">
                                        <button type="button" class="rhc-btn rhc-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="rhc-btn rhc-btn-primary">
                                            <i class="fas fa-save" style="margin-right:0.25rem;"></i> Salvar Alterações
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                Nenhum usuário encontrado.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Novo Usuário --}}
@if($isAdmin)
<div class="modal fade" id="novoUsuarioModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('usuarios.store') }}" method="POST">
            @csrf
            <div class="modal-content" style="border:0; border-radius:1rem; overflow:hidden;">
                <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid var(--slate-100);">
                    <h2 style="font-size:1rem; font-weight:700; color:var(--slate-800); margin:0;">Novo Usuário</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div style="padding:1rem 1.25rem; display:flex; flex-direction:column; gap:1rem;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                        <div>
                            <label class="rhc-label">Usuário (login) <span style="color:var(--red-500);">*</span></label>
                            <input type="text" name="username" class="rhc-input" required placeholder="Ex: joao.silva">
                        </div>
                        <div>
                            <label class="rhc-label">Senha <span style="color:var(--red-500);">*</span></label>
                            <div style="position:relative;">
                                <input type="password" name="password" class="rhc-input password-toggle" required minlength="4" placeholder="Senha de acesso" style="padding-right:2.25rem;">
                                <button type="button" class="toggle-password-btn" style="position:absolute; right:0.625rem; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--slate-400); cursor:pointer; padding:0;">
                                    <i class="fas fa-eye" style="font-size:0.8rem;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                        <div>
                            <label class="rhc-label">Nome Completo <span style="color:var(--red-500);">*</span></label>
                            <input type="text" name="nome" class="rhc-input" required placeholder="Nome do colaborador">
                        </div>
                        <div>
                            <label class="rhc-label">Unidade</label>
                            <select name="unidade_id" class="rhc-select" style="width:100%;">
                                <option value="">Nenhuma</option>
                                @foreach($unidades as $u)
                                    <option value="{{ $u->id }}">{{ $u->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="rhc-label">Nível de Acesso</label>
                        <select name="role" class="rhc-select role-select" style="width:100%;" required>
                            <option value="solicitante">Solicitante</option>
                            <option value="aprovador">Aprovador</option>
                            <option value="comprador">Comprador</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div>
                        <label class="rhc-label">Visualização de Pedidos</label>
                        <div class="scope-selector">
                            <label class="scope-option active" data-scope="operador">
                                <input type="radio" name="scope" value="operador" checked style="display:none;">
                                <i class="fas fa-eye scope-icon"></i>
                                <div>
                                    <div class="scope-title">Operador</div>
                                    <div class="scope-sub">Apenas seus pedidos</div>
                                </div>
                            </label>
                            <label class="scope-option" data-scope="admin">
                                <input type="radio" name="scope" value="admin" style="display:none;">
                                <i class="fas fa-shield-halved scope-icon"></i>
                                <div>
                                    <div class="scope-title">Admin</div>
                                    <div class="scope-sub">Todos os pedidos</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="rhc-label">Acesso aos Módulos</label>
                        <div class="modulo-grid">
                            @foreach($moduloLabels as $key => $label)
                                @php $defaultOn = in_array($key, ['pedidos', 'criar_pedido', 'historico', 'transferencias']); @endphp
                                <label class="modulo-toggle {{ $defaultOn ? 'active' : '' }}">
                                    <input type="hidden" name="modulos[{{ $key }}]" value="0">
                                    <input type="checkbox" name="modulos[{{ $key }}]" value="1" {{ $defaultOn ? 'checked' : '' }} style="display:none;">
                                    <div class="modulo-checkbox">
                                        <svg width="8" height="6" viewBox="0 0 10 8" fill="none" style="display:{{ $defaultOn ? 'block' : 'none' }};">
                                            <path d="M1 4l3 3 5-6" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:0.5rem; padding:1rem 1.25rem; border-top:1px solid var(--slate-100);">
                    <button type="button" class="rhc-btn rhc-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="rhc-btn rhc-btn-primary">
                        <i class="fas fa-user-plus" style="margin-right:0.25rem;"></i> Criar Usuário
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
function filterUsers() {
    const term = document.getElementById('userSearch').value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr[data-nome]').forEach(row => {
        const nome = row.getAttribute('data-nome');
        const username = row.getAttribute('data-username');
        row.style.display = (nome.includes(term) || username.includes(term)) ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password-btn').forEach(btn => {
        btn.addEventListener('mousedown', function() {
            const input = this.parentElement.querySelector('.password-toggle');
            input.type = 'text';
            this.querySelector('i').className = 'fas fa-eye-slash';
        });
        btn.addEventListener('mouseup', function() {
            const input = this.parentElement.querySelector('.password-toggle');
            input.type = 'password';
            this.querySelector('i').className = 'fas fa-eye';
        });
        btn.addEventListener('mouseleave', function() {
            const input = this.parentElement.querySelector('.password-toggle');
            input.type = 'password';
            this.querySelector('i').className = 'fas fa-eye';
        });
    });

    // Scope selector
    document.querySelectorAll('.scope-option').forEach(option => {
        option.addEventListener('click', function() {
            const container = this.closest('.scope-selector');
            container.querySelectorAll('.scope-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input[type=radio]').checked = true;
        });
    });

    // Module toggles
    document.querySelectorAll('.modulo-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const checkbox = this.querySelector('input[type=checkbox]');
            checkbox.checked = !checkbox.checked;
            this.classList.toggle('active', checkbox.checked);
            const svg = this.querySelector('svg');
            svg.style.display = checkbox.checked ? 'block' : 'none';
        });
    });
});
</script>
@endsection
