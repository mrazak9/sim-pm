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
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade');
            $table->text('question_text');
            $table->enum('question_type', [
                'text',
                'textarea',
                'radio',
                'checkbox',
                'dropdown',
                'rating',
                'matrix'
            ])->default('text');
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0); // Display order

            // Options and validation
            $table->json('options')->nullable(); // For radio, checkbox, dropdown (array of options)
            $table->json('validation_rules')->nullable(); // Additional validation (min, max, regex, etc)
            $table->json('conditional_logic')->nullable(); // Show/hide based on previous answers
            $table->text('description')->nullable(); // Help text for the question

            $table->timestamps();

            // Indexes
            $table->index('survey_id');
            $table->index('order');
            $table->index('question_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
