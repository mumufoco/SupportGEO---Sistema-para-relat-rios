# FASE 3: BIBLIOTECAS NBR (CÁLCULOS E VALIDAÇÕES)

**Tempo estimado:** 3-4 dias  
**Objetivo:** Criar bibliotecas especializadas para cálculos SPT e validação de conformidade NBR

---

## 🎯 Objetivos

- Criar SPTCalculator para cálculos geotécnicos
- Criar NBRValidator para validação de conformidade
- Criar SoloClassificador para classificação de solos
- Garantir conformidade com NBR 6484:2020

---

## 📝 COMANDOS INICIAIS

```bash
# Comando 1: Criar diretório de bibliotecas
mkdir -p app/Libraries
touch app/Libraries/SPTCalculator.php
touch app/Libraries/NBRValidator.php
touch app/Libraries/SoloClassificador.php
```

---

## 📚 BIBLIOTECA: SPTCalculator

Criar `app/Libraries/SPTCalculator.php`:

```php
<?php

namespace App\Libraries;

/**
 * SPT Calculator
 * Biblioteca para cálculos geotécnicos conforme NBR 6484:2020
 * 
 * @author Support Solo Sondagens
 * @version 1.0
 */
class SPTCalculator
{
    // ========================================
    // CONSTANTES NBR 6484:2020
    // ========================================
    
    /** Peso do martelo em kgf */
    public const PESO_MARTELO_NBR = 65.0;
    
    /** Altura de queda em cm */
    public const ALTURA_QUEDA_NBR = 75.0;
    
    /** Diâmetro externo do amostrador em mm */
    public const DIAMETRO_EXTERNO_NBR = 50.8;
    
    /** Diâmetro interno do amostrador em mm */
    public const DIAMETRO_INTERNO_NBR = 34.9;
    
    /** Tolerância para diâmetros em mm */
    public const TOLERANCIA_DIAMETRO = 0.2;
    
    /** Penetração padrão por etapa em cm */
    public const PENETRACAO_ETAPA = 15.0;
    
    /** Penetração total em cm */
    public const PENETRACAO_TOTAL = 45.0;
    
    /** Limite de golpes para impenetrável */
    public const LIMITE_GOLPES = 50;

    // ========================================
    // CÁLCULOS NSPT
    // ========================================

    /**
     * Calcular NSPT (N30) - soma dos golpes 2ª + 3ª etapa
     * Conforme NBR 6484:2020 - Item 5.2.2.1
     * 
     * @param int $golpes2a Golpes na 2ª etapa (15 cm)
     * @param int $golpes3a Golpes na 3ª etapa (15 cm)
     * @return int N30 (índice de resistência à penetração)
     */
    public static function calcularN30(int $golpes2a, int $golpes3a): int
    {
        return $golpes2a + $golpes3a;
    }

    /**
     * Calcular NSPT corrigido para energia
     * Correção para energia de 60% (N60)
     * 
     * @param int $n30 Valor N30 medido
     * @param float $eficiencia Eficiência do sistema (0.5 a 1.0)
     * @return float N60 corrigido
     */
    public static function calcularN60(int $n30, float $eficiencia = 0.72): float
    {
        // N60 = N * (Er / 60)
        // Er = eficiência do sistema (típico 72% para sistema manual brasileiro)
        return $n30 * ($eficiencia * 100 / 60);
    }

    /**
     * Calcular profundidade da amostra
     * 
     * @param float $profundidadeInicial Profundidade inicial em metros
     * @param int $etapa Etapa (1, 2 ou 3)
     * @return float Profundidade após a etapa
     */
    public static function calcularProfundidade(float $profundidadeInicial, int $etapa): float
    {
        $penetracaoMetros = self::PENETRACAO_ETAPA / 100;
        return $profundidadeInicial + ($penetracaoMetros * $etapa);
    }

    // ========================================
    // CLASSIFICAÇÃO DE CONSISTÊNCIA/COMPACIDADE
    // ========================================

    /**
     * Classificar consistência de solos argilosos
     * Conforme Tabela NBR 6484:2020
     * 
     * @param int $n30 Índice N30
     * @return string Classificação da consistência
     */
    public static function classificarConsistencia(int $n30): string
    {
        if ($n30 <= 2) {
            return 'muito_mole';
        } elseif ($n30 <= 4) {
            return 'mole';
        } elseif ($n30 <= 8) {
            return 'media';
        } elseif ($n30 <= 15) {
            return 'rija';
        } elseif ($n30 <= 30) {
            return 'muito_rija';
        } else {
            return 'dura';
        }
    }

    /**
     * Obter descrição textual da consistência
     */
    public static function getDescricaoConsistencia(string $consistencia): string
    {
        $descricoes = [
            'muito_mole' => 'muito mole',
            'mole' => 'mole',
            'media' => 'média',
            'rija' => 'rija',
            'muito_rija' => 'muito rija',
            'dura' => 'dura',
        ];

        return $descricoes[$consistencia] ?? $consistencia;
    }

    /**
     * Classificar compacidade de solos arenosos
     * Conforme Tabela NBR 6484:2020
     * 
     * @param int $n30 Índice N30
     * @return string Classificação da compacidade
     */
    public static function classificarCompacidade(int $n30): string
    {
        if ($n30 <= 4) {
            return 'fofa';
        } elseif ($n30 <= 8) {
            return 'pouco_compacta';
        } elseif ($n30 <= 18) {
            return 'medianamente_compacta';
        } elseif ($n30 <= 40) {
            return 'compacta';
        } else {
            return 'muito_compacta';
        }
    }

    /**
     * Obter descrição textual da compacidade
     */
    public static function getDescricaoCompacidade(string $compacidade): string
    {
        $descricoes = [
            'fofa' => 'fofa',
            'pouco_compacta' => 'pouco compacta',
            'medianamente_compacta' => 'medianamente compacta',
            'compacta' => 'compacta',
            'muito_compacta' => 'muito compacta',
        ];

        return $descricoes[$compacidade] ?? $compacidade;
    }

    // ========================================
    // CÁLCULOS DE CAPACIDADE DE CARGA
    // ========================================

    /**
     * Estimar tensão admissível pelo método Décourt-Quaresma
     * Para fundações diretas (sapatas)
     * 
     * @param int $nMedio N30 médio do bulbo de tensões
     * @param float $largura Largura da sapata em metros
     * @param float $profundidade Profundidade de assentamento em metros
     * @return float Tensão admissível em kPa
     */
    public static function tensaoAdmissivelDecourt(int $nMedio, float $largura, float $profundidade): float
    {
        // Fórmula simplificada de Décourt-Quaresma
        // σadm = N/100 * (1 + 0.4*D/B) em MPa
        $tensaoMPa = ($nMedio / 100) * (1 + 0.4 * $profundidade / $largura);
        return $tensaoMPa * 1000; // Converter para kPa
    }

    /**
     * Estimar capacidade de carga de estaca
     * Método Décourt-Quaresma para estacas cravadas
     * 
     * @param array $nsptsLateral Array de N30 ao longo do fuste
     * @param int $nsptPonta N30 na ponta
     * @param float $diametro Diâmetro da estaca em metros
     * @param float $comprimento Comprimento cravado em metros
     * @return array Capacidade de carga [lateral, ponta, total] em kN
     */
    public static function capacidadeEstacaDecourt(
        array $nsptsLateral, 
        int $nsptPonta, 
        float $diametro, 
        float $comprimento
    ): array {
        // Área da ponta
        $areaPonta = pi() * pow($diametro / 2, 2);
        
        // Perímetro
        $perimetro = pi() * $diametro;

        // N médio lateral
        $nMedioLateral = count($nsptsLateral) > 0 ? array_sum($nsptsLateral) / count($nsptsLateral) : 0;

        // Resistência de ponta (rp = α * Np)
        $alpha = 120; // kPa (valor típico para solos residuais)
        $rp = $alpha * $nsptPonta;

        // Resistência lateral (rl = β * Nl)
        $beta = 10; // kPa/m (valor típico)
        $rl = $beta * ($nMedioLateral / 3 + 1);

        // Capacidades
        $qPonta = $rp * $areaPonta;
        $qLateral = $rl * $perimetro * $comprimento;
        $qTotal = $qPonta + $qLateral;

        return [
            'lateral' => round($qLateral, 2),
            'ponta' => round($qPonta, 2),
            'total' => round($qTotal, 2),
            'n_medio_lateral' => round($nMedioLateral, 1),
        ];
    }

    // ========================================
    // CORRELAÇÕES GEOTÉCNICAS
    // ========================================

    /**
     * Estimar peso específico do solo
     * 
     * @param string $tipoSolo Tipo de solo (argila, areia, silte)
     * @param int $n30 Índice N30
     * @return float Peso específico em kN/m³
     */
    public static function estimarPesoEspecifico(string $tipoSolo, int $n30): float
    {
        $base = [
            'argila' => 16.0,
            'silte' => 17.0,
            'areia' => 18.0,
        ];

        $gamma = $base[$tipoSolo] ?? 17.0;
        
        // Incremento baseado em N30
        $incremento = min($n30 / 50, 0.5) * 3;
        
        return $gamma + $incremento;
    }

    /**
     * Estimar coesão não-drenada (Su) para argilas
     * 
     * @param int $n30 Índice N30
     * @return float Coesão em kPa
     */
    public static function estimarCoesao(int $n30): float
    {
        // Su ≈ 6 * N30 (correlação típica para argilas brasileiras)
        return 6.0 * $n30;
    }

    /**
     * Estimar ângulo de atrito para areias
     * 
     * @param int $n30 Índice N30
     * @return float Ângulo de atrito em graus
     */
    public static function estimarAnguloAtrito(int $n30): float
    {
        // φ ≈ 28 + 0.4*N30 (correlação típica)
        $phi = 28 + 0.4 * $n30;
        return min($phi, 45); // Limite superior
    }

    // ========================================
    // UTILITÁRIOS
    // ========================================

    /**
     * Verificar se atingiu impenetrável
     * 
     * @param int $golpes Número de golpes
     * @param float $penetracao Penetração obtida em cm
     * @return bool
     */
    public static function isImpenetravel(int $golpes, float $penetracao): bool
    {
        return $golpes >= self::LIMITE_GOLPES && $penetracao < self::PENETRACAO_ETAPA;
    }

    /**
     * Calcular N30 equivalente quando não atinge 45cm
     * 
     * @param int $golpes Número de golpes
     * @param float $penetracao Penetração obtida em cm
     * @return int N30 equivalente
     */
    public static function calcularN30Equivalente(int $golpes, float $penetracao): int
    {
        if ($penetracao >= 30) {
            return $golpes;
        }
        
        // Extrapolar para 30 cm
        return (int) round($golpes * 30 / $penetracao);
    }

    /**
     * Formatar valor NSPT para exibição
     * 
     * @param int $n30 Valor N30
     * @param bool $impenetravel Flag de impenetrável
     * @return string
     */
    public static function formatarNSPT(int $n30, bool $impenetravel = false): string
    {
        if ($impenetravel) {
            return $n30 . '/0';
        }
        return (string) $n30;
    }
}
```

