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
        Schema::create('ikus', function (Blueprint $table) {
            $table->id();
            $table->string('kode_iku')->unique();
            $table->string('nama_iku');
            $table->text('deskripsi')->nullable();
            $table->enum('satuan', ['persen', 'jumlah', 'skor', 'nilai'])->default('jumlah');
            $table->enum('target_type', ['increase', 'decrease'])->default('increase')->comment('Apakah target harus naik atau turun');
            $table->string('kategori')->nullable();
            $table->integer('bobot')->nullable()->comment('Bobot/kepentingan IKU (1-100)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('kode_iku');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ikus');
    }
};
