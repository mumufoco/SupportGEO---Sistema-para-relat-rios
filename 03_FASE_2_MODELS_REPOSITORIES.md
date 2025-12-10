# FASE 2: MODELS E REPOSITORIES

**Tempo estimado:** 3-5 dias  
**Objetivo:** Criar camada de models com validação NBR e repositories para abstração de dados

---

## 🎯 Objetivos

- Criar todos os Models com validações
- Implementar Repository Pattern para abstração
- Validações específicas conforme NBR 6484:2020
- Relacionamentos entre entidades

---

## 📝 COMANDOS PARA CRIAR MODELS

```bash
# Comando 1: Criar todos os Models
php spark make:model EmpresaModel
php spark make:model ResponsavelTecnicoModel
php spark make:model ProjetoModel
php spark make:model ObraModel
php spark make:model SondagemModel
php spark make:model CamadaModel
php spark make:model AmostraModel
php spark make:model FotoModel
php spark make:model AuditLogModel
php spark make:model UsuarioModel
```

---

## 💾 MODEL PRINCIPAL: SondagemModel

Editar `app/Models/SondagemModel.php`:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class SondagemModel extends Model
{
    protected $table = 'sondagens';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    
    protected $allowedFields = [
        'obra_id', 'responsavel_tecnico_id', 'codigo_sondagem', 'identificacao_cliente',
        'data_execucao', 'hora_inicio', 'hora_termino', 'sondador', 'auxiliares',
        'coordenada_este', 'coordenada_norte', 'cota_boca_furo',
        'nivel_agua_inicial', 'nivel_agua_inicial_profundidade', 'nivel_agua_inicial_data',
        'nivel_agua_final', 'nivel_agua_final_profundidade', 'nivel_agua_final_data',
        'revestimento_profundidade', 'profundidade_trado', 'profundidade_final',
        'peso_martelo', 'altura_queda', 'diametro_amostrador_externo', 'diametro_amostrador_interno',
        'diametro_revestimento', 'diametro_trado', 'sistema_percussao',
        'escala_vertical', 'escala_horizontal',
        'observacoes_gerais', 'observacoes_paralisacao',
        'versao', 'status', 'aprovado_por', 'data_aprovacao',
        'score_conformidade', 'ultima_verificacao_nbr'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // ========================================
    // REGRAS DE VALIDAÇÃO CONFORME NBR 6484:2020
    // ========================================
    protected $validationRules = [
        'obra_id' => 'required|integer|is_not_unique[obras.id]',
        'codigo_sondagem' => 'required|max_length[20]',
        'data_execucao' => 'required|valid_date',
        'sondador' => 'required|max_length[100]',
        'coordenada_este' => 'required|decimal',
        'coordenada_norte' => 'required|decimal',
        'profundidade_final' => 'required|decimal|greater_than[0]',
        
        // Validações NBR 6484:2020 - Equipamento
        'peso_martelo' => 'permit_empty|decimal|exact_value[65.00]',
        'altura_queda' => 'permit_empty|decimal|exact_value[75.00]',
        'diametro_amostrador_externo' => 'permit_empty|decimal|in_range[50.6,51.0]',
        'diametro_amostrador_interno' => 'permit_empty|decimal|in_range[34.7,35.1]',
    ];

    protected $validationMessages = [
        'peso_martelo' => [
            'exact_value' => 'NBR 6484:2020: Peso do martelo deve ser 65 kgf',
        ],
        'altura_queda' => [
            'exact_value' => 'NBR 6484:2020: Altura de queda deve ser 75 cm',
        ],
        'diametro_amostrador_externo' => [
            'in_range' => 'NBR 6484:2020: Diâmetro externo deve ser 50,8 mm ± 0,2',
        ],
        'diametro_amostrador_interno' => [
            'in_range' => 'NBR 6484:2020: Diâmetro interno deve ser 34,9 mm ± 0,2',
        ],
    ];

    // ========================================
    // CALLBACKS
    // ========================================
    protected $beforeInsert = ['setDefaults', 'calculateConformidade'];
    protected $beforeUpdate = ['incrementVersion', 'calculateConformidade'];
    protected $afterInsert = ['logAudit'];
    protected $afterUpdate = ['logAudit'];

    protected function setDefaults(array $data): array
    {
        if (empty($data['data']['peso_martelo'])) {
            $data['data']['peso_martelo'] = 65.00;
        }
        if (empty($data['data']['altura_queda'])) {
            $data['data']['altura_queda'] = 75.00;
        }
        if (empty($data['data']['diametro_amostrador_externo'])) {
            $data['data']['diametro_amostrador_externo'] = 50.80;
        }
        if (empty($data['data']['diametro_amostrador_interno'])) {
            $data['data']['diametro_amostrador_interno'] = 34.90;
        }
        if (empty($data['data']['status'])) {
            $data['data']['status'] = 'rascunho';
        }
        if (empty($data['data']['versao'])) {
            $data['data']['versao'] = 1;
        }
        
        return $data;
    }

    protected function incrementVersion(array $data): array
    {
        if (isset($data['id'])) {
            $current = $this->find($data['id']);
            if ($current) {
                $data['data']['versao'] = ($current['versao'] ?? 0) + 1;
            }
        }
        return $data;
    }

    protected function calculateConformidade(array $data): array
    {
        // Será implementado pela biblioteca NBRValidator
        $data['data']['ultima_verificacao_nbr'] = date('Y-m-d H:i:s');
        return $data;
    }

    protected function logAudit(array $data): array
    {
        $auditModel = new AuditLogModel();
        $auditModel->insert([
            'tabela' => 'sondagens',
            'registro_id' => $data['id'] ?? $data['result'],
            'acao' => isset($data['id']) ? 'update' : 'create',
            'dados_novos' => json_encode($data['data'] ?? []),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $data;
    }

    // ========================================
    // MÉTODOS DE CONSULTA
    // ========================================
    
    /**
     * Buscar sondagens por obra
     */
    public function getByObra(int $obraId): array
    {
        return $this->where('obra_id', $obraId)
                    ->orderBy('codigo_sondagem', 'ASC')
                    ->findAll();
    }

    /**
     * Buscar sondagens por status
     */
    public function getByStatus(string $status): array
    {
        return $this->where('status', $status)
                    ->orderBy('data_execucao', 'DESC')
                    ->findAll();
    }

    /**
     * Buscar sondagens pendentes de aprovação
     */
    public function getPendentes(): array
    {
        return $this->whereIn('status', ['rascunho', 'em_analise', 'revisao'])
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Aprovar sondagem
     */
    public function aprovar(int $id, int $aprovadorId): bool
    {
        return $this->update($id, [
            'status' => 'aprovado',
            'aprovado_por' => $aprovadorId,
            'data_aprovacao' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Rejeitar sondagem
     */
    public function rejeitar(int $id, string $motivo = null): bool
    {
        return $this->update($id, [
            'status' => 'rejeitado',
            'observacoes_gerais' => $motivo,
        ]);
    }
}
```

---

## 💾 MODEL: CamadaModel

Editar `app/Models/CamadaModel.php`:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class CamadaModel extends Model
{
    protected $table = 'camadas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'sondagem_id', 'numero_camada', 'profundidade_inicial', 'profundidade_final',
        'classificacao_principal', 'classificacao_secundaria', 'descricao_completa',
        'cor', 'origem', 'consistencia', 'compacidade', 'amostras_ids', 'cor_grafico'
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'sondagem_id' => 'required|integer',
        'numero_camada' => 'required|integer|greater_than[0]',
        'profundidade_inicial' => 'required|decimal',
        'profundidade_final' => 'required|decimal',
        'classificacao_principal' => 'required',
        'descricao_completa' => 'required',
        'cor' => 'required',
        'origem' => 'required|in_list[SR,SA,AT,AO,RO]',
    ];

    /**
     * Buscar camadas de uma sondagem ordenadas
     */
    public function getBySondagem(int $sondagemId): array
    {
        return $this->where('sondagem_id', $sondagemId)
                    ->orderBy('profundidade_inicial', 'ASC')
                    ->findAll();
    }

    /**
     * Definir cor do gráfico automaticamente baseado na classificação
     */
    public function setCorGrafico(string $classificacao): string
    {
        $cores = [
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

        return $cores[$classificacao] ?? '#CCCCCC';
    }
}
```

---

## 💾 MODEL: AmostraModel

Editar `app/Models/AmostraModel.php`:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class AmostraModel extends Model
{
    protected $table = 'amostras';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'sondagem_id', 'numero_amostra', 'tipo_perfuracao',
        'profundidade_inicial', 'profundidade_30cm_1', 'profundidade_30cm_2',
        'golpes_1a', 'golpes_2a', 'golpes_3a',
        'nspt_1a_2a', 'nspt_2a_3a',
        'penetracao_obtida', 'limite_golpes', 'observacoes'
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'sondagem_id' => 'required|integer',
        'numero_amostra' => 'required|integer|greater_than[0]',
        'tipo_perfuracao' => 'required|in_list[TH,CR]',
        'profundidade_inicial' => 'required|decimal',
        'golpes_2a' => 'required|integer|greater_than_equal_to[0]',
        'golpes_3a' => 'required|integer|greater_than_equal_to[0]',
    ];

    protected $beforeInsert = ['calcularNSPT'];
    protected $beforeUpdate = ['calcularNSPT'];

    /**
     * Calcular NSPT automaticamente
     */
    protected function calcularNSPT(array $data): array
    {
        $golpes1a = $data['data']['golpes_1a'] ?? 0;
        $golpes2a = $data['data']['golpes_2a'] ?? 0;
        $golpes3a = $data['data']['golpes_3a'] ?? 0;

        // NSPT 1ª+2ª (primeiros 30cm)
        $data['data']['nspt_1a_2a'] = $golpes1a + $golpes2a;
        
        // N30 = NSPT 2ª+3ª (NBR 6484:2020 - valor padrão)
        $data['data']['nspt_2a_3a'] = $golpes2a + $golpes3a;

        // Calcular profundidades automaticamente
        $profInicial = $data['data']['profundidade_inicial'] ?? 0;
        $data['data']['profundidade_30cm_1'] = $profInicial + 0.30;
        $data['data']['profundidade_30cm_2'] = $profInicial + 0.45;

        return $data;
    }

    /**
     * Buscar amostras de uma sondagem
     */
    public function getBySondagem(int $sondagemId): array
    {
        return $this->where('sondagem_id', $sondagemId)
                    ->orderBy('numero_amostra', 'ASC')
                    ->findAll();
    }

    /**
     * Obter N30 máximo de uma sondagem
     */
    public function getN30Maximo(int $sondagemId): int
    {
        $result = $this->selectMax('nspt_2a_3a')
                       ->where('sondagem_id', $sondagemId)
                       ->first();
        
        return $result['nspt_2a_3a'] ?? 0;
    }
}
```

---

## 💾 MODEL: FotoModel

Editar `app/Models/FotoModel.php`:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class FotoModel extends Model
{
    protected $table = 'fotos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'sondagem_id', 'arquivo', 'nome_original', 'tipo_foto', 'descricao',
        'latitude', 'longitude', 'altitude', 'velocidade', 'data_hora_exif',
        'coordenada_este', 'coordenada_norte', 'zona_utm',
        'tamanho_bytes', 'mime_type', 'ordem'
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'sondagem_id' => 'required|integer',
        'arquivo' => 'required|max_length[255]',
        'tipo_foto' => 'required|in_list[ensaio_spt,amostrador,amostra,equipamento,local,outra]',
    ];

    /**
     * Buscar fotos de uma sondagem
     */
    public function getBySondagem(int $sondagemId): array
    {
        return $this->where('sondagem_id', $sondagemId)
                    ->orderBy('ordem', 'ASC')
                    ->orderBy('tipo_foto', 'ASC')
                    ->findAll();
    }

    /**
     * Buscar fotos por tipo
     */
    public function getByTipo(int $sondagemId, string $tipo): array
    {
        return $this->where('sondagem_id', $sondagemId)
                    ->where('tipo_foto', $tipo)
                    ->orderBy('ordem', 'ASC')
                    ->findAll();
    }

    /**
     * Contar fotos de uma sondagem
     */
    public function countBySondagem(int $sondagemId): int
    {
        return $this->where('sondagem_id', $sondagemId)->countAllResults();
    }

    /**
     * Verificar se tem mínimo de fotos obrigatórias (NBR)
     */
    public function temFotosObrigatorias(int $sondagemId): bool
    {
        $tiposObrigatorios = ['ensaio_spt', 'amostrador', 'amostra'];
        
        foreach ($tiposObrigatorios as $tipo) {
            $count = $this->where('sondagem_id', $sondagemId)
                          ->where('tipo_foto', $tipo)
                          ->countAllResults();
            if ($count < 1) {
                return false;
            }
        }
        
        return true;
    }
}
```

---

## 📦 REPOSITORY PRINCIPAL: SondagemRepository

Criar `app/Repositories/SondagemRepository.php`:

```php
<?php

namespace App\Repositories;

use App\Models\SondagemModel;
use App\Models\CamadaModel;
use App\Models\AmostraModel;
use App\Models\FotoModel;
use App\Models\ObraModel;
use App\Models\ProjetoModel;
use App\Models\EmpresaModel;
use App\Models\ResponsavelTecnicoModel;

class SondagemRepository
{
    protected SondagemModel $sondagemModel;
    protected CamadaModel $camadaModel;
    protected AmostraModel $amostraModel;
    protected FotoModel $fotoModel;
    protected ObraModel $obraModel;
    protected ProjetoModel $projetoModel;
    protected EmpresaModel $empresaModel;
    protected ResponsavelTecnicoModel $responsavelModel;

    public function __construct()
    {
        $this->sondagemModel = new SondagemModel();
        $this->camadaModel = new CamadaModel();
        $this->amostraModel = new AmostraModel();
        $this->fotoModel = new FotoModel();
        $this->obraModel = new ObraModel();
        $this->projetoModel = new ProjetoModel();
        $this->empresaModel = new EmpresaModel();
        $this->responsavelModel = new ResponsavelTecnicoModel();
    }

    /**
     * Buscar sondagem completa com todos os dados relacionados
     * Este método é CRÍTICO para geração do PDF
     */
    public function getSondagemComDados(int $id): ?array
    {
        $sondagem = $this->sondagemModel->find($id);
        
        if (!$sondagem) {
            return null;
        }

        // Obra
        $obra = $this->obraModel->find($sondagem['obra_id']);
        
        // Projeto
        $projeto = $this->projetoModel->find($obra['projeto_id']);
        
        // Empresa
        $empresa = $this->empresaModel->find($projeto['empresa_id']);
        
        // Responsável Técnico
        $responsavel = null;
        if ($sondagem['responsavel_tecnico_id']) {
            $responsavel = $this->responsavelModel->find($sondagem['responsavel_tecnico_id']);
        }

        // Camadas
        $camadas = $this->camadaModel->getBySondagem($id);
        
        // Amostras
        $amostras = $this->amostraModel->getBySondagem($id);
        
        // Fotos
        $fotos = $this->fotoModel->getBySondagem($id);

        return [
            'sondagem' => $sondagem,
            'obra' => $obra,
            'projeto' => $projeto,
            'empresa' => $empresa,
            'responsavel' => $responsavel,
            'camadas' => $camadas,
            'amostras' => $amostras,
            'fotos' => $fotos,
            'total_fotos' => count($fotos),
            'total_amostras' => count($amostras),
            'total_camadas' => count($camadas),
        ];
    }

    /**
     * Listar sondagens com filtros e paginação
     */
    public function listar(array $filtros = [], int $page = 1, int $perPage = 20): array
    {
        $builder = $this->sondagemModel;

        // Aplicar filtros
        if (!empty($filtros['obra_id'])) {
            $builder = $builder->where('obra_id', $filtros['obra_id']);
        }
        if (!empty($filtros['status'])) {
            $builder = $builder->where('status', $filtros['status']);
        }
        if (!empty($filtros['data_inicio'])) {
            $builder = $builder->where('data_execucao >=', $filtros['data_inicio']);
        }
        if (!empty($filtros['data_fim'])) {
            $builder = $builder->where('data_execucao <=', $filtros['data_fim']);
        }
        if (!empty($filtros['busca'])) {
            $builder = $builder->like('codigo_sondagem', $filtros['busca']);
        }

        $total = $builder->countAllResults(false);
        $dados = $builder->orderBy('data_execucao', 'DESC')
                         ->paginate($perPage, 'default', $page);

        return [
            'dados' => $dados,
            'paginacao' => [
                'total' => $total,
                'pagina_atual' => $page,
                'por_pagina' => $perPage,
                'total_paginas' => ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Criar sondagem completa com camadas e amostras
     */
    public function criarCompleta(array $dados): int
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Inserir sondagem
            $sondagemId = $this->sondagemModel->insert($dados['sondagem']);

            // Inserir camadas
            if (!empty($dados['camadas'])) {
                foreach ($dados['camadas'] as $camada) {
                    $camada['sondagem_id'] = $sondagemId;
                    $this->camadaModel->insert($camada);
                }
            }

            // Inserir amostras
            if (!empty($dados['amostras'])) {
                foreach ($dados['amostras'] as $amostra) {
                    $amostra['sondagem_id'] = $sondagemId;
                    $this->amostraModel->insert($amostra);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Erro ao criar sondagem');
            }

            return $sondagemId;

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Calcular estatísticas de uma sondagem
     */
    public function getEstatisticas(int $id): array
    {
        $amostras = $this->amostraModel->getBySondagem($id);
        
        if (empty($amostras)) {
            return [
                'n30_maximo' => 0,
                'n30_minimo' => 0,
                'n30_medio' => 0,
                'profundidade_maxima' => 0,
            ];
        }

        $n30s = array_column($amostras, 'nspt_2a_3a');
        $profundidades = array_column($amostras, 'profundidade_30cm_2');

        return [
            'n30_maximo' => max($n30s),
            'n30_minimo' => min($n30s),
            'n30_medio' => round(array_sum($n30s) / count($n30s), 1),
            'profundidade_maxima' => max($profundidades),
        ];
    }
}
```

---

## 📦 OUTROS MODELS

### EmpresaModel.php

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class EmpresaModel extends Model
{
    protected $table = 'empresas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'razao_social', 'nome_fantasia', 'cnpj', 'crea_empresa',
        'endereco_completo', 'endereco_filial', 'municipio', 'uf', 'cep',
        'telefone', 'email', 'website', 'logo_path', 'ativo'
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'razao_social' => 'required|max_length[255]',
        'cnpj' => 'required|max_length[18]|is_unique[empresas.cnpj,id,{id}]',
        'municipio' => 'required|max_length[100]',
        'uf' => 'required|exact_length[2]',
    ];
}
```

### ResponsavelTecnicoModel.php

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class ResponsavelTecnicoModel extends Model
{
    protected $table = 'responsaveis_tecnicos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'empresa_id', 'nome', 'crea', 'cargo', 'email', 'telefone',
        'assinatura_path', 'ativo'
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'empresa_id' => 'required|integer',
        'nome' => 'required|max_length[200]',
        'crea' => 'required|max_length[30]',
    ];

    /**
     * Buscar por empresa
     */
    public function getByEmpresa(int $empresaId): array
    {
        return $this->where('empresa_id', $empresaId)
                    ->where('ativo', 1)
                    ->findAll();
    }
}
```

---

## ✅ CHECKLIST FASE 2

- [ ] Todos Models criados (10 models)
- [ ] Validações NBR implementadas em SondagemModel
- [ ] Callbacks de cálculo NSPT em AmostraModel
- [ ] SondagemRepository com método getSondagemComDados()
- [ ] Relacionamentos configurados
- [ ] Testes básicos de CRUD funcionando
- [ ] Audit log funcionando

---

## 🔄 PRÓXIMO PASSO

➡️ **[Fase 3 - Bibliotecas NBR](04_FASE_3_BIBLIOTECAS_NBR.md)**

---

**© 2025 Support Solo Sondagens Ltda**
