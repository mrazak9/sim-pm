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
        // Rapat Tinjauan Manajemen (Management Review Meeting)
        Schema::create('rtms', function (Blueprint $table) {
            $table->id();
            $table->string('rtm_code')->unique(); // RTM-2025-001
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademiks')->onDelete('cascade');
            $table->date('meeting_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->text('agenda')->nullable(); // Agenda rapat
            $table->text('discussion_points')->nullable(); // Poin pembahasan
            $table->text('decisions')->nullable(); // Keputusan
            $table->text('minutes')->nullable(); // Notulen
            $table->text('follow_up_plan')->nullable(); // Rencana tindak lanjut
            $table->enum('status', ['planned', 'ongoing', 'completed', 'cancelled'])->default('planned');
            $table->foreignId('chairman_id')->nullable()->constrained('users')->onDelete('set null'); // Ketua rapat
            $table->foreignId('secretary_id')->nullable()->constrained('users')->onDelete('set null'); // Notulis
            $table->string('minutes_file')->nullable(); // File notulen
            $table->string('attendance_file')->nullable(); // File daftar hadir
            $table->timestamps();
            $table->softDeletes();

            $table->index('rtm_code');
            $table->index(['tahun_akademik_id', 'meeting_date']);
            $table->index('status');
        });

        // Pivot table for RTM participants
        Schema::create('rtm_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtm_id')->constrained('rtms')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('Peserta');
            $table->boolean('is_present')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['rtm_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rtm_participants');
        Schema::dropIfExists('rtms');
    }
};
