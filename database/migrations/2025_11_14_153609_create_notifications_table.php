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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // deadline_reminder, status_change, approval_request, etc.
            $table->string('notifiable_type'); // Polymorphic: PeriodeAkreditasi, PengisianButir, etc.
            $table->unsignedBigInteger('notifiable_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional metadata
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->string('action_url')->nullable(); // Link to related resource
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
