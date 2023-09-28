<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->string('competition_id')->nullable();
            $table->string('country_id');
            $table->string('img')->nullable();
            $table->uuid('user_id');
            $table->integer('priority_no')->default(9999);
            $table->tinyInteger('status')->default(1);
            $table->dateTime('last_fetch');
            $table->dateTime('last_detailed_fetch');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
