<?php

namespace Database\Seeders;

use App\Models\System;
use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        System::updateOrCreate(
            ['code' => 'dnd5e_2024'],
            ['name' => 'D&D 5.5E (2024) SRD'],
        );
    }
}
