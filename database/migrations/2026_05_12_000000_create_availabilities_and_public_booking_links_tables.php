<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->string('scope', 40)->default('contractor');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->index(['scope', 'starts_at']);
        });

        Schema::create('public_booking_links', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('token', 120)->unique();
            $table->uuid('service_id')->nullable();
            $table->text('description')->nullable();
            $table->json('available_weekdays')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('slot_minutes')->default(60);
            $table->unsignedSmallInteger('max_days_ahead')->default(30);
            $table->string('timezone', 80)->default('America/New_York');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_booking_links');
        Schema::dropIfExists('availabilities');
    }
};