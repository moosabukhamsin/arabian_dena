<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_items', function (Blueprint $table) {
            $table->string('product_item_code')->nullable()->after('id');
        });

        // Backfill existing records: default product_item_code = real id
        DB::table('product_items')
            ->whereNull('product_item_code')
            ->update(['product_item_code' => DB::raw('id')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_items', function (Blueprint $table) {
            $table->dropColumn('product_item_code');
        });
    }
};

