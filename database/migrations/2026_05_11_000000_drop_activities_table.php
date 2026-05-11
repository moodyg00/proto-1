<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('activities');
    }

    public function down(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 80);
            $table->string('status')->default('pending');
            $table->string('priority')->default('medium');
            $table->timestampTz('activity_date')->nullable();
            $table->timestampTz('due_at')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->uuid('lead_id')->nullable();
            $table->uuid('contact_id')->nullable();
            $table->uuid('organization_id')->nullable();
            $table->string('related_type', 120)->nullable();
            $table->uuid('related_id')->nullable();
            $table->json('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('lead_id')->references('id')->on('leads')->nullOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->nullOnDelete();
            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->index('type');
            $table->index('status');
            $table->index('priority');
            $table->index('activity_date');
            $table->index('assigned_to');
            $table->index('lead_id');
            $table->index('contact_id');
            $table->index('organization_id');
            $table->index(['related_type', 'related_id']);
        });
    }
};