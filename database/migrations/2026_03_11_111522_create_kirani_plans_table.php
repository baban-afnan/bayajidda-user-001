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
        Schema::create('kirani_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('plan_id');
            $table->timestamps();
        });

        // Insert the available Kirani Plans
        $plans = [
            ['name' => 'Kirani 444.00', 'price' => 444.00, 'plan_id' => 193],
            ['name' => 'Kirani 740.00', 'price' => 740.00, 'plan_id' => 319],
            ['name' => 'Kirani 962.00', 'price' => 962.00, 'plan_id' => 195],
            ['name' => 'Kirani 1184.00', 'price' => 1184.00, 'plan_id' => 196],
            ['name' => 'Kirani 1480.00', 'price' => 1480.00, 'plan_id' => 197],
            ['name' => 'Kirani 1776.00', 'price' => 1776.00, 'plan_id' => 198],
            ['name' => 'Kirani 1998.00', 'price' => 1998.00, 'plan_id' => 199],
            ['name' => 'Kirani 2220.00', 'price' => 2220.00, 'plan_id' => 200],
            ['name' => 'Kirani 2590.00', 'price' => 2590.00, 'plan_id' => 201],
            ['name' => 'Kirani 2960.00', 'price' => 2960.00, 'plan_id' => 202],
            ['name' => 'Kirani 3700.00', 'price' => 3700.00, 'plan_id' => 203],
            ['name' => 'Kirani 4440.00', 'price' => 4440.00, 'plan_id' => 204],
            ['name' => 'Kirani 5180.00', 'price' => 5180.00, 'plan_id' => 205],
            ['name' => 'Kirani 5920.00', 'price' => 5920.00, 'plan_id' => 206],
            ['name' => 'Kirani 6660.00', 'price' => 6660.00, 'plan_id' => 207],
            ['name' => 'Kirani 7400.00', 'price' => 7400.00, 'plan_id' => 208],
            ['name' => 'Kirani 8140.00', 'price' => 8140.00, 'plan_id' => 209],
            ['name' => 'Kirani 8880.00', 'price' => 8880.00, 'plan_id' => 210],
            ['name' => 'Kirani 9620.00', 'price' => 9620.00, 'plan_id' => 211],
            ['name' => 'Kirani 10360.00', 'price' => 10360.00, 'plan_id' => 212],
            ['name' => 'Kirani 11100.00', 'price' => 11100.00, 'plan_id' => 213],
            ['name' => 'Kirani 11840.00', 'price' => 11840.00, 'plan_id' => 214],
            ['name' => 'Kirani 12580.00', 'price' => 12580.00, 'plan_id' => 215],
            ['name' => 'Kirani 13320.00', 'price' => 13320.00, 'plan_id' => 216],
            ['name' => 'Kirani 14060.00', 'price' => 14060.00, 'plan_id' => 217],
            ['name' => 'Kirani 14800.00', 'price' => 14800.00, 'plan_id' => 218],
        ];

        foreach ($plans as &$plan) {
            $plan['created_at'] = now();
            $plan['updated_at'] = now();
        }

        \Illuminate\Support\Facades\DB::table('kirani_plans')->insert($plans);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kirani_plans');
    }
};
