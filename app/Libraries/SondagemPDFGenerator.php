<?php

namespace App\Libraries;

use App\Libraries\PDFService;
use App\Libraries\NBR\NBRReportHelper;
use App\Libraries\NBR\SoloClassificador;
use App\Libraries\NBR\NBRCalculator;

class SondagemPDFGenerator
{
    private PDFService $pdf;
    private NBRReportHelper $reportHelper;
    private SoloClassificador $soloClass;
    private NBRCalculator $nbrCalc;
    private array $dadosCompletos;

    public function __construct()
    {
        $this->pdf = new PDFService('P', 'mm', 'A4', true, 'UTF-8');
        $this->reportHelper = new NBRReportHelper();
        $this->soloClass = new SoloClassificador();
        $this->nbrCalc = new NBRCalculator();
    }

    public function gerar(array $dadosCompletos): string
    {
        $this->dadosCompletos = $dadosCompletos;

        $this->pdf->setHeaderData([
            'titulo' => 'RELATÓRIO DE SONDAGEM A PERCUSSÃO - SPT',
            'subtitulo' => 'NBR 6484:2020 - NBR 6502:2022'
        ]);

        $this->pdf->setFooterData([
            'texto' => 'Gerado pelo GeoSPT Manager'
        ]);

        $this->pdf->SetFont('helvetica', '', 9);

        $this->pdf->AddPage();
        $this->desenharCabecalho();

        $this->pdf->Ln(5);
        $this->desenharDadosGerais();

        $this->pdf->Ln(5);
        $this->desenharEquipamentos();

        $this->pdf->AddPage();
        $this->desenharPerfilGeotecnico();

        $this->pdf->AddPage();
        $this->desenharTabelaAmostras();

        $this->pdf->Ln(5);
        $this->desenharTabelaCamadas();

        $this->pdf->AddPage();
        $this->desenharGraficoNSPT();

        $this->pdf->Ln(5);
        $this->desenharEstatisticas();

        if (!empty($this->dadosCompletos['fotos'])) {
            $this->desenharMemorialFotografico();
        }

        $this->pdf->AddPage();
        $this->desenharObservacoes();
        $this->desenharAssinatura();

        $outputPath = WRITEPATH . 'uploads/pdfs/';
        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0777, true);
        }

        $fileName = $this->reportHelper->gerarNomeArquivo(
            $this->dadosCompletos['cabecalho']['sondagem']['codigo']
        );
        $filePath = $outputPath . $fileName;

        $this->pdf->Output($filePath, 'F');

        return $filePath;
    }

    private function desenharCabecalho(): void
    {
        $cab = $this->dadosCompletos['cabecalho'];

        if (!empty($cab['empresa']['logo'])) {
            $logoPath = WRITEPATH . 'uploads/' . $cab['empresa']['logo'];
            $this->pdf->addImageSafe($logoPath, 15, 15, 40);
        }

        $this->pdf->SetXY(60, 15);
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 6, $cab['empresa']['razao_social'], 0, 1);

        $this->pdf->SetX(60);
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->Cell(0, 4, $cab['empresa']['endereco'], 0, 1);

        $this->pdf->SetX(60);
        $this->pdf->Cell(0, 4, 'Tel: ' . $cab['empresa']['telefone'] . ' | Email: ' . $cab['empresa']['email'], 0, 1);

        $this->pdf->Ln(3);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
    }

    private function desenharDadosGerais(): void
    {
        $this->pdf->addSection('1. DADOS GERAIS DA SONDAGEM');

        $cab = $this->dadosCompletos['cabecalho'];

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(90, 5, 'CLIENTE:', 0, 0);
        $this->pdf->Cell(90, 5, 'PROJETO:', 0, 1);

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(90, 5, $cab['projeto']['cliente'], 0, 0);
        $this->pdf->Cell(90, 5, $cab['projeto']['nome'], 0, 1);

        $this->pdf->Ln(2);

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(90, 5, 'OBRA:', 0, 0);
        $this->pdf->Cell(90, 5, 'LOCAL:', 0, 1);

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(90, 5, $cab['obra']['nome'], 0, 0);
        $this->pdf->Cell(90, 5, $cab['obra']['municipio'] . '/' . $cab['obra']['uf'], 0, 1);

        $this->pdf->Ln(2);

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(60, 5, 'SONDAGEM:', 0, 0);
        $this->pdf->Cell(60, 5, 'DATA:', 0, 0);
        $this->pdf->Cell(60, 5, 'SONDADOR:', 0, 1);

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(60, 5, $cab['sondagem']['codigo'], 0, 0);
        $this->pdf->Cell(60, 5, $this->reportHelper->formatarData($cab['sondagem']['data_execucao']), 0, 0);
        $this->pdf->Cell(60, 5, $cab['sondagem']['sondador'], 0, 1);

        $this->pdf->Ln(2);

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(0, 5, 'COORDENADAS UTM:', 0, 1);

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 5, $cab['sondagem']['coordenadas'] . ' | Datum: ' . $cab['obra']['datum'] . ' | Zona: ' . $cab['obra']['zona_utm'], 0, 1);
    }

    private function desenharEquipamentos(): void
    {
        $this->pdf->addSection('2. EQUIPAMENTOS UTILIZADOS (NBR 6484:2020)');

        $eq = $this->dadosCompletos['equipamentos'];

        $headers = ['Equipamento', 'Especificação', 'Valor'];
        $data = [
            ['Martelo', 'Peso', $eq['martelo']['peso']],
            ['', 'Altura de queda', $eq['martelo']['altura_queda']],
            ['', 'Sistema', $eq['martelo']['sistema']],
            ['Amostrador', 'Diâmetro externo', $eq['amostrador']['diametro_externo']],
            ['', 'Diâmetro interno', $eq['amostrador']['diametro_interno']],
            ['', 'Razão de área', $eq['amostrador']['razao_area']],
            ['Revestimento', 'Diâmetro', $eq['revestimento']['diametro']],
            ['', 'Profundidade', $eq['revestimento']['profundidade']],
            ['Trado', 'Diâmetro', $eq['trado']['diametro']],
            ['', 'Profundidade', $eq['trado']['profundidade']]
        ];

        $widths = [50, 70, 60];
        $this->pdf->addTable($headers, $data, $widths);

        $na = $this->dadosCompletos['nivel_agua'];
        $this->pdf->Ln(3);
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(0, 5, 'NÍVEL D\'ÁGUA:', 0, 1);

        $this->pdf->SetFont('helvetica', '', 9);
        if ($na['inicial']['presente']) {
            $this->pdf->Cell(0, 5, 'Inicial: ' . $na['inicial']['profundidade'], 0, 1);
        } else {
            $this->pdf->Cell(0, 5, 'Inicial: Não encontrado', 0, 1);
        }

        if ($na['final']['presente']) {
            $this->pdf->Cell(0, 5, 'Final: ' . $na['final']['profundidade'], 0, 1);
        } else {
            $this->pdf->Cell(0, 5, 'Final: Não encontrado', 0, 1);
        }
    }

    private function desenharPerfilGeotecnico(): void
    {
        $this->pdf->addSection('3. PERFIL GEOTÉCNICO');

        $x = 20;
        $y = $this->pdf->GetY();
        $larguraPerfil = 80;
        $alturaPerfil = 200;

        $this->pdf->Rect($x, $y, $larguraPerfil, $alturaPerfil);

        $profMax = 0;
        foreach ($this->dadosCompletos['camadas'] as $camada) {
            $profMax = max($profMax, floatval($camada['profundidade']));
        }

        $escalaY = $alturaPerfil / $profMax;

        foreach ($this->dadosCompletos['camadas'] as $camada) {
            $parts = explode(' - ', $camada['profundidade']);
            $profInicial = floatval($parts[0]);
            $profFinal = floatval($parts[1]);

            $yInicial = $y + ($profInicial * $escalaY);
            $yFinal = $y + ($profFinal * $escalaY);
            $altura = $yFinal - $yInicial;

            list($r, $g, $b) = $this->pdf->hexToRgb($camada['cor_grafico']);
            $this->pdf->SetFillColor($r, $g, $b);
            $this->pdf->Rect($x, $yInicial, $larguraPerfil, $altura, 'DF');

            $this->pdf->SetXY($x + 2, $yInicial + 1);
            $this->pdf->SetFont('helvetica', '', 7);
            $this->pdf->MultiCell($larguraPerfil - 4, 3, $camada['classificacao'], 0, 'L');
        }

        $xLegenda = $x + $larguraPerfil + 10;
        $yLegenda = $y;

        $this->pdf->SetXY($xLegenda, $yLegenda);
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(0, 5, 'LEGENDA', 0, 1);

        $yLegenda += 7;
        foreach ($this->dadosCompletos['camadas'] as $i => $camada) {
            if ($i > 10) break;
            $this->pdf->drawLegendItem(
                $xLegenda,
                $yLegenda,
                $camada['cor_grafico'],
                $camada['classificacao']
            );
            $yLegenda += 5;
        }
    }

    private function desenharTabelaAmostras(): void
    {
        $this->pdf->addSection('4. RESULTADOS DOS ENSAIOS SPT');

        $headers = ['Nº', 'Tipo', 'Prof. (m)', '1ª', '2ª', '3ª', 'N(1+2)', 'N(2+3)', 'Pen.'];
        $data = [];

        foreach ($this->dadosCompletos['amostras'] as $amostra) {
            $data[] = [
                $amostra['numero'],
                $amostra['tipo'],
                $amostra['profundidade'],
                $amostra['golpes_1'],
                $amostra['golpes_2'],
                $amostra['golpes_3'],
                $amostra['nspt_1_2'],
                $amostra['nspt_2_3'],
                $amostra['penetracao']
            ];
        }

        $widths = [15, 15, 25, 15, 15, 15, 20, 20, 20];
        $this->pdf->addTable($headers, $data, $widths);
    }

    private function desenharTabelaCamadas(): void
    {
        $this->pdf->addSection('5. PERFIL ESTRATIGRÁFICO (NBR 6502:2022)');

        $headers = ['Nº', 'Prof. (m)', 'Esp. (m)', 'Classificação', 'Descrição'];
        $data = [];

        foreach ($this->dadosCompletos['camadas'] as $camada) {
            $data[] = [
                $camada['numero'],
                $camada['profundidade'],
                $camada['espessura'],
                $camada['classificacao'],
                substr($camada['descricao'], 0, 60)
            ];
        }

        $widths = [15, 25, 20, 40, 80];
        $this->pdf->addTable($headers, $data, $widths);
    }

    private function desenharGraficoNSPT(): void
    {
        $this->pdf->addSection('6. GRÁFICO NSPT vs PROFUNDIDADE');

        $x = 20;
        $y = $this->pdf->GetY();
        $largura = 170;
        $altura = 120;

        $this->pdf->Rect($x, $y, $largura, $altura);

        $this->pdf->SetXY($x + $largura/2 - 20, $y - 7);
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(40, 5, 'NSPT', 0, 0, 'C');

        $nsptMax = 0;
        $profMax = 0;

        foreach ($this->dadosCompletos['amostras'] as $amostra) {
            $nsptMax = max($nsptMax, $amostra['nspt_2_3']);
            $parts = explode(' - ', $amostra['profundidade']);
            $profMax = max($profMax, floatval($parts[0]));
        }

        $nsptMax = ceil($nsptMax / 10) * 10;
        $escalaX = $largura / $nsptMax;
        $escalaY = $altura / $profMax;

        $this->pdf->SetDrawColor(200, 200, 200);
        for ($i = 0; $i <= $nsptMax; $i += 10) {
            $xGrid = $x + ($i * $escalaX);
            $this->pdf->Line($xGrid, $y, $xGrid, $y + $altura);

            $this->pdf->SetXY($xGrid - 5, $y + $altura + 1);
            $this->pdf->SetFont('helvetica', '', 7);
            $this->pdf->Cell(10, 3, (string)$i, 0, 0, 'C');
        }

        $this->pdf->SetDrawColor(0, 0, 255);
        $this->pdf->SetLineWidth(0.5);

        $pontosAnteriores = null;
        foreach ($this->dadosCompletos['amostras'] as $amostra) {
            $parts = explode(' - ', $amostra['profundidade']);
            $prof = floatval($parts[0]);
            $nspt = $amostra['nspt_2_3'];

            $xPonto = $x + ($nspt * $escalaX);
            $yPonto = $y + ($prof * $escalaY);

            if ($pontosAnteriores !== null) {
                $this->pdf->Line($pontosAnteriores[0], $pontosAnteriores[1], $xPonto, $yPonto);
            }

            $this->pdf->Circle($xPonto, $yPonto, 1, 0, 360, 'F');

            $pontosAnteriores = [$xPonto, $yPonto];
        }
    }

    private function desenharEstatisticas(): void
    {
        $this->pdf->addSection('7. ESTATÍSTICAS');

        $stats = $this->dadosCompletos['estatisticas']['basicas'];

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(90, 5, 'Total de amostras: ' . $stats['total_amostras'], 0, 0);
        $this->pdf->Cell(90, 5, 'Profundidade máxima: ' . $stats['profundidade_maxima'] . ' m', 0, 1);

        $this->pdf->Cell(90, 5, 'NSPT mínimo: ' . $stats['nspt_minimo'], 0, 0);
        $this->pdf->Cell(90, 5, 'NSPT máximo: ' . $stats['nspt_maximo'], 0, 1);

        $this->pdf->Cell(90, 5, 'NSPT médio: ' . $stats['nspt_medio'], 0, 0);
        $this->pdf->Cell(90, 5, 'NSPT mediana: ' . $stats['nspt_mediana'], 0, 1);
    }

    private function desenharMemorialFotografico(): void
    {
        $this->pdf->AddPage();
        $this->pdf->addSection('8. MEMORIAL FOTOGRÁFICO (NBR 15492:2007)');

        $fotos = array_slice($this->dadosCompletos['fotos'], 0, 6);
        $fotosPorLinha = 2;
        $larguraFoto = 80;
        $alturaFoto = 60;
        $espacamento = 10;

        $x = 20;
        $y = $this->pdf->GetY();
        $contador = 0;

        foreach ($fotos as $foto) {
            $fotoPath = WRITEPATH . 'uploads/fotos/' . $foto['arquivo'];

            if (file_exists($fotoPath)) {
                $col = $contador % $fotosPorLinha;
                $linha = floor($contador / $fotosPorLinha);

                $xFoto = $x + ($col * ($larguraFoto + $espacamento));
                $yFoto = $y + ($linha * ($alturaFoto + $espacamento + 10));

                $this->pdf->addImageSafe($fotoPath, $xFoto, $yFoto, $larguraFoto, $alturaFoto);

                $this->pdf->SetXY($xFoto, $yFoto + $alturaFoto + 1);
                $this->pdf->SetFont('helvetica', '', 7);
                $this->pdf->MultiCell($larguraFoto, 3, $foto['descricao'] ?? 'Foto ' . ($contador + 1), 0, 'C');

                $contador++;
            }
        }
    }

    private function desenharObservacoes(): void
    {
        $this->pdf->addSection('9. OBSERVAÇÕES');

        $obs = $this->dadosCompletos['observacoes'];

        $this->pdf->SetFont('helvetica', '', 9);

        if (!empty($obs['paralisacao'])) {
            $this->pdf->SetFont('helvetica', 'B', 9);
            $this->pdf->Cell(0, 5, 'Motivo de Paralisação:', 0, 1);
            $this->pdf->SetFont('helvetica', '', 9);
            $this->pdf->MultiCell(0, 5, $obs['paralisacao'], 0, 'J');
            $this->pdf->Ln(3);
        }

        if (!empty($obs['gerais'])) {
            $this->pdf->SetFont('helvetica', 'B', 9);
            $this->pdf->Cell(0, 5, 'Observações Gerais:', 0, 1);
            $this->pdf->SetFont('helvetica', '', 9);
            $this->pdf->MultiCell(0, 5, $obs['gerais'], 0, 'J');
            $this->pdf->Ln(3);
        }

        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(0, 5, 'Referências Normativas:', 0, 1);
        $this->pdf->SetFont('helvetica', '', 8);

        foreach ($this->dadosCompletos['referencias'] as $norma => $descricao) {
            $this->pdf->Cell(0, 4, '• ' . $norma . ': ' . $descricao, 0, 1);
        }
    }

    private function desenharAssinatura(): void
    {
        $this->pdf->Ln(10);

        $cab = $this->dadosCompletos['cabecalho'];

        $this->pdf->SetLineWidth(0.3);
        $this->pdf->Line(50, $this->pdf->GetY(), 150, $this->pdf->GetY());

        $this->pdf->Ln(2);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 5, $cab['responsavel']['nome'], 0, 1, 'C');

        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 5, $cab['responsavel']['cargo'], 0, 1, 'C');
        $this->pdf->Cell(0, 5, 'CREA: ' . $cab['responsavel']['crea'], 0, 1, 'C');

        $this->pdf->Ln(5);
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->Cell(0, 4, 'Relatório gerado em: ' . $this->dadosCompletos['metadata']['gerado_em'], 0, 1, 'C');
    }
}
