<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_deletion_notices', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->index();
            $table->uuid('public_token');
            $table->string('customer_name', 180);
            $table->string('customer_phone', 12);
            $table->string('order_status', 20);
            $table->char('currency', 3)->default('USD');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('deletion_mode', 30)->index();
            $table->unsignedInteger('restored_quantity')->default(0);
            $table->json('items');
            $table->timestamp('submitted_at');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('recorded_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_deletion_notices');
    }
};
