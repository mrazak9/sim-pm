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
        Schema::table('butir_akreditasis', function (Blueprint $table) {
            // Add periode_akreditasi_id column (nullable for template butir)
            // NULL = master template, NOT NULL = butir per-periode
            $table->foreignId('periode_akreditasi_id')
                ->nullable()
                ->after('instrumen')
                ->constrained('periode_akreditasis')
                ->onDelete('cascade');

            // Add index for better query performance
            $table->index('periode_akreditasi_id');

            // Add template_id to track which template a butir was copied from
            $table->foreignId('template_id')
                ->nullable()
                ->after('periode_akreditasi_id')
                ->constrained('butir_akreditasis')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('butir_akreditasis', function (Blueprint $table) {
            $table->dropForeign(['periode_akreditasi_id']);
            $table->dropForeign(['template_id']);
            $table->dropColumn(['periode_akreditasi_id', 'template_id']);
        });
    }
};
