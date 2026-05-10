<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->timestampTz('closed_at')->nullable()->after('expected_value');
            $table->text('lost_reason')->nullable()->after('closed_at');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                UPDATE leads
                SET closed_at = COALESCE(closed_at, converted_at, NULLIF(notes->>'closed_at', '')::timestamptz),
                    lost_reason = COALESCE(lost_reason, NULLIF(notes->>'lost_reason', ''))
                WHERE converted_at IS NOT NULL
                   OR jsonb_exists(notes, 'closed_at')
                   OR jsonb_exists(notes, 'lost_reason')
            SQL);
        }
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['closed_at', 'lost_reason']);
        });
    }
};