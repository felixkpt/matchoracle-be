<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('files');
        Schema::create('model_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('model_instance_id')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->unsignedBigInteger('merged_from_id')->nullable();
            $table->string('name')->nullable();
            $table->string('unique_name')->nullable();
            $table->string('extension');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('update_id')->default(0)->nullable();
            $table->unsignedInteger('created_by')->default(0)->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
            $table->string('form_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_files');
    }
}
