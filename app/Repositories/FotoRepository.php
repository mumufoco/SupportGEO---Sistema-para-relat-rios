<?php

namespace App\Repositories;

use App\Models\FotoModel;

class FotoRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new FotoModel());
    }

    public function findBySondagem(int $sondagemId): array
    {
        return $this->model->findBySondagem($sondagemId);
    }

    public function findByTipo(int $sondagemId, string $tipo): array
    {
        return $this->model->findByTipo($sondagemId, $tipo);
    }

    public function uploadFoto(
        int $sondagemId,
        array $fileData,
        string $tipo = 'ensaio_spt',
        ?string $descricao = null,
        ?int $usuarioId = null
    ): ?array {
        $uploadPath = WRITEPATH . 'uploads/fotos/';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $fileName = uniqid() . '_' . $fileData['name'];
        $filePath = $uploadPath . $fileName;

        if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
            return null;
        }

        $exifData = $this->model->extractExifData($filePath);

        $fotoData = [
            'sondagem_id' => $sondagemId,
            'arquivo' => $fileName,
            'nome_original' => $fileData['name'],
            'tipo_foto' => $tipo,
            'descricao' => $descricao,
            'tamanho_bytes' => $fileData['size'],
            'mime_type' => $fileData['type']
        ];

        $fotoData = array_merge($fotoData, $exifData);

        $fotos = $this->findBySondagem($sondagemId);
        $fotoData['ordem'] = count($fotos) + 1;

        return $this->create($fotoData, $usuarioId);
    }

    public function reordenar(int $sondagemId, array $idsOrdenados, ?int $usuarioId = null): bool
    {
        $result = $this->model->reordenar($sondagemId, $idsOrdenados);

        if ($this->enableAudit && $result) {
            $this->logCustomAction(
                $sondagemId,
                'update',
                $usuarioId,
                ['ordem_anterior' => 'alterada'],
                ['nova_ordem' => $idsOrdenados]
            );
        }

        return $result;
    }

    public function deleteAllBySondagem(int $sondagemId, ?int $usuarioId = null): bool
    {
        $fotos = $this->model->findBySondagem($sondagemId);

        foreach ($fotos as $foto) {
            $filePath = WRITEPATH . 'uploads/fotos/' . $foto['arquivo'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            if ($this->enableAudit) {
                $this->auditLog->log(
                    $usuarioId,
                    $this->model->getTable(),
                    $foto['id'],
                    'delete',
                    $foto,
                    null
                );
            }
        }

        return $this->model->deleteAllBySondagem($sondagemId);
    }

    public function delete($id, ?int $usuarioId = null): bool
    {
        $foto = $this->model->find($id);

        if ($foto) {
            $filePath = WRITEPATH . 'uploads/fotos/' . $foto['arquivo'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        return parent::delete($id, $usuarioId);
    }
}
