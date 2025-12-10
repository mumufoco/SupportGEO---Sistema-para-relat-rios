<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnablePostgresExtensions extends Migration
{
    public function up()
    {
        // Enable PostGIS extension for spatial data support
        $this->db->query('CREATE EXTENSION IF NOT EXISTS postgis');
        $this->db->query('CREATE EXTENSION IF NOT EXISTS postgis_topology');
        
        // Enable pgcrypto for UUID and cryptographic functions
        $this->db->query('CREATE EXTENSION IF NOT EXISTS pgcrypto');
        
        // Enable pg_trgm for full-text search and similarity matching
        $this->db->query('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        
        // Enable uuid-ossp for UUID generation (alternative to pgcrypto)
        $this->db->query('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
    }

    public function down()
    {
        // Drop extensions in reverse order
        $this->db->query('DROP EXTENSION IF EXISTS "uuid-ossp"');
        $this->db->query('DROP EXTENSION IF EXISTS pg_trgm');
        $this->db->query('DROP EXTENSION IF EXISTS pgcrypto');
        $this->db->query('DROP EXTENSION IF EXISTS postgis_topology');
        $this->db->query('DROP EXTENSION IF EXISTS postgis');
    }
}
