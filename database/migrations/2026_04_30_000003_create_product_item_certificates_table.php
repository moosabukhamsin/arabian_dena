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
        Schema::create('product_item_certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_item_id');
            $table->string('certificate')->nullable();
            $table->timestamps();

            $table->index('product_item_id');
            $table->foreign('product_item_id')
                ->references('id')
                ->on('product_items')
                ->onDelete('cascade');
        });

        // Backfill from legacy single certificate field on product_items
        $rows = DB::table('product_items')
            ->select('id', 'certificate', 'created_at', 'updated_at')
            ->whereNotNull('certificate')
            ->get();

        foreach ($rows as $row) {
            DB::table('product_item_certificates')->insert([
                'product_item_id' => $row->id,
                'certificate' => $row->certificate,
                // Use updated_at as the best approximation of "latest upload time"
                'created_at' => $row->updated_at ?? $row->created_at ?? now(),
                'updated_at' => $row->updated_at ?? $row->created_at ?? now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_item_certificates');
    }
};

