<?php

namespace Modules\RoadmapLRC\Database\Seeders;

use Illuminate\Database\Seeder;

class RoadmapLRCDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([ProdiFmRoadmapSeeder::class]);
    }
}
