<?php

namespace App\Libraries\NBR;

class NBRCalculator
{
    public function calcularNSPT(int $golpes2a, int $golpes3a): int
    {
        return $golpes2a + $golpes3a;
    }

    public function calcularNSPTCorrigido(
        int $nspt,
        float $profundidade,
        ?float $nivelAgua = null
    ): float {
        $n60 = $nspt;

        if ($nivelAgua !== null && $profundidade > $nivelAgua) {
            $correctionFactor = 1.0 + 0.5 * (($profundidade - $nivelAgua) / $profundidade);
            $n60 = $nspt * $correctionFactor;
        }

        return round($n60, 2);
    }

    public function calcularCapacidadeCargaTerzaghi(
        int $nspt,
        float $larguraBase,
        float $profundidadeFundacao
    ): float {
        $k = 12;

        $admissivel = ($k * $nspt * pow($larguraBase + 0.3, 2)) / pow($larguraBase, 2);

        $admissivel *= (1 + 0.33 * ($profundidadeFundacao / $larguraBase));

        return round($admissivel, 2);
    }

    public function estimarAngulAtritoAreia(int $nspt): float
    {
        $phi = 28 + 0.15 * $nspt;

        return round(min($phi, 45), 1);
    }

    public function estimarCoesaoArgila(int $nspt): float
    {
        $cu = 10 * $nspt;

        return round($cu, 1);
    }

    public function classificarCompacidadeAreia(int $nspt): string
    {
        if ($nspt <= 4) {
            return 'fofa';
        } elseif ($nspt <= 8) {
            return 'pouco_compacta';
        } elseif ($nspt <= 18) {
            return 'medianamente_compacta';
        } elseif ($nspt <= 40) {
            return 'compacta';
        } else {
            return 'muito_compacta';
        }
    }

    public function classificarConsistenciaArgila(int $nspt): string
    {
        if ($nspt <= 2) {
            return 'muito_mole';
        } elseif ($nspt <= 5) {
            return 'mole';
        } elseif ($nspt <= 10) {
            return 'media';
        } elseif ($nspt <= 19) {
            return 'rija';
        } elseif ($nspt <= 40) {
            return 'muito_rija';
        } else {
            return 'dura';
        }
    }

    public function calcularRazaoAreaAmostrador(
        float $diametroExterno,
        float $diametroInterno
    ): float {
        $areaExterno = pi() * pow($diametroExterno / 2, 2);
        $areaInterno = pi() * pow($diametroInterno / 2, 2);
        $areaParede = $areaExterno - $areaInterno;

        $razao = $areaParede / $areaInterno;

        return round($razao * 100, 2);
    }

    public function calcularEnergiaTeoria(
        float $pesoMartelo,
        float $alturaQueda
    ): float {
        $energia = $pesoMartelo * $alturaQueda * 9.81;

        return round($energia, 2);
    }

    public function calcularEficienciaEnergetica(
        float $pesoMartelo,
        float $alturaQueda,
        string $sistemaPesrcussao = 'manual'
    ): float {
        $eficiencias = [
            'manual' => 0.60,
            'mecanico' => 0.75
        ];

        $eficiencia = $eficiencias[$sistemaPesrcussao] ?? 0.60;

        return round($eficiencia * 100, 1);
    }

    public function normalizarNSPTParaN60(
        int $nspt,
        float $eficiencia = 60.0
    ): int {
        $fatorCorrecao = $eficiencia / 60.0;

        $n60 = $nspt * $fatorCorrecao;

        return (int) round($n60);
    }

    public function calcularRecalqueAproximado(
        float $carga,
        int $nsptMedio,
        float $area
    ): float {
        $recalque = (0.02 * $carga) / ($nsptMedio * $area);

        return round($recalque * 100, 2);
    }

    public function calcularTensaoAdmissivelSimplificada(int $nspt): float
    {
        $tensao = ($nspt / 5) * 100;

        return round($tensao, 2);
    }

    public function verificarImpenetrabilidade(
        int $golpes,
        float $penetracao,
        int $limiteGolpes = 50
    ): array {
        $impenetravel = false;
        $motivo = '';

        if ($golpes >= $limiteGolpes && $penetracao < 45) {
            $impenetravel = true;
            $motivo = sprintf(
                'Impenetrável ao SPT: %d golpes para %.1f cm (< 45 cm)',
                $golpes,
                $penetracao
            );
        }

        return [
            'impenetravel' => $impenetravel,
            'motivo' => $motivo,
            'golpes' => $golpes,
            'penetracao' => $penetracao
        ];
    }

    public function calcularNSPTMedio(array $amostras, float $profInicial, float $profFinal): float
    {
        $valoresNaFaixa = [];

        foreach ($amostras as $amostra) {
            if ($amostra['profundidade_inicial'] >= $profInicial &&
                $amostra['profundidade_inicial'] <= $profFinal) {
                $valoresNaFaixa[] = $amostra['nspt_2a_3a'];
            }
        }

        if (empty($valoresNaFaixa)) {
            return 0.0;
        }

        $media = array_sum($valoresNaFaixa) / count($valoresNaFaixa);

        return round($media, 1);
    }

    public function interpolarNSPT(
        float $profundidadeAlvo,
        array $amostra1,
        array $amostra2
    ): float {
        $prof1 = $amostra1['profundidade_inicial'];
        $prof2 = $amostra2['profundidade_inicial'];
        $nspt1 = $amostra1['nspt_2a_3a'];
        $nspt2 = $amostra2['nspt_2a_3a'];

        if ($prof2 == $prof1) {
            return $nspt1;
        }

        $nsptInterpolado = $nspt1 + (($nspt2 - $nspt1) * ($profundidadeAlvo - $prof1) / ($prof2 - $prof1));

        return round($nsptInterpolado, 1);
    }
}
