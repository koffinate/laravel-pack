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
        Schema::create('cached', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key');
            $table->string('store')->nullable();
            $table->jsonb('tags')->nullable();
            $table->unsignedInteger('renew')->default(1);
            $table->unsignedInteger('expiration')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('created_at');

            $table->index(['key', 'expires_at'], 'cached_main_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cached');
    }
};
