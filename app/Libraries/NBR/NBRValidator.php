<?php

namespace App\Libraries\NBR;

class NBRValidator
{
    private array $errors = [];
    private array $warnings = [];
    private int $score = 100;

    public function validateSondagem(array $sondagem): array
    {
        $this->errors = [];
        $this->warnings = [];
        $this->score = 100;

        $this->validateEquipamentos($sondagem);
        $this->validateCoordenadas($sondagem);
        $this->validateProfundidades($sondagem);
        $this->validateResponsavel($sondagem);
        $this->validateDados($sondagem);
        $this->validateParalisacao($sondagem);

        return [
            'conforme' => empty($this->errors),
            'score' => max(0, $this->score),
            'errors' => $this->errors,
            'warnings' => $this->warnings
        ];
    }

    private function validateEquipamentos(array $sondagem): void
    {
        if (abs($sondagem['peso_martelo'] - 65.00) > 0.5) {
            $this->errors[] = 'Peso do martelo fora da especificação NBR 6484:2020 (65 ± 0,5 kg)';
            $this->score -= 15;
        }

        if (abs($sondagem['altura_queda'] - 75.00) > 0.5) {
            $this->errors[] = 'Altura de queda fora da especificação NBR 6484:2020 (75 ± 0,5 cm)';
            $this->score -= 15;
        }

        $dExterno = $sondagem['diametro_amostrador_externo'];
        if ($dExterno < 50.60 || $dExterno > 51.00) {
            $this->warnings[] = 'Diâmetro externo do amostrador fora do padrão (50,8 mm ± 0,2 mm)';
            $this->score -= 5;
        }

        $dInterno = $sondagem['diametro_amostrador_interno'];
        if ($dInterno < 34.70 || $dInterno > 35.10) {
            $this->warnings[] = 'Diâmetro interno do amostrador fora do padrão (34,9 mm ± 0,2 mm)';
            $this->score -= 5;
        }

        $razaoAreaAmostrador = (($dExterno ** 2) - ($dInterno ** 2)) / ($dInterno ** 2);
        if ($razaoAreaAmostrador < 0.95 || $razaoAreaAmostrador > 1.20) {
            $this->errors[] = sprintf(
                'Razão de área do amostrador (%.2f) fora dos limites NBR 6484:2020 (95%% a 120%%)',
                $razaoAreaAmostrador * 100
            );
            $this->score -= 10;
        }
    }

    private function validateCoordenadas(array $sondagem): void
    {
        if (empty($sondagem['coordenada_este']) || empty($sondagem['coordenada_norte'])) {
            $this->errors[] = 'Coordenadas UTM são obrigatórias conforme NBR 6484:2020 (5.2.1)';
            $this->score -= 10;
        }

        if ($sondagem['coordenada_este'] < 0 || $sondagem['coordenada_norte'] < 0) {
            $this->errors[] = 'Coordenadas UTM inválidas';
            $this->score -= 10;
        }
    }

    private function validateProfundidades(array $sondagem): void
    {
        if (empty($sondagem['profundidade_final']) || $sondagem['profundidade_final'] <= 0) {
            $this->errors[] = 'Profundidade final deve ser maior que zero';
            $this->score -= 15;
        }

        if (!empty($sondagem['profundidade_trado'])) {
            if ($sondagem['profundidade_trado'] > $sondagem['profundidade_final']) {
                $this->errors[] = 'Profundidade de trado não pode ser maior que profundidade final';
                $this->score -= 5;
            }
        }

        if (!empty($sondagem['revestimento_profundidade'])) {
            if ($sondagem['revestimento_profundidade'] > $sondagem['profundidade_final']) {
                $this->warnings[] = 'Profundidade de revestimento maior que profundidade final';
                $this->score -= 3;
            }
        }
    }

