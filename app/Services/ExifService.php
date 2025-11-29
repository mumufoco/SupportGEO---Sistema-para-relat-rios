<?php

namespace App\Services;

use App\Libraries\NBR\CoordenadasConverter;

class ExifService
{
    private CoordenadasConverter $coordConverter;

    public function __construct()
    {
        $this->coordConverter = new CoordenadasConverter();
    }

    public function extrairMetadados(string $filepath): array
    {
        $metadados = [
            'latitude' => null,
            'longitude' => null,
            'altitude' => null,
            'velocidade' => null,
            'data_hora' => null,
            'coordenada_este' => null,
            'coordenada_norte' => null,
            'zona_utm' => null,
            'fabricante' => null,
            'modelo' => null,
            'orientacao' => null,
            'largura' => null,
            'altura' => null
        ];

        if (!file_exists($filepath)) {
            return $metadados;
        }

        if (!function_exists('exif_read_data')) {
            log_message('warning', 'Extensão EXIF não disponível');
            return $metadados;
        }

        try {
            $exif = @exif_read_data($filepath, 'GPS,EXIF,IFD0', true);

            if (!$exif) {
                return $metadados;
            }

            if (isset($exif['GPS'])) {
                $gps = $exif['GPS'];

                if (isset($gps['GPSLatitude']) && isset($gps['GPSLatitudeRef'])) {
                    $metadados['latitude'] = $this->converterGpsParaDecimal(
                        $gps['GPSLatitude'],
                        $gps['GPSLatitudeRef']
                    );
                }

                if (isset($gps['GPSLongitude']) && isset($gps['GPSLongitudeRef'])) {
                    $metadados['longitude'] = $this->converterGpsParaDecimal(
                        $gps['GPSLongitude'],
                        $gps['GPSLongitudeRef']
                    );
                }

                if (isset($gps['GPSAltitude'])) {
                    $metadados['altitude'] = $this->converterFracao($gps['GPSAltitude']);
                }

                if (isset($gps['GPSSpeed'])) {
                    $metadados['velocidade'] = $this->converterFracao($gps['GPSSpeed']);
                }
            }

            if (isset($exif['EXIF']['DateTimeOriginal'])) {
                $metadados['data_hora'] = date('Y-m-d H:i:s',
                    strtotime($exif['EXIF']['DateTimeOriginal']));
            } elseif (isset($exif['IFD0']['DateTime'])) {
                $metadados['data_hora'] = date('Y-m-d H:i:s',
                    strtotime($exif['IFD0']['DateTime']));
            }

            if (isset($exif['IFD0']['Make'])) {
                $metadados['fabricante'] = trim($exif['IFD0']['Make']);
            }

            if (isset($exif['IFD0']['Model'])) {
                $metadados['modelo'] = trim($exif['IFD0']['Model']);
            }

            if (isset($exif['IFD0']['Orientation'])) {
                $metadados['orientacao'] = $exif['IFD0']['Orientation'];
            }

            if (isset($exif['COMPUTED']['Width'])) {
                $metadados['largura'] = $exif['COMPUTED']['Width'];
            }

            if (isset($exif['COMPUTED']['Height'])) {
                $metadados['altura'] = $exif['COMPUTED']['Height'];
            }

            if ($metadados['latitude'] && $metadados['longitude']) {
                $utm = $this->coordConverter->geographicToUTM(
                    $metadados['latitude'],
                    $metadados['longitude']
                );
                $metadados['coordenada_este'] = $utm['este'];
                $metadados['coordenada_norte'] = $utm['norte'];
                $metadados['zona_utm'] = $utm['zona'];
            }

        } catch (\Exception $e) {
            log_message('error', 'Erro ao extrair EXIF: ' . $e->getMessage());
        }

        return $metadados;
    }

    protected function converterGpsParaDecimal(array $gps, string $ref): float
    {
        $graus = $this->converterFracao($gps[0]);
        $minutos = $this->converterFracao($gps[1]);
        $segundos = $this->converterFracao($gps[2]);

        $decimal = $graus + ($minutos / 60) + ($segundos / 3600);

        if ($ref === 'S' || $ref === 'W') {
            $decimal *= -1;
        }

        return round($decimal, 7);
    }

    protected function converterFracao($valor): float
    {
        if (is_string($valor) && strpos($valor, '/') !== false) {
            $partes = explode('/', $valor);
            if (count($partes) === 2 && $partes[1] != 0) {
                return (float) $partes[0] / (float) $partes[1];
            }
        }
        return (float) $valor;
    }

    public function validarCoordenadas(float $latitude, float $longitude): bool
    {
        if ($latitude < -90 || $latitude > 90) {
            return false;
        }

        if ($longitude < -180 || $longitude > 180) {
            return false;
        }

        return true;
    }

    public function obterDimensoesImagem(string $filepath): array
    {
        try {
            $info = getimagesize($filepath);

            return [
                'largura' => $info[0] ?? 0,
                'altura' => $info[1] ?? 0,
                'tipo' => $info[2] ?? 0,
                'mime' => $info['mime'] ?? ''
            ];
        } catch (\Exception $e) {
            return [
                'largura' => 0,
                'altura' => 0,
                'tipo' => 0,
                'mime' => ''
            ];
        }
    }
}
