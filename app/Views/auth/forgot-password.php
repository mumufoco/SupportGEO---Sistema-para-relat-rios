<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - GeoSPT Manager</title>
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
        .forgot-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.175);
            padding: 3rem;
            max-width: 500px;
            width: 100%;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
        }
        .icon-circle i {
            font-size: 2.5rem;
            color: white;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="icon-circle">
            <i class="bi bi-key"></i>
        </div>

        <h3 class="text-center fw-bold mb-2">Esqueceu sua senha?</h3>
        <p class="text-center text-muted mb-4">
            Entre em contato com o administrador do sistema para recuperar seu acesso.
        </p>

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Suporte:</strong><br>
            E-mail: suporte@supportsolosondagens.com.br<br>
            Telefone: (XX) XXXX-XXXX
        </div>

        <div class="d-grid gap-2">
            <a href="<?= base_url('login') ?>" class="btn btn-success">
                <i class="bi bi-arrow-left me-2"></i>Voltar ao Login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
