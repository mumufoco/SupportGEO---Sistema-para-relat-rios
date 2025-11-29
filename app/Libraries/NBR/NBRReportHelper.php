<?php

namespace App\Libraries\NBR;

class NBRReportHelper
{
    private SoloClassificador $soloClass;
    private NBRCalculator $nbrCalc;
    private SPTCalculator $sptCalc;

    public function __construct()
    {
        $this->soloClass = new SoloClassificador();
        $this->nbrCalc = new NBRCalculator();
        $this->sptCalc = new SPTCalculator();
    }

    public function montarDadosCabecalho(
        array $sondagem,
        array $empresa,
        array $responsavel,
        array $obra,
        array $projeto
    ): array {
        return [
            'empresa' => [
                'razao_social' => $empresa['razao_social'],
                'endereco' => $empresa['endereco_completo'],
                'telefone' => $empresa['telefone'] ?? '',
                'email' => $empresa['email'] ?? '',
                'logo' => $empresa['logo_path'] ?? null
            ],
            'responsavel' => [
                'nome' => $responsavel['nome'],
                'crea' => $responsavel['crea'],
                'cargo' => $responsavel['cargo'],
                'assinatura' => $responsavel['assinatura_path'] ?? null
            ],
            'projeto' => [
                'nome' => $projeto['nome'],
                'codigo' => $projeto['codigo'] ?? '',
                'cliente' => $projeto['cliente']
            ],
            'obra' => [
                'nome' => $obra['nome'],
                'endereco' => $obra['endereco'],
                'municipio' => $obra['municipio'],
                'uf' => $obra['uf'],
                'datum' => $obra['datum'] ?? 'SIRGAS2000',
                'zona_utm' => $obra['zona_utm'] ?? ''
            ],
            'sondagem' => [
                'codigo' => $sondagem['codigo_sondagem'],
                'data_execucao' => $sondagem['data_execucao'],
                'sondador' => $sondagem['sondador'],
                'coordenadas' => sprintf(
                    'E: %.2f m, N: %.2f m',
                    $sondagem['coordenada_este'],
                    $sondagem['coordenada_norte']
                ),
                'cota' => $sondagem['cota_boca_furo']
            ]
        ];
    }

    public function montarTabelaAmostras(array $amostras): array
    {
        $tabela = [];

        foreach ($amostras as $amostra) {
            $tabela[] = [
                'numero' => $amostra['numero_amostra'],
                'tipo' => $amostra['tipo_perfuracao'],
                'profundidade' => sprintf(
                    '%.2f - %.2f',
                    $amostra['profundidade_inicial'],
                    $amostra['profundidade_30cm_2']
                ),
                'golpes_1' => $amostra['golpes_1a'] ?? '-',
                'golpes_2' => $amostra['golpes_2a'],
                'golpes_3' => $amostra['golpes_3a'],
                'nspt_1_2' => $amostra['nspt_1a_2a'],
                'nspt_2_3' => $amostra['nspt_2a_3a'],
                'penetracao' => $amostra['penetracao_obtida'],
                'limite_golpes' => $amostra['limite_golpes'] ? 'Sim' : 'Não',
                'observacoes' => $amostra['observacoes'] ?? ''
            ];
        }

        return $tabela;
    }

    public function montarTabelaCamadas(array $camadas): array
    {
        $tabela = [];

        foreach ($camadas as $camada) {
            $tabela[] = [
                'numero' => $camada['numero_camada'],
                'profundidade' => sprintf(
                    '%.2f - %.2f',
                    $camada['profundidade_inicial'],
                    $camada['profundidade_final']
                ),
                'espessura' => sprintf(
                    '%.2f',
                    $camada['profundidade_final'] - $camada['profundidade_inicial']
                ),
                'classificacao' => $this->soloClass->getDescricaoPorClassificacao(
                    $camada['classificacao_principal']
                ),
                'descricao' => $camada['descricao_completa'],
                'cor' => $camada['cor'],
                'origem' => $this->soloClass->getDescricaoOrigem($camada['origem']),
                'cor_grafico' => $this->soloClass->getCorPorClassificacao(
                    $camada['classificacao_principal']
                )
            ];
        }

        return $tabela;
    }

