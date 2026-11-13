<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Crm\ProductRecord;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Ada', 'Ben', 'Cam'] as $name) {
            User::query()->firstOrCreate(['name' => $name]);
        }

        foreach ([['Cable', 10], ['Lamp', 5]] as [$name, $unitPrice]) {
            ProductRecord::query()->firstOrCreate(
                ['name' => $name],
                ['unit_price' => $unitPrice],
            );
        }
    }
}
