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
        Schema::create('audit_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_schedule_id')->constrained('audit_schedules')->onDelete('cascade');

            // Finding classification
            $table->string('finding_code')->unique(); // e.g., "AUD-2025-001"
            $table->enum('category', ['major', 'minor', 'ofi'])->comment('major=Major NC, minor=Minor NC, ofi=Opportunity for Improvement');
            $table->string('standar_reference')->nullable(); // e.g., "ISO 9001:2015 Clause 7.1.6"
            $table->string('clause')->nullable(); // Specific clause/standard number

            // Finding details
            $table->text('description'); // What was found
            $table->text('evidence_description'); // Evidence of the finding
            $table->text('root_cause')->nullable(); // Root cause analysis
            $table->text('recommendation'); // Auditor's recommendation
            $table->text('impact')->nullable(); // Potential impact if not resolved

            // Responsibility
            $table->foreignId('pic_id')->nullable()->constrained('users')->onDelete('set null'); // Person in Charge
            $table->foreignId('unit_kerja_id')->constrained('unit_kerjas')->onDelete('restrict'); // Responsible unit
            $table->date('due_date')->nullable();

            // Status tracking
            $table->enum('status', ['open', 'in_progress', 'resolved', 'verified', 'closed'])->default('open');
            $table->text('resolution_notes')->nullable(); // How it was resolved
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();

            // Priority and severity
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('severity', ['low', 'medium', 'high'])->default('medium');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('audit_schedule_id');
            $table->index('category');
            $table->index('status');
            $table->index('priority');
            $table->index('finding_code');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_findings');
    }
};
