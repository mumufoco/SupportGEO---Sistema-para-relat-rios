<?php

namespace App\Models;

class FotoModel extends BaseModel
{
    protected string $table = 'fotos';
    protected string $primaryKey = 'id';
    protected bool $useTimestamps = true;
    protected bool $useSoftDeletes = false;

    protected array $allowedFields = [
        'sondagem_id',
        'arquivo',
        'nome_original',
        'tipo_foto',
        'descricao',
        'latitude',
        'longitude',
        'altitude',
        'velocidade',
        'data_hora_exif',
        'coordenada_este',
        'coordenada_norte',
        'zona_utm',
        'tamanho_bytes',
        'mime_type',
        'ordem'
    ];

    public function findBySondagem(int $sondagemId): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('sondagem_id', $sondagemId)
            ->order('ordem', 'asc')
            ->get();
    }

    public function findByTipo(int $sondagemId, string $tipo): array
    {
        return $this->supabase
            ->from($this->table)
            ->select('*')
            ->eq('sondagem_id', $sondagemId)
            ->eq('tipo_foto', $tipo)
            ->order('ordem', 'asc')
            ->get();
    }

    public function reordenar(int $sondagemId, array $idsOrdenados): bool
    {
        foreach ($idsOrdenados as $ordem => $fotoId) {
            $this->update($fotoId, ['ordem' => $ordem + 1]);
        }
        return true;
    }

    public function deleteAllBySondagem(int $sondagemId): bool
    {
        return $this->supabase->delete($this->table, [
            'sondagem_id' => $sondagemId
        ]);
    }

    public function extractExifData(string $filePath): array
    {
        $exifData = [];

        if (!function_exists('exif_read_data')) {
            return $exifData;
        }

        $exif = @exif_read_data($filePath);

        if (!$exif) {
            return $exifData;
        }

        if (isset($exif['DateTime'])) {
            $exifData['data_hora_exif'] = date('Y-m-d H:i:s', strtotime($exif['DateTime']));
        }

        if (isset($exif['GPSLatitude']) && isset($exif['GPSLatitudeRef'])) {
            $exifData['latitude'] = $this->getGps($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
        }

        if (isset($exif['GPSLongitude']) && isset($exif['GPSLongitudeRef'])) {
            $exifData['longitude'] = $this->getGps($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
        }

        if (isset($exif['GPSAltitude'])) {
            $exifData['altitude'] = $this->evalGpsData($exif['GPSAltitude']);
        }

        return $exifData;
    }

    private function getGps($exifCoord, $hemi): float
    {
        $degrees = count($exifCoord) > 0 ? $this->evalGpsData($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? $this->evalGpsData($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? $this->evalGpsData($exifCoord[2]) : 0;

        $flip = ($hemi == 'W' || $hemi == 'S') ? -1 : 1;

        return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
    }

    private function evalGpsData($data)
    {
        if (strpos($data, '/') !== false) {
            $parts = explode('/', $data);
            return $parts[1] != 0 ? $parts[0] / $parts[1] : 0;
        }
        return $data;
    }
}
