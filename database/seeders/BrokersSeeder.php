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
            'created_at' => Carbon::now(),
        ]);

        Broker::create([
            'name' => 'Ágora Corretora',
            'created_at' => Carbon::now(),
        ]);
    }
}
