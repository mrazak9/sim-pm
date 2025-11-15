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
        Schema::create('audit_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained('audit_plans')->onDelete('cascade');
            $table->foreignId('unit_kerja_id')->constrained('unit_kerjas')->onDelete('restrict'); // Auditee
            $table->foreignId('auditor_lead_id')->constrained('users')->onDelete('restrict'); // Lead auditor

            $table->dateTime('scheduled_date');
            $table->integer('estimated_duration')->default(120); // Duration in minutes
            $table->string('location')->nullable();
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');

            $table->text('agenda')->nullable(); // Audit agenda
            $table->text('notes')->nullable(); // Additional notes
            $table->text('preparation_notes')->nullable(); // Preparation requirements

            // Completion tracking
            $table->dateTime('actual_start')->nullable();
            $table->dateTime('actual_end')->nullable();
            $table->text('summary')->nullable(); // Audit summary

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('audit_plan_id');
            $table->index('unit_kerja_id');
            $table->index('status');
            $table->index('scheduled_date');
        });

        // Pivot table for additional auditors (team members)
        Schema::create('audit_schedule_auditors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_schedule_id')->constrained('audit_schedules')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['lead', 'member'])->default('member');
            $table->timestamps();

            // Unique constraint to prevent duplicate assignments
            $table->unique(['audit_schedule_id', 'user_id']);

            // Indexes
            $table->index('audit_schedule_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_schedule_auditors');
        Schema::dropIfExists('audit_schedules');
    }
};
