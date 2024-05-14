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
        Schema::create('betting_strategy_pro_cons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['advantage', 'disadvantage'])->default('advantage');

            $table->unsignedInteger('position')->default(9999);
            $table->unsignedBigInteger('status_id')->default(1);
            $table->unsignedBigInteger('user_id')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('betting_strategy_pro_cons');
    }
};
