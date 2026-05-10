<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('work_orders')
                ->where('status', 'assigned')
                ->update([
                    'status' => 'scheduled',
                    'updated_at' => now(),
                ]);

            DB::statement("ALTER TABLE work_orders DROP CONSTRAINT IF EXISTS work_orders_status_check");
            DB::statement("ALTER TABLE work_orders ADD CONSTRAINT work_orders_status_check CHECK (status IN ('new', 'scheduled', 'in_progress', 'completed', 'cancelled', 'rework', 'archived'))");
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            DB::statement("ALTER TABLE work_orders DROP CONSTRAINT IF EXISTS work_orders_status_check");
            DB::statement("ALTER TABLE work_orders ADD CONSTRAINT work_orders_status_check CHECK (status IN ('new', 'scheduled', 'assigned', 'in_progress', 'completed', 'cancelled', 'rework', 'archived'))");
        });
    }
};