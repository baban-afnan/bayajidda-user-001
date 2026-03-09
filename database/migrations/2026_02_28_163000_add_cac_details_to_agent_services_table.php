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
            // Business address fields
            if (!Schema::hasColumn('agent_services', 'business_state')) {
                $table->string('business_state')->nullable()->after('state');
            }
            if (!Schema::hasColumn('agent_services', 'business_lga')) {
                $table->string('business_lga')->nullable()->after('business_state');
            }
            if (!Schema::hasColumn('agent_services', 'business_city')) {
                $table->string('business_city')->nullable()->after('business_lga');
            }
            if (!Schema::hasColumn('agent_services', 'business_house_number')) {
                $table->string('business_house_number')->nullable()->after('business_city');
            }
            if (!Schema::hasColumn('agent_services', 'business_street')) {
                $table->string('business_street')->nullable()->after('business_house_number');
            }
            if (!Schema::hasColumn('agent_services', 'business_description')) {
                $table->text('business_description')->nullable()->after('business_street');
            }

            // Director 2 fields
            if (!Schema::hasColumn('agent_services', 'director2_surname')) {
                $table->string('director2_surname')->nullable()->after('business_description');
            }
            if (!Schema::hasColumn('agent_services', 'director2_first_name')) {
                $table->string('director2_first_name')->nullable()->after('director2_surname');
            }
            if (!Schema::hasColumn('agent_services', 'director2_middle_name')) {
                $table->string('director2_middle_name')->nullable()->after('director2_first_name');
            }
            if (!Schema::hasColumn('agent_services', 'director2_phone')) {
                $table->string('director2_phone')->nullable()->after('director2_middle_name');
            }
            if (!Schema::hasColumn('agent_services', 'director2_gender')) {
                $table->string('director2_gender')->nullable()->after('director2_phone');
            }
            if (!Schema::hasColumn('agent_services', 'director2_dob')) {
                $table->date('director2_dob')->nullable()->after('director2_gender');
            }
            if (!Schema::hasColumn('agent_services', 'director2_email')) {
                $table->string('director2_email')->nullable()->after('director2_dob');
            }
            if (!Schema::hasColumn('agent_services', 'director2_address')) {
                $table->text('director2_address')->nullable()->after('director2_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_services', function (Blueprint $table) {
            $table->dropColumn([
                'business_state', 'business_lga', 'business_city', 'business_house_number', 'business_street', 'business_description',
                'director2_surname', 'director2_first_name', 'director2_middle_name', 'director2_phone', 'director2_gender', 'director2_dob', 'director2_email', 'director2_address'
            ]);
        });
    }
};
