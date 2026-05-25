<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'categories',
        'products',
        'product_items',
        'orders',
        'backloads',
        'companies',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('approval_status')->default('approved');
                $table->foreignId('modified_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('authorized_by_id')->nullable()->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['modified_by_id']);
                $table->dropForeign(['authorized_by_id']);
                $table->dropColumn(['approval_status', 'modified_by_id', 'authorized_by_id']);
            });
        }
    }
};
