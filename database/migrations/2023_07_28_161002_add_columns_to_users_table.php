<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->unsignedBigInteger('phone')->nullable();
            $table->tinyInteger('two_factor_valid')->default(0);
            $table->dateTime('last_login_date')->nullable();
            $table->dateTime('two_factor_expires_at')->nullable();
            $table->string('two_factor_code')->nullable();
            $table->string('api_token', 80)->nullable();
            $table->string('avatar')->nullable();
            $table->longText('session_id')->nullable();
            $table->unsignedTinyInteger('is_session_valid')->default(0);
            $table->unsignedInteger('allowed_session_no')->default(1);
            $table->unsignedTinyInteger('is_online')->default(0);
            $table->unsignedTinyInteger('two_factor_enabled')->default(1);
            $table->unsignedBigInteger('default_role_id')->default(0);
            $table->string('theme')->default('light');
            $table->unsignedBigInteger('status_id')->default(1);
            $table->unsignedBigInteger('user_id')->default(0)->nullable();
            
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('middle_name');
            $table->dropColumn('phone');
            $table->dropColumn('two_factor_valid');
            $table->dropColumn('last_login_date');
            $table->dropColumn('two_factor_expires_at');
            $table->dropColumn('two_factor_code');
            $table->dropColumn('api_token');
            $table->dropColumn('avatar');
            $table->dropColumn('session_id');
            $table->dropColumn('is_session_valid');
            $table->dropColumn('allowed_session_no');
            $table->dropColumn('is_online');
            $table->dropColumn('remember_token');
            $table->dropColumn('two_factor_enabled');
            $table->dropColumn('theme');
            $table->dropColumn('status_id');
            $table->dropColumn('user_id');
        });
    }
};
