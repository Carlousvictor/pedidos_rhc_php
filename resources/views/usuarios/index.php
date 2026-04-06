<?php
$__title = 'Gestão de Usuários';
ob_start();
?>
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
<?php $__styles = ob_get_clean();
ob_start();
?>
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
<?php $__scripts = ob_get_clean();
include __DIR__ . '/../layouts/header.php';
?>
<?php 
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
 ?>


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
                    <?= e(count($usuarios)) ?> usuário<?= e(count($usuarios) !== 1 ? 's' : '') ?> no sistema
                </p>
            </div>
        </div>
    </div>
    <?php if ($isAdmin): ?>
        <button class="rhc-btn rhc-btn-primary" data-bs-toggle="modal" data-bs-target="#novoUsuarioModal" style="flex-shrink:0;">
            <i class="fas fa-user-plus" style="margin-right:0.375rem;"></i> Novo Usuário
        </button>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../components/flash-messages.php'; ?>


<div class="rhc-card" style="overflow:hidden;">
    
    <div style="padding:1.5rem; border-bottom:1px solid var(--slate-100);">
        <div style="position:relative; width:100%; max-width:24rem;">
            <i class="fas fa-search" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--slate-400); font-size:0.8rem;"></i>
            <input type="text" id="userSearch" class="rhc-input" placeholder="Buscar por nome ou usuário..."
                   style="padding-left:2.5rem; width:100%;" onkeyup="filterUsers()">
        </div>
    </div>

    
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
                <?php if (count($usuarios) > 0): ?><?php foreach ($usuarios as $user): ?>
                    <?php 
                        $permissoes = $user->permissoes ?? [];
                        $scope = $permissoes['scope'] ?? null;
                        $modulos = $permissoes['modulos'] ?? [];
                     ?>
                    <tr data-nome="<?= e(strtolower($user->nome)) ?>" data-username="<?= e(strtolower($user->username)) ?>">
                        <td>
                            <div style="display:flex; align-items:center; gap:0.75rem;">
                                <div class="user-avatar" style="background:<?= e(getAvatarColor($user->nome)) ?>;">
                                    <?= e(getInitials($user->nome)) ?>
                                </div>
                                <span style="font-weight:600; color:var(--slate-800); font-size:0.875rem;"><?= e($user->username) ?></span>
                            </div>
                        </td>
                        <td style="font-size:0.875rem; color:var(--slate-600);"><?= e($user->nome) ?></td>
                        <td>
                            <span class="role-badge <?= e($roleClasses[$user->role] ?? '') ?>"><?= e($user->role) ?></span>
                        </td>
                        <td>
                            <?php if ($scope): ?>
                                <span class="scope-badge <?= e($scope === 'admin' ? 'scope-admin' : 'scope-operador') ?>">
                                    <i class="fas <?= e($scope === 'admin' ? 'fa-shield-halved' : 'fa-eye') ?>" style="font-size:0.6rem;"></i>
                                    <?= e($scope === 'admin' ? 'Todos' : 'Próprios') ?>
                                </span>
                            <?php else: ?>
                                <span style="color:var(--slate-400); font-size:0.75rem;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.875rem; color:var(--slate-500);"><?= e($user->unidade->nome ?? '—') ?></td>
                        <td style="font-size:0.875rem; color:var(--slate-500);"><?= e($user->created_at ? $user->created_at->format('d/m/Y') : '—') ?></td>
                        <td style="text-align:right;">
                            <?php if ($isAdmin): ?>
                                <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.375rem;">
                                    <button class="action-btn action-btn-edit" data-bs-toggle="modal" data-bs-target="#editarUsuarioModal<?= e($user->id) ?>">
                                        <i class="fas fa-pencil" style="font-size:0.65rem;"></i> Editar
                                    </button>
                                    <?php if ($user->id !== $usuario->id): ?>
                                        <form action="<?= e(route('usuarios.destroy', $user->id)) ?>" method="POST" class="d-inline" onsubmit="return confirm('O usuário &quot;<?= e($user->nome) ?>&quot; (<?= e($user->username) ?>) será excluído permanentemente. Esta ação não pode ser desfeita.');">
                                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="action-btn action-btn-delete">
                                                <i class="fas fa-trash" style="font-size:0.65rem;"></i> Excluir
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>

                    
                    <?php if ($isAdmin): ?>
                    <div class="modal fade" id="editarUsuarioModal<?= e($user->id) ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="<?= e(route('usuarios.update', $user->id)) ?>" method="POST">
                                <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="_method" value="PUT">
                                <div class="modal-content" style="border:0; border-radius:1rem; overflow:hidden;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid var(--slate-100);">
                                        <h2 style="font-size:1rem; font-weight:700; color:var(--slate-800); margin:0;">Editar Usuário</h2>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div style="padding:1rem 1.25rem; display:flex; flex-direction:column; gap:1rem;">
                                        
                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                                            <div>
                                                <label class="rhc-label">Usuário (login) <span style="color:var(--red-500);">*</span></label>
                                                <input type="text" name="username" class="rhc-input" value="<?= e($user->username) ?>" required placeholder="Ex: joao.silva">
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
                                        
                                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                                            <div>
                                                <label class="rhc-label">Nome Completo <span style="color:var(--red-500);">*</span></label>
                                                <input type="text" name="nome" class="rhc-input" value="<?= e($user->nome) ?>" required placeholder="Nome do colaborador">
                                            </div>
                                            <div>
                                                <label class="rhc-label">Unidade</label>
                                                <select name="unidade_id" class="rhc-select" style="width:100%;">
                                                    <option value="">Nenhuma</option>
                                                    <?php foreach ($unidades as $u): ?>
                                                        <option value="<?= e($u->id) ?>" <?= e($user->unidade_id == $u->id ? 'selected' : '') ?>><?= e($u->nome) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label class="rhc-label">Nível de Acesso</label>
                                            <select name="role" class="rhc-select role-select" style="width:100%;" data-user-id="<?= e($user->id) ?>" required>
                                                <option value="solicitante" <?= e($user->role == 'solicitante' ? 'selected' : '') ?>>Solicitante</option>
                                                <option value="aprovador" <?= e($user->role == 'aprovador' ? 'selected' : '') ?>>Aprovador</option>
                                                <option value="comprador" <?= e($user->role == 'comprador' ? 'selected' : '') ?>>Comprador</option>
                                                <option value="admin" <?= e($user->role == 'admin' ? 'selected' : '') ?>>Administrador</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="rhc-label">Visualização de Pedidos</label>
                                            <div class="scope-selector">
                                                <label class="scope-option <?= e(($scope ?? 'operador') === 'operador' ? 'active' : '') ?>" data-scope="operador">
                                                    <input type="radio" name="scope" value="operador" <?= e(($scope ?? 'operador') === 'operador' ? 'checked' : '') ?> style="display:none;">
                                                    <i class="fas fa-eye scope-icon"></i>
                                                    <div>
                                                        <div class="scope-title">Operador</div>
                                                        <div class="scope-sub">Apenas seus pedidos</div>
                                                    </div>
                                                </label>
                                                <label class="scope-option <?= e(($scope ?? '') === 'admin' ? 'active' : '') ?>" data-scope="admin">
                                                    <input type="radio" name="scope" value="admin" <?= e(($scope ?? '') === 'admin' ? 'checked' : '') ?> style="display:none;">
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
                                                <?php foreach ($moduloLabels as $key => $label): ?>
                                                    <label class="modulo-toggle <?= e(($modulos[$key] ?? false) ? 'active' : '') ?>">
                                                        <input type="hidden" name="modulos[<?= e($key) ?>]" value="0">
                                                        <input type="checkbox" name="modulos[<?= e($key) ?>]" value="1" <?= e(($modulos[$key] ?? false) ? 'checked' : '') ?> style="display:none;">
                                                        <div class="modulo-checkbox">
                                                            <svg width="8" height="6" viewBox="0 0 10 8" fill="none" style="display:<?= e(($modulos[$key] ?? false) ? 'block' : 'none') ?>;">
                                                                <path d="M1 4l3 3 5-6" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </div>
                                                        <?= e($label) ?>
                                                    </label>
                                                <?php endforeach; ?>
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
                    <?php endif; ?>
                <?php endforeach; ?><?php else: ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                Nenhum usuário encontrado.
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php if ($isAdmin): ?>
<div class="modal fade" id="novoUsuarioModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= e(route('usuarios.store')) ?>" method="POST">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
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
                                <?php foreach ($unidades as $u): ?>
                                    <option value="<?= e($u->id) ?>"><?= e($u->nome) ?></option>
                                <?php endforeach; ?>
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
                            <?php foreach ($moduloLabels as $key => $label): ?>
                                <?php  $defaultOn = in_array($key, ['pedidos', 'criar_pedido', 'historico', 'transferencias']);  ?>
                                <label class="modulo-toggle <?= e($defaultOn ? 'active' : '') ?>">
                                    <input type="hidden" name="modulos[<?= e($key) ?>]" value="0">
                                    <input type="checkbox" name="modulos[<?= e($key) ?>]" value="1" <?= e($defaultOn ? 'checked' : '') ?> style="display:none;">
                                    <div class="modulo-checkbox">
                                        <svg width="8" height="6" viewBox="0 0 10 8" fill="none" style="display:<?= e($defaultOn ? 'block' : 'none') ?>;">
                                            <path d="M1 4l3 3 5-6" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <?= e($label) ?>
                                </label>
                            <?php endforeach; ?>
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
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
