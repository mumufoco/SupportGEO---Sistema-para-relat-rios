# FASE 6: INTERFACE WEB

**Tempo estimado:** 7-10 dias  
**Objetivo:** Criar interface web responsiva para gerenciamento de sondagens

---

## 🎯 Objetivos

- Criar layout base responsivo com Bootstrap 5
- Implementar dashboard com estatísticas
- Criar formulário completo de sondagem
- Implementar DataTables para listagem
- Criar visualização de perfil estratigráfico

---

## 📝 COMANDOS INICIAIS

```bash
# Comando 1: Criar estrutura de views
mkdir -p app/Views/layouts
mkdir -p app/Views/sondagens
mkdir -p app/Views/admin
mkdir -p app/Views/components
mkdir -p public/assets/css
mkdir -p public/assets/js

# Comando 2: Criar arquivos
touch app/Views/layouts/main.php
touch app/Views/admin/dashboard.php
touch app/Views/sondagens/index.php
touch app/Views/sondagens/create.php
touch app/Views/sondagens/edit.php
touch app/Views/sondagens/show.php
touch public/assets/css/app.css
touch public/assets/js/app.js
```

---

## 🎨 LAYOUT PRINCIPAL

Criar `app/Views/layouts/main.php`:

```php
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= $title ?? 'GeoSPT Manager' ?> - Support Solo Sondagens</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
    
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('admin/dashboard') ?>">
                <img src="<?= base_url('assets/images/logo-white.png') ?>" alt="Logo" height="30" class="me-2">
                GeoSPT Manager
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
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('admin/obras') ?>">
                            <i class="bi bi-building"></i> Obras
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
                            <li><a class="dropdown-item" href="<?= base_url('admin/configuracoes') ?>">
                                <i class="bi bi-gear"></i> Configurações
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

    <!-- Sidebar -->
    <div class="d-flex" id="wrapper">
        <div class="bg-light border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 border-bottom">
                <strong>Menu</strong>
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
                <a href="<?= base_url('admin/usuarios') ?>" class="list-group-item list-group-item-action">
                    <i class="bi bi-people me-2"></i> Usuários
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid py-4">
                <!-- Breadcrumb -->
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

                <!-- Flash Messages -->
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

                <!-- Page Content -->
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center text-muted py-3 border-top">
        <small>
            © <?= date('Y') ?> Support Solo Sondagens Ltda - GeoSPT Manager v1.0
            | CONFORME NBR 6484:2020
        </small>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
    
    <?= $this->renderSection('scripts') ?>
</body>
</html>
```

---

## 📊 DASHBOARD

Criar `app/Views/admin/dashboard.php`:

```php
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
        <p class="text-muted">Visão geral do sistema de sondagens</p>
    </div>
</div>

<!-- Cards de Estatísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total de Sondagens
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['total_sondagens'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-geo-alt fs-1 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Aprovadas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['aprovadas'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fs-1 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pendentes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['pendentes'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock fs-1 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Obras Ativas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['obras_ativas'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fs-1 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos e Tabelas -->
<div class="row">
    <!-- Últimas Sondagens -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h6 class="m-0"><i class="bi bi-list me-2"></i>Últimas Sondagens</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Obra</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimas_sondagens ?? [] as $s): ?>
                            <tr>
                                <td><strong><?= $s['codigo_sondagem'] ?></strong></td>
                                <td><?= $s['obra_nome'] ?? '-' ?></td>
                                <td><?= date('d/m/Y', strtotime($s['data_execucao'])) ?></td>
                                <td>
                                    <?php
                                    $badgeClass = [
                                        'rascunho' => 'bg-secondary',
                                        'em_analise' => 'bg-info',
                                        'aprovado' => 'bg-success',
                                        'rejeitado' => 'bg-danger',
                                    ][$s['status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($s['status']) ?></span>
                                </td>
                                <td>
                                    <a href="<?= base_url("admin/sondagens/{$s['id']}") ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= base_url("api/reports/sondagem/{$s['id']}/pdf") ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                        <i class="bi bi-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h6 class="m-0"><i class="bi bi-lightning me-2"></i>Ações Rápidas</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('admin/sondagens/create') ?>" class="btn btn-success">
                        <i class="bi bi-plus-circle me-2"></i>Nova Sondagem
                    </a>
                    <a href="<?= base_url('admin/import') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-upload me-2"></i>Importar Excel
                    </a>
                    <a href="<?= base_url('api/import/template') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-download me-2"></i>Baixar Template
                    </a>
                    <a href="<?= base_url('admin/relatorios') ?>" class="btn btn-outline-info">
                        <i class="bi bi-graph-up me-2"></i>Relatórios
                    </a>
                </div>
            </div>
        </div>

        <!-- Conformidade NBR -->
        <div class="card shadow mt-4">
            <div class="card-header bg-info text-white">
                <h6 class="m-0"><i class="bi bi-award me-2"></i>Conformidade NBR</h6>
            </div>
            <div class="card-body text-center">
                <div class="display-4 text-success mb-2">
                    <?= $stats['conformidade_media'] ?? 100 ?>%
                </div>
                <p class="text-muted mb-0">Score médio de conformidade</p>
                <small class="text-muted">NBR 6484:2020</small>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
```