---

## 📚 BIBLIOTECA: NBRValidator

Criar `app/Libraries/NBRValidator.php`:

```php
<?php

namespace App\Libraries;

/**
 * NBR Validator
 * Validação de conformidade com NBR 6484:2020
 * 
 * @author Support Solo Sondagens
 * @version 1.0
 */
class NBRValidator
{
    protected array $erros = [];
    protected array $avisos = [];
    protected int $score = 100;

    // ========================================
    // PESOS DOS CRITÉRIOS
    // ========================================
    private const PESO_EQUIPAMENTO = 20;
    private const PESO_COORDENADAS = 15;
    private const PESO_CAMADAS = 15;
    private const PESO_AMOSTRAS = 20;
    private const PESO_FOTOS = 15;
    private const PESO_RESPONSAVEL = 10;
    private const PESO_OBSERVACOES = 5;

    /**
     * Validar sondagem completa
     * 
     * @param array $dados Dados completos da sondagem (do Repository)
     * @return array Resultado da validação
     */
    public function validarSondagem(array $dados): array
    {
        $this->erros = [];
        $this->avisos = [];
        $this->score = 100;

        $sondagem = $dados['sondagem'] ?? [];
        $camadas = $dados['camadas'] ?? [];
        $amostras = $dados['amostras'] ?? [];
        $fotos = $dados['fotos'] ?? [];
        $responsavel = $dados['responsavel'] ?? null;

        // Executar validações
        $this->validarEquipamento($sondagem);
        $this->validarCoordenadas($sondagem);
        $this->validarCamadas($camadas);
        $this->validarAmostras($amostras);
        $this->validarFotos($fotos);
        $this->validarResponsavelTecnico($responsavel);
        $this->validarObservacoes($sondagem);

        return [
            'conforme' => empty($this->erros),
            'score' => max(0, $this->score),
            'erros' => $this->erros,
            'avisos' => $this->avisos,
            'total_erros' => count($this->erros),
            'total_avisos' => count($this->avisos),
        ];
    }

    /**
     * Validar equipamento conforme NBR 6484:2020
     */
    protected function validarEquipamento(array $sondagem): void
    {
        // Peso do martelo: 65 kgf
        $pesoMartelo = $sondagem['peso_martelo'] ?? 0;
        if (abs($pesoMartelo - SPTCalculator::PESO_MARTELO_NBR) > 0.1) {
            $this->addErro(
                'equipamento',
                "Peso do martelo ({$pesoMartelo} kgf) não conforme. NBR exige 65 kgf.",
                self::PESO_EQUIPAMENTO / 4
            );
        }

        // Altura de queda: 75 cm
        $alturaQueda = $sondagem['altura_queda'] ?? 0;
        if (abs($alturaQueda - SPTCalculator::ALTURA_QUEDA_NBR) > 0.1) {
            $this->addErro(
                'equipamento',
                "Altura de queda ({$alturaQueda} cm) não conforme. NBR exige 75 cm.",
                self::PESO_EQUIPAMENTO / 4
            );
        }

        // Diâmetro externo: 50,8 mm ± 0,2
        $diamExterno = $sondagem['diametro_amostrador_externo'] ?? 0;
        if (abs($diamExterno - SPTCalculator::DIAMETRO_EXTERNO_NBR) > SPTCalculator::TOLERANCIA_DIAMETRO) {
            $this->addErro(
                'equipamento',
                "Diâmetro externo ({$diamExterno} mm) fora da tolerância. NBR: 50,8 ± 0,2 mm.",
                self::PESO_EQUIPAMENTO / 4
            );
        }

        // Diâmetro interno: 34,9 mm ± 0,2
        $diamInterno = $sondagem['diametro_amostrador_interno'] ?? 0;
        if (abs($diamInterno - SPTCalculator::DIAMETRO_INTERNO_NBR) > SPTCalculator::TOLERANCIA_DIAMETRO) {
            $this->addErro(
                'equipamento',
                "Diâmetro interno ({$diamInterno} mm) fora da tolerância. NBR: 34,9 ± 0,2 mm.",
                self::PESO_EQUIPAMENTO / 4
            );
        }
    }

    /**
     * Validar coordenadas
     */
    protected function validarCoordenadas(array $sondagem): void
    {
        if (empty($sondagem['coordenada_este']) || empty($sondagem['coordenada_norte'])) {
            $this->addErro(
                'coordenadas',
                'Coordenadas UTM obrigatórias.',
                self::PESO_COORDENADAS
            );
        } else {
            // Validar faixa de coordenadas UTM para Brasil
            $este = $sondagem['coordenada_este'];
            $norte = $sondagem['coordenada_norte'];

            if ($este < 100000 || $este > 900000) {
                $this->addAviso('coordenadas', "Coordenada E ({$este}) fora da faixa típica UTM.");
            }
            if ($norte < 1000000 || $norte > 10000000) {
                $this->addAviso('coordenadas', "Coordenada N ({$norte}) fora da faixa típica UTM.");
            }
        }
    }

    /**
     * Validar camadas estratigráficas
     */
    protected function validarCamadas(array $camadas): void
    {
        if (empty($camadas)) {
            $this->addErro(
                'camadas',
                'Nenhuma camada estratigráfica registrada.',
                self::PESO_CAMADAS
            );
            return;
        }

        foreach ($camadas as $i => $camada) {
            $num = $i + 1;

            // Verificar campos obrigatórios
            if (empty($camada['classificacao_principal'])) {
                $this->addErro('camadas', "Camada {$num}: Classificação principal obrigatória.");
            }
            if (empty($camada['descricao_completa'])) {
                $this->addErro('camadas', "Camada {$num}: Descrição completa obrigatória.");
            }
            if (empty($camada['cor'])) {
                $this->addErro('camadas', "Camada {$num}: Cor obrigatória.");
            }
            if (empty($camada['origem'])) {
                $this->addErro('camadas', "Camada {$num}: Origem obrigatória.");
            }

            // Verificar consistência de profundidades
            $profInicial = $camada['profundidade_inicial'] ?? 0;
            $profFinal = $camada['profundidade_final'] ?? 0;

            if ($profFinal <= $profInicial) {
                $this->addErro(
                    'camadas',
                    "Camada {$num}: Profundidade final deve ser maior que inicial."
                );
            }
        }

        // Verificar continuidade das camadas
        for ($i = 1; $i < count($camadas); $i++) {
            $profAnteriorFinal = $camadas[$i - 1]['profundidade_final'] ?? 0;
            $profAtualInicial = $camadas[$i]['profundidade_inicial'] ?? 0;

            if (abs($profAtualInicial - $profAnteriorFinal) > 0.01) {
                $this->addAviso(
                    'camadas',
                    "Descontinuidade entre camadas " . $i . " e " . ($i + 1) . "."
                );
            }
        }
    }

    /**
     * Validar amostras SPT
     */
    protected function validarAmostras(array $amostras): void
    {
        if (empty($amostras)) {
            $this->addErro(
                'amostras',
                'Nenhuma amostra SPT registrada.',
                self::PESO_AMOSTRAS
            );
            return;
        }

        foreach ($amostras as $amostra) {
            $num = $amostra['numero_amostra'] ?? '?';

            // Verificar golpes
            $golpes2a = $amostra['golpes_2a'] ?? null;
            $golpes3a = $amostra['golpes_3a'] ?? null;

            if ($golpes2a === null || $golpes3a === null) {
                $this->addErro('amostras', "Amostra {$num}: Golpes 2ª e 3ª etapa obrigatórios.");
            }

            // Verificar cálculo N30
            $nspt2a3a = $amostra['nspt_2a_3a'] ?? 0;
            $esperado = ($golpes2a ?? 0) + ($golpes3a ?? 0);

            if ($nspt2a3a != $esperado) {
                $this->addErro(
                    'amostras',
                    "Amostra {$num}: NSPT incorreto. Calculado: {$esperado}, Registrado: {$nspt2a3a}."
                );
            }

            // Verificar profundidade
            if (empty($amostra['profundidade_inicial'])) {
                $this->addErro('amostras', "Amostra {$num}: Profundidade inicial obrigatória.");
            }
        }

        // Verificar sequência de numeração
        $numeros = array_column($amostras, 'numero_amostra');
        sort($numeros);
        for ($i = 0; $i < count($numeros); $i++) {
            if ($numeros[$i] != $i + 1) {
                $this->addAviso('amostras', 'Numeração de amostras não sequencial.');
                break;
            }
        }
    }

    /**
     * Validar fotos (Memorial Fotográfico)
     */
    protected function validarFotos(array $fotos): void
    {
        $tiposObrigatorios = [
            'ensaio_spt' => 'Foto do ensaio SPT',
            'amostrador' => 'Foto do amostrador',
            'amostra' => 'Foto das amostras',
        ];

        if (empty($fotos)) {
            $this->addErro(
                'fotos',
                'Nenhuma foto registrada. Memorial fotográfico é obrigatório.',
                self::PESO_FOTOS
            );
            return;
        }

        // Verificar tipos obrigatórios
        $tiposPresentes = array_column($fotos, 'tipo_foto');
        
        foreach ($tiposObrigatorios as $tipo => $descricao) {
            if (!in_array($tipo, $tiposPresentes)) {
                $this->addErro('fotos', "{$descricao} obrigatória.", self::PESO_FOTOS / 3);
            }
        }

        // Verificar metadados EXIF
        $fotosComGPS = 0;
        foreach ($fotos as $foto) {
            if (!empty($foto['latitude']) && !empty($foto['longitude'])) {
                $fotosComGPS++;
            }
        }

        if ($fotosComGPS < count($fotos) * 0.5) {
            $this->addAviso('fotos', 'Menos de 50% das fotos possuem coordenadas GPS.');
        }
    }

    /**
     * Validar responsável técnico
     */
    protected function validarResponsavelTecnico(?array $responsavel): void
    {
        if (empty($responsavel)) {
            $this->addErro(
                'responsavel',
                'Responsável técnico não definido.',
                self::PESO_RESPONSAVEL
            );
            return;
        }

        if (empty($responsavel['nome'])) {
            $this->addErro('responsavel', 'Nome do responsável técnico obrigatório.');
        }

        if (empty($responsavel['crea'])) {
            $this->addErro('responsavel', 'CREA do responsável técnico obrigatório.');
        } else {
            // Validar formato CREA (UF 00000/D ou UF-00000/D)
            $crea = $responsavel['crea'];
            if (!preg_match('/^[A-Z]{2}[\s\-]?\d{4,6}\/[A-Z]$/i', $crea)) {
                $this->addAviso('responsavel', "Formato do CREA '{$crea}' pode estar incorreto.");
            }
        }
    }

    /**
     * Validar observações de paralisação
     */
    protected function validarObservacoes(array $sondagem): void
    {
        // Verificar se tem observação de paralisação quando necessário
        $profFinal = $sondagem['profundidade_final'] ?? 0;
        
        if ($profFinal < 15 && empty($sondagem['observacoes_paralisacao'])) {
            $this->addAviso(
                'observacoes',
                'Sondagem rasa (< 15m). Considere documentar motivo de paralisação.'
            );
        }
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    protected function addErro(string $categoria, string $mensagem, int $penalidade = 5): void
    {
        $this->erros[] = [
            'categoria' => $categoria,
            'mensagem' => $mensagem,
        ];
        $this->score -= $penalidade;
    }

    protected function addAviso(string $categoria, string $mensagem): void
    {
        $this->avisos[] = [
            'categoria' => $categoria,
            'mensagem' => $mensagem,
        ];
    }

    /**
     * Obter resumo da validação
     */
    public function getResumo(): array
    {
        return [
            'conforme' => empty($this->erros),
            'score' => max(0, $this->score),
            'classificacao' => $this->getClassificacao(),
            'erros' => count($this->erros),
            'avisos' => count($this->avisos),
        ];
    }

    /**
     * Classificar conformidade por score
     */
    protected function getClassificacao(): string
    {
        if ($this->score >= 90) return 'Excelente';
        if ($this->score >= 70) return 'Bom';
        if ($this->score >= 50) return 'Regular';
        if ($this->score >= 30) return 'Ruim';
        return 'Crítico';
    }
}
```

