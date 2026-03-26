<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            UPDATE backloads
            SET `date` = CASE
                WHEN `date` REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN DATE_FORMAT(STR_TO_DATE(`date`, '%Y-%m-%d'), '%Y-%m-%d')
                WHEN `date` REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' THEN DATE_FORMAT(STR_TO_DATE(`date`, '%d/%m/%Y'), '%Y-%m-%d')
                ELSE NULL
            END
        ");

        DB::statement("ALTER TABLE backloads MODIFY COLUMN `date` DATE NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE backloads MODIFY COLUMN `date` VARCHAR(255) NULL");
    }
};
