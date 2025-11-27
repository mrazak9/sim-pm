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
        Schema::table('program_studis', function (Blueprint $table) {
            // Change akreditasi column from varchar(5) to varchar(50)
            // to accommodate new accreditation system (Unggul, Baik Sekali, Baik, Tidak Terakreditasi)
            $table->string('akreditasi', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_studis', function (Blueprint $table) {
            // Revert back to varchar(5)
            $table->string('akreditasi', 5)->nullable()->change();
        });
    }
};
