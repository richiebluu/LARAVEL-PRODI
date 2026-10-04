<?php

namespace Database\Seeders;

use App\Services\RankingService;
use Illuminate\Database\Seeder;

class RankingBobotSeeder extends Seeder
{
    public function run(): void
    {
        app(RankingService::class)->sinkronBobot();
    }
}
