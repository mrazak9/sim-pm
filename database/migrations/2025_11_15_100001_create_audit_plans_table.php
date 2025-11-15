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
        Schema::create('audit_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademiks')->onDelete('restrict');
            $table->enum('periode', ['semester_1', 'semester_2', 'tahunan'])->default('tahunan');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'approved', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->text('objectives')->nullable(); // Audit objectives
            $table->text('scope')->nullable(); // Audit scope

            // Approval tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('tahun_akademik_id');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_plans');
    }
};
