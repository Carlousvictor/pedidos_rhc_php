<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title>Login - RHC Pedidos</title>
    <link rel="icon" type="image/png" href="/logo.png">

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            margin: 1rem;
        }

        /* Logo area */
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border-radius: 0.75rem;
            padding: 0.75rem 1.25rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
            border: 1px solid #f1f5f9;
            margin-bottom: 0.75rem;
        }
        .login-logo-box img {
            height: 44px;
            width: auto;
            object-fit: contain;
        }
        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }
        .login-subtitle {
            font-size: 0.875rem;
            color: #64748b;
        }

        /* Card */
        .login-card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.07), 0 2px 4px -2px rgba(0,0,0,.05);
            border: 1px solid #f1f5f9;
            padding: 2rem;
        }

        /* Error */
        .login-error {
            padding: 0.75rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 0.5rem;
            color: #dc2626;
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #334155;
            margin-bottom: 0.375rem;
        }
        .input-wrapper {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            pointer-events: none;
        }
        .form-input {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.625rem 1rem 0.625rem 2.75rem;
            font-size: 0.875rem;
            font-family: inherit;
            color: #1e293b;
            transition: border-color 0.15s, background-color 0.15s, box-shadow 0.15s;
        }
        .form-input:focus {
            outline: none;
            border-color: #001A72;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(0,26,114,0.1);
        }
        .form-input::placeholder {
            color: #94a3b8;
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.25rem;
            transition: color 0.15s;
        }
        .password-toggle:hover {
            color: #001A72;
        }

        /* Submit */
        .login-btn {
            width: 100%;
            background-color: #001A72;
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 0.9375rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background-color 0.15s;
            margin-top: 0.5rem;
        }
        .login-btn:hover {
            background-color: #001250;
        }
        .login-btn:active {
            background-color: #000e3d;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo -->
        <div class="login-logo">
            <div class="login-logo-box">
                <img src="/logo.png" alt="RHC" width="160" height="48">
            </div>
            <h1 class="login-title">RHC Pedidos</h1>
            <p class="login-subtitle">Sistema de Pedidos Hospitalares</p>
        </div>

        <!-- Card -->
        <div class="login-card">
            <!-- Error messages -->
            <?php if ($errors->any()): ?>
                <div class="login-error">
                    <i class="fas fa-circle-exclamation" style="margin-right:0.25rem;"></i>
                    <?php foreach ($errors->all() as $erro): ?>
                        <?= e($erro) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (session('error')): ?>
                <div class="login-error">
                    <i class="fas fa-circle-exclamation" style="margin-right:0.25rem;"></i>
                    <?= e(session('error')) ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" autocomplete="off">
                <input type="hidden" name="_token" value="<?= csrf_token() ?>">

                <div class="form-group">
                    <label class="form-label" for="username">Usuário</label>
                    <div class="input-wrapper">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <input type="text"
                               class="form-input"
                               id="username"
                               name="username"
                               placeholder="Digite seu usuário"
                               value="<?= e(old('username')) ?>"
                               required
                               autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Senha</label>
                    <div class="input-wrapper">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password"
                               class="form-input"
                               id="password"
                               name="password"
                               placeholder="Digite sua senha"
                               required
                               style="padding-right: 2.5rem;">
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-right-to-bracket"></i>
                    Entrar
                </button>
            </form>
        </div>

        <div class="login-footer">
            Sistema de Pedidos Internos RHC v1.0
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        toggleBtn.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleIcon.classList.toggle('fa-eye');
            toggleIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
