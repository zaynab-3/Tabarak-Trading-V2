<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('purchase_order_number', 80)->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('payment_method', 20)->nullable();
            $table->string('check_number', 80)->nullable();
        });

        Schema::table('order_deletion_notices', function (Blueprint $table) {
            $table->string('purchase_order_number', 80)->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('payment_method', 20)->nullable();
            $table->string('check_number', 80)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('order_deletion_notices', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_order_number',
                'billing_address',
                'shipping_address',
                'delivery_date',
                'payment_method',
                'check_number',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_order_number',
                'billing_address',
                'shipping_address',
                'delivery_date',
                'payment_method',
                'check_number',
            ]);
        });
    }
};
