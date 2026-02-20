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
            $table->string('visa_type')->nullable()->after('trip_type');
            $table->string('applicant_class')->nullable()->after('visa_type'); // Adult, Child, Infant
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_services', function (Blueprint $table) {
            $table->dropColumn(['visa_type', 'applicant_class']);
        });
    }

};
