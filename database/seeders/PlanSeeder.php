<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::create([
            'name' => 'Plan Básico',
            'price' => 0.00,
            'limit_consultas' => 10,
            'duration_days' => 30,
            'information' => [
                'description' => 'Ideal para probar el sistema',
                'soporte' => 'Email'
            ]
        ]);

        // Plan Pro
        Plan::create([
            'name' => 'Plan Pro',
            'price' => 10.00,
            'limit_consultas' => 100,
            'duration_days' => 30,
            'information' => [
                'description' => 'Para usuarios frecuentes',
                'soporte' => 'WhatsApp'
            ]
        ]);
    }
}
