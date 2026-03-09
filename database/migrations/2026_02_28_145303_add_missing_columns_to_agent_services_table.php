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
        Schema::table('agent_services', function (Blueprint $table) {
            // Add missing columns for CAC registration
            if (!Schema::hasColumn('agent_services', 'company_type')) {
                $table->string('company_type')->nullable()->after('company_name');
            }
            
            if (!Schema::hasColumn('agent_services', 'from_country')) {
                $table->string('from_country')->nullable()->after('country');
            }
            
            if (!Schema::hasColumn('agent_services', 'to_country')) {
                $table->string('to_country')->nullable()->after('from_country');
            }
            
            if (!Schema::hasColumn('agent_services', 'departure_date')) {
                $table->date('departure_date')->nullable()->after('to_country');
            }
            
            if (!Schema::hasColumn('agent_services', 'return_date')) {
                $table->date('return_date')->nullable()->after('departure_date');
            }
            
            if (!Schema::hasColumn('agent_services', 'trip_type')) {
                $table->string('trip_type')->nullable()->after('return_date');
            }
            
            if (!Schema::hasColumn('agent_services', 'visa_type')) {
                $table->string('visa_type')->nullable()->after('trip_type');
            }
            
            if (!Schema::hasColumn('agent_services', 'applicant_class')) {
                $table->string('applicant_class')->nullable()->after('visa_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_services', function (Blueprint $table) {
            $columns = [
                'company_type',
                'from_country',
                'to_country',
                'departure_date',
                'return_date',
                'trip_type',
                'visa_type',
                'applicant_class'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('agent_services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};