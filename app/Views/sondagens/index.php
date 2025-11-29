<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-geo-alt me-2"></i>Sondagens SPT</h2>
        <p class="text-muted">Gerenciamento completo de sondagens</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url('admin/sondagens/create') ?>" class="btn btn-success">
            <i class="bi bi-plus-circle me-2"></i>Nova Sondagem
        </a>
        <a href="<?= base_url('api/import/template') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-download me-2"></i>Template Excel
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0"><i class="bi bi-list me-2"></i>Lista de Sondagens</h5>
            </div>
            <div class="col-auto">
                <select class="form-select form-select-sm" id="filtroStatus">
                    <option value="">Todos os Status</option>
                    <option value="rascunho">Rascunho</option>
                    <option value="em_analise">Em Análise</option>
                    <option value="aprovado">Aprovado</option>
                    <option value="rejeitado">Rejeitado</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="tabelaSondagens" class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Obra</th>
                        <th>Projeto</th>
                        <th>Data Execução</th>
                        <th>Prof. Final</th>
                        <th>Status</th>
                        <th>Conformidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sondagens ?? [] as $s): ?>
                    <tr>
                        <td><strong><?= $s['codigo_sondagem'] ?></strong></td>
                        <td><?= $s['obra_nome'] ?? '-' ?></td>
                        <td><?= $s['projeto_nome'] ?? '-' ?></td>
                        <td><?= date('d/m/Y', strtotime($s['data_execucao'])) ?></td>
                        <td><?= number_format($s['profundidade_final'], 2) ?> m</td>
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
                            <?php if (isset($s['score_conformidade'])): ?>
                            <span class="badge bg-<?= $s['score_conformidade'] >= 70 ? 'success' : 'warning' ?>">
                                <?= $s['score_conformidade'] ?>%
                            </span>
                            <?php else: ?>
                            <span class="badge bg-secondary">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= base_url("admin/sondagens/{$s['id']}") ?>" class="btn btn-outline-primary" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= base_url("admin/sondagens/{$s['id']}/edit") ?>" class="btn btn-outline-secondary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= base_url("api/pdf/preview/{$s['id']}") ?>" class="btn btn-outline-success" target="_blank" title="PDF">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                                <button class="btn btn-outline-danger" onclick="excluirSondagem(<?= $s['id'] ?>)" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#tabelaSondagens').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
        },
        order: [[3, 'desc']],
        pageLength: 25
    });

    $('#filtroStatus').on('change', function() {
        var status = $(this).val();
        if (status) {
            window.location.href = '<?= base_url('admin/sondagens') ?>?status=' + status;
        } else {
            window.location.href = '<?= base_url('admin/sondagens') ?>';
        }
    });
});

function excluirSondagem(id) {
    if (confirm('Tem certeza que deseja excluir esta sondagem?')) {
        fetch('<?= base_url('api/sondagens') ?>/' + id, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Sondagem excluída com sucesso!');
                location.reload();
            } else {
                alert('Erro ao excluir sondagem: ' + data.message);
            }
        })
        .catch(error => {
            alert('Erro ao excluir sondagem');
            console.error(error);
        });
    }
}
</script>
<?= $this->endSection() ?>
