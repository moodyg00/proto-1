<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! App::environment(['local', 'staging', 'testing'])) {
            throw new RuntimeException(
                'bootstrap_phase1_schema can only run in local, staging, or testing environments.'
            );
        }

        $schemaPath = base_path('schema.sql');

        if (! file_exists($schemaPath)) {
            throw new RuntimeException('schema.sql was not found at project root.');
        }

        $sql = file_get_contents($schemaPath);

        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException('schema.sql is empty or unreadable.');
        }

        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     *
     * This bootstrap migration is intended for fresh environments.
     * Down is intentionally limited to prevent accidental destructive rollback.
     */
    public function down(): void
    {
        throw new RuntimeException(
            'Rollback for bootstrap_phase1_schema is intentionally disabled. ' .
            'Use a fresh database reset for rollback operations.'
        );
    }
};
