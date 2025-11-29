<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GeoSPT Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.175);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }
        .login-left {
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-right {
            padding: 3rem;
        }
        .logo-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #146c43 0%, #0d5132 100%);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="login-container">
                    <div class="row g-0">
                        <div class="col-lg-5 login-left d-none d-lg-block">
                            <div class="text-center">
                                <i class="bi bi-geo-alt-fill logo-icon"></i>
                                <h2 class="fw-bold mb-3">GeoSPT Manager</h2>
                                <p class="lead mb-4">Sistema de Gestão de Sondagens SPT</p>
                                <div class="text-start">
                                    <p class="mb-2"><i class="bi bi-check-circle me-2"></i> Conforme NBR 6484:2020</p>
                                    <p class="mb-2"><i class="bi bi-check-circle me-2"></i> Geração automática de PDF</p>
                                    <p class="mb-2"><i class="bi bi-check-circle me-2"></i> Importação de Excel</p>
                                    <p class="mb-2"><i class="bi bi-check-circle me-2"></i> Controle de qualidade</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 login-right">
                            <div class="d-lg-none text-center mb-4">
                                <i class="bi bi-geo-alt-fill text-success" style="font-size: 3rem;"></i>
                                <h3 class="fw-bold text-success">GeoSPT Manager</h3>
                            </div>

                            <h3 class="fw-bold mb-1">Bem-vindo!</h3>
                            <p class="text-muted mb-4">Faça login para continuar</p>

                            <?php if (session()->getFlashdata('erro')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?= session()->getFlashdata('erro') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>

                            <?php if (session()->getFlashdata('sucesso')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                <?= session()->getFlashdata('sucesso') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>

                            <form id="formLogin" method="POST" action="<?= base_url('auth/login') ?>">
                                <?= csrf_field() ?>

                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" id="email" name="email"
                                               placeholder="seu@email.com" required autofocus>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Senha</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password"
                                               placeholder="••••••••" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye" id="eyeIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        Lembrar de mim
                                    </label>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-success btn-login">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                                    </button>
                                </div>

                                <div class="text-center">
                                    <a href="<?= base_url('auth/forgot-password') ?>" class="text-decoration-none text-success">
                                        Esqueceu sua senha?
                                    </a>
                                </div>
                            </form>

                            <hr class="my-4">

                            <div class="text-center text-muted small">
                                <p class="mb-0">Support Solo Sondagens Ltda</p>
                                <p class="mb-0">© <?= date('Y') ?> - Todos os direitos reservados</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });

        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                setTimeout(() => bsAlert.close(), 5000);
            });
        }, 100);
    </script>
</body>
</html>
