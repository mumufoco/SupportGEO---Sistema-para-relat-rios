<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\SondagemModel;
use App\Models\CamadaModel;
use App\Models\AmostraModel;
use App\Repositories\SondagemRepository;
use App\Repositories\CamadaRepository;
use App\Repositories\AmostraRepository;

class ImportService
{
    private SondagemRepository $sondagemRepo;
    private CamadaRepository $camadaRepo;
    private AmostraRepository $amostraRepo;

    public function __construct()
    {
        $this->sondagemRepo = new SondagemRepository();
        $this->camadaRepo = new CamadaRepository();
        $this->amostraRepo = new AmostraRepository();
    }

    public function importarExcel(string $filepath, int $obraId, int $usuarioId): array
    {
        $resultados = [
            'sucesso' => true,
            'sondagens_criadas' => 0,
            'amostras_criadas' => 0,
            'camadas_criadas' => 0,
            'erros' => [],
            'avisos' => []
        ];

        try {
            $spreadsheet = IOFactory::load($filepath);

            $abaSondagens = $spreadsheet->getSheetByName('Sondagens');
            if ($abaSondagens) {
                $this->processarAbaSondagens($abaSondagens, $obraId, $usuarioId, $resultados);
            }

            $abaAmostras = $spreadsheet->getSheetByName('Amostras');
            if ($abaAmostras) {
                $this->processarAbaAmostras($abaAmostras, $usuarioId, $resultados);
            }

            $abaCamadas = $spreadsheet->getSheetByName('Camadas');
            if ($abaCamadas) {
                $this->processarAbaCamadas($abaCamadas, $usuarioId, $resultados);
            }

        } catch (\Exception $e) {
            $resultados['sucesso'] = false;
            $resultados['erros'][] = 'Erro ao processar arquivo: ' . $e->getMessage();
        }

        return $resultados;
    }

    protected function processarAbaSondagens($aba, int $obraId, int $usuarioId, array &$resultados): void
    {
        $linhas = $aba->toArray();
        $cabecalho = array_shift($linhas);

        foreach ($linhas as $index => $linha) {
            $numeroLinha = $index + 2;

            try {
                $codigoSondagem = trim($linha[0] ?? '');

                if (empty($codigoSondagem)) {
                    $resultados['avisos'][] = "Linha {$numeroLinha}: Código de sondagem vazio, ignorada";
                    continue;
                }

                $sondagemModel = new SondagemModel();
                $existe = $sondagemModel
                    ->where('obra_id', $obraId)
                    ->where('codigo_sondagem', $codigoSondagem)
                    ->first();

                if ($existe) {
                    $resultados['avisos'][] = "Linha {$numeroLinha}: Sondagem {$codigoSondagem} já existe";
                    continue;
                }

                $dados = [
                    'obra_id' => $obraId,
                    'codigo_sondagem' => $codigoSondagem,
                    'data_execucao' => $this->converterData($linha[1] ?? ''),
                    'sondador' => trim($linha[2] ?? ''),
                    'coordenada_este' => floatval($linha[3] ?? 0),
                    'coordenada_norte' => floatval($linha[4] ?? 0),
                    'cota_boca_furo' => floatval($linha[5] ?? 0),
                    'profundidade_final' => floatval($linha[6] ?? 0),
                    'nivel_agua_inicial' => ($linha[7] ?? '') === 'Presente' ? 'presente' : 'ausente',
                    'nivel_agua_inicial_profundidade' => floatval($linha[8] ?? 0) ?: null,
                    'observacoes_paralisacao' => trim($linha[9] ?? ''),
                    'status' => 'rascunho'
                ];

                $sondagem = $this->sondagemRepo->create($dados, $usuarioId);

                if ($sondagem) {
                    $resultados['sondagens_criadas']++;
                } else {
                    $resultados['erros'][] = "Linha {$numeroLinha}: Erro ao criar sondagem";
                }

            } catch (\Exception $e) {
                $resultados['erros'][] = "Linha {$numeroLinha}: " . $e->getMessage();
            }
        }
    }

    protected function processarAbaAmostras($aba, int $usuarioId, array &$resultados): void
    {
        $linhas = $aba->toArray();
        $cabecalho = array_shift($linhas);

        foreach ($linhas as $index => $linha) {
            $numeroLinha = $index + 2;

            try {
                $codigoSondagem = trim($linha[0] ?? '');

                $sondagemModel = new SondagemModel();
                $sondagem = $sondagemModel->where('codigo_sondagem', $codigoSondagem)->first();

                if (!$sondagem) {
                    $resultados['avisos'][] = "Linha {$numeroLinha}: Sondagem {$codigoSondagem} não encontrada";
                    continue;
                }

                $dados = [
                    'sondagem_id' => $sondagem['id'],
                    'numero_amostra' => intval($linha[1] ?? 0),
                    'tipo_perfuracao' => strtoupper(trim($linha[2] ?? 'CR')),
                    'profundidade_inicial' => floatval($linha[3] ?? 0),
                    'golpes_1a' => intval($linha[4] ?? 0) ?: null,
                    'golpes_2a' => intval($linha[5] ?? 0),
                    'golpes_3a' => intval($linha[6] ?? 0)
                ];

                $amostra = $this->amostraRepo->create($dados, $usuarioId);

                if ($amostra) {
                    $resultados['amostras_criadas']++;
                } else {
                    $resultados['erros'][] = "Linha {$numeroLinha}: Erro ao criar amostra";
                }

            } catch (\Exception $e) {
                $resultados['erros'][] = "Linha {$numeroLinha}: " . $e->getMessage();
            }
        }
    }

