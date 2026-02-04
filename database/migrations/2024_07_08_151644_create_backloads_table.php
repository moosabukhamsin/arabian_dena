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
        Schema::create('backloads', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->nullable();
            $table->string('date')->nullable();
            $table->string('back_load_note')->nullable();
            $table->string('truck_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_mobile')->nullable();
            $table->string('driver_id_number')->nullable();
            $table->string('attachment')->nullable();
            $table->boolean('is_active')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backloads');
    }
};
