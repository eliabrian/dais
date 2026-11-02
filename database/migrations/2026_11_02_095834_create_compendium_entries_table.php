<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('compendium_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('system_id')->references('id')->on('systems')->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('name')->index();
            $table->string('summary')->nullable();
            $table->text('description')->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_srd')->default(true);
            $table->timestamps();

            $table->index(['system_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compendium_entries');
    }
};
