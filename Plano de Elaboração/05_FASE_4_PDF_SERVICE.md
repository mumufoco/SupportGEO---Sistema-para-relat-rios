# FASE 4: GERAÇÃO DE PDF - RÉPLICA EXATA DO MODELO

**Tempo estimado:** 7-10 dias  
**Objetivo:** Gerar PDF que replique EXATAMENTE o modelo fornecido, conforme NBR 6484:2020

---

## 🎯 Objetivos

- Replicar layout exato do relatório modelo
- Implementar cabeçalho com logotipos
- Desenhar gráfico estratigráfico colorido
- Desenhar gráfico de resistência N30
- Implementar memorial fotográfico
- Gerar tabela de amostras

---

## 📝 COMANDOS INICIAIS

```bash
# Comando 1: Criar serviços
mkdir -p app/Services
touch app/Services/PDFService.php
```

---

## 💾 PDF SERVICE COMPLETO

Criar `app/Services/PDFService.php`:

```php
<?php

namespace App\Services;

use TCPDF;
use App\Repositories\SondagemRepository;
use App\Libraries\SPTCalculator;
use App\Libraries\SoloClassificador;

/**
 * PDF Service
 * Generates SPT reports exactly as per NBR 6484:2020
 * Replicates the exact format from the provided template
 */
class PDFService extends TCPDF
{
    protected array $sondagem;
    protected SondagemRepository $repository;
    protected int $paginaAtual = 1;
    protected int $totalPaginas = 5;
    
    // ========================================
    // CORES (RGB)
    // ========================================
    private const COR_CABECALHO_FUNDO = [41, 128, 85];  // Verde
    private const COR_BORDA = [0, 0, 0];
    private const COR_TEXTO = [0, 0, 0];
    private const COR_FUNDO_CLARO = [240, 240, 240];
    private const COR_LINHA_GRAFICO_AZUL = [0, 0, 255];
    private const COR_LINHA_GRAFICO_VERMELHO = [255, 0, 0];

    // ========================================
    // MEDIDAS (mm)
    // ========================================
    private const MARGEM_LATERAL = 10;
    private const MARGEM_SUPERIOR = 10;
    private const LARGURA_PAGINA = 190;
    private const ALTURA_CABECALHO = 45;
    private const ALTURA_RODAPE = 30;

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);
        
        $this->repository = new SondagemRepository();
        
        // Configurações do documento
        $this->SetCreator('GeoSPT Manager');
        $this->SetAuthor('Support Solo Sondagens Ltda');
        $this->SetTitle('Relatório de Sondagem SPT');
        $this->SetSubject('NBR 6484:2020');
        $this->SetKeywords('SPT, Sondagem, Geotecnia, NBR 6484');
        
        // Configurações de página
        $this->SetMargins(self::MARGEM_LATERAL, self::MARGEM_SUPERIOR, self::MARGEM_LATERAL);
        $this->SetAutoPageBreak(false);
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Fonte padrão
        $this->SetFont('helvetica', '', 9);
    }

    /**
     * Gerar relatório completo
     */
    public function gerarRelatorio(int $sondagemId): string
    {
        $this->sondagem = $this->repository->getSondagemComDados($sondagemId);
        
        if (!$this->sondagem) {
            throw new \Exception('Sondagem não encontrada');
        }

        // Calcular total de páginas
        $numFotos = count($this->sondagem['fotos'] ?? []);
        $this->totalPaginas = 2 + ceil($numFotos > 0 ? $numFotos : 1);

        // PÁGINA 1: Perfil Estratigráfico e Gráfico
        $this->AddPage();
        $this->paginaAtual = 1;
        $this->desenharPagina1();

        // PÁGINA 2: Tabela de Amostras
        $this->AddPage();
        $this->paginaAtual = 2;
        $this->desenharPagina2();

        // PÁGINAS 3+: Memorial Fotográfico
        $fotos = $this->sondagem['fotos'] ?? [];
        foreach ($fotos as $index => $foto) {
            $this->AddPage();
            $this->paginaAtual = 3 + $index;
            $this->desenharPaginaFoto($foto, $index + 1);
        }

        // Salvar arquivo
        $codigo = $this->sondagem['sondagem']['codigo_sondagem'];
        $data = date('Ymd_His');
        $filename = "SPT_{$codigo}_{$data}.pdf";
        $filepath = WRITEPATH . "uploads/reports/{$filename}";

        $this->Output($filepath, 'F');
        
        return $filepath;
    }

    // ========================================
    // CABEÇALHO PADRÃO
    // ========================================
    protected function desenharCabecalho(): void
    {
        $empresa = $this->sondagem['empresa'];
        $sondagem = $this->sondagem['sondagem'];
        $obra = $this->sondagem['obra'];
        $projeto = $this->sondagem['projeto'];

        // Logo da empresa (esquerda)
        $logoPath = FCPATH . 'assets/images/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 10, 25, 25);
        }

        // Nome da empresa (centro)
        $this->SetFont('helvetica', 'B', 14);
        $this->SetXY(40, 12);
        $this->Cell(110, 6, strtoupper($empresa['razao_social']), 0, 1, 'C');

        // Subtítulo
        $this->SetFont('helvetica', '', 9);
        $this->SetXY(40, 18);
        $this->Cell(110, 5, 'Sondagem de Reconhecimento a Percussão', 0, 1, 'C');

        // Identificação cliente (direita superior)
        $this->SetFont('helvetica', 'B', 10);
        $this->SetXY(155, 10);
        $this->Cell(45, 5, $sondagem['identificacao_cliente'] ?? '', 1, 1, 'C');

        // Código sondagem
        $this->SetXY(155, 15);
        $this->Cell(45, 8, $sondagem['codigo_sondagem'], 1, 1, 'C');

        // Página
        $this->SetFont('helvetica', '', 8);
        $this->SetXY(155, 23);
        $this->Cell(22, 5, 'Página', 1, 0, 'C');
        $this->Cell(23, 5, "{$this->paginaAtual}/{$this->totalPaginas}", 1, 1, 'C');

        // Data
        $this->SetXY(155, 28);
        $this->Cell(22, 5, 'Início/Término', 1, 0, 'C');
        $this->Cell(23, 5, date('d/m/Y', strtotime($sondagem['data_execucao'])), 1, 1, 'C');

        // Dados do projeto
        $this->SetFont('helvetica', '', 9);
        $this->SetXY(40, 24);
        $this->Cell(20, 5, 'Cliente:', 0, 0, 'L');
        $this->Cell(90, 5, $projeto['cliente'], 0, 1, 'L');

        $this->SetXY(40, 29);
        $this->Cell(20, 5, 'Obra:', 0, 0, 'L');
        $this->Cell(90, 5, $obra['nome'], 0, 1, 'L');

        $this->SetXY(40, 34);
        $this->Cell(20, 5, 'Local:', 0, 0, 'L');
        $endereco = "{$obra['endereco']}, {$obra['municipio']}/{$obra['uf']}, CEP {$obra['cep']}";
        $this->Cell(90, 5, $endereco, 0, 1, 'L');

        // Linha separadora
        $this->Line(10, 42, 200, 42);
    }

    // ========================================
    // RODAPÉ PADRÃO
    // ========================================
    protected function desenharRodape(): void
    {
        $empresa = $this->sondagem['empresa'];
        $responsavel = $this->sondagem['responsavel'];

        $yRodape = 255;

        // Linha separadora
        $this->Line(10, $yRodape, 200, $yRodape);

        // Observações
        $this->SetFont('helvetica', '', 7);
        $this->SetXY(10, $yRodape + 2);
        
        $obs = $this->sondagem['sondagem']['observacoes_paralisacao'] ?? '';
        if ($obs) {
            $this->MultiCell(120, 4, "Obs.: {$obs}", 0, 'L');
        }

        // Origem
        $this->SetXY(10, $yRodape + 8);
        $this->Cell(0, 4, 'Origem: SR-Solo residual', 0, 1, 'L');

        // Sondador
        $this->SetXY(10, $yRodape + 12);
        $sondador = $this->sondagem['sondagem']['sondador'];
        $this->Cell(0, 4, "Sondador: {$sondador}", 0, 1, 'L');

        // Dados empresa
        $this->SetXY(10, $yRodape + 18);
        $this->Cell(95, 4, "Matriz - {$empresa['endereco_completo']}", 0, 0, 'L');
        
        $this->SetXY(110, $yRodape + 18);
        $this->Cell(40, 4, 'Resp. Técnico', 0, 0, 'L');

        // Filial
        $this->SetXY(10, $yRodape + 22);
        $filial = $empresa['endereco_filial'] ?? '';
        $this->Cell(95, 4, "Filial - {$filial}", 0, 1, 'L');

        // Contato
        $this->SetXY(10, $yRodape + 26);
        $this->Cell(95, 4, "Contato: {$empresa['telefone']}    E-mail: {$empresa['email']}", 0, 0, 'L');

        // Website
        $this->SetXY(10, $yRodape + 30);
        $this->Cell(95, 4, $empresa['website'], 0, 0, 'L');

        // Assinatura
        if ($responsavel) {
            $this->SetXY(110, $yRodape + 26);
            $this->SetFont('helvetica', '', 8);
            $this->Cell(90, 4, $responsavel['nome'], 0, 1, 'C');
            
            $this->SetXY(110, $yRodape + 30);
            $this->Cell(90, 4, "{$responsavel['cargo']} - CREA/{$responsavel['crea']}", 0, 1, 'C');
        }

        // Referência normas
        $this->SetFont('helvetica', '', 6);
        $this->SetXY(165, 10);
        $this->MultiCell(35, 3, 'CONFORME NBR 6484:2020; NBR 6502:2022; NBR 13441:2021; NBR 15492:2007', 0, 'R');
    }

    // ========================================
    // PÁGINA 1: PERFIL ESTRATIGRÁFICO
    // ========================================
    protected function desenharPagina1(): void
    {
        $this->desenharCabecalho();
        $this->desenharDadosTecnicos();
        $this->desenharGraficoEstratigraficoeN30();
        $this->desenharTabelaCamadas();
        $this->desenharRodape();
    }

    protected function desenharDadosTecnicos(): void
    {
        $s = $this->sondagem['sondagem'];
        $y = 44;

        $this->SetFont('helvetica', '', 7);

        // Linha 1: Diâmetros
        $this->SetXY(10, $y);
        $this->Cell(15, 4, '∅ Amostrador', 0, 0, 'L');
        $this->Cell(20, 4, "Ext.: {$s['diametro_amostrador_externo']} mm", 0, 0, 'L');
        $this->Cell(25, 4, "Altura de queda: {$s['altura_queda']} cm", 0, 0, 'L');
        $this->Cell(30, 4, "Cota da boca do furo: {$s['cota_boca_furo']} m", 0, 0, 'L');
        $this->Cell(15, 4, 'Tempo', 0, 0, 'L');
        $this->Cell(25, 4, 'Coordenadas', 0, 1, 'L');

        // Linha 2
        $this->SetXY(25, $y + 4);
        $this->Cell(20, 4, "Int.: {$s['diametro_amostrador_interno']} mm", 0, 0, 'L');
        $this->Cell(25, 4, "Peso: {$s['peso_martelo']} kgf", 0, 0, 'L');
        $this->Cell(30, 4, "Revestimento: {$s['revestimento_profundidade']} m", 0, 0, 'L');
        $this->Cell(15, 4, '', 0, 0, 'L');
        $this->Cell(35, 4, "Este: {$s['coordenada_este']} m", 0, 1, 'L');

        // Linha 3
        $this->SetXY(10, $y + 8);
        $this->Cell(15, 4, '∅ Revestimento:', 0, 0, 'L');
        $this->Cell(20, 4, "{$s['diametro_revestimento']} mm", 0, 0, 'L');
        $this->Cell(25, 4, "Escala vertical: {$s['escala_vertical']}", 0, 0, 'L');
        $nivelAgua = $s['nivel_agua_inicial'] === 'ausente' ? 'Ausente' : $s['nivel_agua_inicial_profundidade'] . ' m';
        $this->Cell(30, 4, "Nível d'água: {$nivelAgua}", 0, 0, 'L');
        $this->Cell(15, 4, '', 0, 0, 'L');
        $this->Cell(35, 4, "Norte: {$s['coordenada_norte']} m", 0, 1, 'L');

        // Linha 4
        $this->SetXY(10, $y + 12);
        $this->Cell(15, 4, '∅ Trado:', 0, 0, 'L');
        $this->Cell(20, 4, "{$s['diametro_trado']} mm", 0, 0, 'L');
        $sistema = ucfirst($s['sistema_percussao']);
        $this->Cell(25, 4, "Sistema: {$sistema}", 0, 0, 'L');
        $this->Cell(45, 4, '', 0, 0, 'L');
        $obra = $this->sondagem['obra'];
        $this->Cell(35, 4, "Datum: {$obra['datum']}", 0, 1, 'L');

        // Legenda perfuração
        $this->SetXY(10, $y + 17);
        $this->SetFont('helvetica', '', 6);
        $this->Cell(0, 3, 'Perfuração: CR-Cravação TH-Trado Helicoidal', 0, 1, 'L');
    }

    protected function desenharGraficoEstratigraficoeN30(): void
    {
        $camadas = $this->sondagem['camadas'];
        $amostras = $this->sondagem['amostras'];
        
        $xInicio = 75;
        $yInicio = 75;
        $larguraGrafico = 60;
        $alturaGrafico = 170;
        $escala = 10; // 1 metro = 10 mm
        
        $profMax = $this->sondagem['sondagem']['profundidade_final'];

        // Eixo Y (Profundidade)
        $this->SetDrawColor(0, 0, 0);
        $this->Line($xInicio, $yInicio, $xInicio, $yInicio + min($profMax * $escala, $alturaGrafico));

        // Escalas de profundidade
        $this->SetFont('helvetica', '', 6);
        for ($p = 0; $p <= $profMax; $p++) {
            $yPos = $yInicio + ($p * $escala);
            $this->Line($xInicio - 2, $yPos, $xInicio, $yPos);
            $this->SetXY($xInicio - 12, $yPos - 2);
            $this->Cell(10, 4, number_format($p, 0), 0, 0, 'R');
        }

        // Desenhar camadas (retângulos coloridos)
        foreach ($camadas as $camada) {
            $yTop = $yInicio + ($camada['profundidade_inicial'] * $escala);
            $yBottom = $yInicio + ($camada['profundidade_final'] * $escala);
            $altura = $yBottom - $yTop;

            // Cor da camada
            $cor = SoloClassificador::getCorGrafico($camada['classificacao_principal']);
            list($r, $g, $b) = sscanf($cor, "#%02x%02x%02x");
            $this->SetFillColor($r, $g, $b);

            // Retângulo da camada
            $this->Rect($xInicio, $yTop, 15, $altura, 'F');
        }

        // Gráfico N30 (linhas)
        $xGraficoN30 = $xInicio + 20;
        
        // Eixo X do N30
        $this->Line($xGraficoN30, $yInicio, $xGraficoN30 + 50, $yInicio);
        
        // Escalas N30 (0, 10, 20, 30, 40, 50)
        for ($n = 0; $n <= 50; $n += 10) {
            $xPos = $xGraficoN30 + $n;
            $this->Line($xPos, $yInicio, $xPos, $yInicio - 3);
            $this->SetXY($xPos - 3, $yInicio - 8);
            $this->Cell(6, 4, $n, 0, 0, 'C');
        }

        // Plotar pontos N30
        $pontos1a2a = [];
        $pontos2a3a = [];

        foreach ($amostras as $amostra) {
            $prof = ($amostra['profundidade_30cm_1'] + $amostra['profundidade_30cm_2']) / 2;
            $yPonto = $yInicio + ($prof * $escala);
            
            // 1ª+2ª (linha azul)
            $n1a2a = min($amostra['nspt_1a_2a'], 50);
            $x1a2a = $xGraficoN30 + $n1a2a;
            $pontos1a2a[] = ['x' => $x1a2a, 'y' => $yPonto];
            
            // 2ª+3ª (linha vermelha) - N30
            $n2a3a = min($amostra['nspt_2a_3a'], 50);
            $x2a3a = $xGraficoN30 + $n2a3a;
            $pontos2a3a[] = ['x' => $x2a3a, 'y' => $yPonto];
        }

        // Desenhar linha 1ª+2ª (azul)
        $this->SetDrawColor(0, 0, 255);
        for ($i = 1; $i < count($pontos1a2a); $i++) {
            $this->Line(
                $pontos1a2a[$i-1]['x'], $pontos1a2a[$i-1]['y'],
                $pontos1a2a[$i]['x'], $pontos1a2a[$i]['y']
            );
        }

        // Desenhar linha 2ª+3ª (vermelha) - N30
        $this->SetDrawColor(255, 0, 0);
        for ($i = 1; $i < count($pontos2a3a); $i++) {
            $this->Line(
                $pontos2a3a[$i-1]['x'], $pontos2a3a[$i-1]['y'],
                $pontos2a3a[$i]['x'], $pontos2a3a[$i]['y']
            );
        }

        $this->SetDrawColor(0, 0, 0);
    }

    protected function desenharTabelaCamadas(): void
    {
        $camadas = $this->sondagem['camadas'];
        $xTabela = 140;
        $yTabela = 75;
        $largura = 60;

        $this->SetFont('helvetica', 'B', 7);
        $this->SetXY($xTabela, $yTabela);
        $this->Cell($largura, 5, 'Classificação do Material', 1, 1, 'C');

        $this->SetFont('helvetica', '', 7);
        foreach ($camadas as $camada) {
            $yAtual = $this->GetY();
            
            // Profundidade
            $prof = number_format($camada['profundidade_inicial'], 2);
            $this->SetXY($xTabela, $yAtual);
            $this->Cell(10, 10, $prof, 1, 0, 'C');

            // Descrição
            $descricao = $camada['descricao_completa'];
            $this->SetXY($xTabela + 10, $yAtual);
            $this->MultiCell($largura - 10, 5, $descricao, 1, 'L');
        }

        // Limite de sondagem
        $yFinal = $this->GetY();
        $profFinal = $this->sondagem['sondagem']['profundidade_final'];
        $this->SetXY($xTabela, $yFinal);
        $this->Cell(10, 5, number_format($profFinal, 2), 1, 0, 'C');
        $this->Cell($largura - 10, 5, 'LIMITE DE SONDAGEM', 1, 1, 'C');
    }

    // ========================================
    // PÁGINA 2: TABELA DE AMOSTRAS
    // ========================================
    protected function desenharPagina2(): void
    {
        $this->desenharCabecalho();
        $this->desenharTabelaAmostrasCompleta();
        $this->desenharRodape();
    }

    protected function desenharTabelaAmostrasCompleta(): void
    {
        $amostras = $this->sondagem['amostras'];
        $camadas = $this->sondagem['camadas'];
        
        $y = 50;
        $this->SetFont('helvetica', 'B', 7);

        // Cabeçalho da tabela
        $this->SetXY(10, $y);
        $this->Cell(12, 10, 'Amostra', 1, 0, 'C');
        $this->Cell(12, 10, 'Perf.', 1, 0, 'C');
        $this->Cell(40, 5, 'Profundidade (m)', 1, 0, 'C');
        $this->Cell(25, 5, 'Golpes 30 cm', 1, 0, 'C');
        $this->Cell(12, 10, 'Origem', 1, 0, 'C');
        $this->Cell(20, 5, 'Prof. Camada', 1, 0, 'C');
        $this->Cell(69, 10, 'Classificação do Material', 1, 1, 'C');

        // Subcabeçalhos
        $this->SetXY(34, $y + 5);
        $this->Cell(13, 5, 'Inicial', 1, 0, 'C');
        $this->Cell(13, 5, '1ª+2ª', 1, 0, 'C');
        $this->Cell(14, 5, '2ª+3ª', 1, 0, 'C');
        $this->Cell(12, 5, '1ª+2ª', 1, 0, 'C');
        $this->Cell(13, 5, '2ª+3ª', 1, 0, 'C');
        $this->Cell(12, 5, '', 1, 0, 'C');
        $this->Cell(20, 5, '(m)', 1, 1, 'C');

        // Dados
        $this->SetFont('helvetica', '', 7);
        $yCamadaAtual = 0;

        foreach ($amostras as $amostra) {
            $yLinha = $this->GetY();
            
            $this->SetXY(10, $yLinha);
            $this->Cell(12, 6, sprintf('%02d', $amostra['numero_amostra']), 1, 0, 'C');
            $this->Cell(12, 6, $amostra['tipo_perfuracao'], 1, 0, 'C');
            $this->Cell(13, 6, number_format($amostra['profundidade_inicial'], 2), 1, 0, 'C');
            $this->Cell(13, 6, number_format($amostra['profundidade_30cm_1'], 2), 1, 0, 'C');
            $this->Cell(14, 6, number_format($amostra['profundidade_30cm_2'], 2), 1, 0, 'C');
            $this->Cell(12, 6, sprintf('%02d', $amostra['nspt_1a_2a']), 1, 0, 'C');
            $this->Cell(13, 6, sprintf('%02d', $amostra['nspt_2a_3a']), 1, 0, 'C');

            // Buscar camada correspondente
            $camadaTexto = 'SR';
            $profCamada = '';
            $descricao = '';
            
            foreach ($camadas as $camada) {
                if ($amostra['profundidade_inicial'] >= $camada['profundidade_inicial'] 
                    && $amostra['profundidade_inicial'] < $camada['profundidade_final']) {
                    $camadaTexto = $camada['origem'];
                    if ($camada['profundidade_inicial'] != $yCamadaAtual) {
                        $profCamada = number_format($camada['profundidade_inicial'], 2);
                        $descricao = $camada['descricao_completa'];
                        $yCamadaAtual = $camada['profundidade_inicial'];
                    }
                    break;
                }
            }

            $this->Cell(12, 6, $camadaTexto, 1, 0, 'C');
            $this->Cell(20, 6, $profCamada, 1, 0, 'C');
            $this->Cell(69, 6, substr($descricao, 0, 50), 1, 1, 'L');
        }

        // Linha de limite
        $yFinal = $this->GetY();
        $profFinal = $this->sondagem['sondagem']['profundidade_final'];
        $this->SetXY(10, $yFinal);
        $this->Cell(111, 6, '', 1, 0, 'C');
        $this->Cell(20, 6, number_format($profFinal, 2), 1, 0, 'C');
        $this->Cell(69, 6, 'LIMITE DE SONDAGEM', 1, 1, 'L');
    }

    // ========================================
    // PÁGINAS DE FOTOS
    // ========================================
    protected function desenharPaginaFoto(array $foto, int $numero): void
    {
        $this->desenharCabecalhoFoto();

        // Título da foto
        $this->SetFont('helvetica', '', 10);
        $this->SetXY(10, 45);
        $descricao = $foto['descricao'] ?? $this->getTipoFotoDescricao($foto['tipo_foto']);
        $this->Cell(190, 6, "Imagem {$numero} – {$descricao}", 0, 1, 'L');

        // Foto
        $fotoPath = WRITEPATH . "uploads/fotos/{$foto['arquivo']}";
        if (file_exists($fotoPath)) {
            // Centralizar foto
            $this->Image($fotoPath, 20, 55, 170, 0, '', '', '', false, 150);
        }

        // Metadados EXIF na foto
        $metadados = $this->formatarMetadadosExif($foto);
        $this->SetFont('helvetica', '', 8);
        $this->SetXY(100, 200);
        $this->MultiCell(90, 4, $metadados, 0, 'R');

        $this->desenharRodape();
    }

    protected function desenharCabecalhoFoto(): void
    {
        $empresa = $this->sondagem['empresa'];
        $sondagem = $this->sondagem['sondagem'];
        $obra = $this->sondagem['obra'];
        $projeto = $this->sondagem['projeto'];

        // Similar ao cabeçalho padrão, mas com título "Memorial Fotográfico"
        $logoPath = FCPATH . 'assets/images/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 10, 25, 25);
        }

        $this->SetFont('helvetica', 'B', 14);
        $this->SetXY(40, 12);
        $this->Cell(110, 6, strtoupper($empresa['razao_social']), 0, 1, 'C');

        $this->SetFont('helvetica', '', 9);
        $this->SetXY(40, 18);
        $this->Cell(110, 5, 'Memorial Fotográfico', 0, 1, 'C');

        // Demais informações...
        $this->SetXY(155, 10);
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(45, 5, $sondagem['identificacao_cliente'] ?? '', 1, 1, 'C');
        $this->SetXY(155, 15);
        $this->Cell(45, 8, $sondagem['codigo_sondagem'], 1, 1, 'C');

        $this->SetFont('helvetica', '', 8);
        $this->SetXY(155, 23);
        $this->Cell(22, 5, 'Página', 1, 0, 'C');
        $this->Cell(23, 5, "{$this->paginaAtual}/{$this->totalPaginas}", 1, 1, 'C');
        $this->SetXY(155, 28);
        $this->Cell(22, 5, 'Início/Término', 1, 0, 'C');
        $this->Cell(23, 5, date('d/m/Y', strtotime($sondagem['data_execucao'])), 1, 1, 'C');

        $this->SetFont('helvetica', '', 9);
        $this->SetXY(40, 24);
        $this->Cell(20, 5, 'Cliente:', 0, 0, 'L');
        $this->Cell(90, 5, $projeto['cliente'], 0, 1, 'L');
        $this->SetXY(40, 29);
        $this->Cell(20, 5, 'Obra:', 0, 0, 'L');
        $this->Cell(90, 5, $obra['nome'], 0, 1, 'L');
        $this->SetXY(40, 34);
        $this->Cell(20, 5, 'Local:', 0, 0, 'L');
        $this->Cell(90, 5, "{$obra['endereco']}, {$obra['municipio']}/{$obra['uf']}, CEP {$obra['cep']}", 0, 1, 'L');

        $this->Line(10, 42, 200, 42);
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    
    protected function getTipoFotoDescricao(string $tipo): string
    {
        $tipos = [
            'ensaio_spt' => 'Ensaio SPT',
            'amostrador' => 'Amostrador',
            'amostra' => 'Amostra(s)',
            'equipamento' => 'Equipamento',
            'local' => 'Vista do Local',
            'outra' => 'Outra',
        ];
        return $tipos[$tipo] ?? 'Foto';
    }

    protected function formatarMetadadosExif(array $foto): string
    {
        $linhas = [];
        
        if (!empty($foto['data_hora_exif'])) {
            $linhas[] = date('d/m/Y H:i', strtotime($foto['data_hora_exif']));
        }
        
        if (!empty($foto['coordenada_este']) && !empty($foto['coordenada_norte'])) {
            $zona = $foto['zona_utm'] ?? '23K';
            $linhas[] = "{$zona} " . number_format($foto['coordenada_este'], 0) . " " . number_format($foto['coordenada_norte'], 0);
        }
        
        if (!empty($foto['altitude'])) {
            $linhas[] = "Altitude:" . number_format($foto['altitude'], 1) . "m";
        }
        
        if (!empty($foto['velocidade'])) {
            $linhas[] = "Velocidade:" . number_format($foto['velocidade'], 1) . "km/h";
        }

        $obra = $this->sondagem['obra'];
        $sondagem = $this->sondagem['sondagem'];
        $linhas[] = "{$obra['nome']} - Sondagem a Percussão- 1/4";

        return implode("\n", $linhas);
    }
}
```

