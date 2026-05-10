<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE leads
            SET source = COALESCE(NULLIF(type, ''), NULLIF(source, ''))
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE leads
            DROP COLUMN IF EXISTS sentiment,
            DROP COLUMN IF EXISTS type
        SQL);

        DB::statement('ALTER TABLE leads DROP CONSTRAINT IF EXISTS leads_source_check');

        DB::statement(<<<'SQL'
            ALTER TABLE leads
            ADD CONSTRAINT leads_source_check CHECK (
                source IS NULL
                OR source IN (
                    'website_organic',
                    'facebook',
                    'instagram',
                    'craigslist',
                    'nextdoor',
                    'referral',
                    'physical_media',
                    'in_person'
                )
            )
        SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE leads DROP CONSTRAINT IF EXISTS leads_source_check');

        DB::statement("ALTER TABLE leads ADD COLUMN type text");
        DB::statement("ALTER TABLE leads ADD COLUMN sentiment text NOT NULL DEFAULT 'warm'");

        DB::statement(<<<'SQL'
            UPDATE leads
            SET type = source,
                sentiment = 'warm'
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE leads
            ADD CONSTRAINT leads_type_check CHECK (
                type IS NULL
                OR type IN (
                    'website_organic',
                    'facebook',
                    'instagram',
                    'craigslist',
                    'nextdoor',
                    'referral',
                    'physical_media',
                    'in_person'
                )
            )
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE leads
            ADD CONSTRAINT leads_sentiment_check CHECK (sentiment IN ('hot', 'warm', 'cold'))
        SQL);
    }
};