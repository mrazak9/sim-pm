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
        Schema::create('document_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('shared_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_with')->constrained('users')->onDelete('cascade');

            $table->enum('permission', ['view', 'download', 'edit'])->default('view');
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('document_id');
            $table->index('shared_with');
            $table->index('expires_at');
            $table->unique(['document_id', 'shared_with']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_shares');
    }
};
