<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
        <p class="text-muted">Visão geral do sistema de sondagens SPT</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase mb-1">Total de Sondagens</div>
                        <div class="h3 mb-0 fw-bold text-primary"><?= $stats['total_sondagens'] ?? 0 ?></div>
                    </div>
                    <div class="text-primary" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase mb-1">Aprovadas</div>
                        <div class="h3 mb-0 fw-bold text-success"><?= $stats['aprovadas'] ?? 0 ?></div>
                    </div>
                    <div class="text-success" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase mb-1">Pendentes</div>
                        <div class="h3 mb-0 fw-bold text-warning"><?= $stats['pendentes'] ?? 0 ?></div>
                    </div>
                    <div class="text-warning" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase mb-1">Obras Ativas</div>
                        <div class="h3 mb-0 fw-bold text-info"><?= $stats['obras_ativas'] ?? 0 ?></div>
                    </div>
                    <div class="text-info" style="font-size: 3rem; opacity: 0.3;">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="bi bi-list me-2"></i>Últimas Sondagens</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
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
                                    $badges = [
                                        'rascunho' => 'secondary',
                                        'em_analise' => 'info',
                                        'aprovado' => 'success',
                                        'rejeitado' => 'danger',
                                    ];
                                    $badgeColor = $badges[$s['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badgeColor ?>"><?= ucfirst(str_replace('_', ' ', $s['status'])) ?></span>
                                </td>
                                <td>
                                    <a href="<?= base_url("admin/sondagens/{$s['id']}") ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= base_url("api/pdf/preview/{$s['id']}") ?>" class="btn btn-sm btn-outline-success" target="_blank">
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

    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Ações Rápidas</h5>
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

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="bi bi-award me-2"></i>Conformidade NBR</h5>
            </div>
            <div class="card-body text-center">
                <div class="display-3 text-success mb-2 fw-bold">
                    <?= $stats['conformidade_media'] ?? 100 ?>%
                </div>
                <p class="text-muted mb-0">Score médio de conformidade</p>
                <small class="text-muted">NBR 6484:2020</small>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