---

## 🎮 CONTROLLER DE REPORTS

Criar `app/Controllers/Reports/SondagemReportController.php`:

```php
<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Services\PDFService;
use App\Repositories\SondagemRepository;
use App\Libraries\NBRValidator;
use CodeIgniter\HTTP\ResponseInterface;

class SondagemReportController extends BaseController
{
    protected PDFService $pdfService;
    protected SondagemRepository $repository;
    protected NBRValidator $validator;

    public function __construct()
    {
        $this->pdfService = new PDFService();
        $this->repository = new SondagemRepository();
        $this->validator = new NBRValidator();
    }

    /**
     * Gerar PDF
     * GET /api/reports/sondagem/{id}/pdf
     */
    public function pdf(int $id)
    {
        try {
            $sondagem = $this->repository->getSondagemComDados($id);
            
            if (!$sondagem) {
                return $this->response
                    ->setJSON(['erro' => 'Sondagem não encontrada'])
                    ->setStatusCode(404);
            }

            // Validar conformidade NBR antes de gerar
            $validacao = $this->validator->validarSondagem($sondagem);

            if (!$validacao['conforme']) {
                return $this->response
                    ->setJSON([
                        'erro' => 'Sondagem não conforme com NBR 6484:2020',
                        'detalhes' => $validacao,
                        'mensagem' => 'Corrija os erros antes de gerar o relatório'
                    ])
                    ->setStatusCode(400);
            }

            // Gerar PDF
            $filepath = $this->pdfService->gerarRelatorio($id);

            // Retornar arquivo para download
            return $this->response
                ->download($filepath, null)
                ->setFileName(basename($filepath));

        } catch (\Exception $e) {
            log_message('error', 'Erro ao gerar PDF: ' . $e->getMessage());
            return $this->response
                ->setJSON(['erro' => 'Erro ao gerar PDF: ' . $e->getMessage()])
                ->setStatusCode(500);
        }
    }

    /**
     * Verificar conformidade
     * GET /api/reports/sondagem/{id}/conformidade
     */
    public function conformidade(int $id)
    {
        $sondagem = $this->repository->getSondagemComDados($id);
        
        if (!$sondagem) {
            return $this->response
                ->setJSON(['erro' => 'Sondagem não encontrada'])
                ->setStatusCode(404);
        }

        $validacao = $this->validator->validarSondagem($sondagem);

        return $this->response->setJSON($validacao);
    }

    /**
     * Gerar PDFs em lote
     * POST /api/reports/sondagens/batch
     */
    public function batch()
    {
        $ids = $this->request->getJSON(true)['ids'] ?? [];
        
        if (empty($ids)) {
            return $this->response
                ->setJSON(['erro' => 'Nenhuma sondagem selecionada'])
                ->setStatusCode(400);
        }

        $resultados = [];
        $sucesso = 0;
        $falhas = 0;

        foreach ($ids as $id) {
            try {
                $filepath = $this->pdfService->gerarRelatorio($id);
                $resultados[] = [
                    'id' => $id,
                    'sucesso' => true,
                    'arquivo' => basename($filepath)
                ];
                $sucesso++;
            } catch (\Exception $e) {
                $resultados[] = [
                    'id' => $id,
                    'sucesso' => false,
                    'erro' => $e->getMessage()
                ];
                $falhas++;
            }
        }

        return $this->response->setJSON([
            'sucesso' => true,
            'estatisticas' => [
                'total' => count($ids),
                'sucesso' => $sucesso,
                'falhas' => $falhas
            ],
            'resultados' => $resultados
        ]);
    }
}
```

---

## ✅ CHECKLIST FASE 4

- [ ] PDFService criado (700+ linhas)
- [ ] Réplica exata do modelo fornecido
- [ ] Cabeçalho com logo e dados completos
- [ ] Gráfico estratigráfico colorido
- [ ] Gráfico N30 com linhas 1ª+2ª e 2ª+3ª
- [ ] Tabela de amostras completa
- [ ] Memorial fotográfico com EXIF
- [ ] Rodapé com assinatura
- [ ] Controller de reports funcionando
- [ ] Validação NBR antes de gerar

---

## 🔄 PRÓXIMO PASSO

➡️ **[Fase 5 - API REST](06_FASE_5_API_REST.md)**

---

**© 2025 Support Solo Sondagens Ltda**
