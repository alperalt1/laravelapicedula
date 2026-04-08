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
        $planes = [
            [
                'name' => 'Básico',
                'price' => 0.00,
                'limit_consultas' => 5,
                'duration_days' => 30,
                'information' => json_encode([
                    'features' => ['5 Consultas', 'Soporte vía Email'],
                    'popular' => false
                ])
            ],
            [
                'name' => 'Pro',
                'price' => 10.00,
                'limit_consultas' => 100,
                'duration_days' => 30,
                'information' => json_encode([
                    'features' => ['100 Consultas', 'Soporte WhatsApp', 'Exportar a Excel'],
                    'popular' => true
                ])
            ],
            [
                'name' => 'Empresarial',
                'price' => 45.00,
                'limit_consultas' => 500,
                'duration_days' => 365,
                'information' => json_encode([
                    'features' => ['500 Consultas', 'Prioridad alta', 'API Access'],
                    'popular' => false
                ])
            ],
        ];
        
        foreach ($planes as $plan) {
            Plan::create($plan);
        }
    }
}
