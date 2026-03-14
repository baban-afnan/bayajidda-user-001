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
            ALTER TABLE transactions 
            MODIFY COLUMN status ENUM(
                'completed',
                'failed',
                'pending',
                'query',
                'processing'
            ) DEFAULT 'pending'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE transactions 
            MODIFY COLUMN status ENUM(
                'completed',
                'failed',
                'pending',
                'query'
            ) DEFAULT 'pending'
        ");
    }
};
