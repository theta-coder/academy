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
        // Use raw SQL because changing ENUMs with Doctrine DBAL can be tricky or require it as a dependency.
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE fee_fine_rules MODIFY COLUMN fine_type ENUM('fixed', 'percentage', 'daily_fixed', 'daily_percentage') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // To reverse, we'd theoretically need to handle any rows that have the new values first,
        // but for a simple rollback we can just attempt to revert the definition.
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE fee_fine_rules MODIFY COLUMN fine_type ENUM('fixed', 'percentage') NOT NULL");
    }
};
