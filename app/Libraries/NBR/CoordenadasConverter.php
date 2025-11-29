<?php

namespace App\Libraries\NBR;

class CoordenadasConverter
{
    private const DATUM_SIRGAS2000 = 'SIRGAS2000';
    private const DATUM_WGS84 = 'WGS84';
    private const DATUM_SAD69 = 'SAD69';

    private const SEMI_EIXO_MAIOR = 6378137.0;
    private const ACHATAMENTO = 1 / 298.257223563;

    public function geographicToUTM(
        float $latitude,
        float $longitude,
        ?string $datum = self::DATUM_SIRGAS2000
    ): array {
        $zona = $this->calcularZonaUTM($longitude);
        $hemisferio = $latitude >= 0 ? 'N' : 'S';

        $lat_rad = deg2rad($latitude);
        $lon_rad = deg2rad($longitude);

        $lon_central = deg2rad(($zona - 1) * 6 - 180 + 3);

        $a = self::SEMI_EIXO_MAIOR;
        $f = self::ACHATAMENTO;
        $e2 = 2 * $f - $f * $f;

        $N = $a / sqrt(1 - $e2 * sin($lat_rad) * sin($lat_rad));
        $T = tan($lat_rad) * tan($lat_rad);
        $C = $e2 * cos($lat_rad) * cos($lat_rad) / (1 - $e2);
        $A = ($lon_rad - $lon_central) * cos($lat_rad);

        $M = $a * ((1 - $e2/4 - 3*$e2*$e2/64) * $lat_rad
            - (3*$e2/8 + 3*$e2*$e2/32) * sin(2*$lat_rad)
            + (15*$e2*$e2/256) * sin(4*$lat_rad));

        $k0 = 0.9996;

        $este = $k0 * $N * ($A + (1-$T+$C)*$A*$A*$A/6
            + (5-18*$T+$T*$T+72*$C)*$A*$A*$A*$A*$A/120) + 500000;

        $norte = $k0 * ($M + $N*tan($lat_rad) * ($A*$A/2
            + (5-$T+9*$C+4*$C*$C)*$A*$A*$A*$A/24
            + (61-58*$T+$T*$T)*$A*$A*$A*$A*$A*$A/720));

        if ($hemisferio == 'S') {
            $norte += 10000000;
        }

        return [
            'este' => round($este, 2),
            'norte' => round($norte, 2),
            'zona' => $zona . ($hemisferio == 'S' ? 'K' : 'N'),
            'hemisferio' => $hemisferio,
            'datum' => $datum
        ];
    }

    public function utmToGeographic(
        float $este,
        float $norte,
        string $zona,
        ?string $datum = self::DATUM_SIRGAS2000
    ): array {
        $zonaNum = (int) preg_replace('/[^0-9]/', '', $zona);
        $hemisferio = (strpos($zona, 'S') !== false || strpos($zona, 'K') !== false) ? 'S' : 'N';

        $a = self::SEMI_EIXO_MAIOR;
        $f = self::ACHATAMENTO;
        $e2 = 2 * $f - $f * $f;

        $k0 = 0.9996;

        $este_corrigido = $este - 500000;
        $norte_corrigido = $hemisferio == 'S' ? $norte - 10000000 : $norte;

        $M = $norte_corrigido / $k0;

        $mu = $M / ($a * (1 - $e2/4 - 3*$e2*$e2/64));

        $lat_rad = $mu + (3*$e2/8 + 3*$e2*$e2/32) * sin(2*$mu)
            + (15*$e2*$e2/256) * sin(4*$mu);

        $N = $a / sqrt(1 - $e2 * sin($lat_rad) * sin($lat_rad));
        $T = tan($lat_rad) * tan($lat_rad);
        $C = $e2 * cos($lat_rad) * cos($lat_rad) / (1 - $e2);
        $D = $este_corrigido / ($N * $k0);

        $latitude = $lat_rad - ($N*tan($lat_rad)/$a) * ($D*$D/2
            - (5 + 3*$T + 10*$C - 4*$C*$C) * $D*$D*$D*$D/24);

        $lon_central = deg2rad(($zonaNum - 1) * 6 - 180 + 3);
        $longitude = $lon_central + ($D - (1 + 2*$T + $C) * $D*$D*$D/6
            + (5 - 2*$C + 28*$T) * $D*$D*$D*$D*$D/120) / cos($lat_rad);

        return [
            'latitude' => round(rad2deg($latitude), 7),
            'longitude' => round(rad2deg($longitude), 7),
            'datum' => $datum
        ];
    }

    public function calcularZonaUTM(float $longitude): int
    {
        return (int) floor(($longitude + 180) / 6) + 1;
    }

    public function determinarZonaUTMBrasil(string $estado): ?string
    {
        $zonas = [
            'AC' => '18S', 'AL' => '25S', 'AP' => '22N', 'AM' => '20S',
            'BA' => '24S', 'CE' => '24S', 'DF' => '23S', 'ES' => '24S',
            'GO' => '22S', 'MA' => '23S', 'MT' => '21S', 'MS' => '21S',
            'MG' => '23S', 'PA' => '22S', 'PB' => '25S', 'PR' => '22S',
            'PE' => '25S', 'PI' => '23S', 'RJ' => '23S', 'RN' => '25S',
            'RS' => '22S', 'RO' => '20S', 'RR' => '20N', 'SC' => '22S',
            'SP' => '23S', 'SE' => '24S', 'TO' => '22S'
        ];

        return $zonas[$estado] ?? null;
    }

    public function calcularDistancia(
        float $este1,
        float $norte1,
        float $este2,
        float $norte2
    ): float {
        $deltaEste = $este2 - $este1;
        $deltaNorte = $norte2 - $norte1;

        $distancia = sqrt($deltaEste * $deltaEste + $deltaNorte * $deltaNorte);

        return round($distancia, 2);
    }

    public function calcularAzimute(
        float $este1,
        float $norte1,
        float $este2,
        float $norte2
    ): float {
        $deltaEste = $este2 - $este1;
        $deltaNorte = $norte2 - $norte1;

        $azimute = rad2deg(atan2($deltaEste, $deltaNorte));

        if ($azimute < 0) {
            $azimute += 360;
        }

        return round($azimute, 2);
    }

    public function validarCoordenadasUTM(
        float $este,
        float $norte,
        string $zona
    ): bool {
        if ($este < 160000 || $este > 840000) {
            return false;
        }

        if ($norte < 0 || $norte > 10000000) {
            return false;
        }

        $zonaNum = (int) preg_replace('/[^0-9]/', '', $zona);
        if ($zonaNum < 1 || $zonaNum > 60) {
            return false;
        }

        return true;
    }

    public function formatarCoordenadas(float $este, float $norte, string $zona): string
    {
        return sprintf(
            'E: %.2f m, N: %.2f m (Zona %s)',
            $este,
            $norte,
            $zona
        );
    }

    public function converterDDparaGMS(float $decimal): string
    {
        $graus = floor(abs($decimal));
        $minutosDecimal = (abs($decimal) - $graus) * 60;
        $minutos = floor($minutosDecimal);
        $segundos = ($minutosDecimal - $minutos) * 60;

        $sinal = $decimal < 0 ? '-' : '';

        return sprintf(
            "%s%d° %d' %.2f\"",
            $sinal,
            $graus,
            $minutos,
            $segundos
        );
    }

    public function converterGMSparaDD(int $graus, int $minutos, float $segundos): float
    {
        $decimal = abs($graus) + ($minutos / 60) + ($segundos / 3600);

        if ($graus < 0) {
            $decimal *= -1;
        }

        return round($decimal, 7);
    }
}
