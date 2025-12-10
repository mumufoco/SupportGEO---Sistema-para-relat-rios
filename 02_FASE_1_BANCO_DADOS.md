# FASE 1: ESTRUTURA DO BANCO MYSQL

**Tempo estimado:** 2-3 dias  
**Objetivo:** Criar schema completo conforme NBR 6484:2020

---

## 🎯 Objetivos

- Criar todas as 11 tabelas com relacionamentos
- Implementar auditoria e versionamento
- Garantir integridade referencial
- Configurar índices para performance

---

## 📊 DIAGRAMA DE RELACIONAMENTOS

```
┌──────────────┐
│   empresas   │
└──────┬───────┘
       │ 1:N
       ▼
┌──────────────────────────┐
│ responsaveis_tecnicos    │
└──────────────────────────┘
       │
       ├─────────────────┐
       │                 │
       │ 1:N             │ 1:N
       ▼                 ▼
┌──────────┐        ┌──────────┐
│ projetos │        │ usuarios │
└────┬─────┘        └──────────┘
     │ 1:N
     ▼
┌────────┐
│  obras │
└───┬────┘
    │ 1:N
    ▼
┌────────────┐
│ sondagens  │◄──────────────┐
└───┬────────┘               │
    │                        │
    │ 1:N                    │ N:1
    ├────────┬──────┐        │
    │        │      │        │
    ▼        ▼      ▼        │
┌─────┐  ┌─────┐ ┌────┐      │
│cama │  │amo  │ │fot │      │
│das  │  │stras│ │os  │      │
└─────┘  └─────┘ └────┘      │
                             │
                        ┌────┴────┐
                        │audit_log│
                        └─────────┘
```

---

## 📝 COMANDOS PARA EXECUÇÃO

### Comando 1: Criar Migration Principal

```bash
php spark make:migration CreateCompleteGeoSPTStructure
```

---

## 💾 CÓDIGO DA MIGRATION COMPLETA

