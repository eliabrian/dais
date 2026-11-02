<?php

use App\Models\Character;
use App\Models\CompendiumEntry;
use App\Models\System;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('characters index lists user characters', function () {
    $user = User::factory()->create();

    $system = System::create([
        'name' => 'D&D 5.5E (2024) SRD',
        'code' => 'dnd5e_2024',
    ]);

    $character = Character::create([
        'user_id' => $user->id,
        'system_id' => $system->id,
        'name' => 'Seraphina Vale',
        'sheet_data' => [
            'species' => 'Elf',
            'class' => 'Ranger',
            'level' => 3,
        ],
    ]);

    $response = $this->actingAs($user)->get(route('characters.index'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Characters/Index')
            ->has('characters', 1)
            ->where('characters.0.id', $character->id)
        );
});

test('user can create a dnd 2024 character and view sheet', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('characters.store'), [
        'name' => 'Thorne Ironfist',
        'species' => 'Dwarf',
        'class' => 'Fighter',
        'background' => 'Soldier',
        'level' => 2,
    ]);

    $character = Character::where('user_id', $user->id)->first();

    expect($character)->not->toBeNull();
    expect($character->sheet_data['species'])->toBe('Dwarf');
    expect($character->sheet_data['class'])->toBe('Fighter');
    expect($character->sheet_data['level'])->toBe(2);

    $response->assertRedirect(route('characters.show', $character));
});

test('user can create a character via the multi-step wizard with full sheet_data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('characters.store'), [
        'name' => 'Kaelen Dawnbringer',
        'species' => 'Elf (Wood Elf)',
        'class' => 'Ranger',
        'background' => 'Guide',
        'level' => 1,
        'sheet_data' => [
            'species' => 'Elf (Wood Elf)',
            'class' => 'Ranger',
            'background' => 'Guide',
            'origin_feat' => 'Magic Initiate (Druid)',
            'level' => 1,
            'speed' => 35,
            'armor_class' => 15,
            'hp' => ['current' => 12, 'max' => 12, 'temp' => 0],
            'abilities' => ['str' => 12, 'dex' => 16, 'con' => 14, 'int' => 10, 'wis' => 16, 'cha' => 8],
            'saving_throws' => ['str', 'dex'],
            'skills' => [
                'perception' => 'proficient',
                'stealth' => 'proficient',
                'survival' => 'proficient',
            ],
            'weapon_masteries' => ['Vex', 'Nick'],
            'features_traits' => 'Wanderer, Magic Initiate (Druid), Favored Enemy',
            'proficiencies' => 'Light Armor, Medium Armor, Shields, Simple & Martial Weapons',
        ],
    ]);

    $character = Character::where('user_id', $user->id)->first();

    expect($character)->not->toBeNull();
    expect($character->name)->toBe('Kaelen Dawnbringer');
    expect($character->sheet_data['origin_feat'])->toBe('Magic Initiate (Druid)');
    expect($character->sheet_data['speed'])->toBe(35);
    expect($character->sheet_data['abilities']['dex'])->toBe(16);
    expect($character->sheet_data['abilities']['wis'])->toBe(16);
    expect($character->sheet_data['skills']['stealth'])->toBe('proficient');
    expect($character->sheet_data['weapon_masteries'])->toContain('Vex');

    $response->assertRedirect(route('characters.show', $character));
});

test('user can update their character sheet data', function () {
    $user = User::factory()->create();

    $system = System::create([
        'name' => 'D&D 5.5E (2024) SRD',
        'code' => 'dnd5e_2024',
    ]);

    $character = Character::create([
        'user_id' => $user->id,
        'system_id' => $system->id,
        'name' => 'Lyra Moonwhisper',
        'sheet_data' => [
            'level' => 1,
            'abilities' => ['str' => 10, 'dex' => 16, 'con' => 14, 'int' => 12, 'wis' => 10, 'cha' => 14],
        ],
    ]);

    $response = $this->actingAs($user)->patch(route('characters.update', $character), [
        'name' => 'Lyra Moonwhisper',
        'sheet_data' => [
            'level' => 4,
            'abilities' => ['str' => 10, 'dex' => 18, 'con' => 14, 'int' => 12, 'wis' => 10, 'cha' => 14],
        ],
    ]);

    $response->assertRedirect();
    expect($character->fresh()->sheet_data['level'])->toBe(4);
    expect($character->fresh()->sheet_data['abilities']['dex'])->toBe(18);
});

test('character show view receives compendium entries for their system', function () {
    $user = User::factory()->create();

    $system = System::create([
        'name' => 'D&D 5.5E (2024) SRD',
        'code' => 'dnd5e_2024',
    ]);

    $otherSystem = System::create([
        'name' => 'Daggerheart',
        'code' => 'daggerheart',
    ]);

    $character = Character::create([
        'user_id' => $user->id,
        'system_id' => $system->id,
        'name' => 'Garrick Flameheart',
        'sheet_data' => ['level' => 1],
    ]);

    CompendiumEntry::create([
        'system_id' => $system->id,
        'type' => 'weapon',
        'name' => 'Longsword',
        'summary' => '1d8/1d10 Slashing (Sap)',
        'description' => 'A versatile martial blade.',
        'data' => ['damage' => '1d8', 'properties' => ['Versatile (1d10)'], 'mastery' => 'Sap'],
        'is_srd' => true,
    ]);

    CompendiumEntry::create([
        'system_id' => $otherSystem->id,
        'type' => 'weapon',
        'name' => 'Daggerheart Dual Blades',
        'summary' => 'Agility weapon',
        'description' => 'From another system',
        'data' => [],
        'is_srd' => true,
    ]);

    $response = $this->actingAs($user)->get(route('characters.show', $character));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Characters/Show')
            ->has('compendiumEntries', 1)
            ->where('compendiumEntries.0.name', 'Longsword')
        );
});
