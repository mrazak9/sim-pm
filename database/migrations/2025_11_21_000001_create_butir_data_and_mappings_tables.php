<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create butir_column_mappings table
        Schema::create('butir_column_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('butir_akreditasi_id')
                ->constrained('butir_akreditasis')
                ->onDelete('cascade');

            // Field information
            $table->string('field_name', 100);
            $table->string('field_label', 200);
            $table->string('column_name', 10);

            // Type & config
            $table->string('field_type', 50);
            $table->jsonb('field_config')->nullable();

            // Display
            $table->integer('display_order');
            $table->string('width', 20)->nullable();

            // Validation
            $table->boolean('is_required')->default(false);

            // Metadata
            $table->text('help_text')->nullable();
            $table->string('placeholder')->nullable();

            $table->timestamps();

            // Unique constraints
            $table->unique(['butir_akreditasi_id', 'field_name']);
            $table->unique(['butir_akreditasi_id', 'column_name']);
            $table->unique(['butir_akreditasi_id', 'display_order']);

            // Indexes
            $table->index('field_name');
            $table->index('column_name');
        });

        // 2. Create butir_data table
        Schema::create('butir_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengisian_butir_id')
                ->constrained('pengisian_butirs')
                ->onDelete('cascade');
            $table->integer('row_number')->default(1);

            // 30 columns for data
            for ($i = 1; $i <= 30; $i++) {
                $table->text("c{$i}")->nullable();
            }

            // Metadata for documents, notes, etc
            $table->jsonb('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('pengisian_butir_id');
            $table->index('row_number');

            // Index first 10 columns (most frequently queried)
            for ($i = 1; $i <= 10; $i++) {
                $table->index("c{$i}");
            }
        });

        // GIN index for metadata
        DB::statement('CREATE INDEX idx_butir_data_metadata ON butir_data USING GIN (metadata)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('butir_data');
        Schema::dropIfExists('butir_column_mappings');
    }
};
