<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('original_size')->nullable()->after('size');
            $table->timestamp('optimized_at')->nullable()->after('checksum')->index();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['optimized_at']);
            $table->dropColumn(['original_size', 'optimized_at']);
        });
    }
};
