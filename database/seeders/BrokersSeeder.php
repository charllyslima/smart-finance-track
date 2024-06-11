<?php

namespace Database\Seeders;

use App\Models\Broker;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrokersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Broker::create([
            'name' => 'Clear Corretora',
            'slug' => 'broker-clear',
            'created_at' => Carbon::now(),
        ]);

        Broker::create([
            'name' => 'Ágora Corretora',
            'slug' => 'broker-agora',
            'created_at' => Carbon::now(),
        ]);

        Broker::create([
            'name' => 'BTG Pactual Investimentos',
            'slug' => 'broker-btg',
            'created_at' => Carbon::now(),
        ]);
    }
}
