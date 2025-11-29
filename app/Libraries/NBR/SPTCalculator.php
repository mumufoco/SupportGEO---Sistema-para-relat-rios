<?php

namespace App\Libraries\NBR;

class SPTCalculator
{
    private NBRCalculator $nbrCalc;

    public function __construct()
    {
        $this->nbrCalc = new NBRCalculator();
    }

    public function gerarEstatisticasSondagem(array $amostras): array
    {
        if (empty($amostras)) {
            return [
                'total_amostras' => 0,
                'nspt_minimo' => 0,
                'nspt_maximo' => 0,
                'nspt_medio' => 0,
                'nspt_mediana' => 0,
                'profundidade_maxima' => 0
            ];
        }

        $nspts = array_column($amostras, 'nspt_2a_3a');
        sort($nspts);

        $count = count($nspts);
        $mediana = $count % 2 == 0
            ? ($nspts[$count/2 - 1] + $nspts[$count/2]) / 2
            : $nspts[floor($count/2)];

        $profundidades = array_column($amostras, 'profundidade_inicial');

        return [
            'total_amostras' => count($amostras),
            'nspt_minimo' => min($nspts),
            'nspt_maximo' => max($nspts),
            'nspt_medio' => round(array_sum($nspts) / count($nspts), 1),
            'nspt_mediana' => round($mediana, 1),
            'profundidade_maxima' => max($profundidades)
        ];
    }

    public function gerarPerfilResistencia(array $amostras): array
    {
        $perfil = [
            'muito_baixa' => 0,
            'baixa' => 0,
            'media' => 0,
            'alta' => 0,
            'muito_alta' => 0
        ];

        foreach ($amostras as $amostra) {
            $nspt = $amostra['nspt_2a_3a'];

            if ($nspt <= 4) {
                $perfil['muito_baixa']++;
            } elseif ($nspt <= 10) {
                $perfil['baixa']++;
            } elseif ($nspt <= 30) {
                $perfil['media']++;
            } elseif ($nspt <= 50) {
                $perfil['alta']++;
            } else {
                $perfil['muito_alta']++;
            }
        }

        return $perfil;
    }

    public function identificarCamadasImpenetraveleis(array $amostras): array
    {
        $impenetraveleis = [];

        foreach ($amostras as $amostra) {
            if ($amostra['limite_golpes'] ||
                ($amostra['nspt_2a_3a'] > 50 && $amostra['penetracao_obtida'] < 45)) {
                $impenetraveleis[] = [
                    'profundidade' => $amostra['profundidade_inicial'],
                    'nspt' => $amostra['nspt_2a_3a'],
                    'penetracao' => $amostra['penetracao_obtida'],
                    'numero_amostra' => $amostra['numero_amostra']
                ];
            }
        }

        return $impenetraveleis;
    }

    public function sugerirProfundidadeFundacao(
        array $amostras,
        int $nsptMinimo = 8,
        float $espessuraMinima = 2.0
    ): ?array {
        $profundidadeSugerida = null;
        $espessuraAtual = 0;
        $profInicial = null;

        foreach ($amostras as $amostra) {
            $nspt = $amostra['nspt_2a_3a'];
            $prof = $amostra['profundidade_inicial'];

            if ($nspt >= $nsptMinimo) {
                if ($profInicial === null) {
                    $profInicial = $prof;
                }
                $espessuraAtual = $prof - $profInicial + 0.45;

                if ($espessuraAtual >= $espessuraMinima) {
                    $profundidadeSugerida = [
                        'profundidade_inicial' => $profInicial,
                        'profundidade_final' => $prof + 0.45,
                        'espessura' => $espessuraAtual,
                        'nspt_medio' => $this->nbrCalc->calcularNSPTMedio(
                            $amostras,
                            $profInicial,
                            $prof + 0.45
                        )
                    ];
                    break;
                }
            } else {
                $profInicial = null;
                $espessuraAtual = 0;
            }
        }

        return $profundidadeSugerida;
    }

