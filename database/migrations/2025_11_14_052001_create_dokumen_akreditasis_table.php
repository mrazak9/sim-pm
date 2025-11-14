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
        Schema::create('dokumen_akreditasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_akreditasi_id')->constrained('periode_akreditasis')->onDelete('cascade');
            $table->string('nama_dokumen'); // Nama dokumen
            $table->string('nomor_dokumen')->nullable(); // Nomor dokumen (jika ada)
            $table->enum('jenis_dokumen', ['kebijakan', 'pedoman', 'manual', 'sop', 'formulir', 'instruksi_kerja', 'laporan', 'sertifikat', 'sk', 'lainnya'])->default('lainnya');
            $table->text('deskripsi')->nullable(); // Deskripsi dokumen
            $table->string('file_path')->nullable(); // Path file dokumen
            $table->string('file_type')->nullable(); // PDF, DOC, XLS, dll
            $table->unsignedBigInteger('file_size')->default(0); // Size in bytes
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('metadata')->nullable(); // Extra metadata
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('periode_akreditasi_id');
            $table->index('jenis_dokumen');
            $table->index('uploaded_by');
        });

        // Pivot table: Matrix pemetaan dokumen ke butir akreditasi
        Schema::create('butir_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('butir_akreditasi_id')->constrained('butir_akreditasis')->onDelete('cascade');
            $table->foreignId('dokumen_akreditasi_id')->constrained('dokumen_akreditasis')->onDelete('cascade');
            $table->text('keterangan')->nullable(); // Keterangan relevansi
            $table->integer('urutan')->default(0); // Urutan dokumen untuk butir ini
            $table->timestamps();

            // Unique constraint
            $table->unique(['butir_akreditasi_id', 'dokumen_akreditasi_id'], 'unique_butir_dokumen');

            // Indexes
            $table->index('butir_akreditasi_id');
            $table->index('dokumen_akreditasi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('butir_dokumen');
        Schema::dropIfExists('dokumen_akreditasis');
    }
};