    protected function processarAbaCamadas($aba, int $usuarioId, array &$resultados): void
    {
        $linhas = $aba->toArray();
        $cabecalho = array_shift($linhas);

        foreach ($linhas as $index => $linha) {
            $numeroLinha = $index + 2;

            try {
                $codigoSondagem = trim($linha[0] ?? '');

                $sondagemModel = new SondagemModel();
                $sondagem = $sondagemModel->where('codigo_sondagem', $codigoSondagem)->first();

                if (!$sondagem) {
                    $resultados['avisos'][] = "Linha {$numeroLinha}: Sondagem {$codigoSondagem} não encontrada";
                    continue;
                }

                $dados = [
                    'sondagem_id' => $sondagem['id'],
                    'numero_camada' => intval($linha[1] ?? 0),
                    'profundidade_inicial' => floatval($linha[2] ?? 0),
                    'profundidade_final' => floatval($linha[3] ?? 0),
                    'classificacao_principal' => strtolower(trim($linha[4] ?? '')),
                    'cor' => trim($linha[5] ?? ''),
                    'descricao_completa' => trim($linha[6] ?? '')
                ];

                $camada = $this->camadaRepo->create($dados, $usuarioId);

                if ($camada) {
                    $resultados['camadas_criadas']++;
                } else {
                    $resultados['erros'][] = "Linha {$numeroLinha}: Erro ao criar camada";
                }

            } catch (\Exception $e) {
                $resultados['erros'][] = "Linha {$numeroLinha}: " . $e->getMessage();
            }
        }
    }

    public function gerarTemplate(): string
    {
        $spreadsheet = new Spreadsheet();

        $abaSondagens = $spreadsheet->getActiveSheet();
        $abaSondagens->setTitle('Sondagens');

        $cabecalhoSondagens = [
            'Código', 'Data Execução', 'Sondador', 'Coord. Este', 'Coord. Norte',
            'Cota Boca', 'Prof. Final', 'Nível Água', 'Prof. NA', 'Observações'
        ];
        $abaSondagens->fromArray($cabecalhoSondagens, null, 'A1');

        $exemploSondagem = [
            'SP-01', '2025-01-15', 'João Silva', '487801.00', '7666164.00',
            '0.00', '12.45', 'Ausente', '', 'Paralisada por definição do contratante'
        ];
        $abaSondagens->fromArray($exemploSondagem, null, 'A2');

        foreach (range('A', 'J') as $col) {
            $abaSondagens->getColumnDimension($col)->setAutoSize(true);
        }

        $abaAmostras = $spreadsheet->createSheet();
        $abaAmostras->setTitle('Amostras');

        $cabecalhoAmostras = [
            'Código Sondagem', 'Nº Amostra', 'Tipo', 'Prof. Inicial',
            'Golpes 1ª', 'Golpes 2ª', 'Golpes 3ª'
        ];
        $abaAmostras->fromArray($cabecalhoAmostras, null, 'A1');

        $exemplosAmostras = [
            ['SP-01', 1, 'TH', '0.00', '', '', ''],
            ['SP-01', 2, 'CR', '1.00', '5', '4', '2'],
            ['SP-01', 3, 'CR', '2.00', '4', '5', '6']
        ];
        $abaAmostras->fromArray($exemplosAmostras, null, 'A2');

        foreach (range('A', 'G') as $col) {
            $abaAmostras->getColumnDimension($col)->setAutoSize(true);
        }

        $abaCamadas = $spreadsheet->createSheet();
        $abaCamadas->setTitle('Camadas');

        $cabecalhoCamadas = [
            'Código Sondagem', 'Nº Camada', 'Prof. Inicial', 'Prof. Final',
            'Classificação', 'Cor', 'Descrição'
        ];
        $abaCamadas->fromArray($cabecalhoCamadas, null, 'A1');

        $exemplosCamadas = [
            ['SP-01', 1, '0.00', '2.50', 'argila_arenosa', 'marrom', 'Argila arenosa, marrom, consistência média'],
            ['SP-01', 2, '2.50', '5.00', 'areia', 'amarela', 'Areia fina a média, amarela, medianamente compacta']
        ];
        $abaCamadas->fromArray($exemplosCamadas, null, 'A2');

        foreach (range('A', 'G') as $col) {
            $abaCamadas->getColumnDimension($col)->setAutoSize(true);
        }

        $uploadPath = WRITEPATH . 'uploads/imports/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $filepath = $uploadPath . 'template_importacao_sondagens.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }

    protected function converterData($valor): string
    {
        if (empty($valor)) {
            return date('Y-m-d');
        }

        if (is_numeric($valor)) {
            $unix = ($valor - 25569) * 86400;
            return date('Y-m-d', $unix);
        }

        $timestamp = strtotime($valor);
        if ($timestamp) {
            return date('Y-m-d', $timestamp);
        }

        return date('Y-m-d');
    }
}