---

## 📝 FORMULÁRIO DE SONDAGEM

Criar `app/Views/sondagens/create.php`:

```php
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-plus-circle me-2"></i>Nova Sondagem SPT</h2>
        <p class="text-muted">Cadastro conforme NBR 6484:2020</p>
    </div>
</div>

<form id="formSondagem" action="<?= base_url('admin/sondagens') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    
    <div class="row">
        <!-- Coluna Principal -->
        <div class="col-lg-8">
            <!-- Dados Básicos -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="bi bi-info-circle me-2"></i>Dados Básicos</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Código da Sondagem *</label>
                            <input type="text" name="codigo_sondagem" class="form-control" 
                                   placeholder="SP-01" required maxlength="20">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Obra *</label>
                            <select name="obra_id" class="form-select" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($obras ?? [] as $obra): ?>
                                <option value="<?= $obra['id'] ?>"><?= $obra['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Data de Execução *</label>
                            <input type="date" name="data_execucao" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sondador *</label>
                            <input type="text" name="sondador" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Identificação do Cliente</label>
                            <input type="text" name="identificacao_cliente" class="form-control" 
                                   placeholder="Ex: Araxá Eng.">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coordenadas -->
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="bi bi-geo-alt me-2"></i>Coordenadas e Cotas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Coordenada Este (m) *</label>
                            <input type="number" name="coordenada_este" class="form-control" 
                                   step="0.01" required placeholder="487801.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Coordenada Norte (m) *</label>
                            <input type="number" name="coordenada_norte" class="form-control" 
                                   step="0.01" required placeholder="7666164.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cota da Boca do Furo (m)</label>
                            <input type="number" name="cota_boca_furo" class="form-control" 
                                   step="0.01" value="0.00">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Profundidade Final (m) *</label>
                            <input type="number" name="profundidade_final" class="form-control" 
                                   step="0.01" required placeholder="12.45">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Profundidade do Trado (m)</label>
                            <input type="number" name="profundidade_trado" class="form-control" 
                                   step="0.01" placeholder="1.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Revestimento (m)</label>
                            <input type="number" name="revestimento_profundidade" class="form-control" 
                                   step="0.01" value="0.00">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nível d'água -->
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="bi bi-droplet me-2"></i>Nível d'Água</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nível Inicial</label>
                            <select name="nivel_agua_inicial" class="form-select" id="nivelAguaInicial">
                                <option value="ausente" selected>Ausente</option>
                                <option value="presente">Presente</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="divProfNivelInicial" style="display:none;">
                            <label class="form-label">Profundidade (m)</label>
                            <input type="number" name="nivel_agua_inicial_profundidade" 
                                   class="form-control" step="0.01">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amostras SPT -->
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h6 class="m-0"><i class="bi bi-layers me-2"></i>Amostras SPT</h6>
                    <button type="button" class="btn btn-sm btn-dark" onclick="adicionarAmostra()">
                        <i class="bi bi-plus"></i> Adicionar
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm" id="tabelaAmostras">
                            <thead class="table-light">
                                <tr>
                                    <th>Nº</th>
                                    <th>Tipo</th>
                                    <th>Prof. Inicial</th>
                                    <th>Golpes 1ª</th>
                                    <th>Golpes 2ª</th>
                                    <th>Golpes 3ª</th>
                                    <th>N30</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="corpoAmostras">
                                <!-- Linhas dinâmicas -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="col-lg-4">
            <!-- Equipamento NBR -->
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="m-0"><i class="bi bi-tools me-2"></i>Equipamento (NBR 6484)</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Peso do Martelo (kgf)</label>
                        <input type="number" name="peso_martelo" class="form-control" 
                               value="65.00" step="0.01" readonly>
                        <small class="text-muted">NBR 6484:2020 - 65 kgf</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Altura de Queda (cm)</label>
                        <input type="number" name="altura_queda" class="form-control" 
                               value="75.00" step="0.01" readonly>
                        <small class="text-muted">NBR 6484:2020 - 75 cm</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">∅ Amostrador Externo (mm)</label>
                        <input type="number" name="diametro_amostrador_externo" class="form-control" 
                               value="50.80" step="0.01">
                        <small class="text-muted">NBR: 50,8 ± 0,2 mm</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">∅ Amostrador Interno (mm)</label>
                        <input type="number" name="diametro_amostrador_interno" class="form-control" 
                               value="34.90" step="0.01">
                        <small class="text-muted">NBR: 34,9 ± 0,2 mm</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sistema</label>
                        <select name="sistema_percussao" class="form-select">
                            <option value="manual" selected>Manual</option>
                            <option value="mecanico">Mecânico</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Responsável Técnico -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0"><i class="bi bi-person-badge me-2"></i>Responsável Técnico</h6>
                </div>
                <div class="card-body">
                    <select name="responsavel_tecnico_id" class="form-select">
                        <option value="">Selecione...</option>
                        <?php foreach ($responsaveis ?? [] as $r): ?>
                        <option value="<?= $r['id'] ?>"><?= $r['nome'] ?> - <?= $r['crea'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Observações -->
            <div class="card shadow mb-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="m-0"><i class="bi bi-chat-text me-2"></i>Observações</h6>
                </div>
                <div class="card-body">
                    <textarea name="observacoes_paralisacao" class="form-control" rows="4"
                              placeholder="Motivo de paralisação..."></textarea>
                </div>
            </div>

            <!-- Botões -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle me-2"></i>Salvar Sondagem
                </button>
                <a href="<?= base_url('admin/sondagens') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let numeroAmostra = 1;

function adicionarAmostra() {
    const tbody = document.getElementById('corpoAmostras');
    const tr = document.createElement('tr');
    
    tr.innerHTML = `
        <td>
            <input type="number" name="amostras[${numeroAmostra}][numero_amostra]" 
                   class="form-control form-control-sm" value="${numeroAmostra}" readonly style="width:50px">
        </td>
        <td>
            <select name="amostras[${numeroAmostra}][tipo_perfuracao]" class="form-select form-select-sm">
                <option value="CR">CR</option>
                <option value="TH">TH</option>
            </select>
        </td>
        <td>
            <input type="number" name="amostras[${numeroAmostra}][profundidade_inicial]" 
                   class="form-control form-control-sm" step="0.01" style="width:80px">
        </td>
        <td>
            <input type="number" name="amostras[${numeroAmostra}][golpes_1a]" 
                   class="form-control form-control-sm" onchange="calcularN30(this)" style="width:60px">
        </td>
        <td>
            <input type="number" name="amostras[${numeroAmostra}][golpes_2a]" 
                   class="form-control form-control-sm" onchange="calcularN30(this)" style="width:60px">
        </td>
        <td>
            <input type="number" name="amostras[${numeroAmostra}][golpes_3a]" 
                   class="form-control form-control-sm" onchange="calcularN30(this)" style="width:60px">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm n30-display" readonly style="width:60px">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removerAmostra(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(tr);
    numeroAmostra++;
}

