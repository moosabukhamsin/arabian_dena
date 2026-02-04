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
        Schema::create('company_price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('pricing_type', ['daily_monthly', 'daily_weekly_monthly'])->default('daily_weekly_monthly');
            $table->decimal('daily_price', 10, 2)->nullable();
            $table->decimal('weekly_price', 10, 2)->nullable();
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure unique combination of company and product
            $table->unique(['company_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_price_lists');
    }
};
