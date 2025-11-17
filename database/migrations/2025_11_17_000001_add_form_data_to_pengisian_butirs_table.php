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
        Schema::table('pengisian_butirs', function (Blueprint $table) {
            // Add structured form data column (JSON)
            // This will store data based on dynamic form templates
            $table->json('form_data')->nullable()->after('konten_plain');

            // Add index for better query performance
            $table->index('butir_akreditasi_id');
            $table->index('periode_akreditasi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengisian_butirs', function (Blueprint $table) {
            $table->dropIndex(['butir_akreditasi_id']);
            $table->dropIndex(['periode_akreditasi_id']);
            $table->dropColumn('form_data');
        });
    }
};
