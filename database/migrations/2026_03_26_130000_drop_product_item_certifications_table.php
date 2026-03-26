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
        Schema::dropIfExists('product_item_certifications');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('product_item_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_item_id')->constrained('product_items')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }
};
