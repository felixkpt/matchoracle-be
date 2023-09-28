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
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->references('id')->on('post_categories');
            $table->uuid('topic_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('content_short')->nullable();
            $table->longText('content');
            $table->string('image')->nullable();
            $table->unsignedInteger('priority_number')->default(9999);
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
        Schema::dropIfExists('posts');
    }
};
