<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($__title ?? "RHC Pedidos") ?></title>
    <link rel="icon" type="image/png" href="/logo.png">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>

        :root {
            --navy: #001A72;
            --navy-dark: #001250;
            --bg: #F3F4F6;
            --white: #ffffff;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --red-500: #ef4444;
            --green-500: #22c55e;
            --green-600: #16a34a;
            --yellow-400: #facc15;
            --yellow-800: #854d0e;
            --orange-400: #fb923c;
            --orange-500: #f97316;
            --orange-800: #9a3412;
            --amber-500: #f59e0b;
            --amber-800: #92400e;
            --blue-100: #dbeafe;
            --purple-500: #a855f7;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg);
            color: var(--slate-900);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
        }

        /* ===== NAVBAR ===== */
        .rhc-navbar {
            min-height: 64px;
            background-color: var(--navy);
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1);
            display: flex;
            align-items: center;
            padding: 0 1rem;
        }
        .rhc-navbar-inner {
            display: flex;
            align-items: center;
            width: 100%;
            gap: 0.75rem;
        }
        .rhc-logo-area {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            flex-shrink: 0;
        }
        .rhc-logo-box {
            background: var(--white);
            border-radius: 0.5rem;
            padding: 0.25rem 0.5rem;
            display: flex;
            align-items: center;
        }
        .rhc-logo-box img {
            height: 36px;
            width: auto;
            object-fit: contain;
        }
        .rhc-logo-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--white);
            letter-spacing: -0.025em;
            white-space: nowrap;
        }

        /* Nav links */
        .rhc-nav-links {
            display: flex;
            align-items: center;
            gap: 0.125rem;
            list-style: none;
            flex-wrap: nowrap;
            margin: 0;
            padding: 0;
        }
        .rhc-nav-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.3rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.7rem;
            font-weight: 500;
            transition: background-color 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .rhc-nav-link:hover {
            background-color: var(--navy-dark);
            color: var(--white);
        }
        .rhc-nav-link.active {
            background-color: var(--navy-dark);
            color: var(--white);
        }

        /* Right side */
        .rhc-nav-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }
        .rhc-role-badge {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            color: var(--white);
        }
        .rhc-role-admin { background-color: var(--purple-500); }
        .rhc-role-comprador { background-color: #60a5fa; }
        .rhc-role-solicitante { background-color: var(--green-500); }
        .rhc-role-aprovador { background-color: var(--orange-500); }

        .rhc-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 9999px;
            background-color: var(--orange-500);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .rhc-user-name {
            color: rgba(255,255,255,0.9);
            font-size: 0.8rem;
            font-weight: 500;
        }
        .rhc-logout-btn {
            background-color: var(--navy-dark);
            color: rgba(255,255,255,0.85);
            border: none;
            border-radius: 9999px;
            padding: 0.375rem 1rem;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            transition: background-color 0.15s;
        }
        .rhc-logout-btn:hover {
            background-color: rgba(0,18,80,0.8);
            color: var(--white);
        }

        /* Mobile toggle */
        .rhc-mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.25rem;
        }
        .rhc-nav-collapse { display: contents; flex: 1; min-width: 0; }

        @media (max-width: 1024px) {
            .rhc-mobile-toggle { display: block; }
            .rhc-nav-collapse {
                display: none;
                position: absolute;
                top: 64px;
                left: 0;
                right: 0;
                background: var(--navy);
                padding: 0.75rem 1rem;
                flex-direction: column;
                box-shadow: 0 4px 6px rgba(0,0,0,0.15);
            }
            .rhc-nav-collapse.open {
                display: flex;
            }
            .rhc-nav-links {
                flex-direction: column;
                align-items: stretch;
            }
            .rhc-nav-right {
                margin-left: 0;
                padding-top: 0.75rem;
                border-top: 1px solid rgba(255,255,255,0.1);
                margin-top: 0.5rem;
            }
        }

        /* ===== MAIN CONTENT ===== */
        .rhc-main {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 1.5rem 2rem;
        }

        /* ===== CARDS ===== */
        .rhc-card {
            background: var(--white);
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
            border: 1px solid var(--slate-100);
        }

        /* ===== STATUS BADGES ===== */
        .rhc-status {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.625rem;
            font-size: 11px;
            line-height: 1.25rem;
            font-weight: 600;
            border-radius: 9999px;
            text-transform: capitalize;
        }
        .rhc-status-aguardando { background: #fef9c3; color: var(--yellow-800); }
        .rhc-status-pendente { background: #ffedd5; color: var(--orange-800); }
        .rhc-status-cotacao { background: #fef3c7; color: var(--amber-800); }
        .rhc-status-realizado { background: var(--blue-100); color: var(--navy); }
        .rhc-status-recebido { background: #dcfce7; color: #166534; }

        /* ===== BUTTONS ===== */
        .rhc-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.15s, color 0.15s;
            line-height: 1.25rem;
        }
        .rhc-btn-primary {
            background-color: var(--navy);
            color: var(--white);
        }
        .rhc-btn-primary:hover {
            background-color: var(--navy-dark);
            color: var(--white);
        }
        .rhc-btn-outline {
            background: transparent;
            color: var(--navy);
            border: 1px solid var(--navy);
        }
        .rhc-btn-outline:hover {
            background: #eff6ff;
            color: var(--navy);
        }
        .rhc-btn-ghost {
            background: var(--slate-100);
            color: var(--slate-600);
        }
        .rhc-btn-ghost:hover {
            background: var(--slate-200);
            color: var(--slate-700);
        }
        .rhc-btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        /* ===== FORMS ===== */
        .rhc-input {
            width: 100%;
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 0.5rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            color: var(--slate-800);
            font-family: inherit;
            transition: border-color 0.15s, background-color 0.15s, box-shadow 0.15s;
        }
        .rhc-input:focus {
            outline: none;
            border-color: var(--navy);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(0,26,114,0.1);
        }
        .rhc-input::placeholder {
            color: var(--slate-400);
        }
        .rhc-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--slate-700);
            margin-bottom: 0.375rem;
        }
        .rhc-select {
            width: 100%;
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 0.5rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            color: var(--slate-800);
            font-family: inherit;
            cursor: pointer;
        }
        .rhc-select:focus {
            outline: none;
            border-color: var(--navy);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(0,26,114,0.1);
        }

        /* ===== TABLE ===== */
        .rhc-table-wrap {
            overflow-x: auto;
        }
        .rhc-table {
            width: 100%;
            border-collapse: collapse;
        }
        .rhc-table thead {
            background: var(--slate-50);
        }
        .rhc-table th {
            padding: 0.875rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--slate-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            border-bottom: 1px solid var(--slate-100);
        }
        .rhc-table td {
            padding: 0.875rem 1.5rem;
            font-size: 0.875rem;
            color: var(--slate-600);
            border-bottom: 1px solid var(--slate-100);
            vertical-align: middle;
        }
        .rhc-table tbody tr {
            transition: background-color 0.15s;
        }
        .rhc-table tbody tr:hover {
            background-color: rgba(248,250,252,0.5);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--slate-400);
        }
        .empty-state i {
            font-size: 2.5rem;
            color: var(--slate-200);
            margin-bottom: 0.75rem;
            display: block;
        }

        /* ===== FLASH MESSAGES ===== */
        .rhc-flash {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .rhc-flash-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .rhc-flash-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .rhc-flash-warning {
            background: #fef3c7;
            color: var(--amber-800);
            border: 1px solid #fde68a;
        }

        /* ===== PAGINATION ===== */
        .rhc-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            margin-top: 1.5rem;
        }
        .rhc-pagination a,
        .rhc-pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            padding: 0 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.15s;
        }
        .rhc-pagination a {
            color: var(--slate-600);
            border: 1px solid var(--slate-200);
        }
        .rhc-pagination a:hover {
            background: var(--slate-50);
            color: var(--navy);
        }
        .rhc-pagination .active-page {
            background: var(--navy);
            color: var(--white);
            border: 1px solid var(--navy);
        }
        .rhc-pagination .disabled-page {
            color: var(--slate-300);
            border: 1px solid var(--slate-100);
            pointer-events: none;
        }

        /* ===== BOOTSTRAP OVERRIDES ===== */
        .btn-primary {
            background-color: var(--navy) !important;
            border-color: var(--navy) !important;
        }
        .btn-primary:hover {
            background-color: var(--navy-dark) !important;
            border-color: var(--navy-dark) !important;
        }
        .btn-outline-primary {
            color: var(--navy) !important;
            border-color: var(--navy) !important;
        }
        .btn-outline-primary:hover {
            background-color: var(--navy) !important;
            border-color: var(--navy) !important;
            color: var(--white) !important;
        }
        .text-primary {
            color: var(--navy) !important;
        }
        .bg-primary {
            background-color: var(--navy) !important;
        }
        .border-primary {
            border-color: var(--navy) !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--navy);
            box-shadow: 0 0 0 0.2rem rgba(0,26,114,0.15);
        }
        a {
            color: var(--navy);
        }
        a:hover {
            color: var(--navy-dark);
        }
        .badge.bg-primary {
            background-color: var(--navy) !important;
        }
        .progress-bar.bg-primary {
            background-color: var(--navy) !important;
        }

        /* ===== UTILITIES ===== */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-slate-400 { color: var(--slate-400); }
        .text-slate-500 { color: var(--slate-500); }
        .text-navy { color: var(--navy); }
        .font-mono { font-family: ui-monospace, SFMono-Regular, 'Cascadia Code', monospace; }
        .font-bold { font-weight: 700; }
        .font-semibold { font-weight: 600; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-6 { margin-top: 1.5rem; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 0.75rem; }
        .gap-4 { gap: 1rem; }
        .flex { display: flex; }
        .flex-wrap { flex-wrap: wrap; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .grid { display: grid; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-6 { grid-template-columns: repeat(6, 1fr); }
        @media (max-width: 1024px) {
            .grid-6 { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 640px) {
            .grid-6 { grid-template-columns: repeat(2, 1fr); }
            .rhc-main { padding: 1rem; }
        }
    </style>

    <?= $__styles ?? "" ?>
</head>
<body>
    <?php 
        $usuario = session('usuario');
        $modulos = $usuario->permissoes['modulos'] ?? [];
     ?>

    <!-- Navbar -->
    <nav class="rhc-navbar">
        <div class="rhc-navbar-inner">
            <!-- Logo -->
            <a href="/" class="rhc-logo-area">
                <span class="rhc-logo-box">
                    <img src="/logo.png" alt="RHC" width="120" height="36">
                </span>
                <span class="rhc-logo-title">RHC Pedidos</span>
            </a>

            <!-- Mobile Toggle -->
            <button class="rhc-mobile-toggle" onclick="document.getElementById('navCollapse').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Collapsible content -->
            <div class="rhc-nav-collapse" id="navCollapse">
                <!-- Nav links -->
                <ul class="rhc-nav-links">
                    <li>
                        <a class="rhc-nav-link <?= e(Request::is('/') ? 'active' : '') ?>" href="/">
                            <i class="fas fa-list fa-sm"></i> Pedidos
                        </a>
                    </li>
                    <?php if ($modulos['criar_pedido'] ?? false): ?>
                    <li>
                        <a class="rhc-nav-link <?= e(Request::is('pedidos/novo') ? 'active' : '') ?>" href="/pedidos/novo">
                            <i class="fas fa-plus-circle fa-sm"></i> Novo Pedido
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($modulos['historico'] ?? false): ?>
                    <li>
                        <a class="rhc-nav-link <?= e(Request::is('historico*') ? 'active' : '') ?>" href="/historico">
                            <i class="fas fa-clock-rotate-left fa-sm"></i> Histórico
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($modulos['itens'] ?? false): ?>
                    <li>
                        <a class="rhc-nav-link <?= e(Request::is('itens*') ? 'active' : '') ?>" href="/itens">
                            <i class="fas fa-boxes-stacked fa-sm"></i> Itens
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($modulos['relatorios'] ?? false): ?>
                    <li>
                        <a class="rhc-nav-link <?= e(Request::is('relatorios*') ? 'active' : '') ?>" href="/relatorios">
                            <i class="fas fa-chart-bar fa-sm"></i> Relatórios
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($modulos['transferencias'] ?? false): ?>
                    <li>
                        <a class="rhc-nav-link <?= e(Request::is('transferencias*') ? 'active' : '') ?>" href="/transferencias">
                            <i class="fas fa-right-left fa-sm"></i> Transferências
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if ($modulos['usuarios'] ?? false): ?>
                    <li>
                        <a class="rhc-nav-link <?= e(Request::is('usuarios*') ? 'active' : '') ?>" href="/usuarios">
                            <i class="fas fa-users-gear fa-sm"></i> Usuários
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a class="rhc-nav-link <?= e(Request::is('ajuda*') ? 'active' : '') ?>" href="/ajuda">
                            <i class="fas fa-circle-question fa-sm"></i> Ajuda
                        </a>
                    </li>
                </ul>

                <!-- Right side -->
                <div class="rhc-nav-right">
                    <span class="rhc-role-badge rhc-role-<?= e($usuario->role) ?>"><?= e($usuario->role) ?></span>
                    <span class="rhc-user-avatar"><?= e(strtoupper(substr($usuario->nome, 0, 1))) ?></span>
                    <span class="rhc-user-name"><?= e($usuario->nome) ?></span>
                    <form action="/logout" method="POST" style="margin:0;">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                        <button type="submit" class="rhc-logout-btn">
                            <i class="fas fa-right-from-bracket fa-sm"></i>
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="rhc-main" style="padding-bottom: 0;">
        <?php if (session('success')): ?>
            <div class="rhc-flash rhc-flash-success">
                <i class="fas fa-check-circle"></i> <?= e(session('success')) ?>
            </div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="rhc-flash rhc-flash-error">
                <i class="fas fa-circle-exclamation"></i> <?= e(session('error')) ?>
            </div>
        <?php endif; ?>
        <?php if (session('warning')): ?>
            <div class="rhc-flash rhc-flash-warning">
                <i class="fas fa-triangle-exclamation"></i> <?= e(session('warning')) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div class="rhc-main">
        