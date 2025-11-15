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
        Schema::create('audit_evidence', function (Blueprint $table) {
            $table->id();

            // Evidence can be attached to either a finding or a schedule
            $table->foreignId('audit_finding_id')->nullable()->constrained('audit_findings')->onDelete('cascade');
            $table->foreignId('audit_schedule_id')->nullable()->constrained('audit_schedules')->onDelete('cascade');

            // File information
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 100); // image/jpeg, application/pdf, etc.
            $table->unsignedBigInteger('file_size'); // Size in bytes
            $table->string('mime_type', 100)->nullable();

            // Evidence metadata
            $table->string('title')->nullable(); // Evidence title
            $table->text('description')->nullable(); // Description of what this evidence shows
            $table->enum('type', ['photo', 'document', 'video', 'audio', 'other'])->default('document');
            $table->dateTime('captured_at')->nullable(); // When evidence was captured (can be different from upload time)

            // Upload tracking
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('restrict');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('audit_finding_id');
            $table->index('audit_schedule_id');
            $table->index('type');
            $table->index('uploaded_by');

            // Ensure evidence is attached to either finding or schedule
            // This will be checked in the model
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_evidence');
    }
};