Editar o arquivo gerado em `app/Database/Migrations/`:

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompleteGeoSPTStructure extends Migration
{
    public function up()
    {
        // ========================================
        // TABELA 1: empresas
        // Empresas/Clientes do sistema
        // ========================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'razao_social' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'nome_fantasia' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'cnpj' => [
                'type' => 'VARCHAR',
                'constraint' => 18,
                'unique' => true,
            ],
            'crea_empresa' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'endereco_completo' => [
                'type' => 'TEXT',
            ],
            'endereco_filial' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'municipio' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'uf' => [
                'type' => 'CHAR',
                'constraint' => 2,
            ],
            'cep' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'telefone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'website' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'logo_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'ativo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('cnpj');
        $this->forge->createTable('empresas', true);

        // ========================================
        // TABELA 2: responsaveis_tecnicos
        // Engenheiros responsáveis (NBR 6484:2020)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'empresa_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'crea' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'comment' => 'Formato: UF 00000/D',
            ],
            'cargo' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'Engenheiro Civil',
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'telefone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'assinatura_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'ativo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('empresa_id');
        $this->forge->addKey('crea');
        $this->forge->addForeignKey('empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('responsaveis_tecnicos', true);

        // ========================================
        // TABELA 3: projetos
        // Projetos/Contratos
        // ========================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'empresa_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'cliente' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'cnpj_cliente' => [
                'type' => 'VARCHAR',
                'constraint' => 18,
                'null' => true,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'data_inicio' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'data_previsao_termino' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['ativo', 'concluido', 'pausado', 'cancelado'],
                'default' => 'ativo',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('empresa_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('projetos', true);

        // ========================================
        // TABELA 4: obras
        // Obras/Locais de sondagem
        // ========================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'projeto_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'endereco' => [
                'type' => 'TEXT',
            ],
            'municipio' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'uf' => [
                'type' => 'CHAR',
                'constraint' => 2,
            ],
            'cep' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'datum' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'SIRGAS2000',
            ],
            'zona_utm' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'comment' => 'Ex: 23K',
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('projeto_id');
        $this->forge->addForeignKey('projeto_id', 'projetos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('obras', true);

        // ========================================
        // TABELA 5: sondagens
        // Tabela principal - Sondagens SPT
        // Conforme NBR 6484:2020
        // ========================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'obra_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'responsavel_tecnico_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'codigo_sondagem' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'comment' => 'Ex: SP-01, SP-02',
            ],
            'identificacao_cliente' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Ex: Araxá Eng.',
            ],
            'data_execucao' => [
                'type' => 'DATE',
            ],
            'hora_inicio' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'hora_termino' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'sondador' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'auxiliares' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            
            // Coordenadas UTM
            'coordenada_este' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'comment' => 'Coordenada E (metros)',
            ],
            'coordenada_norte' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'comment' => 'Coordenada N (metros)',
            ],
            'cota_boca_furo' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'default' => 0,
                'comment' => 'Cota relativa ou absoluta (m)',
            ],
            
            // Nível d'água
            'nivel_agua_inicial' => [
                'type' => 'ENUM',
                'constraint' => ['ausente', 'presente'],
                'default' => 'ausente',
            ],
            'nivel_agua_inicial_profundidade' => [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'null' => true,
            ],
            'nivel_agua_inicial_data' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'nivel_agua_final' => [
                'type' => 'ENUM',
                'constraint' => ['ausente', 'presente'],
                'default' => 'ausente',
            ],
            'nivel_agua_final_profundidade' => [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'null' => true,
            ],
            'nivel_agua_final_data' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            
            // Revestimento
            'revestimento_profundidade' => [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'default' => 0,
            ],
            
            // Profundidades
            'profundidade_trado' => [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'null' => true,
                'comment' => 'Profundidade atingida com trado',
            ],
            'profundidade_final' => [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'comment' => 'Profundidade final da sondagem',
            ],
            
            // Equipamento NBR 6484:2020
            'peso_martelo' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 65.00,
                'comment' => 'NBR: 65 kgf (±0)',
            ],
            'altura_queda' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 75.00,
                'comment' => 'NBR: 75 cm (±0)',
            ],
            'diametro_amostrador_externo' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 50.80,
                'comment' => 'NBR: 50,8 mm (±0,2)',
            ],
            'diametro_amostrador_interno' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 34.90,
                'comment' => 'NBR: 34,9 mm (±0,2)',
            ],
            'diametro_revestimento' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 63.50,
            ],
            'diametro_trado' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 63.50,
            ],
            'sistema_percussao' => [
                'type' => 'ENUM',
                'constraint' => ['manual', 'mecanico'],
                'default' => 'manual',
            ],
            
            // Escalas
            'escala_vertical' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => '1:100',
            ],
            'escala_horizontal' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => '1:100',
            ],
            
            // Observações
            'observacoes_gerais' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'observacoes_paralisacao' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Motivo de paralisação conforme NBR',
            ],
            
            // Status e versão
            'versao' => [
                'type' => 'INT',
                'default' => 1,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['rascunho', 'em_analise', 'revisao', 'aprovado', 'rejeitado'],
                'default' => 'rascunho',
            ],
            'aprovado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'data_aprovacao' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            
            // Conformidade NBR
            'score_conformidade' => [
                'type' => 'INT',
                'null' => true,
                'comment' => 'Score 0-100 de conformidade NBR',
            ],
            'ultima_verificacao_nbr' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            
            // Timestamps
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('obra_id');
        $this->forge->addKey('responsavel_tecnico_id');
        $this->forge->addKey('codigo_sondagem');
        $this->forge->addKey('status');
        $this->forge->addKey('data_execucao');
        $this->forge->addForeignKey('obra_id', 'obras', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('responsavel_tecnico_id', 'responsaveis_tecnicos', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('sondagens', true);

        // ========================================
        // TABELA 6: camadas
        // Perfil estratigráfico (NBR 6484:2020 - 5.2.3)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'sondagem_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'numero_camada' => [
                'type' => 'INT',
                'constraint' => 3,
            ],
            'profundidade_inicial' => [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
            ],
            'profundidade_final' => [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
            ],
            
            // Classificação NBR 6502:2022
            'classificacao_principal' => [
                'type' => 'ENUM',
                'constraint' => [
                    'argila', 'silte', 'areia', 'pedregulho',
                    'argila_arenosa', 'argila_siltosa', 'argila_silto_arenosa',
                    'silte_arenoso', 'silte_argiloso', 'silte_argilo_arenoso',
                    'areia_argilosa', 'areia_siltosa', 'areia_silto_argilosa',
                    'aterro', 'turfa', 'materia_organica', 'rocha',
                    'vegetacao', 'expurgo'
                ],
            ],
            'classificacao_secundaria' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'descricao_completa' => [
                'type' => 'TEXT',
                'comment' => 'Descrição livre conforme NBR',
            ],
            
            // Características
            'cor' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'comment' => 'Ex: vermelha, amarela, variegada',
            ],
            'origem' => [
                'type' => 'ENUM',
                'constraint' => ['SR', 'SA', 'AT', 'AO', 'RO'],
                'default' => 'SR',
                'comment' => 'SR=Solo residual, SA=Solo aluvionar, AT=Aterro, AO=Alteração orgânica, RO=Rocha',
            ],
            
            // Consistência/Compacidade
            'consistencia' => [
                'type' => 'ENUM',
                'constraint' => ['muito_mole', 'mole', 'media', 'rija', 'muito_rija', 'dura'],
                'null' => true,
                'comment' => 'Para solos argilosos',
            ],
            'compacidade' => [
                'type' => 'ENUM',
                'constraint' => ['fofa', 'pouco_compacta', 'medianamente_compacta', 'compacta', 'muito_compacta'],
                'null' => true,
                'comment' => 'Para solos arenosos',
            ],
            
            // Amostras relacionadas
            'amostras_ids' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'Lista de amostras. Ex: (1, 2, 3)',
            ],
            
            // Cor para gráfico (hexadecimal)
            'cor_grafico' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'null' => true,
                'comment' => 'Cor hex para perfil. Ex: #FF0000',
            ],
            
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('sondagem_id');
        $this->forge->addForeignKey('sondagem_id', 'sondagens', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('camadas', true);

        // ========================================
        // TABELA 7: amostras
        // Ensaios SPT (NBR 6484:2020 - 5.2.2)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'sondagem_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'numero_amostra' => [
                'type' => 'INT',
                'constraint' => 3,
            ],
            'tipo_perfuracao' => [
                'type' => 'ENUM',
                'constraint' => ['TH', 'CR'],
                'default' => 'CR',
                'comment' => 'TH=Trado Helicoidal, CR=Cravação',
            ],
            
            // Profundidades (NBR 6484:2020)
            'profundidade_inicial' => [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
            ],
            'profundidade_30cm_1' => [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'comment' => 'Prof. após 1ª penetração de 30cm',
            ],
            'profundidade_30cm_2' => [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'comment' => 'Prof. após 2ª penetração de 30cm',
            ],
            
            // Golpes SPT
            'golpes_1a' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => true,
                'comment' => 'Golpes primeiros 15cm (descartado)',
            ],
            'golpes_2a' => [
                'type' => 'INT',
                'constraint' => 3,
                'comment' => 'Golpes segundos 15cm',
            ],
            'golpes_3a' => [
                'type' => 'INT',
                'constraint' => 3,
                'comment' => 'Golpes terceiros 15cm',
            ],
            
            // NSPT calculados
            'nspt_1a_2a' => [
                'type' => 'INT',
                'constraint' => 3,
                'comment' => 'Soma golpes 1ª + 2ª (15+15cm)',
            ],
            'nspt_2a_3a' => [
                'type' => 'INT',
                'constraint' => 3,
                'comment' => 'N30 = Soma golpes 2ª + 3ª (NBR 6484)',
            ],
            
            // Penetração
            'penetracao_obtida' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 45.00,
                'comment' => 'Penetração total obtida (cm)',
            ],
            'limite_golpes' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Atingiu limite de golpes?',
            ],
            
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('sondagem_id');
        $this->forge->addForeignKey('sondagem_id', 'sondagens', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('amostras', true);

        // ========================================
        // TABELA 8: fotos
        // Memorial fotográfico (NBR 15492:2007)
        // ========================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'sondagem_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'arquivo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'nome_original' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'tipo_foto' => [
                'type' => 'ENUM',
                'constraint' => ['ensaio_spt', 'amostrador', 'amostra', 'equipamento', 'local', 'outra'],
                'default' => 'ensaio_spt',
            ],
            'descricao' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            
            // Metadados EXIF
            'latitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,7',
                'null' => true,
            ],
            'longitude' => [
                'type' => 'DECIMAL',
                'constraint' => '10,7',
                'null' => true,
            ],
            'altitude' => [
                'type' => 'DECIMAL',
                'constraint' => '8,2',
                'null' => true,
            ],
            'velocidade' => [
                'type' => 'DECIMAL',
                'constraint' => '6,2',
                'null' => true,
                'comment' => 'Velocidade GPS km/h',
            ],
            'data_hora_exif' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            
            // Coordenadas UTM (convertidas)
            'coordenada_este' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
            ],
            'coordenada_norte' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
            ],
            'zona_utm' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            
            // Arquivo
            'tamanho_bytes' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'mime_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            
            'ordem' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('sondagem_id');
        $this->forge->addKey('tipo_foto');
        $this->forge->addForeignKey('sondagem_id', 'sondagens', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('fotos', true);

        // ========================================
        // TABELA 9: audit_log
        // Log de auditoria para rastreabilidade
        // ========================================
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'tabela' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'registro_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'acao' => [
                'type' => 'ENUM',
                'constraint' => ['create', 'update', 'delete', 'approve', 'reject', 'generate_pdf'],
            ],
            'dados_antigos' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'dados_novos' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('usuario_id');
        $this->forge->addKey('tabela');
        $this->forge->addKey('created_at');
        $this->forge->createTable('audit_log', true);

        // ========================================
        // TABELA 10: usuarios
        // Usuários do sistema
        // ========================================
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'empresa_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'responsavel_tecnico_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'unique' => true,
            ],
            'password_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'tipo_usuario' => [
                'type' => 'ENUM',
                'constraint' => ['admin', 'engenheiro', 'operador', 'visualizador'],
                'default' => 'operador',
            ],
            'ativo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'ultimo_login' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('empresa_id');
        $this->forge->addKey('email');
        $this->forge->addForeignKey('empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('usuarios', true);

        // ========================================
        // TABELA 11: ci_sessions
        // Sessões em banco de dados
        // ========================================
        $this->forge->addField([
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
            ],
            'timestamp' => [
                'type' => 'TIMESTAMP',
                'default' => null,
            ],
            'data' => [
                'type' => 'BLOB',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('timestamp');
        $this->forge->createTable('ci_sessions', true);
    }

    public function down()
    {
        // Drop em ordem reversa para respeitar foreign keys
        $this->forge->dropTable('ci_sessions', true);
        $this->forge->dropTable('usuarios', true);
        $this->forge->dropTable('audit_log', true);
        $this->forge->dropTable('fotos', true);
        $this->forge->dropTable('amostras', true);
        $this->forge->dropTable('camadas', true);
        $this->forge->dropTable('sondagens', true);
        $this->forge->dropTable('obras', true);
        $this->forge->dropTable('projetos', true);
        $this->forge->dropTable('responsaveis_tecnicos', true);
        $this->forge->dropTable('empresas', true);
    }
}
```

---

## 🌱 SEEDER DE DADOS INICIAIS

### Comando 2: Criar Seeder

```bash
php spark make:seeder InitialDataSeeder
```

### Código do Seeder

Editar `app/Database/Seeds/InitialDataSeeder.php`:

```php
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // ==========================================
        // 1. Empresa - Support Solo Sondagens
        // ==========================================
        $this->db->table('empresas')->insert([
            'razao_social' => 'SUPPORT SOLO SONDAGENS LTDA',
            'nome_fantasia' => 'Support Solo',
            'cnpj' => '00.000.000/0001-00',
            'endereco_completo' => 'Av. T-4, 619 - St. Bueno, Goiânia - GO, 74230-035',
            'endereco_filial' => 'Rua Antônio Borges, Residencial JK, Bom Jesus das Selvas - MA, 65395-000',
            'municipio' => 'Goiânia',
            'uf' => 'GO',
            'cep' => '74230-035',
            'telefone' => '+55 62 9 9190 6100',
            'email' => 'contato@supportsondagens.com.br',
            'website' => 'https://www.supportsondagens.com.br',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $empresaId = $this->db->insertID();

        // ==========================================
        // 2. Responsável Técnico
        // ==========================================
        $this->db->table('responsaveis_tecnicos')->insert([
            'empresa_id' => $empresaId,
            'nome' => 'Murillo Gomes Abreu',
            'crea' => 'GO 22994/D',
            'cargo' => 'Engenheiro Civil',
            'email' => 'murillo@supportsondagens.com.br',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $responsavelId = $this->db->insertID();

        // ==========================================
        // 3. Projeto Exemplo - UFV Perdões I
        // ==========================================
        $this->db->table('projetos')->insert([
            'empresa_id' => $empresaId,
            'nome' => 'UFV Perdões I',
            'codigo' => 'PRJ-2025-001',
            'cliente' => 'Araxá Transmissão Ltda',
            'descricao' => 'Usina Fotovoltaica Perdões I',
            'data_inicio' => '2025-08-01',
            'status' => 'ativo',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $projetoId = $this->db->insertID();

        // ==========================================
        // 4. Obra
        // ==========================================
        $this->db->table('obras')->insert([
            'projeto_id' => $projetoId,
            'nome' => 'UFV Perdões I',
            'endereco' => 'Zona Rural',
            'municipio' => 'Perdões',
            'uf' => 'MG',
            'cep' => '37260-000',
            'datum' => 'SIRGAS2000',
            'zona_utm' => '23K',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $obraId = $this->db->insertID();

        // ==========================================
        // 5. Sondagem SP-01
        // ==========================================
        $this->db->table('sondagens')->insert([
            'obra_id' => $obraId,
            'responsavel_tecnico_id' => $responsavelId,
            'codigo_sondagem' => 'SP-01',
            'identificacao_cliente' => 'Araxá Eng.',
            'data_execucao' => '2025-08-17',
            'sondador' => 'Henrique Luiz da Silva',
            'coordenada_este' => 487801.00,
            'coordenada_norte' => 7666164.00,
            'cota_boca_furo' => 0.00,
            'nivel_agua_inicial' => 'ausente',
            'nivel_agua_final' => 'ausente',
            'revestimento_profundidade' => 0.00,
            'profundidade_trado' => 1.00,
            'profundidade_final' => 12.45,
            'peso_martelo' => 65.00,
            'altura_queda' => 75.00,
            'diametro_amostrador_externo' => 50.80,
            'diametro_amostrador_interno' => 34.90,
            'diametro_revestimento' => 63.50,
            'diametro_trado' => 63.50,
            'sistema_percussao' => 'manual',
            'observacoes_paralisacao' => 'Paralisada por definição do contratante ou seu preposto (5.2.4.1/6.2.4.1 NBR 6484:2020).',
            'status' => 'aprovado',
            'score_conformidade' => 100,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $sondagemId = $this->db->insertID();

        // ==========================================
        // 6. Usuário Admin
        // ==========================================
        $this->db->table('usuarios')->insert([
            'empresa_id' => $empresaId,
            'responsavel_tecnico_id' => $responsavelId,
            'nome' => 'Administrador',
            'email' => 'admin@supportsondagens.com.br',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'tipo_usuario' => 'admin',
            'ativo' => true,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        echo "✅ Dados iniciais inseridos com sucesso!\n";
        echo "   - Empresa: SUPPORT SOLO SONDAGENS LTDA\n";
        echo "   - Responsável: Murillo Gomes Abreu\n";
        echo "   - Projeto: UFV Perdões I\n";
        echo "   - Obra: UFV Perdões I\n";
        echo "   - Sondagem: SP-01\n";
        echo "   - Usuário: admin@supportsondagens.com.br / admin123\n";
    }
}
```

---

## 📝 COMANDOS DE EXECUÇÃO

```bash
# Comando 3: Executar migrations
php spark migrate

# Comando 4: Verificar status das migrations
php spark migrate:status

# Comando 5: Executar seeder
php spark db:seed InitialDataSeeder

# Comando 6: Verificar tabelas criadas
mysql -u geospt_user -p geospt_db -e "SHOW TABLES;"
```

---

## ✅ CHECKLIST FASE 1

- [ ] Migration criada (11 tabelas)
- [ ] Migration executada com sucesso
- [ ] Seeder criado com dados exemplo
- [ ] Dados iniciais inseridos
- [ ] Todas tabelas visíveis no banco
- [ ] Foreign keys configuradas
- [ ] Índices criados corretamente
- [ ] Campos NBR validados

---

## 🔄 PRÓXIMO PASSO

➡️ **[Fase 2 - Models e Repositories](03_FASE_2_MODELS_REPOSITORIES.md)**

---

**© 2025 Support Solo Sondagens Ltda**
