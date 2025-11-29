<?php

namespace App\Repositories;

use App\Models\SondagemModel;
use App\Models\CamadaModel;
use App\Models\AmostraModel;
use App\Models\FotoModel;

class SondagemRepository extends BaseRepository
{
    private CamadaModel $camadaModel;
    private AmostraModel $amostraModel;
    private FotoModel $fotoModel;

    public function __construct()
    {
        parent::__construct(new SondagemModel());
        $this->camadaModel = new CamadaModel();
        $this->amostraModel = new AmostraModel();
        $this->fotoModel = new FotoModel();
    }

    public function findWithRelations(int $id): ?array
    {
        $sondagem = $this->model->find($id);

        if (!$sondagem) {
            return null;
        }

        $sondagem['camadas'] = $this->camadaModel->findBySondagem($id);
        $sondagem['amostras'] = $this->amostraModel->findBySondagem($id);
        $sondagem['fotos'] = $this->fotoModel->findBySondagem($id);

        return $sondagem;
    }

    public function findByObra(int $obraId): array
    {
        return $this->model->findByObra($obraId);
    }

    public function aprovar(int $sondagemId, int $usuarioId): array
    {
        $result = $this->model->aprovar($sondagemId, $usuarioId);

        $this->logCustomAction(
            $sondagemId,
            'approve',
            $usuarioId
        );

        return $result;
    }

    public function rejeitar(int $sondagemId, int $usuarioId): array
    {
        $result = $this->model->rejeitar($sondagemId);

        $this->logCustomAction(
            $sondagemId,
            'reject',
            $usuarioId
        );

        return $result;
    }

    public function duplicar(int $sondagemId, array $novoCodigo, int $usuarioId): ?array
    {
        $original = $this->findWithRelations($sondagemId);

        if (!$original) {
            return null;
        }

        unset($original['id']);
        unset($original['created_at']);
        unset($original['updated_at']);
        $original['codigo_sondagem'] = $novoCodigo['codigo_sondagem'];
        $original['status'] = 'rascunho';
        $original['versao'] = 1;

        $novaSondagem = $this->create($original, $usuarioId);

        if (empty($novaSondagem)) {
            return null;
        }

        $novaSondagemId = $novaSondagem[0]['id'];

        foreach ($original['camadas'] as $camada) {
            unset($camada['id']);
            unset($camada['created_at']);
            unset($camada['updated_at']);
            $camada['sondagem_id'] = $novaSondagemId;
            $this->camadaModel->insert($camada);
        }

        foreach ($original['amostras'] as $amostra) {
            unset($amostra['id']);
            unset($amostra['created_at']);
            unset($amostra['updated_at']);
            $amostra['sondagem_id'] = $novaSondagemId;
            $this->amostraModel->insert($amostra);
        }

        return $this->findWithRelations($novaSondagemId);
    }

    public function calcularConformidadeNBR(int $sondagemId): int
    {
        $sondagem = $this->model->find($sondagemId);

        if (!$sondagem) {
            return 0;
        }

        $score = 100;

        if (abs($sondagem['peso_martelo'] - 65.00) > 0.01) {
            $score -= 10;
        }

        if (abs($sondagem['altura_queda'] - 75.00) > 0.01) {
            $score -= 10;
        }

        if (abs($sondagem['diametro_amostrador_externo'] - 50.80) > 0.2) {
            $score -= 10;
        }

        if (abs($sondagem['diametro_amostrador_interno'] - 34.90) > 0.2) {
            $score -= 10;
        }

        if (empty($sondagem['responsavel_tecnico_id'])) {
            $score -= 20;
        }

        if (empty($sondagem['observacoes_paralisacao']) && $sondagem['profundidade_final'] < 20) {
            $score -= 10;
        }

        $amostras = $this->amostraModel->findBySondagem($sondagemId);
        if (empty($amostras)) {
            $score -= 20;
        }

        $camadas = $this->camadaModel->findBySondagem($sondagemId);
        if (empty($camadas)) {
            $score -= 10;
        }

        $score = max(0, min(100, $score));

        $this->model->updateConformidade($sondagemId, $score);

        return $score;
    }
}
