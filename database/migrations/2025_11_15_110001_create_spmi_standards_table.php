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
        Schema::create('spmi_standards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // SPMI-PEND-001, SPMI-PENEL-001, etc.
            $table->string('name');
            $table->enum('category', ['pendidikan', 'penelitian', 'pengabdian', 'pengelolaan'])->default('pendidikan');
            $table->text('description')->nullable();
            $table->text('objective')->nullable(); // Tujuan standar
            $table->text('scope')->nullable(); // Ruang lingkup
            $table->text('reference')->nullable(); // Rujukan/referensi
            $table->enum('status', ['draft', 'active', 'revision', 'archived'])->default('draft');
            $table->date('effective_date')->nullable(); // Tanggal berlaku
            $table->date('review_date')->nullable(); // Tanggal review berikutnya
            $table->string('version', 20)->default('1.0');
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerjas')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('category');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmi_standards');
    }
};
