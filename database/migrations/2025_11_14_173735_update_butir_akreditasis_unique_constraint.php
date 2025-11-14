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
            // Drop the old unique constraint on kode only
            $table->dropUnique(['kode']);

            // Add composite unique constraint on kode, instrumen, and periode_akreditasi_id
            // This allows same kode for different instruments or different periods
            $table->unique(['kode', 'instrumen', 'periode_akreditasi_id'], 'butir_kode_instrumen_periode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('butir_akreditasis', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('butir_kode_instrumen_periode_unique');

            // Restore the old unique constraint on kode only
            $table->unique('kode');
        });
    }
};
