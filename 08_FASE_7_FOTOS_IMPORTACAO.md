# FASE 7: UPLOAD DE FOTOS E IMPORTAÇÃO

**Tempo estimado:** 3-5 dias  
**Objetivo:** Implementar upload de fotos com extração EXIF e importação de dados Excel/CSV

---

## 🎯 Objetivos

- Upload de fotos com extração de metadados EXIF
- Conversão de coordenadas GPS para UTM
- Importação de dados via Excel/CSV
- Geração de template de importação

---

## 📝 COMANDOS INICIAIS

```bash
# Comando 1: Criar serviços
touch app/Services/ExifService.php
touch app/Services/ImportService.php
touch app/Controllers/Api/FotoController.php
touch app/Controllers/Api/ImportController.php
```

---

## 📷 SERVIÇO DE EXTRAÇÃO EXIF

Criar `app/Services/ExifService.php`:

```php
<?php

namespace App\Services;

/**
 * EXIF Service
 * Extração de metadados de fotos (GPS, data/hora, etc.)
 */
class ExifService
{
    /**
     * Extrair metadados EXIF de uma imagem
     */
    public function extrairMetadados(string $filepath): array
    {
        $metadados = [
            'latitude' => null,
            'longitude' => null,
            'altitude' => null,
            'velocidade' => null,
            'data_hora' => null,
            'coordenada_este' => null,
            'coordenada_norte' => null,
            'zona_utm' => null,
        ];

        if (!file_exists($filepath)) {
            return $metadados;
        }

        // Verificar se extensão exif está disponível
        if (!function_exists('exif_read_data')) {
            log_message('warning', 'Extensão EXIF não disponível');
            return $metadados;
        }

        try {
            $exif = @exif_read_data($filepath, 'GPS,EXIF', true);
            
            if (!$exif) {
                return $metadados;
            }

            // Extrair coordenadas GPS
            if (isset($exif['GPS'])) {
                $gps = $exif['GPS'];
                
                // Latitude
                if (isset($gps['GPSLatitude']) && isset($gps['GPSLatitudeRef'])) {
                    $metadados['latitude'] = $this->converterGpsParaDecimal(
                        $gps['GPSLatitude'],
                        $gps['GPSLatitudeRef']
                    );
                }

                // Longitude
                if (isset($gps['GPSLongitude']) && isset($gps['GPSLongitudeRef'])) {
                    $metadados['longitude'] = $this->converterGpsParaDecimal(
                        $gps['GPSLongitude'],
                        $gps['GPSLongitudeRef']
                    );
                }

                // Altitude
                if (isset($gps['GPSAltitude'])) {
                    $metadados['altitude'] = $this->converterFracao($gps['GPSAltitude']);
                }

                // Velocidade
                if (isset($gps['GPSSpeed'])) {
                    $metadados['velocidade'] = $this->converterFracao($gps['GPSSpeed']);
                }
            }

            // Data/hora original
            if (isset($exif['EXIF']['DateTimeOriginal'])) {
                $metadados['data_hora'] = date('Y-m-d H:i:s', 
                    strtotime($exif['EXIF']['DateTimeOriginal']));
            } elseif (isset($exif['IFD0']['DateTime'])) {
                $metadados['data_hora'] = date('Y-m-d H:i:s', 
                    strtotime($exif['IFD0']['DateTime']));
            }

            // Converter para UTM se tiver coordenadas
            if ($metadados['latitude'] && $metadados['longitude']) {
                $utm = $this->converterParaUTM(
                    $metadados['latitude'], 
                    $metadados['longitude']
                );
                $metadados['coordenada_este'] = $utm['este'];
                $metadados['coordenada_norte'] = $utm['norte'];
                $metadados['zona_utm'] = $utm['zona'];
            }

        } catch (\Exception $e) {
            log_message('error', 'Erro ao extrair EXIF: ' . $e->getMessage());
        }

        return $metadados;
    }

    /**
     * Converter coordenadas GPS para decimal
     */
    protected function converterGpsParaDecimal(array $gps, string $ref): float
    {
        $graus = $this->converterFracao($gps[0]);
        $minutos = $this->converterFracao($gps[1]);
        $segundos = $this->converterFracao($gps[2]);

        $decimal = $graus + ($minutos / 60) + ($segundos / 3600);

        if ($ref === 'S' || $ref === 'W') {
            $decimal *= -1;
        }

        return round($decimal, 7);
    }

    /**
     * Converter fração EXIF para número
     */
    protected function converterFracao($valor): float
    {
        if (is_string($valor) && strpos($valor, '/') !== false) {
            $partes = explode('/', $valor);
            if (count($partes) === 2 && $partes[1] != 0) {
                return (float) $partes[0] / (float) $partes[1];
            }
        }
        return (float) $valor;
    }

    /**
     * Converter Lat/Long para UTM (SIRGAS2000)
     */
    public function converterParaUTM(float $latitude, float $longitude): array
    {
        // Constantes do elipsóide GRS80 (SIRGAS2000)
        $a = 6378137.0; // Semi-eixo maior
        $f = 1 / 298.257222101; // Achatamento
        $k0 = 0.9996; // Fator de escala

        // Calcular zona UTM
        $zona = floor(($longitude + 180) / 6) + 1;
        $hemisferio = $latitude >= 0 ? 'N' : 'S';
        $zonaCompleta = $zona . ($latitude >= 0 ? 'N' : 'S');

        // Meridiano central da zona
        $lambda0 = deg2rad((($zona - 1) * 6 - 180 + 3));

        // Cálculos
        $e2 = 2 * $f - pow($f, 2);
        $e = sqrt($e2);
        $ep2 = $e2 / (1 - $e2);
        
        $phi = deg2rad($latitude);
        $lambda = deg2rad($longitude);
        
        $N = $a / sqrt(1 - $e2 * pow(sin($phi), 2));
        $T = pow(tan($phi), 2);
        $C = $ep2 * pow(cos($phi), 2);
        $A = ($lambda - $lambda0) * cos($phi);
        
        $M = $a * (
            (1 - $e2/4 - 3*pow($e2, 2)/64 - 5*pow($e2, 3)/256) * $phi
            - (3*$e2/8 + 3*pow($e2, 2)/32 + 45*pow($e2, 3)/1024) * sin(2*$phi)
            + (15*pow($e2, 2)/256 + 45*pow($e2, 3)/1024) * sin(4*$phi)
            - (35*pow($e2, 3)/3072) * sin(6*$phi)
        );

        $este = $k0 * $N * (
            $A 
            + (1 - $T + $C) * pow($A, 3) / 6
            + (5 - 18*$T + pow($T, 2) + 72*$C - 58*$ep2) * pow($A, 5) / 120
        ) + 500000;

        $norte = $k0 * (
            $M + $N * tan($phi) * (
                pow($A, 2) / 2
                + (5 - $T + 9*$C + 4*pow($C, 2)) * pow($A, 4) / 24
                + (61 - 58*$T + pow($T, 2) + 600*$C - 330*$ep2) * pow($A, 6) / 720
            )
        );

        // Ajuste para hemisfério sul
        if ($latitude < 0) {
            $norte += 10000000;
        }

        return [
            'este' => round($este, 2),
            'norte' => round($norte, 2),
            'zona' => $zonaCompleta,
        ];
    }
}
```

