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
        Schema::create('survey_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade');
            $table->enum('distribution_type', ['email', 'link', 'qr', 'embedded'])->default('link');

            // Distribution details
            $table->text('distribution_url')->nullable(); // Generated URL/link
            $table->string('email_subject')->nullable(); // For email distribution
            $table->text('email_body')->nullable(); // Email template

            // Statistics
            $table->integer('sent_count')->default(0); // How many sent
            $table->integer('response_count')->default(0); // How many responded

            // QR Code
            $table->string('qr_code_path')->nullable(); // Path to QR code image

            // Tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');

            $table->timestamps();

            // Indexes
            $table->index('survey_id');
            $table->index('distribution_type');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_distributions');
    }
};
