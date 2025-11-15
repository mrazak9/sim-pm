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
        Schema::create('rtl_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rtl_id')->constrained('rtls')->onDelete('cascade');

            // Progress update details
            $table->date('progress_date'); // Date of this progress update
            $table->integer('progress_percentage'); // Progress at this point (0-100)
            $table->text('description'); // Description of progress made
            $table->text('achievements')->nullable(); // What was achieved in this update
            $table->text('challenges')->nullable(); // Challenges faced

            // Evidence of progress
            $table->string('evidence_file')->nullable(); // File path for supporting evidence
            $table->string('evidence_type')->nullable(); // Type of evidence (photo, document, etc.)
            $table->unsignedBigInteger('evidence_size')->nullable(); // File size in bytes

            // Additional details
            $table->text('next_steps')->nullable(); // Next steps to be taken
            $table->date('next_review_date')->nullable(); // When to review next

            // Tracking
            $table->foreignId('reported_by')->constrained('users')->onDelete('restrict');
            $table->boolean('is_milestone')->default(false); // Mark significant progress points

            $table->timestamps();

            // Indexes
            $table->index('rtl_id');
            $table->index('progress_date');
            $table->index('reported_by');
            $table->index(['rtl_id', 'progress_date']); // Composite index for queries by RTL and date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rtl_progress');
    }
};
