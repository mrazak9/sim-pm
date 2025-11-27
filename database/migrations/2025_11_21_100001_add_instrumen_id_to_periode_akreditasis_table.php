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
        Schema::table('periode_akreditasis', function (Blueprint $table) {
            // Add instrumen_id foreign key
            $table->foreignId('instrumen_id')
                ->nullable()
                ->after('lembaga')
                ->constrained('instrumen_akreditasis')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periode_akreditasis', function (Blueprint $table) {
            $table->dropForeign(['instrumen_id']);
            $table->dropColumn('instrumen_id');
        });
    }
};
