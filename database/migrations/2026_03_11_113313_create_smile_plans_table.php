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
        Schema::create('smile_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('plan_id');
            $table->timestamps();
        });

        // Insert the available Smile Plans
        $plans = [
            ['name' => 'Smile mini 1GB + 1GB Streaming', 'price' => 450.00, 'plan_id' => 252],
            ['name' => 'Smile Mini 2.5GB+1GB Steamin', 'price' => 750.00, 'plan_id' => 282],
            ['name' => 'Smile Mini 1GB Streamin', 'price' => 750.00, 'plan_id' => 284],
            ['name' => 'SmileVoice ONLY 65', 'price' => 900.00, 'plan_id' => 253],
            ['name' => 'Smile Mini 2GB+4GB Steamin', 'price' => 1500.00, 'plan_id' => 294],
            ['name' => 'Smile Mini 1.5GB+3GB Steamin', 'price' => 1550.00, 'plan_id' => 285],
            ['name' => 'Smile Mini 5GB+1GB Steamin', 'price' => 1650.00, 'plan_id' => 292],
            ['name' => 'Smile Midi 2GB+4GB Steamin', 'price' => 1800.00, 'plan_id' => 286],
            ['name' => 'SmileVoice ONLY 135', 'price' => 1850.00, 'plan_id' => 254],
            ['name' => 'Smile Mini 3GB+5GB Steamin', 'price' => 2000.00, 'plan_id' => 295],
            ['name' => 'Smile Mini 6GB+2GB Steamin', 'price' => 2300.00, 'plan_id' => 293],
            ['name' => 'Smile Voice ONLY 150', 'price' => 2700.00, 'plan_id' => 283],
            ['name' => 'Smile Mini 5GB+1GB Steamin', 'price' => 3000.00, 'plan_id' => 287],
            ['name' => 'Smile Mini 6GB+5GB Steamin', 'price' => 3000.00, 'plan_id' => 296],
            ['name' => 'Smile Mini 8GB+5GB Steamin', 'price' => 3500.00, 'plan_id' => 297],
            ['name' => 'SmileVoice ONLY 175', 'price' => 3600.00, 'plan_id' => 258],
            ['name' => 'Smile Mini 6GB+5GB Steamin', 'price' => 3800.00, 'plan_id' => 288],
            ['name' => 'Smile Mini 10GB+5GB Steamin', 'price' => 4600.00, 'plan_id' => 289],
            ['name' => 'Smile Mini 13GB+5GB Steamin', 'price' => 5000.00, 'plan_id' => 298],
            ['name' => 'SmileVoice ONLY 430', 'price' => 5700.00, 'plan_id' => 255],
            ['name' => 'Smile Mini 18GB+5GB Steamin', 'price' => 6000.00, 'plan_id' => 299],
            ['name' => 'Smile Mini 20GB+5GB Steamin', 'price' => 7000.00, 'plan_id' => 290],
            ['name' => 'SmileVoice ONLY 450', 'price' => 7200.00, 'plan_id' => 256],
            ['name' => 'SmileVoice ONLY 500', 'price' => 9000.00, 'plan_id' => 257],
            ['name' => 'Smile Mini 40GB+5GB Steamin', 'price' => 12500.00, 'plan_id' => 300],
            ['name' => 'Smile Annual 20GB', 'price' => 14000.00, 'plan_id' => 309],
            ['name' => 'Smile Midi 65GB+5GB Steamin', 'price' => 15000.00, 'plan_id' => 301],
            ['name' => 'Smile Mini 40GB+5GB Steamin', 'price' => 15500.00, 'plan_id' => 291],
            ['name' => 'Smile Midi 100GB+5GB Steamin', 'price' => 20000.00, 'plan_id' => 302],
            ['name' => 'Smile Midi 130GB+3GB Steamin', 'price' => 25000.00, 'plan_id' => 303],
            ['name' => 'Smile jumbo 90GB', 'price' => 25000.00, 'plan_id' => 305],
            ['name' => 'Smile Annual 50GB', 'price' => 29000.00, 'plan_id' => 310],
            ['name' => 'Smile Midi 210GB+3GB Steamin', 'price' => 40000.00, 'plan_id' => 304],
            ['name' => 'Smile Annual 120GB', 'price' => 49500.00, 'plan_id' => 311],
            ['name' => 'Smile jumbo 300GB+3GB Steamin', 'price' => 50000.00, 'plan_id' => 306],
            ['name' => 'Smile jumbo 350GB', 'price' => 60000.00, 'plan_id' => 307],
            ['name' => 'Smile jumbo 500GB', 'price' => 77000.00, 'plan_id' => 308],
            ['name' => 'Smile Annual 250GB', 'price' => 77000.00, 'plan_id' => 312],
            ['name' => 'Smile Annual 450GB', 'price' => 107000.00, 'plan_id' => 313],
            ['name' => 'Smile Annual 700GB', 'price' => 154000.00, 'plan_id' => 314],
            ['name' => 'Smile Annual 1TB', 'price' => 180000.00, 'plan_id' => 315],
        ];

        foreach ($plans as &$plan) {
            $plan['created_at'] = now();
            $plan['updated_at'] = now();
        }

        \Illuminate\Support\Facades\DB::table('smile_plans')->insert($plans);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smile_plans');
    }
};
