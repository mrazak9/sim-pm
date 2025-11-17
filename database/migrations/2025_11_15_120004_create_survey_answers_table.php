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
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')->constrained('survey_responses')->onDelete('cascade');
            $table->foreignId('survey_question_id')->constrained('survey_questions')->onDelete('cascade');

            // Different answer types
            $table->text('answer_text')->nullable(); // For text/textarea questions
            $table->json('answer_option')->nullable(); // For radio/checkbox/dropdown (selected option(s))
            $table->integer('answer_rating')->nullable(); // For rating questions (1-5, 1-10, etc)

            $table->timestamps();

            // Indexes
            $table->index('survey_response_id');
            $table->index('survey_question_id');
            $table->index('answer_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
