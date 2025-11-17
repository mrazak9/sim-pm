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
        Schema::create('spmi_monitorings', function (Blueprint $table) {
            $table->id();
            $table->string('monitoring_code')->unique(); // MON-2025-001
            $table->foreignId('spmi_standard_id')->constrained('spmi_standards')->onDelete('cascade');
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademiks')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('monitoring_date');
            $table->enum('monitoring_type', ['desk_evaluation', 'field_visit', 'interview', 'document_review', 'mixed'])->default('mixed');
            $table->text('findings')->nullable(); // Temuan
            $table->text('strengths')->nullable(); // Kekuatan
            $table->text('weaknesses')->nullable(); // Kelemahan
            $table->text('opportunities')->nullable(); // Peluang
            $table->text('threats')->nullable(); // Ancaman (SWOT)
            $table->text('recommendations')->nullable(); // Rekomendasi
            $table->enum('compliance_level', ['very_low', 'low', 'medium', 'high', 'very_high'])->nullable(); // Tingkat kesesuaian
            $table->integer('compliance_score')->nullable(); // Skor 0-100
            $table->enum('status', ['planned', 'ongoing', 'completed', 'reported'])->default('planned');
            $table->foreignId('monitored_by')->nullable()->constrained('users')->onDelete('set null'); // Auditor/Evaluator
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('set null'); // Unit yang dimonitor
            $table->string('report_file')->nullable(); // File laporan
            $table->timestamps();
            $table->softDeletes();

            $table->index('monitoring_code');
            $table->index(['spmi_standard_id', 'tahun_akademik_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmi_monitorings');
    }
};
