<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FiAssetsSeeder::class, //B3
            FiAssetsInformationsSeeder::class, //B3
            FiAssetsDividendsSeeder::class, //CVM
            FiAssetsValuesSeeder::class //STATUS INVEST
        ]);
    }
}
