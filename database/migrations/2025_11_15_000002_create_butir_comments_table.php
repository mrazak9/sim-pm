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
        Schema::create('butir_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengisian_butir_id')
                ->constrained('pengisian_butirs')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('butir_comments')
                ->onDelete('cascade');
            $table->text('comment');
            $table->boolean('is_resolved')->default(false);
            $table->json('mentioned_users')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('pengisian_butir_id');
            $table->index('user_id');
            $table->index('parent_id');
            $table->index('is_resolved');
            $table->index('created_at');
        });

        // Table for tracking who is currently editing
        Schema::create('pengisian_butir_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengisian_butir_id')
                ->unique()
                ->constrained('pengisian_butirs')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->timestamp('locked_at');
            $table->timestamp('expires_at');

            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengisian_butir_locks');
        Schema::dropIfExists('butir_comments');
    }
};
