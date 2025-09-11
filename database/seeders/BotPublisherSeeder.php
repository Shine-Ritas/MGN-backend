<?php

namespace Database\Seeders;

use App\Models\BotPublisher;
use Illuminate\Database\Seeder;

class BotPublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        BotPublisher::factory()
            ->count(5)
            ->create();
    }
}
