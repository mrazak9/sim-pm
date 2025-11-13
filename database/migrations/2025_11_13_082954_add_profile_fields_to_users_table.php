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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 30)->nullable()->after('email');
            $table->string('nidn', 20)->nullable()->after('nip');
            $table->string('phone', 20)->nullable()->after('nidn');
            $table->text('address')->nullable()->after('phone');
            $table->foreignId('unit_kerja_id')->nullable()->after('address')->constrained('unit_kerjas')->onDelete('set null');
            $table->foreignId('jabatan_id')->nullable()->after('unit_kerja_id')->constrained('jabatans')->onDelete('set null');
            $table->boolean('is_active')->default(true)->after('jabatan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_kerja_id']);
            $table->dropForeign(['jabatan_id']);
            $table->dropColumn(['nip', 'nidn', 'phone', 'address', 'unit_kerja_id', 'jabatan_id', 'is_active']);
        });
    }
};
