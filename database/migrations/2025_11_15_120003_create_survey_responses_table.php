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
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Null if anonymous
            $table->string('response_code')->unique(); // RESP-2025-001

            // Tracking information
            $table->string('ip_address')->nullable(); // IP address for anonymous responses
            $table->text('user_agent')->nullable(); // Browser information

            // Timing
            $table->timestamp('started_at')->nullable(); // When user started the survey
            $table->timestamp('completed_at')->nullable(); // When submitted
            $table->boolean('is_completed')->default(false);
            $table->integer('completion_time_seconds')->nullable(); // How long it took to complete

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('survey_id');
            $table->index('user_id');
            $table->index('response_code');
            $table->index('completed_at');
            $table->index('is_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
