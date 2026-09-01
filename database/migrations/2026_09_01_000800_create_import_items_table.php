<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $table->string('status', 20)->default('pending');
            $table->string('suggested_name')->nullable();
            $table->string('suggested_brand')->nullable();
            $table->string('suggested_category')->nullable();
            $table->string('suggested_weight')->nullable();
            $table->json('suggested_metadata')->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->json('warnings')->nullable();
            $table->json('provider_metadata')->nullable();
            $table->foreignId('approved_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->timestamps();

            $table->index(['import_batch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_items');
    }
};