---

## 📷 CONTROLLER DE FOTOS

Criar `app/Controllers/Api/FotoController.php`:

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\FotoModel;
use App\Models\SondagemModel;
use App\Services\ExifService;

class FotoController extends ResourceController
{
    protected $modelName = 'App\Models\FotoModel';
    protected $format = 'json';
    protected ExifService $exifService;

    public function __construct()
    {
        $this->exifService = new ExifService();
    }

    /**
     * Listar fotos de uma sondagem
     * GET /api/sondagens/{id}/fotos
     */
    public function index($sondagemId = null)
    {
        $fotos = $this->model->getBySondagem($sondagemId);
        
        return $this->respond([
            'sucesso' => true,
            'sondagem_id' => $sondagemId,
            'total' => count($fotos),
            'dados' => $fotos,
        ]);
    }

    /**
     * Upload de fotos
     * POST /api/sondagens/{id}/fotos
     */
    public function upload($sondagemId = null)
    {
        try {
            // Verificar sondagem
            $sondagemModel = new SondagemModel();
            $sondagem = $sondagemModel->find($sondagemId);
            
            if (!$sondagem) {
                return $this->failNotFound('Sondagem não encontrada');
            }

            // Obter arquivos
            $arquivos = $this->request->getFiles();
            
            if (empty($arquivos['fotos'])) {
                return $this->fail('Nenhuma foto enviada', 400);
            }

            $uploadPath = WRITEPATH . 'uploads/fotos/';
            $tipoFoto = $this->request->getPost('tipo_foto') ?? 'ensaio_spt';
            
            $fotosEnviadas = [];
            $erros = [];

            foreach ($arquivos['fotos'] as $foto) {
                if (!$foto->isValid()) {
                    $erros[] = $foto->getName() . ': ' . $foto->getErrorString();
                    continue;
                }

                // Validar tipo
                $extensao = strtolower($foto->getExtension());
                if (!in_array($extensao, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $erros[] = $foto->getName() . ': Tipo de arquivo não permitido';
                    continue;
                }

                // Validar tamanho (10MB)
                if ($foto->getSizeByUnit('mb') > 10) {
                    $erros[] = $foto->getName() . ': Arquivo muito grande (max 10MB)';
                    continue;
                }

                // Gerar nome único
                $nomeOriginal = $foto->getName();
                $novoNome = uniqid() . '_' . time() . '.' . $extensao;

                // Mover arquivo
                $foto->move($uploadPath, $novoNome);
                $caminhoCompleto = $uploadPath . $novoNome;

                // Extrair EXIF
                $metadados = $this->exifService->extrairMetadados($caminhoCompleto);

                // Salvar no banco
                $dadosFoto = [
                    'sondagem_id' => $sondagemId,
                    'arquivo' => $novoNome,
                    'nome_original' => $nomeOriginal,
                    'tipo_foto' => $tipoFoto,
                    'latitude' => $metadados['latitude'],
                    'longitude' => $metadados['longitude'],
                    'altitude' => $metadados['altitude'],
                    'velocidade' => $metadados['velocidade'],
                    'data_hora_exif' => $metadados['data_hora'],
                    'coordenada_este' => $metadados['coordenada_este'],
                    'coordenada_norte' => $metadados['coordenada_norte'],
                    'zona_utm' => $metadados['zona_utm'],
                    'tamanho_bytes' => filesize($caminhoCompleto),
                    'mime_type' => mime_content_type($caminhoCompleto),
                    'ordem' => $this->model->countBySondagem($sondagemId) + 1,
                ];

                $id = $this->model->insert($dadosFoto);

                if ($id) {
                    $fotosEnviadas[] = $this->model->find($id);
                } else {
                    $erros[] = $nomeOriginal . ': Erro ao salvar no banco';
                }
            }

            return $this->respondCreated([
                'sucesso' => true,
                'mensagem' => count($fotosEnviadas) . ' foto(s) enviada(s) com sucesso',
                'fotos' => $fotosEnviadas,
                'erros' => $erros,
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Excluir foto
     * DELETE /api/fotos/{id}
     */
    public function delete($id = null)
    {
        try {
            $foto = $this->model->find($id);
            
            if (!$foto) {
                return $this->failNotFound('Foto não encontrada');
            }

            // Excluir arquivo físico
            $filepath = WRITEPATH . 'uploads/fotos/' . $foto['arquivo'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            // Excluir do banco
            $this->model->delete($id);

            return $this->respondDeleted([
                'sucesso' => true,
                'mensagem' => 'Foto excluída com sucesso',
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Reordenar fotos
     * POST /api/sondagens/{id}/fotos/reordenar
     */
    public function reordenar($sondagemId = null)
    {
        try {
            $ordem = $this->request->getJSON(true)['ordem'] ?? [];
            
            foreach ($ordem as $index => $fotoId) {
                $this->model->update($fotoId, ['ordem' => $index + 1]);
            }

            return $this->respond([
                'sucesso' => true,
                'mensagem' => 'Fotos reordenadas com sucesso',
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
```

---

## 📥 SERVIÇO DE IMPORTAÇÃO

Criar `app/Services/ImportService.php`:

```php
<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\SondagemModel;
use App\Models\CamadaModel;
use App\Models\AmostraModel;

/**
 * Import Service
 * Importação de dados de Excel/CSV
 */
class ImportService
{
    protected SondagemModel $sondagemModel;
    protected CamadaModel $camadaModel;
    protected AmostraModel $amostraModel;

    public function __construct()
    {
        $this->sondagemModel = new SondagemModel();
        $this->camadaModel = new CamadaModel();
        $this->amostraModel = new AmostraModel();
    }

    /**
     * Importar dados de arquivo Excel
     */
    public function importarExcel(string $filepath, int $obraId): array
    {
        $resultados = [
            'sucesso' => true,
            'sondagens_criadas' => 0,
            'amostras_criadas' => 0,
            'erros' => [],
            'avisos' => [],
        ];

        try {
            $spreadsheet = IOFactory::load($filepath);
            
            // Processar aba de sondagens
            $abaSondagens = $spreadsheet->getSheetByName('Sondagens');
            if ($abaSondagens) {
                $this->processarAbaSondagens($abaSondagens, $obraId, $resultados);
            }

            // Processar aba de amostras
            $abaAmostras = $spreadsheet->getSheetByName('Amostras');
            if ($abaAmostras) {
                $this->processarAbaAmostras($abaAmostras, $resultados);
            }

        } catch (\Exception $e) {
            $resultados['sucesso'] = false;
            $resultados['erros'][] = 'Erro ao processar arquivo: ' . $e->getMessage();
        }

        return $resultados;
    }

    /**
     * Processar aba de sondagens
     */
    protected function processarAbaSondagens($aba, int $obraId, array &$resultados): void
    {
        $linhas = $aba->toArray();
        $cabecalho = array_shift($linhas); // Remover cabeçalho

        foreach ($linhas as $index => $linha) {
            $numeroLinha = $index + 2; // +2 porque removemos cabeçalho e índice começa em 0

            try {
                // Mapear colunas
                $dados = [
                    'obra_id' => $obraId,
                    'codigo_sondagem' => trim($linha[0] ?? ''),
                    'data_execucao' => $this->converterData($linha[1] ?? ''),
                    'sondador' => trim($linha[2] ?? ''),
                    'coordenada_este' => floatval($linha[3] ?? 0),
                    'coordenada_norte' => floatval($linha[4] ?? 0),
                    'cota_boca_furo' => floatval($linha[5] ?? 0),
                    'profundidade_final' => floatval($linha[6] ?? 0),
                    'nivel_agua_inicial' => ($linha[7] ?? '') === 'Presente' ? 'presente' : 'ausente',
                    'nivel_agua_inicial_profundidade' => floatval($linha[8] ?? 0) ?: null,
                    'observacoes_paralisacao' => trim($linha[9] ?? ''),
                ];

                // Validar dados obrigatórios
                if (empty($dados['codigo_sondagem'])) {
                    $resultados['avisos'][] = "Linha {$numeroLinha}: Código de sondagem vazio, ignorada";
                    continue;
                }

                // Verificar duplicidade
                $existe = $this->sondagemModel
                    ->where('obra_id', $obraId)
                    ->where('codigo_sondagem', $dados['codigo_sondagem'])
                    ->first();

                if ($existe) {
                    $resultados['avisos'][] = "Linha {$numeroLinha}: Sondagem {$dados['codigo_sondagem']} já existe";
                    continue;
                }

                // Inserir
                $id = $this->sondagemModel->insert($dados);
                
                if ($id) {
                    $resultados['sondagens_criadas']++;
                } else {
                    $resultados['erros'][] = "Linha {$numeroLinha}: " . implode(', ', $this->sondagemModel->errors());
                }

            } catch (\Exception $e) {
                $resultados['erros'][] = "Linha {$numeroLinha}: " . $e->getMessage();
            }
        }
    }

    /**
     * Processar aba de amostras
     */
    protected function processarAbaAmostras($aba, array &$resultados): void
    {
        $linhas = $aba->toArray();
        $cabecalho = array_shift($linhas);

        foreach ($linhas as $index => $linha) {
            $numeroLinha = $index + 2;

            try {
                $codigoSondagem = trim($linha[0] ?? '');
                
                // Buscar sondagem
                $sondagem = $this->sondagemModel->where('codigo_sondagem', $codigoSondagem)->first();
                
                if (!$sondagem) {
                    $resultados['avisos'][] = "Linha {$numeroLinha}: Sondagem {$codigoSondagem} não encontrada";
                    continue;
                }

                $dados = [
                    'sondagem_id' => $sondagem['id'],
                    'numero_amostra' => intval($linha[1] ?? 0),
                    'tipo_perfuracao' => strtoupper(trim($linha[2] ?? 'CR')),
                    'profundidade_inicial' => floatval($linha[3] ?? 0),
                    'golpes_1a' => intval($linha[4] ?? 0),
                    'golpes_2a' => intval($linha[5] ?? 0),
                    'golpes_3a' => intval($linha[6] ?? 0),
                ];

                // Inserir
                $id = $this->amostraModel->insert($dados);
                
                if ($id) {
                    $resultados['amostras_criadas']++;
                } else {
                    $resultados['erros'][] = "Linha {$numeroLinha}: " . implode(', ', $this->amostraModel->errors());
                }

            } catch (\Exception $e) {
                $resultados['erros'][] = "Linha {$numeroLinha}: " . $e->getMessage();
            }
        }
    }

    /**
     * Gerar template de importação
     */
    public function gerarTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        
        // Aba Sondagens
        $abaSondagens = $spreadsheet->getActiveSheet();
        $abaSondagens->setTitle('Sondagens');
        
        $cabecalhoSondagens = [
            'Código', 'Data Execução', 'Sondador', 'Coord. Este', 'Coord. Norte',
            'Cota Boca', 'Prof. Final', 'Nível Água', 'Prof. NA', 'Observações'
        ];
        $abaSondagens->fromArray($cabecalhoSondagens, null, 'A1');
        
        // Exemplo
        $exemploSondagem = [
            'SP-01', '2025-08-17', 'João Silva', '487801.00', '7666164.00',
            '0.00', '12.45', 'Ausente', '', 'Paralisada por definição do contratante'
        ];
        $abaSondagens->fromArray($exemploSondagem, null, 'A2');

        // Aba Amostras
        $abaAmostras = $spreadsheet->createSheet();
        $abaAmostras->setTitle('Amostras');
        
        $cabecalhoAmostras = [
            'Código Sondagem', 'Nº Amostra', 'Tipo', 'Prof. Inicial',
            'Golpes 1ª', 'Golpes 2ª', 'Golpes 3ª'
        ];
        $abaAmostras->fromArray($cabecalhoAmostras, null, 'A1');
        
        // Exemplos
        $exemplosAmostras = [
            ['SP-01', 1, 'TH', '0.00', '', '', ''],
            ['SP-01', 2, 'CR', '1.00', '5', '4', '2'],
            ['SP-01', 3, 'CR', '2.00', '4', '5', '6'],
        ];
        $abaAmostras->fromArray($exemplosAmostras, null, 'A2');

        // Salvar
        $filepath = WRITEPATH . 'uploads/imports/template_importacao.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }

    /**
     * Converter data do Excel
     */
    protected function converterData($valor): string
    {
        if (empty($valor)) {
            return date('Y-m-d');
        }

        // Se for número (serial do Excel)
        if (is_numeric($valor)) {
            $unix = ($valor - 25569) * 86400;
            return date('Y-m-d', $unix);
        }

        // Tentar parsear
        $timestamp = strtotime($valor);
        if ($timestamp) {
            return date('Y-m-d', $timestamp);
        }

        return date('Y-m-d');
    }
}
```

---

## 📥 CONTROLLER DE IMPORTAÇÃO

Criar `app/Controllers/Api/ImportController.php`:

```php
<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Services\ImportService;

class ImportController extends ResourceController
{
    protected $format = 'json';
    protected ImportService $importService;

    public function __construct()
    {
        $this->importService = new ImportService();
    }

    /**
     * Importar arquivo Excel
     * POST /api/import/excel
     */
    public function excel()
    {
        try {
            $arquivo = $this->request->getFile('arquivo');
            $obraId = $this->request->getPost('obra_id');

            if (!$arquivo || !$arquivo->isValid()) {
                return $this->fail('Arquivo não enviado ou inválido', 400);
            }

            if (!$obraId) {
                return $this->fail('ID da obra é obrigatório', 400);
            }

            // Validar extensão
            $extensao = strtolower($arquivo->getExtension());
            if (!in_array($extensao, ['xlsx', 'xls', 'csv'])) {
                return $this->fail('Formato de arquivo não suportado. Use xlsx, xls ou csv.', 400);
            }

            // Mover para pasta temporária
            $novoNome = uniqid() . '.' . $extensao;
            $uploadPath = WRITEPATH . 'uploads/imports/';
            $arquivo->move($uploadPath, $novoNome);
            $filepath = $uploadPath . $novoNome;

            // Processar importação
            $resultado = $this->importService->importarExcel($filepath, $obraId);

            // Remover arquivo temporário
            @unlink($filepath);

            return $this->respond([
                'sucesso' => $resultado['sucesso'],
                'mensagem' => "Importação concluída: {$resultado['sondagens_criadas']} sondagens, {$resultado['amostras_criadas']} amostras",
                'detalhes' => $resultado,
            ]);

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }

    /**
     * Baixar template de importação
     * GET /api/import/template
     */
    public function template()
    {
        try {
            $filepath = $this->importService->gerarTemplate();

            return $this->response
                ->download($filepath, null)
                ->setFileName('template_importacao_sondagens.xlsx');

        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
```

---

## ✅ CHECKLIST FASE 7

- [ ] ExifService com extração de metadados
- [ ] Conversão GPS para UTM funcionando
- [ ] FotoController com upload múltiplo
- [ ] ImportService para Excel/CSV
- [ ] Template de importação gerado
- [ ] Validações de arquivos
- [ ] Tratamento de erros

---

## 🔄 PRÓXIMO PASSO

➡️ **[Fase 8 - Testes Automatizados](09_FASE_8_TESTES.md)**

---

**© 2025 Support Solo Sondagens Ltda**
