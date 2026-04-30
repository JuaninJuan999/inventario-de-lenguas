<?php

namespace Database\Seeders;

use App\Services\ModulosMatrizSynchronizer;
use Illuminate\Database\Seeder;

class ModulosMatrizSeeder extends Seeder
{
    public function run(): void
    {
        $sync = app(ModulosMatrizSynchronizer::class);
        $sync->sync();
    }
}
