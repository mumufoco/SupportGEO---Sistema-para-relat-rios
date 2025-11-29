<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= $title ?? 'GeoSPT Manager' ?> - Support Solo Sondagens</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">

    <?= $this->renderSection('styles') ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="<?= base_url('admin/dashboard') ?>">
                <i class="bi bi-geo-alt-fill me-2"></i>GeoSPT Manager
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/dashboard') ?>">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/sondagens') ?>">
                            <i class="bi bi-geo-alt"></i> Sondagens
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/projetos') ?>">
                            <i class="bi bi-folder"></i> Projetos
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?= session()->get('usuario_nome') ?? 'Usuário' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= base_url('admin/perfil') ?>">
                                <i class="bi bi-person"></i> Meu Perfil
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= base_url('auth/logout') ?>">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="d-flex" id="wrapper">
        <div class="bg-light border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 border-bottom bg-success text-white">
                <strong><i class="bi bi-list-ul me-2"></i>Menu</strong>
            </div>
            <div class="list-group list-group-flush">
                <a href="<?= base_url('admin/dashboard') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="<?= base_url('admin/sondagens') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-geo-alt me-2"></i> Sondagens
                </a>
                <a href="<?= base_url('admin/sondagens/create') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-plus-circle me-2"></i> Nova Sondagem
                </a>
                <a href="<?= base_url('admin/projetos') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-folder me-2"></i> Projetos
                </a>
                <a href="<?= base_url('admin/obras') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-building me-2"></i> Obras
                </a>
                <a href="<?= base_url('admin/empresas') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-briefcase me-2"></i> Empresas
                </a>
                <a href="<?= base_url('api/import/template') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-download me-2"></i> Template Excel
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <div class="container-fluid py-4">
                <?php if (isset($breadcrumb)): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Home</a></li>
                        <?php foreach ($breadcrumb as $item): ?>
                        <li class="breadcrumb-item <?= $item['active'] ? 'active' : '' ?>">
                            <?php if ($item['active']): ?>
                                <?= $item['title'] ?>
                            <?php else: ?>
                                <a href="<?= $item['url'] ?>"><?= $item['title'] ?></a>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                <?php endif; ?>

                <?php if (session()->getFlashdata('sucesso')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= session()->getFlashdata('sucesso') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('erro')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= session()->getFlashdata('erro') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

    <footer class="bg-light text-center text-muted py-3 border-top">
        <small>
            © <?= date('Y') ?> Support Solo Sondagens Ltda - GeoSPT Manager v1.0
            | CONFORME NBR 6484:2020
        </small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
