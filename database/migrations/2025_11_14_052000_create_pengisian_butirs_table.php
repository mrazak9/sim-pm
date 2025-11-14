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
        Schema::create('pengisian_butirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_akreditasi_id')->constrained('periode_akreditasis')->onDelete('cascade');
            $table->foreignId('butir_akreditasi_id')->constrained('butir_akreditasis')->onDelete('cascade');
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->onDelete('set null'); // PIC pengisian

            // Content
            $table->longText('konten')->nullable(); // Rich text content (HTML)
            $table->longText('konten_plain')->nullable(); // Plain text untuk search

            // Files & Documents
            $table->json('files')->nullable(); // Array of uploaded files

            // Status & Versioning
            $table->enum('status', ['draft', 'submitted', 'review', 'revision', 'approved'])->default('draft');
            $table->integer('version')->default(1); // Version number
            $table->text('notes')->nullable(); // Catatan/komentar

            // Review & Approval
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            // Completion tracking
            $table->decimal('completion_percentage', 5, 2)->default(0.00); // 0.00 - 100.00
            $table->boolean('is_complete')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['periode_akreditasi_id', 'butir_akreditasi_id']);
            $table->index('status');
            $table->index('pic_user_id');
            $table->index('is_complete');

            // Unique constraint: satu butir per periode
            $table->unique(['periode_akreditasi_id', 'butir_akreditasi_id'], 'unique_periode_butir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengisian_butirs');
    }
};
