<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'default_role_id'))
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('default_role_id')->default(0);
            });
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'default_role_id'))
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('default_role_id');
            });
    }
};
