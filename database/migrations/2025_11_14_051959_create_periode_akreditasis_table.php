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
        Schema::create('periode_akreditasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Nama periode akreditasi
            $table->enum('jenis_akreditasi', ['institusi', 'program_studi'])->default('program_studi');
            $table->enum('lembaga', ['BAN-PT', 'LAM', 'Internasional'])->default('BAN-PT');
            $table->string('instrumen')->nullable(); // Nama instrumen (misal: 4.0, 9.0)
            $table->string('jenjang')->nullable(); // D3, S1, S2, S3 (jika prodi)

            // Foreign keys
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('set null');
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studis')->onDelete('set null');

            // Timeline
            $table->date('tanggal_mulai')->nullable();
            $table->date('deadline_pengumpulan')->nullable();
            $table->date('jadwal_visitasi')->nullable();
            $table->date('tanggal_berakhir')->nullable();

            // Status & Metadata
            $table->enum('status', ['persiapan', 'pengisian', 'review', 'submit', 'visitasi', 'selesai'])->default('persiapan');
            $table->text('keterangan')->nullable();
            $table->json('metadata')->nullable(); // Extra data jika diperlukan

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('jenis_akreditasi');
            $table->index(['unit_kerja_id', 'program_studi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_akreditasis');
    }
};
