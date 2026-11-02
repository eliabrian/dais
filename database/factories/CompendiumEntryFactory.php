<?php

namespace Database\Factories;

use App\Models\CompendiumEntry;
use App\Models\System;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompendiumEntry>
 */
class CompendiumEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'system_id' => System::factory(),
            'type' => 'weapon',
            'name' => fake()->word(),
            'summary' => '1d8 Slashing, Versatile',
            'description' => fake()->sentence(),
            'data' => [
                'damage' => '1d8',
                'damage_type' => 'slashing',
                'ability' => 'str',
                'mastery' => 'Sap',
            ],
            'is_srd' => true,
        ];
    }
}
