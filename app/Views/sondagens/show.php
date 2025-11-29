<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-geo-alt me-2"></i><?= $sondagem['codigo_sondagem'] ?></h2>
        <p class="text-muted">Visualização da sondagem SPT</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url("admin/sondagens/{$sondagem['id']}/edit") ?>" class="btn btn-primary">
            <i class="bi bi-pencil me-2"></i>Editar
        </a>
        <a href="<?= base_url("api/pdf/preview/{$sondagem['id']}") ?>" class="btn btn-success" target="_blank">
            <i class="bi bi-file-pdf me-2"></i>Gerar PDF
        </a>
        <a href="<?= base_url('admin/sondagens') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informações Gerais</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Código:</strong><br>
                        <?= $sondagem['codigo_sondagem'] ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Data de Execução:</strong><br>
                        <?= date('d/m/Y', strtotime($sondagem['data_execucao'])) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Sondador:</strong><br>
                        <?= $sondagem['sondador'] ?? '-' ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        <?php
                        $badges = [
                            'rascunho' => 'secondary',
                            'em_analise' => 'info',
                            'aprovado' => 'success',
                            'rejeitado' => 'danger',
                        ];
                        $badgeColor = $badges[$sondagem['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $badgeColor ?>"><?= ucfirst(str_replace('_', ' ', $sondagem['status'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Coordenadas e Profundidades</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <strong>Coordenada Este:</strong><br>
                        <?= number_format($sondagem['coordenada_este'], 2) ?> m
                    </div>
                    <div class="col-md-4">
                        <strong>Coordenada Norte:</strong><br>
                        <?= number_format($sondagem['coordenada_norte'], 2) ?> m
                    </div>
                    <div class="col-md-4">
                        <strong>Cota Boca Furo:</strong><br>
                        <?= number_format($sondagem['cota_boca_furo'], 2) ?> m
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Profundidade Final:</strong><br>
                        <?= number_format($sondagem['profundidade_final'], 2) ?> m
                    </div>
                    <div class="col-md-6">
                        <strong>Nível d'Água:</strong><br>
                        <?= ucfirst($sondagem['nivel_agua_inicial']) ?>
                        <?php if ($sondagem['nivel_agua_inicial_profundidade']): ?>
                            - <?= number_format($sondagem['nivel_agua_inicial_profundidade'], 2) ?> m
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($sondagem['amostras'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-layers me-2"></i>Amostras SPT (<?= count($sondagem['amostras']) ?>)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Nº</th>
                                <th>Tipo</th>
                                <th>Prof. (m)</th>
                                <th>Golpes 1ª</th>
                                <th>Golpes 2ª</th>
                                <th>Golpes 3ª</th>
                                <th>N30</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sondagem['amostras'] as $amostra): ?>
                            <tr>
                                <td><?= $amostra['numero_amostra'] ?></td>
                                <td><span class="badge bg-secondary"><?= $amostra['tipo_perfuracao'] ?></span></td>
                                <td><?= number_format($amostra['profundidade_inicial'], 2) ?></td>
                                <td><?= $amostra['golpes_1a'] ?? '-' ?></td>
                                <td><?= $amostra['golpes_2a'] ?></td>
                                <td><?= $amostra['golpes_3a'] ?></td>
                                <td><strong><?= $amostra['nspt'] ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($sondagem['observacoes_paralisacao'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0"><i class="bi bi-chat-text me-2"></i>Observações</h6>
            </div>
            <div class="card-body">
                <?= nl2br(esc($sondagem['observacoes_paralisacao'])) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-award me-2"></i>Conformidade NBR</h6>
            </div>
            <div class="card-body text-center">
                <?php
                $score = $sondagem['score_conformidade'] ?? 100;
                $scoreClass = $score >= 70 ? 'success' : 'warning';
                ?>
                <div class="display-3 text-<?= $scoreClass ?> mb-2 fw-bold">
                    <?= $score ?>%
                </div>
                <p class="text-muted mb-0">Score de conformidade</p>
                <small class="text-muted">NBR 6484:2020</small>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-tools me-2"></i>Equipamento</h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Peso do Martelo:</strong><br>
                    <?= number_format($sondagem['peso_martelo'], 2) ?> kgf
                </p>
                <p class="mb-2">
                    <strong>Altura de Queda:</strong><br>
                    <?= number_format($sondagem['altura_queda'], 2) ?> cm
                </p>
                <p class="mb-0">
                    <strong>Sistema:</strong><br>
                    <?= ucfirst($sondagem['sistema_percussao']) ?>
                </p>
            </div>
        </div>

        <?php if (!empty($sondagem['fotos'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-images me-2"></i>Fotos (<?= count($sondagem['fotos']) ?>)</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <?php foreach ($sondagem['fotos'] as $foto): ?>
                    <div class="col-6">
                        <img src="<?= base_url('writable/uploads/fotos/' . $foto['arquivo']) ?>"
                             class="img-fluid rounded" alt="Foto">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
