<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEnumTypes extends Migration
{
    public function up()
    {
        // Create ENUM types for PostgreSQL
        
        // Status da sondagem
        $this->db->query("
            CREATE TYPE sondagem_status AS ENUM (
                'rascunho',
                'em_campo',
                'em_analise',
                'concluido',
                'cancelado'
            )
        ");

        // Tipo de perfuração
        $this->db->query("
            CREATE TYPE tipo_perfuracao AS ENUM (
                'TH',
                'CR',
                'PD'
            )
        ");

        // Classificação de solo
        $this->db->query("
            CREATE TYPE classificacao_solo AS ENUM (
                'argila',
                'argila_arenosa',
                'argila_siltosa',
                'areia',
                'areia_argilosa',
                'areia_siltosa',
                'silte',
                'silte_arenoso',
                'silte_argiloso',
                'pedregulho',
                'rocha',
                'aterro',
                'turfa',
                'outro'
            )
        ");

        // Consistência do solo
        $this->db->query("
            CREATE TYPE consistencia_solo AS ENUM (
                'muito_mole',
                'mole',
                'media',
                'rija',
                'muito_rija',
                'dura'
            )
        ");

        // Compacidade do solo
        $this->db->query("
            CREATE TYPE compacidade_solo AS ENUM (
                'fofa',
                'pouco_compacta',
                'medianamente_compacta',
                'compacta',
                'muito_compacta'
            )
        ");

        // Nível de água
        $this->db->query("
            CREATE TYPE nivel_agua AS ENUM (
                'presente',
                'ausente'
            )
        ");

        // Tipo de foto
        $this->db->query("
            CREATE TYPE tipo_foto AS ENUM (
                'ensaio_spt',
                'panoramica',
                'equipamento',
                'solo_amostra',
                'local_sondagem',
                'outro'
            )
        ");

        // Tipo de usuário (role)
        $this->db->query("
            CREATE TYPE usuario_role AS ENUM (
                'admin',
                'tecnico',
                'cliente',
                'visualizador'
            )
        ");

        // Status de job
        $this->db->query("
            CREATE TYPE job_status AS ENUM (
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled'
            )
        ");

        // Tipo de job
        $this->db->query("
            CREATE TYPE job_type AS ENUM (
                'pdf_generation',
                'image_processing',
                'data_import',
                'data_export',
                'email_sending'
            )
        ");
    }

    public function down()
    {
        // Drop ENUM types in reverse order
        $this->db->query('DROP TYPE IF EXISTS job_type');
        $this->db->query('DROP TYPE IF EXISTS job_status');
        $this->db->query('DROP TYPE IF EXISTS usuario_role');
        $this->db->query('DROP TYPE IF EXISTS tipo_foto');
        $this->db->query('DROP TYPE IF EXISTS nivel_agua');
        $this->db->query('DROP TYPE IF EXISTS compacidade_solo');
        $this->db->query('DROP TYPE IF EXISTS consistencia_solo');
        $this->db->query('DROP TYPE IF EXISTS classificacao_solo');
        $this->db->query('DROP TYPE IF EXISTS tipo_perfuracao');
        $this->db->query('DROP TYPE IF EXISTS sondagem_status');
    }
}
