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
        Schema::create('iku_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iku_target_id')->constrained('iku_targets')->onDelete('cascade');
            $table->date('tanggal_capaian');
            $table->decimal('nilai_capaian', 10, 2);
            $table->decimal('persentase_capaian', 5, 2)->nullable()->comment('Persentase capaian terhadap target');
            $table->text('keterangan')->nullable();
            $table->string('bukti_dokumen')->nullable()->comment('Path ke file dokumen pendukung');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('iku_target_id');
            $table->index('tanggal_capaian');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iku_progress');
    }
};
