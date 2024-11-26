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
        Schema::create('trackings', function (Blueprint $table) {
            $table->id();
            $table->string("ip");
            $table->timestamp("created_at");
            $table->string("url");
            $table->float("latitude")->nullable();
            $table->float("longitude")->nullable();
            $table->string("os")->nullable();
            $table->string("device")->nullable();
            $table->string("referer")->nullable();
            $table->string("language")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trackings');
    }
};