    public function montarDadosEquipamentos(array $sondagem): array
    {
        return [
            'martelo' => [
                'peso' => sprintf('%.2f kg', $sondagem['peso_martelo']),
                'altura_queda' => sprintf('%.2f cm', $sondagem['altura_queda']),
                'sistema' => $sondagem['sistema_percussao'] === 'manual' ? 'Manual' : 'Mecânico'
            ],
            'amostrador' => [
                'diametro_externo' => sprintf('%.2f mm', $sondagem['diametro_amostrador_externo']),
                'diametro_interno' => sprintf('%.2f mm', $sondagem['diametro_amostrador_interno']),
                'razao_area' => sprintf(
                    '%.1f%%',
                    $this->nbrCalc->calcularRazaoAreaAmostrador(
                        $sondagem['diametro_amostrador_externo'],
                        $sondagem['diametro_amostrador_interno']
                    )
                )
            ],
            'revestimento' => [
                'diametro' => sprintf('%.2f mm', $sondagem['diametro_revestimento']),
                'profundidade' => sprintf('%.2f m', $sondagem['revestimento_profundidade'])
            ],
            'trado' => [
                'diametro' => sprintf('%.2f mm', $sondagem['diametro_trado']),
                'profundidade' => sprintf('%.2f m', $sondagem['profundidade_trado'] ?? 0)
            ]
        ];
    }

    public function montarDadosNivelAgua(array $sondagem): array
    {
        $dados = [];

        if ($sondagem['nivel_agua_inicial'] === 'presente') {
            $dados['inicial'] = [
                'presente' => true,
                'profundidade' => sprintf('%.2f m', $sondagem['nivel_agua_inicial_profundidade']),
                'data' => $sondagem['nivel_agua_inicial_data'] ?? null
            ];
        } else {
            $dados['inicial'] = ['presente' => false];
        }

        if ($sondagem['nivel_agua_final'] === 'presente') {
            $dados['final'] = [
                'presente' => true,
                'profundidade' => sprintf('%.2f m', $sondagem['nivel_agua_final_profundidade']),
                'data' => $sondagem['nivel_agua_final_data'] ?? null
            ];
        } else {
            $dados['final'] = ['presente' => false];
        }

        return $dados;
    }

    public function montarObservacoes(array $sondagem): array
    {
        $observacoes = [];

        if (!empty($sondagem['observacoes_paralisacao'])) {
            $observacoes['paralisacao'] = $sondagem['observacoes_paralisacao'];
        }

        if (!empty($sondagem['observacoes_gerais'])) {
            $observacoes['gerais'] = $sondagem['observacoes_gerais'];
        }

        $observacoes['profundidades'] = sprintf(
            'Profundidade final: %.2f m | Trado: %.2f m',
            $sondagem['profundidade_final'],
            $sondagem['profundidade_trado'] ?? 0
        );

        return $observacoes;
    }

    public function montarEstatisticas(array $amostras): array
    {
        $stats = $this->sptCalc->gerarEstatisticasSondagem($amostras);
        $perfil = $this->sptCalc->gerarPerfilResistencia($amostras);

        return [
            'basicas' => $stats,
            'perfil_resistencia' => $perfil,
            'camadas_impenetraveleis' => $this->sptCalc->identificarCamadasImpenetraveleis($amostras)
        ];
    }

    public function montarReferenciasNormativas(): array
    {
        return [
            'NBR 6484:2020' => 'Solo - Sondagem de simples reconhecimento com SPT - Método de ensaio',
            'NBR 6502:2022' => 'Rochas e solos - Terminologia',
            'NBR 13441:2021' => 'Rochas e solos - Simbologia gráfica',
            'NBR 15492:2007' => 'Sondagem de reconhecimento para fins de qualidade ambiental - Procedimento'
        ];
    }

    public function formatarData(string $data): string
    {
        return date('d/m/Y', strtotime($data));
    }

    public function formatarDataHora(string $dataHora): string
    {
        return date('d/m/Y H:i', strtotime($dataHora));
    }

    public function gerarNomeArquivo(string $codigoSondagem): string
    {
        $timestamp = date('Ymd_His');
        return sprintf('Sondagem_%s_%s.pdf', $codigoSondagem, $timestamp);
    }

    public function montarDadosCompletos(
        array $sondagem,
        array $amostras,
        array $camadas,
        array $fotos,
        array $empresa,
        array $responsavel,
        array $obra,
        array $projeto
    ): array {
        return [
            'cabecalho' => $this->montarDadosCabecalho(
                $sondagem,
                $empresa,
                $responsavel,
                $obra,
                $projeto
            ),
            'equipamentos' => $this->montarDadosEquipamentos($sondagem),
            'nivel_agua' => $this->montarDadosNivelAgua($sondagem),
            'amostras' => $this->montarTabelaAmostras($amostras),
            'camadas' => $this->montarTabelaCamadas($camadas),
            'observacoes' => $this->montarObservacoes($sondagem),
            'estatisticas' => $this->montarEstatisticas($amostras),
            'fotos' => $fotos,
            'referencias' => $this->montarReferenciasNormativas(),
            'metadata' => [
                'gerado_em' => date('Y-m-d H:i:s'),
                'versao' => $sondagem['versao'],
                'status' => $sondagem['status']
            ]
        ];
    }
}
