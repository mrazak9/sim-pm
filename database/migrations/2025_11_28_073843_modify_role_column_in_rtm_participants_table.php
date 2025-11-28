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
        Schema::table('rtm_participants', function (Blueprint $table) {
            // Change role from enum to string for more flexibility
            $table->string('role')->default('Peserta')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rtm_participants', function (Blueprint $table) {
            // Revert to enum if needed (but this might fail if data doesn't match enum values)
            DB::statement("ALTER TABLE rtm_participants ALTER COLUMN role TYPE VARCHAR(255)");
        });
    }
};
