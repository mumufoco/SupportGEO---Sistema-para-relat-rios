<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-plus-circle me-2"></i>Nova Sondagem SPT</h2>
        <p class="text-muted">Cadastro conforme NBR 6484:2020</p>
    </div>
</div>

<form id="formSondagem" method="POST" action="<?= base_url('api/sondagens') ?>">
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Dados Básicos</h6>
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
                            <input type="date" name="data_execucao" class="form-control"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sondador *</label>
                            <input type="text" name="sondador" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Identificação do Cliente</label>
                            <input type="text" name="identificacao_cliente" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Coordenadas e Cotas</h6>
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
                                   step="0.01" value="0.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Revestimento (m)</label>
                            <input type="number" name="revestimento_profundidade" class="form-control"
                                   step="0.01" value="0.00">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-droplet me-2"></i>Nível d'Água</h6>
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

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-layers me-2"></i>Amostras SPT</h6>
                    <button type="button" class="btn btn-sm btn-dark" onclick="adicionarAmostra()">
                        <i class="bi bi-plus"></i> Adicionar
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="tabelaAmostras">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">Nº</th>
                                    <th width="80">Tipo</th>
                                    <th>Prof. (m)</th>
                                    <th>Golpes 1ª</th>
                                    <th>Golpes 2ª</th>
                                    <th>Golpes 3ª</th>
                                    <th>N30</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="corpoAmostras">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-tools me-2"></i>Equipamento NBR 6484</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Peso do Martelo (kgf)</label>
                        <input type="number" name="peso_martelo" class="form-control"
                               value="65.00" step="0.01" readonly>
                        <small class="text-muted">NBR: 65 kgf</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Altura de Queda (cm)</label>
                        <input type="number" name="altura_queda" class="form-control"
                               value="75.00" step="0.01" readonly>
                        <small class="text-muted">NBR: 75 cm</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sistema</label>
                        <select name="sistema_percussao" class="form-select">
                            <option value="manual">Manual</option>
                            <option value="mecanico">Mecânico</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="bi bi-chat-text me-2"></i>Observações</h6>
                </div>
                <div class="card-body">
                    <textarea name="observacoes_paralisacao" class="form-control" rows="4"
                              placeholder="Motivo de paralisação, observações gerais..."></textarea>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle me-2"></i>Salvar Sondagem
                </button>
                <a href="<?= base_url('admin/sondagens') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancelar
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
                   class="form-control form-control-sm text-center" value="${numeroAmostra}" readonly>
        </td>
        <td>
            <select name="amostras[${numeroAmostra}][tipo_perfuracao]" class="form-select form-select-sm">
                <option value="CR">CR</option>
                <option value="TH">TH</option>
            </select>
        </td>
        <td>
            <input type="number" name="amostras[${numeroAmostra}][profundidade_inicial]"
                   class="form-control form-control-sm" step="0.01" required>
        </td>
        <td>
            <input type="number" name="amostras[${numeroAmostra}][golpes_1a]"
                   class="form-control form-control-sm" onchange="calcularN30(this)">
        </td>
        <td>
            <input type="number" name="amostras[${numeroAmostra}][golpes_2a]"
                   class="form-control form-control-sm" onchange="calcularN30(this)" required>
        </td>
        <td>
            <input type="number" name="amostras[${numeroAmostra}][golpes_3a]"
                   class="form-control form-control-sm" onchange="calcularN30(this)" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm n30-display text-center fw-bold" readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removerAmostra(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    numeroAmostra++;
}

function removerAmostra(btn) {
    if (confirm('Remover esta amostra?')) {
        btn.closest('tr').remove();
    }
}

function calcularN30(input) {
    const tr = input.closest('tr');
    const golpes2a = parseInt(tr.querySelector('[name*="golpes_2a"]').value) || 0;
    const golpes3a = parseInt(tr.querySelector('[name*="golpes_3a"]').value) || 0;
    const n30 = golpes2a + golpes3a;
    tr.querySelector('.n30-display').value = n30;
}

document.getElementById('nivelAguaInicial').addEventListener('change', function() {
    document.getElementById('divProfNivelInicial').style.display =
        this.value === 'presente' ? 'block' : 'none';
});

document.getElementById('formSondagem').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = {};

    formData.forEach((value, key) => {
        if (key.startsWith('amostras[')) {
            return;
        }
        data[key] = value;
    });

    fetch(this.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            alert('Sondagem salva com sucesso!');
            window.location.href = '<?= base_url('admin/sondagens') ?>';
        } else {
            alert('Erro ao salvar: ' + result.message);
        }
    })
    .catch(error => {
        alert('Erro ao salvar sondagem');
        console.error(error);
    });
});

adicionarAmostra();
</script>
<?= $this->endSection() ?>
