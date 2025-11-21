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
        Schema::create('instrumen_akreditasis', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // 4.0, 9.0, etc
            $table->string('nama'); // IAPT 4.0, IAPT 9.0
            $table->text('deskripsi')->nullable();
            $table->enum('jenis', ['institusi', 'program_studi', 'both'])->default('both');
            $table->string('lembaga')->nullable(); // BAN-PT, LAM, etc
            $table->integer('tahun_berlaku')->nullable(); // 2023, 2024
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('kode');
            $table->index('jenis');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instrumen_akreditasis');
    }
};
