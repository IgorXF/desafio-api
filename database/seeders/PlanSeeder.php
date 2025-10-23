<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::query()->delete(); 

        Plan::create([
            'name' => 'Plano Básico',
            'price' => 50.00,
            'quotas' => 10,
            'storage_limit_gb' => 20
        ]);

        Plan::create([
            'name' => 'Plano Pro',
            'price' => 100.00,
            'quotas' => 50,
            'storage_limit_gb' => 100
        ]);
        
        Plan::create([
            'name' => 'Plano Mega',
            'price' => 200.00,
            'quotas' => 200,
            'storage_limit_gb' => 500
        ]);
    }
}