<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_address', 255)->nullable()->after('customer_phone');
        });

        Schema::table('order_deletion_notices', function (Blueprint $table) {
            $table->string('customer_address', 255)->nullable()->after('customer_phone');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_address');
        });

        Schema::table('order_deletion_notices', function (Blueprint $table) {
            $table->dropColumn('customer_address');
        });
    }
};