function removerAmostra(btn) {
    btn.closest('tr').remove();
}

function calcularN30(input) {
    const tr = input.closest('tr');
    const golpes2a = parseInt(tr.querySelector('[name*="golpes_2a"]').value) || 0;
    const golpes3a = parseInt(tr.querySelector('[name*="golpes_3a"]').value) || 0;
    const n30 = golpes2a + golpes3a;
    tr.querySelector('.n30-display').value = n30;
}

// Nível d'água
document.getElementById('nivelAguaInicial').addEventListener('change', function() {
    document.getElementById('divProfNivelInicial').style.display = 
        this.value === 'presente' ? 'block' : 'none';
});

// Adicionar primeira amostra
adicionarAmostra();
</script>
<?= $this->endSection() ?>
```

---

## 🎨 CSS PERSONALIZADO

Criar `public/assets/css/app.css`:

```css
/* GeoSPT Manager - Custom Styles */

:root {
    --primary-color: #198754;
    --secondary-color: #6c757d;
}

/* Sidebar */
#sidebar-wrapper {
    min-height: 100vh;
    width: 250px;
    margin-left: -250px;
    transition: margin 0.25s ease-out;
    margin-top: 56px;
}

#wrapper.toggled #sidebar-wrapper {
    margin-left: 0;
}

#page-content-wrapper {
    min-width: 100vw;
    margin-top: 56px;
}

@media (min-width: 768px) {
    #sidebar-wrapper {
        margin-left: 0;
    }
    
    #page-content-wrapper {
        min-width: 0;
        width: 100%;
        padding-left: 250px;
    }
}

/* Cards */
.card {
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

/* Tables */
.table th {
    font-weight: 600;
    background-color: #f8f9fa;
}

/* Forms */
.form-label {
    font-weight: 500;
    color: #495057;
}

/* Buttons */
.btn-success {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* NBR Badge */
.badge-nbr {
    background-color: #0d6efd;
    color: white;
    font-size: 0.75rem;
}

/* Status badges */
.badge-aprovado { background-color: #198754; }
.badge-pendente { background-color: #ffc107; color: #000; }
.badge-rejeitado { background-color: #dc3545; }

/* Print styles */
@media print {
    #sidebar-wrapper, .navbar, footer {
        display: none !important;
    }
    
    #page-content-wrapper {
        padding-left: 0 !important;
        margin-top: 0 !important;
    }
}
```

---

## ✅ CHECKLIST FASE 6

- [ ] Layout principal responsivo
- [ ] Dashboard com estatísticas
- [ ] Formulário de sondagem completo
- [ ] Tabela dinâmica de amostras
- [ ] Cálculo automático de N30
- [ ] DataTables para listagem
- [ ] CSS personalizado
- [ ] Validações client-side

---

## 🔄 PRÓXIMO PASSO

➡️ **[Fase 7 - Upload de Fotos e Importação](08_FASE_7_FOTOS_IMPORTACAO.md)**

---

**© 2025 Support Solo Sondagens Ltda**
