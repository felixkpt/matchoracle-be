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
        // stages migration
        Schema::create('stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('competition_id')->default(0);
            $table->string('name');
            $table->uuid('status_id')->default(0);
            $table->uuid('user_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
