<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('report_type', 80);
            $table->text('description')->nullable();
            $table->json('filters')->nullable();
            $table->json('email_recipients')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->string('last_export_path')->nullable();
            $table->string('last_export_format', 40)->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->index(['report_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_reports');
    }
};