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
        Schema::create('butir_akreditasis', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique(); // Misal: C.1.1.a, C.2.3.b
            $table->string('nama'); // Nama butir
            $table->text('deskripsi')->nullable(); // Deskripsi/pertanyaan butir
            $table->string('instrumen')->default('4.0'); // Instrumen akreditasi (4.0, 9.0, dll)
            $table->string('kategori')->nullable(); // Misal: Kriteria 1, Kriteria 2, dst
            $table->decimal('bobot', 5, 2)->default(0.00); // Bobot untuk scoring
            $table->foreignId('parent_id')->nullable()->constrained('butir_akreditasis')->onDelete('cascade'); // Untuk hirarki butir
            $table->integer('urutan')->default(0); // Urutan tampilan
            $table->boolean('is_mandatory')->default(true); // Wajib diisi atau tidak
            $table->json('metadata')->nullable(); // Data tambahan (format jawaban, validasi, dll)
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('instrumen');
            $table->index('kategori');
            $table->index('parent_id');
            $table->index('urutan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('butir_akreditasis');
    }
};
