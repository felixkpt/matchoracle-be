<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Set startingValue 1100
            DB::statement('ALTER SEQUENCE users_id_seq RESTART WITH 1100');

            // Add UUID
            $table->uuid('uuid')->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
            // Reset ID column if needed, or leave as is
        });
    }
};
