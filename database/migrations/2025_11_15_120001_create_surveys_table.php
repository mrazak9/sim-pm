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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('survey_code')->unique(); // SURV-2025-001
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['internal', 'external', 'public'])->default('internal');
            $table->enum('status', ['draft', 'published', 'closed', 'archived'])->default('draft');

            // Schedule
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Settings
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('allow_multiple_responses')->default(false);
            $table->boolean('require_login')->default(true);
            $table->boolean('show_results')->default(false);
            $table->integer('max_responses')->nullable(); // Limit number of responses

            // Relations
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('survey_code');
            $table->index('status');
            $table->index('created_by');
            $table->index(['start_date', 'end_date']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
