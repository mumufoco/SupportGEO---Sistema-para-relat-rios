<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompleteGeoSPTStructure extends Migration
{
    public function up()
    {
        // Tabela: empresas
        $this->forge->addField([
            'id' => [
                'type' => 'BIGSERIAL',
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
            'inscricao_estadual' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'inscricao_municipal' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'telefone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'endereco' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'cidade' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'estado' => [
                'type' => 'VARCHAR',
                'constraint' => 2,
                'null' => true,
            ],
            'cep' => [
                'type' => 'VARCHAR',
                'constraint' => 9,
                'null' => true,
            ],
            'logo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'ativo' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('cnpj');
        $this->forge->createTable('empresas', true);

        // Tabela: usuarios
        $this->forge->addField([
            'id' => [
                'type' => 'BIGSERIAL',
            ],
            'empresa_id' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => true,
            ],
            'senha' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'cpf' => [
                'type' => 'VARCHAR',
                'constraint' => 14,
                'null' => true,
                'unique' => true,
            ],
            'telefone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'avatar' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'role' => [
                'type' => 'usuario_role',
                'default' => 'visualizador',
            ],
            'ativo' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'ultimo_acesso' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('email');
        $this->forge->addKey('empresa_id');
        $this->forge->addForeignKey('empresa_id', 'empresas', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('usuarios', true);

        // Tabela: responsaveis_tecnicos
        $this->forge->addField([
            'id' => [
                'type' => 'BIGSERIAL',
            ],
            'empresa_id' => [
                'type' => 'BIGINT',
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'crea' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'especialidade' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'telefone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'assinatura' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'ativo' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('empresa_id');
        $this->forge->addKey('crea');
        $this->forge->addForeignKey('empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('responsaveis_tecnicos', true);

        // Tabela: projetos
        $this->forge->addField([
            'id' => [
                'type' => 'BIGSERIAL',
            ],
            'empresa_id' => [
                'type' => 'BIGINT',
            ],
            'codigo_projeto' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'cliente' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'data_inicio' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'data_fim' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'ativo' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('empresa_id');
        $this->forge->addKey('codigo_projeto');
        $this->forge->addForeignKey('empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('projetos', true);

        // Tabela: obras
        $this->forge->addField([
            'id' => [
                'type' => 'BIGSERIAL',
            ],
            'projeto_id' => [
                'type' => 'BIGINT',
            ],
            'codigo_obra' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'endereco' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'cidade' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'estado' => [
                'type' => 'VARCHAR',
                'constraint' => 2,
                'null' => true,
            ],
            'cep' => [
                'type' => 'VARCHAR',
                'constraint' => 9,
                'null' => true,
            ],
            'latitude' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'longitude' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'data_inicio' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'data_fim' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'ativo' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('projeto_id');
        $this->forge->addKey('codigo_obra');
        $this->forge->addForeignKey('projeto_id', 'projetos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('obras', true);

        // Add PostGIS geometry column for obras
        $this->db->query("
            SELECT AddGeometryColumn('obras', 'localizacao', 4674, 'POINT', 2);
        ");
        $this->db->query("
            CREATE INDEX idx_obras_localizacao ON obras USING GIST (localizacao);
        ");

        // Continued in next part...
        $this->createSondagensTable();
        $this->createAmostrasTable();
        $this->createCamadasTable();
        $this->createFotosTable();
        $this->createJobsTable();
        $this->createAuditLogTable();
    }

    private function createSondagensTable()
    {
        // Tabela: sondagens
        $this->forge->addField([
            'id' => [
                'type' => 'BIGSERIAL',
            ],
            'obra_id' => [
                'type' => 'BIGINT',
            ],
            'responsavel_tecnico_id' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'codigo_sondagem' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'data_execucao' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'sondador' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'coordenada_este' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'coordenada_norte' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'zona_utm' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'latitude' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'longitude' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'cota_boca_furo' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'profundidade_final' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'nivel_agua_inicial' => [
                'type' => 'nivel_agua',
                'default' => 'ausente',
            ],
            'nivel_agua_inicial_profundidade' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'nivel_agua_final' => [
                'type' => 'nivel_agua',
                'default' => 'ausente',
            ],
            'nivel_agua_final_profundidade' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'observacoes_paralisacao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'sondagem_status',
                'default' => 'rascunho',
            ],
            'pdf_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('obra_id');
        $this->forge->addKey('responsavel_tecnico_id');
        $this->forge->addKey(['obra_id', 'codigo_sondagem']);
        $this->forge->addKey('status');
        $this->forge->addForeignKey('obra_id', 'obras', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('responsavel_tecnico_id', 'responsaveis_tecnicos', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('sondagens', true);

        // Add PostGIS geometry column for sondagens
        $this->db->query("
            SELECT AddGeometryColumn('sondagens', 'localizacao', 4674, 'POINT', 2);
        ");
        $this->db->query("
            CREATE INDEX idx_sondagens_localizacao ON sondagens USING GIST (localizacao);
        ");
    }

    private function createAmostrasTable()
    {
        // Tabela: amostras
        $this->forge->addField([
            'id' => [
                'type' => 'BIGSERIAL',
            ],
            'sondagem_id' => [
                'type' => 'BIGINT',
            ],
            'numero_amostra' => [
                'type' => 'INTEGER',
            ],
            'tipo_perfuracao' => [
                'type' => 'tipo_perfuracao',
                'default' => 'CR',
            ],
            'profundidade_inicial' => [
                'type' => 'DOUBLE PRECISION',
            ],
            'golpes_1a' => [
                'type' => 'INTEGER',
                'null' => true,
            ],
            'golpes_2a' => [
                'type' => 'INTEGER',
                'null' => true,
            ],
            'golpes_3a' => [
                'type' => 'INTEGER',
                'null' => true,
            ],
            'nspt' => [
                'type' => 'INTEGER',
                'null' => true,
            ],
            'torque' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sondagem_id');
        $this->forge->addKey(['sondagem_id', 'numero_amostra']);
        $this->forge->addForeignKey('sondagem_id', 'sondagens', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('amostras', true);
    }

    private function createCamadasTable()
    {
        // Tabela: camadas
        $this->forge->addField([
            'id' => [
                'type' => 'BIGSERIAL',
            ],
            'sondagem_id' => [
                'type' => 'BIGINT',
            ],
            'numero_camada' => [
                'type' => 'INTEGER',
            ],
            'profundidade_inicial' => [
                'type' => 'DOUBLE PRECISION',
            ],
            'profundidade_final' => [
                'type' => 'DOUBLE PRECISION',
            ],
            'classificacao_principal' => [
                'type' => 'classificacao_solo',
            ],
            'classificacao_secundaria' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'cor' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'umidade' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'consistencia' => [
                'type' => 'consistencia_solo',
                'null' => true,
            ],
            'compacidade' => [
                'type' => 'compacidade_solo',
                'null' => true,
            ],
            'descricao_completa' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'observacoes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sondagem_id');
        $this->forge->addKey(['sondagem_id', 'numero_camada']);
        $this->forge->addForeignKey('sondagem_id', 'sondagens', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('camadas', true);
    }

    private function createFotosTable()
    {
        // Tabela: fotos
        $this->forge->addField([
            'id' => [
                'type' => 'BIGSERIAL',
            ],
            'sondagem_id' => [
                'type' => 'BIGINT',
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
                'type' => 'tipo_foto',
                'default' => 'ensaio_spt',
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ordem' => [
                'type' => 'INTEGER',
                'default' => 0,
            ],
            'latitude' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'longitude' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'altitude' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'velocidade' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'data_hora_exif' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'coordenada_este' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'coordenada_norte' => [
                'type' => 'DOUBLE PRECISION',
                'null' => true,
            ],
            'zona_utm' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'metadata_exif' => [
                'type' => 'JSONB',
                'null' => true,
            ],
            'fabricante' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'modelo' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'orientacao' => [
                'type' => 'INTEGER',
                'null' => true,
            ],
            'largura' => [
                'type' => 'INTEGER',
                'null' => true,
            ],
            'altura' => [
                'type' => 'INTEGER',
                'null' => true,
            ],
            'tamanho_bytes' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'mime_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'thumbnail_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            's3_key' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('sondagem_id');
        $this->forge->addKey('tipo_foto');
        $this->forge->addForeignKey('sondagem_id', 'sondagens', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('fotos', true);

        // Add PostGIS geometry column for fotos
        $this->db->query("
            SELECT AddGeometryColumn('fotos', 'localizacao', 4674, 'POINT', 2);
        ");
        $this->db->query("
            CREATE INDEX idx_fotos_localizacao ON fotos USING GIST (localizacao);
        ");

        // Add JSONB index for metadata searches
        $this->db->query("
            CREATE INDEX idx_fotos_metadata_exif ON fotos USING GIN (metadata_exif);
        ");
    }

    private function createJobsTable()
    {
        // Tabela: jobs
        $this->forge->addField([
            'id' => [
                'type' => 'BIGSERIAL',
            ],
            'uuid' => [
                'type' => 'UUID',
                'default' => new \CodeIgniter\Database\RawSql('gen_random_uuid()'),
                'unique' => true,
            ],
            'queue' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'default',
            ],
            'type' => [
                'type' => 'job_type',
            ],
            'status' => [
                'type' => 'job_status',
                'default' => 'pending',
            ],
            'payload' => [
                'type' => 'JSONB',
            ],
            'result' => [
                'type' => 'JSONB',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'attempts' => [
                'type' => 'INTEGER',
                'default' => 0,
            ],
            'max_attempts' => [
                'type' => 'INTEGER',
                'default' => 3,
            ],
            'reserved_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'available_at' => [
                'type' => 'TIMESTAMP',
            ],
            'started_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'failed_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('uuid');
        $this->forge->addKey(['queue', 'status']);
        $this->forge->addKey('status');
        $this->forge->addKey('available_at');
        $this->forge->createTable('jobs', true);

        // Add JSONB indexes for efficient payload/result searches
        $this->db->query("
            CREATE INDEX idx_jobs_payload ON jobs USING GIN (payload);
        ");
        $this->db->query("
            CREATE INDEX idx_jobs_result ON jobs USING GIN (result);
        ");
    }

    private function createAuditLogTable()
    {
        // Tabela: audit_log
        $this->forge->addField([
            'id' => [
                'type' => 'BIGSERIAL',
            ],
            'usuario_id' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'tabela' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'registro_id' => [
                'type' => 'BIGINT',
            ],
            'acao' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'dados_antigos' => [
                'type' => 'JSONB',
                'null' => true,
            ],
            'dados_novos' => [
                'type' => 'JSONB',
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('usuario_id');
        $this->forge->addKey(['tabela', 'registro_id']);
        $this->forge->addKey('acao');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('audit_log', true);

        // Add JSONB indexes for efficient data searches
        $this->db->query("
            CREATE INDEX idx_audit_dados_antigos ON audit_log USING GIN (dados_antigos);
        ");
        $this->db->query("
            CREATE INDEX idx_audit_dados_novos ON audit_log USING GIN (dados_novos);
        ");
    }

    public function down()
    {
        // Drop tables in reverse order of dependencies
        $this->forge->dropTable('audit_log', true);
        $this->forge->dropTable('jobs', true);
        $this->forge->dropTable('fotos', true);
        $this->forge->dropTable('camadas', true);
        $this->forge->dropTable('amostras', true);
        $this->forge->dropTable('sondagens', true);
        $this->forge->dropTable('obras', true);
        $this->forge->dropTable('projetos', true);
        $this->forge->dropTable('responsaveis_tecnicos', true);
        $this->forge->dropTable('usuarios', true);
        $this->forge->dropTable('empresas', true);
    }
}
