<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('temporary_tokens', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->uuid('uuid')->unique();
            $table->string('token', 32)->unique();
            $table->timestamp('expires_at');
            $table->unsignedBigInteger('user_id')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_tokens');
    }
};
