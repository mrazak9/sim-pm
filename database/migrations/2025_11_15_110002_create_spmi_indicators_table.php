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
        Schema::create('spmi_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmi_standard_id')->constrained('spmi_standards')->onDelete('cascade');
            $table->string('code')->unique(); // IND-PEND-001
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('measurement_unit')->nullable(); // satuan: %, jumlah, skor, dll
            $table->enum('measurement_type', ['kuantitatif', 'kualitatif'])->default('kuantitatif');
            $table->text('formula')->nullable(); // Rumus perhitungan
            $table->text('data_source')->nullable(); // Sumber data
            $table->enum('frequency', ['harian', 'mingguan', 'bulanan', 'semesteran', 'tahunan'])->default('semesteran');
            $table->foreignId('pic_id')->nullable()->constrained('users')->onDelete('set null'); // PIC pengumpulan data
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('spmi_standard_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmi_indicators');
    }
};
