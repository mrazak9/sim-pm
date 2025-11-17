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
        Schema::create('rtm_action_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtm_id')->constrained('rtms')->onDelete('cascade');
            $table->string('action_code')->unique(); // RTM-ACT-2025-001
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->foreignId('pic_id')->constrained('users')->onDelete('restrict'); // Penanggung jawab
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('set null');
            $table->date('due_date');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'overdue', 'cancelled'])->default('not_started');
            $table->integer('completion_percentage')->default(0);
            $table->text('progress_notes')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('completion_remarks')->nullable(); // Catatan penyelesaian
            $table->string('evidence_file')->nullable(); // File bukti penyelesaian
            $table->timestamps();
            $table->softDeletes();

            $table->index('action_code');
            $table->index(['rtm_id', 'status']);
            $table->index('due_date');
        });

        // Progress tracking for action items
        Schema::create('rtm_action_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtm_action_item_id')->constrained('rtm_action_items')->onDelete('cascade');
            $table->date('progress_date');
            $table->integer('progress_percentage');
            $table->text('description');
            $table->string('evidence_file')->nullable();
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('rtm_action_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rtm_action_progress');
        Schema::dropIfExists('rtm_action_items');
    }
};
