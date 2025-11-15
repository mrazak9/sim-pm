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
        Schema::create('pengisian_butir_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengisian_butir_id')->constrained('pengisian_butirs')->onDelete('cascade');
            $table->integer('version_number');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Snapshot of data at this version
            $table->longText('konten')->nullable();
            $table->text('konten_plain')->nullable();
            $table->json('files')->nullable();
            $table->string('status', 50)->default('draft');
            $table->text('notes')->nullable();
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->boolean('is_complete')->default(false);

            // Metadata about this version
            $table->string('change_type', 50)->nullable(); // created, updated, submitted, reviewed, approved, etc.
            $table->text('change_summary')->nullable(); // Brief description of changes
            $table->json('metadata')->nullable(); // Additional metadata

            $table->timestamps();

            // Indexes
            $table->index(['pengisian_butir_id', 'version_number']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengisian_butir_versions');
    }
};
