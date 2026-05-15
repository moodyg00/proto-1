<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('companies')) {
            return;
        }

        DB::statement('DROP TABLE companies');
    }

    public function down(): void
    {
        if (Schema::hasTable('companies')) {
            return;
        }

        DB::statement(<<<'SQL'
            CREATE TABLE companies (
                id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
                name varchar(255) NOT NULL,
                logo_url text,
                settings jsonb,
                invoice_template jsonb,
                address jsonb,
                tax_settings jsonb,
                is_active boolean NOT NULL DEFAULT true,
                created_at timestamptz DEFAULT now(),
                updated_at timestamptz DEFAULT now(),
                created_by uuid REFERENCES users(id) ON DELETE SET NULL,
                updated_by uuid REFERENCES users(id) ON DELETE SET NULL
            )
        SQL);

        DB::statement('CREATE INDEX idx_companies_name ON companies(name)');
        DB::statement('CREATE INDEX idx_companies_is_active ON companies(is_active)');
    }
};