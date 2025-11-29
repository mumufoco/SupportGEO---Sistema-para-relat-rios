<?php

namespace App\Libraries;

use TCPDF;

class PDFService extends TCPDF
{
    protected array $headerData = [];
    protected array $footerData = [];

    public function __construct(
        $orientation = 'P',
        $unit = 'mm',
        $format = 'A4',
        $unicode = true,
        $encoding = 'UTF-8'
    ) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding);

        $this->SetCreator('GeoSPT Manager');
        $this->SetAuthor('Support Solo Sondagens');
        $this->SetTitle('Relatório de Sondagem SPT');
        $this->SetSubject('Sondagem SPT - NBR 6484:2020');

        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(true, 20);
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
    }

    public function setHeaderData(array $data): void
    {
        $this->headerData = $data;
    }

    public function setFooterData(array $data): void
    {
        $this->footerData = $data;
    }

    public function Header(): void
    {
        if (empty($this->headerData)) {
            return;
        }

        $this->SetY(10);
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(0, 5, $this->headerData['titulo'] ?? 'Relatório de Sondagem SPT', 0, 1, 'C');

        if (!empty($this->headerData['subtitulo'])) {
            $this->SetFont('helvetica', '', 8);
            $this->Cell(0, 4, $this->headerData['subtitulo'], 0, 1, 'C');
        }

        $this->Ln(2);
        $this->SetLineWidth(0.3);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(3);
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);

        $footerText = sprintf(
            'Página %d de %d',
            $this->getAliasNumPage(),
            $this->getAliasNbPages()
        );

        if (!empty($this->footerData['texto'])) {
            $footerText .= ' | ' . $this->footerData['texto'];
        }

        $this->Cell(0, 10, $footerText, 0, 0, 'C');
    }

    public function drawBox(
        float $x,
        float $y,
        float $width,
        float $height,
        string $title = '',
        string $style = 'D'
    ): void {
        $this->Rect($x, $y, $width, $height, $style);

        if ($title) {
            $this->SetXY($x + 2, $y + 1);
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(0, 4, $title, 0, 1);
        }
    }

    public function addLabel(string $label, string $value, float $width = 90): void
    {
        $this->SetFont('helvetica', 'B', 9);
        $this->Cell($width * 0.4, 5, $label . ':', 0, 0, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell($width * 0.6, 5, $value, 0, 1, 'L');
    }

    public function addTable(array $headers, array $data, array $widths = []): void
    {
        $numCols = count($headers);

        if (empty($widths)) {
            $totalWidth = $this->getPageWidth() - 30;
            $widths = array_fill(0, $numCols, $totalWidth / $numCols);
        }

        $this->SetFillColor(230, 230, 230);
        $this->SetFont('helvetica', 'B', 9);

        foreach ($headers as $i => $header) {
            $this->Cell($widths[$i], 7, $header, 1, 0, 'C', true);
        }
        $this->Ln();

        $this->SetFont('helvetica', '', 8);
        $fill = false;

        foreach ($data as $row) {
            if ($this->GetY() > 270) {
                $this->AddPage();
                $this->SetFont('helvetica', 'B', 9);
                foreach ($headers as $i => $header) {
                    $this->Cell($widths[$i], 7, $header, 1, 0, 'C', true);
                }
                $this->Ln();
                $this->SetFont('helvetica', '', 8);
            }

            foreach ($row as $i => $cell) {
                $this->Cell($widths[$i], 6, (string)$cell, 1, 0, 'C', $fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
    }

    public function addSection(string $title, float $height = 5): void
    {
        $this->SetFont('helvetica', 'B', 10);
        $this->SetFillColor(200, 220, 255);
        $this->Cell(0, $height, $title, 1, 1, 'L', true);
        $this->Ln(2);
    }

    public function drawLegendItem(
        float $x,
        float $y,
        string $color,
        string $label,
        float $boxSize = 4
    ): void {
        list($r, $g, $b) = $this->hexToRgb($color);
        $this->SetFillColor($r, $g, $b);
        $this->Rect($x, $y, $boxSize, $boxSize, 'DF');

        $this->SetXY($x + $boxSize + 2, $y);
        $this->SetFont('helvetica', '', 7);
        $this->Cell(0, $boxSize, $label, 0, 1);
    }

    public function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) == 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return [$r, $g, $b];
    }

    public function addImageSafe(
        string $file,
        float $x = null,
        float $y = null,
        float $w = 0,
        float $h = 0
    ): bool {
        if (!file_exists($file)) {
            return false;
        }

        try {
            $this->Image($file, $x, $y, $w, $h, '', '', '', true, 300);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