---

## 📚 BIBLIOTECA: SoloClassificador

Criar `app/Libraries/SoloClassificador.php`:

```php
<?php

namespace App\Libraries;

/**
 * Solo Classificador
 * Classificação de solos conforme NBR 6502:2022
 * 
 * @author Support Solo Sondagens
 * @version 1.0
 */
class SoloClassificador
{
    /**
     * Classificações principais de solo
     */
    public const TIPOS_SOLO = [
        'argila' => 'Argila',
        'silte' => 'Silte',
        'areia' => 'Areia',
        'pedregulho' => 'Pedregulho',
        'argila_arenosa' => 'Argila arenosa',
        'argila_siltosa' => 'Argila siltosa',
        'argila_silto_arenosa' => 'Argila silto-arenosa',
        'silte_arenoso' => 'Silte arenoso',
        'silte_argiloso' => 'Silte argiloso',
        'silte_argilo_arenoso' => 'Silte argilo-arenoso',
        'areia_argilosa' => 'Areia argilosa',
        'areia_siltosa' => 'Areia siltosa',
        'areia_silto_argilosa' => 'Areia silto-argilosa',
        'aterro' => 'Aterro',
        'turfa' => 'Turfa',
        'materia_organica' => 'Matéria orgânica',
        'rocha' => 'Rocha',
        'vegetacao' => 'Vegetação',
        'expurgo' => 'Expurgo',
    ];

    /**
     * Origens do material
     */
    public const ORIGENS = [
        'SR' => 'Solo residual',
        'SA' => 'Solo aluvionar',
        'AT' => 'Aterro',
        'AO' => 'Alteração orgânica',
        'RO' => 'Rocha',
    ];

    /**
     * Cores padrão para gráfico estratigráfico
     */
    public const CORES_GRAFICO = [
        'argila' => '#8B4513',
        'silte' => '#D2691E',
        'areia' => '#F4A460',
        'pedregulho' => '#808080',
        'argila_arenosa' => '#A0522D',
        'argila_siltosa' => '#CD853F',
        'silte_arenoso' => '#DEB887',
        'silte_argiloso' => '#BC8F8F',
        'areia_argilosa' => '#D2B48C',
        'areia_siltosa' => '#F5DEB3',
        'aterro' => '#696969',
        'vegetacao' => '#228B22',
        'expurgo' => '#32CD32',
        'rocha' => '#A9A9A9',
    ];

    /**
     * Obter descrição formatada do solo
     */
    public static function formatarDescricao(
        string $classificacao, 
        string $cor, 
        ?string $consistencia = null,
        ?string $compacidade = null,
        ?string $amostrasIds = null
    ): string {
        $tipoSolo = self::TIPOS_SOLO[$classificacao] ?? ucfirst($classificacao);
        
        $descricao = "{$tipoSolo}, cor {$cor}";

        if ($consistencia) {
            $descricao .= ", consistência " . SPTCalculator::getDescricaoConsistencia($consistencia);
        }

        if ($compacidade) {
            $descricao .= ", compacidade " . SPTCalculator::getDescricaoCompacidade($compacidade);
        }

        if ($amostrasIds) {
            $descricao .= ". {$amostrasIds}";
        }

        return $descricao;
    }

    /**
     * Obter cor para gráfico
     */
    public static function getCorGrafico(string $classificacao): string
    {
        return self::CORES_GRAFICO[$classificacao] ?? '#CCCCCC';
    }

    /**
     * Verificar se solo é coesivo
     */
    public static function isCoesivo(string $classificacao): bool
    {
        $coesivos = ['argila', 'argila_arenosa', 'argila_siltosa', 'argila_silto_arenosa'];
        return in_array($classificacao, $coesivos);
    }

    /**
     * Verificar se solo é granular
     */
    public static function isGranular(string $classificacao): bool
    {
        $granulares = ['areia', 'areia_argilosa', 'areia_siltosa', 'areia_silto_argilosa', 'pedregulho'];
        return in_array($classificacao, $granulares);
    }
}
```

---

## ✅ CHECKLIST FASE 3

- [ ] SPTCalculator criado com 15+ métodos
- [ ] NBRValidator com validação completa
- [ ] SoloClassificador com cores e descrições
- [ ] Constantes NBR 6484:2020 definidas
- [ ] Cálculos de N30, consistência, compacidade
- [ ] Validação de equipamento
- [ ] Validação de fotos obrigatórias
- [ ] Score de conformidade funcionando

---

## 🔄 PRÓXIMO PASSO

➡️ **[Fase 4 - Geração de PDF](05_FASE_4_PDF_SERVICE.md)**

---

**© 2025 Support Solo Sondagens Ltda**
