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
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');

            $table->integer('version_number');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50);
            $table->string('mime_type');
            $table->bigInteger('file_size');

            $table->text('change_notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['document_id', 'version_number']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
