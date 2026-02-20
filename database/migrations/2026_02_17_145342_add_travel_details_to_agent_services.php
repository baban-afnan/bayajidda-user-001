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
            $table->date('departure_date')->nullable()->after('submission_date');
            $table->date('return_date')->nullable()->after('departure_date');
            $table->string('trip_type')->nullable()->after('return_date'); // one_way, round_trip
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_services', function (Blueprint $table) {
            $table->dropColumn(['departure_date', 'return_date', 'trip_type']);
        });
    }

};
