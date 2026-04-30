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
        Schema::table('backloads', function (Blueprint $table) {
            $table->string('backload_number')->nullable()->after('id');
            $table->string('address')->nullable()->after('date');
            $table->unique('backload_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backloads', function (Blueprint $table) {
            $table->dropUnique(['backload_number']);
            $table->dropColumn(['backload_number', 'address']);
        });
    }
};

