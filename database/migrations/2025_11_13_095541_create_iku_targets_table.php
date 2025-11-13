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
        Schema::create('iku_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iku_id')->constrained('ikus')->onDelete('cascade');
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademiks')->onDelete('cascade');
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('cascade');
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studis')->onDelete('cascade');
            $table->decimal('target_value', 10, 2);
            $table->enum('periode', ['tahunan', 'semester_1', 'semester_2', 'triwulan_1', 'triwulan_2', 'triwulan_3', 'triwulan_4'])->default('tahunan');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['iku_id', 'tahun_akademik_id']);
            $table->index('unit_kerja_id');
            $table->index('program_studi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iku_targets');
    }
};