    private function validateResponsavel(array $sondagem): void
    {
        if (empty($sondagem['responsavel_tecnico_id'])) {
            $this->errors[] = 'Responsável técnico habilitado é obrigatório (NBR 6484:2020 - 5.1.1)';
            $this->score -= 20;
        }

        if (empty($sondagem['sondador'])) {
            $this->warnings[] = 'Nome do sondador não informado (NBR 6484:2020 - 5.2.1)';
            $this->score -= 5;
        }
    }

    private function validateDados(array $sondagem): void
    {
        if (empty($sondagem['data_execucao'])) {
            $this->errors[] = 'Data de execução é obrigatória';
            $this->score -= 10;
        }

        if (empty($sondagem['codigo_sondagem'])) {
            $this->errors[] = 'Código da sondagem é obrigatório';
            $this->score -= 10;
        }
    }

    private function validateParalisacao(array $sondagem): void
    {
        if ($sondagem['profundidade_final'] < 20) {
            if (empty($sondagem['observacoes_paralisacao'])) {
                $this->errors[] = 'Sondagens com menos de 20m devem ter justificativa de paralisação (NBR 6484:2020 - 5.2.4.1)';
                $this->score -= 15;
            }
        }
    }

    public function validateAmostra(array $amostra): array
    {
        $errors = [];
        $warnings = [];

        if ($amostra['golpes_2a'] < 0 || $amostra['golpes_2a'] > 60) {
            $warnings[] = 'Número de golpes da 2ª seção anormal (0-60 esperado)';
        }

        if ($amostra['golpes_3a'] < 0 || $amostra['golpes_3a'] > 60) {
            $warnings[] = 'Número de golpes da 3ª seção anormal (0-60 esperado)';
        }

        $nsptCalculado = $amostra['golpes_2a'] + $amostra['golpes_3a'];
        if ($amostra['nspt_2a_3a'] != $nsptCalculado) {
            $errors[] = sprintf(
                'NSPT calculado (%d) difere do informado (%d)',
                $nsptCalculado,
                $amostra['nspt_2a_3a']
            );
        }

        if (!empty($amostra['golpes_1a'])) {
            $nspt1a2a = $amostra['golpes_1a'] + $amostra['golpes_2a'];
            if ($amostra['nspt_1a_2a'] != $nspt1a2a) {
                $errors[] = sprintf(
                    'NSPT 1ª+2ª calculado (%d) difere do informado (%d)',
                    $nspt1a2a,
                    $amostra['nspt_1a_2a']
                );
            }
        }

        if ($amostra['penetracao_obtida'] < 45.0 && !$amostra['limite_golpes']) {
            $warnings[] = 'Penetração menor que 45cm sem indicação de limite de golpes';
        }

        return [
            'valido' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    public function validateCamada(array $camada): array
    {
        $errors = [];
        $warnings = [];

        if ($camada['profundidade_final'] <= $camada['profundidade_inicial']) {
            $errors[] = 'Profundidade final deve ser maior que profundidade inicial';
        }

        if (empty($camada['classificacao_principal'])) {
            $errors[] = 'Classificação do solo é obrigatória (NBR 6502:2022)';
        }

        if (empty($camada['cor'])) {
            $warnings[] = 'Cor do solo não informada (NBR 6484:2020 - 5.2.3)';
        }

        if (empty($camada['descricao_completa'])) {
            $errors[] = 'Descrição completa da camada é obrigatória (NBR 6484:2020 - 5.2.3)';
        }

        $classificacoesValidas = [
            'argila', 'silte', 'areia', 'pedregulho',
            'argila_arenosa', 'argila_siltosa', 'argila_silto_arenosa',
            'silte_arenoso', 'silte_argiloso', 'silte_argilo_arenoso',
            'areia_argilosa', 'areia_siltosa', 'areia_silto_argilosa',
            'aterro', 'turfa', 'materia_organica', 'rocha', 'vegetacao', 'expurgo'
        ];

        if (!in_array($camada['classificacao_principal'], $classificacoesValidas)) {
            $errors[] = 'Classificação do solo inválida conforme NBR 6502:2022';
        }

        return [
            'valido' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function getScore(): int
    {
        return max(0, $this->score);
    }
}
