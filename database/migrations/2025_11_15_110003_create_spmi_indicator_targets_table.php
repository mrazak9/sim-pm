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
        Schema::create('spmi_indicator_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmi_indicator_id')->constrained('spmi_indicators')->onDelete('cascade');
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademiks')->onDelete('cascade');
            $table->enum('periode', ['semester_1', 'semester_2', 'tahunan'])->default('tahunan');
            $table->decimal('target_value', 10, 2);
            $table->decimal('achievement_value', 10, 2)->nullable(); // Realisasi
            $table->integer('achievement_percentage')->nullable(); // Persentase capaian
            $table->text('notes')->nullable();
            $table->enum('status', ['not_started', 'on_track', 'at_risk', 'achieved', 'not_achieved'])->default('not_started');
            $table->date('measurement_date')->nullable(); // Tanggal pengukuran
            $table->foreignId('measured_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['spmi_indicator_id', 'tahun_akademik_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmi_indicator_targets');
    }
};
