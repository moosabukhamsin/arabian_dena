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
            if (Schema::hasColumn('backloads', 'back_load_note')) {
                $table->dropColumn('back_load_note');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backloads', function (Blueprint $table) {
            if (!Schema::hasColumn('backloads', 'back_load_note')) {
                $table->string('back_load_note')->nullable()->after('date');
            }
        });
    }
};

