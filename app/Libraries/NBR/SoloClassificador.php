<?php

namespace App\Libraries\NBR;

class SoloClassificador
{
    private array $cores = [
        'argila' => '#8B4513',
        'silte' => '#D2B48C',
        'areia' => '#F4A460',
        'pedregulho' => '#A9A9A9',
        'argila_arenosa' => '#A0522D',
        'argila_siltosa' => '#CD853F',
        'argila_silto_arenosa' => '#B8860B',
        'silte_arenoso' => '#DEB887',
        'silte_argiloso' => '#BC8F8F',
        'silte_argilo_arenoso' => '#D2691E',
        'areia_argilosa' => '#DAA520',
        'areia_siltosa' => '#F0E68C',
        'areia_silto_argilosa' => '#EEE8AA',
        'aterro' => '#696969',
        'turfa' => '#2F4F4F',
        'materia_organica' => '#3B3B3B',
        'rocha' => '#708090',
        'vegetacao' => '#228B22',
        'expurgo' => '#FFFFFF'
    ];

    private array $descricoes = [
        'argila' => 'Argila',
        'silte' => 'Silte',
        'areia' => 'Areia',
        'pedregulho' => 'Pedregulho',
        'argila_arenosa' => 'Argila Arenosa',
        'argila_siltosa' => 'Argila Siltosa',
        'argila_silto_arenosa' => 'Argila Silto-Arenosa',
        'silte_arenoso' => 'Silte Arenoso',
        'silte_argiloso' => 'Silte Argiloso',
        'silte_argilo_arenoso' => 'Silte Argilo-Arenoso',
        'areia_argilosa' => 'Areia Argilosa',
        'areia_siltosa' => 'Areia Siltosa',
        'areia_silto_argilosa' => 'Areia Silto-Argilosa',
        'aterro' => 'Aterro',
        'turfa' => 'Turfa',
        'materia_organica' => 'Matéria Orgânica',
        'rocha' => 'Rocha',
        'vegetacao' => 'Vegetação',
        'expurgo' => 'Expurgo'
    ];

    private array $origensDescricao = [
        'SR' => 'Solo Residual',
        'SA' => 'Solo Aluvionar',
        'AT' => 'Aterro',
        'AO' => 'Argila Orgânica',
        'RO' => 'Rocha'
    ];

    public function getCorPorClassificacao(string $classificacao): string
    {
        return $this->cores[$classificacao] ?? '#CCCCCC';
    }

    public function getDescricaoPorClassificacao(string $classificacao): string
    {
        return $this->descricoes[$classificacao] ?? $classificacao;
    }

    public function getDescricaoOrigem(string $origem): string
    {
        return $this->origensDescricao[$origem] ?? $origem;
    }

    public function montarDescricaoCompleta(array $camada): string
    {
        $descricao = [];

        $descricao[] = $this->getDescricaoPorClassificacao($camada['classificacao_principal']);

        if (!empty($camada['classificacao_secundaria'])) {
            $descricao[] = 'com ' . strtolower($camada['classificacao_secundaria']);
        }

        if (!empty($camada['cor'])) {
            $descricao[] = strtolower($camada['cor']);
        }

        if (!empty($camada['consistencia'])) {
            $descricao[] = $this->traduzirConsistencia($camada['consistencia']);
        }

        if (!empty($camada['compacidade'])) {
            $descricao[] = $this->traduzirCompacidade($camada['compacidade']);
        }

        if (!empty($camada['origem'])) {
            $descricao[] = '(' . $this->getDescricaoOrigem($camada['origem']) . ')';
        }

        return implode(', ', $descricao);
    }

    private function traduzirConsistencia(string $consistencia): string
    {
        $traducoes = [
            'muito_mole' => 'muito mole',
            'mole' => 'mole',
            'media' => 'média',
            'rija' => 'rija',
            'muito_rija' => 'muito rija',
            'dura' => 'dura'
        ];

        return $traducoes[$consistencia] ?? $consistencia;
    }

