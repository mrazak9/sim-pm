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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('document_categories')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');

            // Document Info
            $table->string('title');
            $table->string('document_number')->unique()->nullable();
            $table->text('description')->nullable();

            // File Info
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50); // pdf, doc, docx, xls, xlsx, etc
            $table->string('mime_type');
            $table->bigInteger('file_size'); // in bytes

            // Status & Workflow
            $table->enum('status', ['draft', 'review', 'approved', 'archived'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Version Control
            $table->integer('current_version')->default(1);
            $table->boolean('is_latest')->default(true);

            // Metadata
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();

            // Access Control
            $table->enum('visibility', ['public', 'private', 'restricted'])->default('private');

            // Retention
            $table->date('retention_until')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('category_id');
            $table->index('uploaded_by');
            $table->index('status');
            $table->index('is_latest');
            $table->index('visibility');
            $table->index('is_archived');
            $table->index('created_at');
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
