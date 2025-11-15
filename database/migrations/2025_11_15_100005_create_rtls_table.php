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
        Schema::create('rtls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_finding_id')->constrained('audit_findings')->onDelete('cascade');

            // RTL (Rencana Tindak Lanjut / Follow-up Action Plan) details
            $table->string('rtl_code')->unique(); // e.g., "RTL-2025-001"
            $table->text('action_plan'); // What action will be taken
            $table->text('success_indicator')->nullable(); // How to measure success
            $table->text('implementation_steps')->nullable(); // Steps to implement

            // Resources
            $table->foreignId('pic_id')->constrained('users')->onDelete('restrict'); // Person in Charge
            $table->foreignId('unit_kerja_id')->constrained('unit_kerjas')->onDelete('restrict'); // Responsible unit
            $table->date('target_date'); // Target completion date
            $table->decimal('budget', 15, 2)->nullable(); // Budget allocated (if any)
            $table->text('resources_needed')->nullable(); // Resources required

            // Progress tracking
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'overdue', 'cancelled'])->default('not_started');
            $table->integer('completion_percentage')->default(0); // 0-100
            $table->text('current_status_notes')->nullable(); // Current progress notes

            // Completion tracking
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable(); // Notes upon completion

            // Verification
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->enum('verification_status', ['pending', 'approved', 'rejected', 'revision'])->nullable();

            // Risk assessment
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium');
            $table->text('risk_description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('audit_finding_id');
            $table->index('pic_id');
            $table->index('unit_kerja_id');
            $table->index('status');
            $table->index('target_date');
            $table->index('rtl_code');
            $table->index('completion_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rtls');
    }
};
