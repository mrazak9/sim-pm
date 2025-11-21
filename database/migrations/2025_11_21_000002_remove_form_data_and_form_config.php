<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove form_data column from pengisian_butirs
        Schema::table('pengisian_butirs', function (Blueprint $table) {
            $table->dropColumn('form_data');
        });

        // Remove form_config from butir_akreditasis metadata
        // This is optional - we keep metadata for other purposes
        // but clear out form_config
        // Note: The '-' operator is safe even if the key doesn't exist
        DB::statement("
            UPDATE butir_akreditasis
            SET metadata = metadata - 'form_config'
            WHERE metadata IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back form_data column
        Schema::table('pengisian_butirs', function (Blueprint $table) {
            $table->jsonb('form_data')->nullable();
        });

        // Note: We cannot restore form_config data as it's been removed
        // If you need to rollback, restore from backup
    }
};