    private function traduzirCompacidade(string $compacidade): string
    {
        $traducoes = [
            'fofa' => 'fofa',
            'pouco_compacta' => 'pouco compacta',
            'medianamente_compacta' => 'medianamente compacta',
            'compacta' => 'compacta',
            'muito_compacta' => 'muito compacta'
        ];

        return $traducoes[$compacidade] ?? $compacidade;
    }

    public function sugerirClassificacao(
        int $nspt,
        ?string $descricaoVisual = null
    ): array {
        $sugestoes = [];

        if ($nspt <= 4) {
            $sugestoes[] = [
                'tipo' => 'areia',
                'secundario' => null,
                'compacidade' => 'fofa',
                'probabilidade' => 60
            ];
            $sugestoes[] = [
                'tipo' => 'argila',
                'secundario' => null,
                'consistencia' => 'muito_mole',
                'probabilidade' => 40
            ];
        } elseif ($nspt <= 10) {
            $sugestoes[] = [
                'tipo' => 'areia',
                'secundario' => 'siltosa',
                'compacidade' => 'medianamente_compacta',
                'probabilidade' => 50
            ];
            $sugestoes[] = [
                'tipo' => 'argila',
                'secundario' => 'arenosa',
                'consistencia' => 'media',
                'probabilidade' => 50
            ];
        } elseif ($nspt <= 30) {
            $sugestoes[] = [
                'tipo' => 'areia',
                'secundario' => null,
                'compacidade' => 'compacta',
                'probabilidade' => 70
            ];
            $sugestoes[] = [
                'tipo' => 'argila',
                'secundario' => 'arenosa',
                'consistencia' => 'rija',
                'probabilidade' => 30
            ];
        } else {
            $sugestoes[] = [
                'tipo' => 'areia',
                'secundario' => 'pedregulhosa',
                'compacidade' => 'muito_compacta',
                'probabilidade' => 60
            ];
            $sugestoes[] = [
                'tipo' => 'argila',
                'secundario' => null,
                'consistencia' => 'dura',
                'probabilidade' => 20
            ];
            $sugestoes[] = [
                'tipo' => 'rocha',
                'secundario' => 'alterada',
                'probabilidade' => 20
            ];
        }

        return $sugestoes;
    }

    public function validarClassificacao(string $classificacao): bool
    {
        return array_key_exists($classificacao, $this->cores);
    }

    public function getTodasClassificacoes(): array
    {
        return array_keys($this->cores);
    }

    public function getClassificacoesPorGrupo(): array
    {
        return [
            'Finos' => ['argila', 'silte', 'argila_siltosa', 'silte_argiloso'],
            'Arenosos' => ['areia', 'areia_siltosa', 'areia_argilosa', 'areia_silto_argilosa'],
            'Mistos' => ['argila_arenosa', 'argila_silto_arenosa', 'silte_arenoso', 'silte_argilo_arenoso'],
            'Grossos' => ['pedregulho'],
            'Especiais' => ['aterro', 'turfa', 'materia_organica', 'rocha', 'vegetacao', 'expurgo']
        ];
    }

    public function inferirGranulometria(string $classificacao): array
    {
        $granulometrias = [
            'argila' => ['argila' => 60, 'silte' => 30, 'areia' => 10],
            'silte' => ['silte' => 60, 'argila' => 20, 'areia' => 20],
            'areia' => ['areia' => 80, 'silte' => 15, 'argila' => 5],
            'pedregulho' => ['pedregulho' => 70, 'areia' => 25, 'finos' => 5],
            'argila_arenosa' => ['argila' => 50, 'areia' => 35, 'silte' => 15],
            'argila_siltosa' => ['argila' => 50, 'silte' => 35, 'areia' => 15],
            'silte_arenoso' => ['silte' => 50, 'areia' => 40, 'argila' => 10],
            'areia_siltosa' => ['areia' => 60, 'silte' => 30, 'argila' => 10],
            'areia_argilosa' => ['areia' => 60, 'argila' => 25, 'silte' => 15]
        ];

        return $granulometrias[$classificacao] ?? [];
    }
}