    public function calcularTensaoAdmissivel(
        array $amostras,
        float $profundidadeFundacao,
        float $larguraBase = 1.0,
        int $fatorSeguranca = 3
    ): ?array {
        $amostrasProfundidade = array_filter($amostras, function($a) use ($profundidadeFundacao) {
            return $a['profundidade_inicial'] >= $profundidadeFundacao - 0.5 &&
                   $a['profundidade_inicial'] <= $profundidadeFundacao + 1.5;
        });

        if (empty($amostrasProfundidade)) {
            return null;
        }

        $nsptMedio = $this->nbrCalc->calcularNSPTMedio(
            array_values($amostrasProfundidade),
            $profundidadeFundacao - 0.5,
            $profundidadeFundacao + 1.5
        );

        $capacidade = $this->nbrCalc->calcularCapacidadeCargaTerzaghi(
            (int) $nsptMedio,
            $larguraBase,
            $profundidadeFundacao
        );

        $admissivel = $capacidade / $fatorSeguranca;

        return [
            'profundidade' => $profundidadeFundacao,
            'nspt_medio' => $nsptMedio,
            'capacidade_carga' => $capacidade,
            'tensao_admissivel' => round($admissivel, 2),
            'fator_seguranca' => $fatorSeguranca
        ];
    }

    public function compararSondagens(array $sondagens): array
    {
        $comparacao = [];

        foreach ($sondagens as $codigo => $amostras) {
            $stats = $this->gerarEstatisticasSondagem($amostras);
            $comparacao[$codigo] = $stats;
        }

        return $comparacao;
    }

    public function detectarCamadaMole(
        array $amostras,
        int $nsptMaximo = 4,
        float $espessuraMinima = 1.0
    ): array {
        $camadasMoles = [];
        $profInicial = null;
        $profFinal = null;

        foreach ($amostras as $amostra) {
            $nspt = $amostra['nspt_2a_3a'];
            $prof = $amostra['profundidade_inicial'];

            if ($nspt <= $nsptMaximo) {
                if ($profInicial === null) {
                    $profInicial = $prof;
                }
                $profFinal = $prof + 0.45;
            } else {
                if ($profInicial !== null && ($profFinal - $profInicial) >= $espessuraMinima) {
                    $camadasMoles[] = [
                        'profundidade_inicial' => $profInicial,
                        'profundidade_final' => $profFinal,
                        'espessura' => round($profFinal - $profInicial, 2),
                        'nspt_medio' => $this->nbrCalc->calcularNSPTMedio(
                            $amostras,
                            $profInicial,
                            $profFinal
                        )
                    ];
                }
                $profInicial = null;
                $profFinal = null;
            }
        }

        if ($profInicial !== null && ($profFinal - $profInicial) >= $espessuraMinima) {
            $camadasMoles[] = [
                'profundidade_inicial' => $profInicial,
                'profundidade_final' => $profFinal,
                'espessura' => round($profFinal - $profInicial, 2),
                'nspt_medio' => $this->nbrCalc->calcularNSPTMedio(
                    $amostras,
                    $profInicial,
                    $profFinal
                )
            ];
        }

        return $camadasMoles;
    }

    public function gerarGraficoDados(array $amostras): array
    {
        $dados = [
            'labels' => [],
            'nspt' => [],
            'profundidades' => []
        ];

        foreach ($amostras as $amostra) {
            $dados['labels'][] = 'SP-' . $amostra['numero_amostra'];
            $dados['nspt'][] = $amostra['nspt_2a_3a'];
            $dados['profundidades'][] = $amostra['profundidade_inicial'];
        }

        return $dados;
    }

    public function estimarLiquefacao(
        array $amostras,
        float $profundidadeNivelAgua
    ): array {
        $risco = [];

        foreach ($amostras as $amostra) {
            $prof = $amostra['profundidade_inicial'];
            $nspt = $amostra['nspt_2a_3a'];

            if ($prof > $profundidadeNivelAgua && $prof < 20) {
                $nsptCorrigido = $this->nbrCalc->calcularNSPTCorrigido(
                    $nspt,
                    $prof,
                    $profundidadeNivelAgua
                );

                if ($nsptCorrigido < 10) {
                    $risco[] = [
                        'profundidade' => $prof,
                        'nspt' => $nspt,
                        'nspt_corrigido' => $nsptCorrigido,
                        'nivel_risco' => $nsptCorrigido < 5 ? 'alto' : 'moderado'
                    ];
                }
            }
        }

        return $risco;
    }
}
