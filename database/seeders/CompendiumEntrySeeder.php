<?php

namespace Database\Seeders;

use App\Models\CompendiumEntry;
use App\Models\System;
use Illuminate\Database\Seeder;

class CompendiumEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $system = System::where('code', 'dnd5e_2024')->first();

        if (! $system) {
            return;
        }

        // 1. Weapons (2024 SRD with Weapon Masteries)
        $weapons = [
            [
                'name' => 'Dagger',
                'summary' => '1d4 Piercing · Finesse, Light, Thrown (20/60) · Nick',
                'description' => 'A small, concealable blade. With the Nick property, you can make the extra light weapon attack as part of your Attack action rather than a Bonus Action.',
                'data' => [
                    'category' => 'Simple Melee',
                    'damage' => '1d4',
                    'damage_type' => 'piercing',
                    'ability' => 'dex_or_str',
                    'mastery' => 'Nick',
                    'properties' => ['Finesse', 'Light', 'Thrown (range 20/60)'],
                    'range' => '20/60 ft',
                    'weight' => '1 lb',
                ],
            ],
            [
                'name' => 'Shortsword',
                'summary' => '1d6 Piercing · Finesse, Light · Vex',
                'description' => 'A quick thrusting blade. The Vex property grants Advantage on your next attack roll against the target before the end of your next turn when you hit.',
                'data' => [
                    'category' => 'Martial Melee',
                    'damage' => '1d6',
                    'damage_type' => 'piercing',
                    'ability' => 'dex_or_str',
                    'mastery' => 'Vex',
                    'properties' => ['Finesse', 'Light'],
                    'range' => 'Melee',
                    'weight' => '2 lb',
                ],
            ],
            [
                'name' => 'Scimitar',
                'summary' => '1d6 Slashing · Finesse, Light · Nick',
                'description' => 'A curved slashing blade favored by dervishes and agile fighters.',
                'data' => [
                    'category' => 'Martial Melee',
                    'damage' => '1d6',
                    'damage_type' => 'slashing',
                    'ability' => 'dex_or_str',
                    'mastery' => 'Nick',
                    'properties' => ['Finesse', 'Light'],
                    'range' => 'Melee',
                    'weight' => '3 lb',
                ],
            ],
            [
                'name' => 'Rapier',
                'summary' => '1d8 Piercing · Finesse · Vex',
                'description' => 'A duelist sword designed for precision lunges.',
                'data' => [
                    'category' => 'Martial Melee',
                    'damage' => '1d8',
                    'damage_type' => 'piercing',
                    'ability' => 'dex_or_str',
                    'mastery' => 'Vex',
                    'properties' => ['Finesse'],
                    'range' => 'Melee',
                    'weight' => '2 lb',
                ],
            ],
            [
                'name' => 'Handaxe',
                'summary' => '1d6 Slashing · Light, Thrown (20/60) · Vex',
                'description' => 'A balanced hand axe suitable for close combat or throwing.',
                'data' => [
                    'category' => 'Simple Melee',
                    'damage' => '1d6',
                    'damage_type' => 'slashing',
                    'ability' => 'str',
                    'mastery' => 'Vex',
                    'properties' => ['Light', 'Thrown (range 20/60)'],
                    'range' => '20/60 ft',
                    'weight' => '2 lb',
                ],
            ],
            [
                'name' => 'Javelin',
                'summary' => '1d6 Piercing · Thrown (30/120) · Slow',
                'description' => 'A light spear designed for throwing. The Slow property reduces the target’s speed by 10 ft until the start of your next turn.',
                'data' => [
                    'category' => 'Simple Melee',
                    'damage' => '1d6',
                    'damage_type' => 'piercing',
                    'ability' => 'str',
                    'mastery' => 'Slow',
                    'properties' => ['Thrown (range 30/120)'],
                    'range' => '30/120 ft',
                    'weight' => '2 lb',
                ],
            ],
            [
                'name' => 'Mace',
                'summary' => '1d6 Bludgeoning · Slow',
                'description' => 'A heavy metal-headed club favored by clerics.',
                'data' => [
                    'category' => 'Simple Melee',
                    'damage' => '1d6',
                    'damage_type' => 'bludgeoning',
                    'ability' => 'str',
                    'mastery' => 'Slow',
                    'properties' => [],
                    'range' => 'Melee',
                    'weight' => '4 lb',
                ],
            ],
            [
                'name' => 'Quarterstaff',
                'summary' => '1d6 Bludgeoning · Versatile (1d8) · Topple',
                'description' => 'A simple wooden staff. The Topple property forces a Constitution saving throw (DC 8 + Prof + Ability Mod) or knocks the target Prone.',
                'data' => [
                    'category' => 'Simple Melee',
                    'damage' => '1d6',
                    'damage_type' => 'bludgeoning',
                    'ability' => 'str',
                    'mastery' => 'Topple',
                    'properties' => ['Versatile (1d8)'],
                    'range' => 'Melee',
                    'weight' => '4 lb',
                ],
            ],
            [
                'name' => 'Spear',
                'summary' => '1d6 Piercing · Thrown (20/60), Versatile (1d8) · Sap',
                'description' => 'A versatile polearm. The Sap property gives the target Disadvantage on its next attack roll before the start of your next turn.',
                'data' => [
                    'category' => 'Simple Melee',
                    'damage' => '1d6',
                    'damage_type' => 'piercing',
                    'ability' => 'str',
                    'mastery' => 'Sap',
                    'properties' => ['Thrown (range 20/60)', 'Versatile (1d8)'],
                    'range' => '20/60 ft',
                    'weight' => '3 lb',
                ],
            ],
            [
                'name' => 'Shortbow',
                'summary' => '1d6 Piercing · Ammunition (80/320), Two-Handed · Vex',
                'description' => 'A compact bow designed for skirmishing.',
                'data' => [
                    'category' => 'Simple Ranged',
                    'damage' => '1d6',
                    'damage_type' => 'piercing',
                    'ability' => 'dex',
                    'mastery' => 'Vex',
                    'properties' => ['Ammunition (range 80/320)', 'Two-Handed'],
                    'range' => '80/320 ft',
                    'weight' => '2 lb',
                ],
            ],
            [
                'name' => 'Light Crossbow',
                'summary' => '1d8 Piercing · Ammunition (80/320), Loading, Two-Handed · Slow',
                'description' => 'A mechanical bow that shoots bolts with solid stopping power.',
                'data' => [
                    'category' => 'Simple Ranged',
                    'damage' => '1d8',
                    'damage_type' => 'piercing',
                    'ability' => 'dex',
                    'mastery' => 'Slow',
                    'properties' => ['Ammunition (range 80/320)', 'Loading', 'Two-Handed'],
                    'range' => '80/320 ft',
                    'weight' => '5 lb',
                ],
            ],
            [
                'name' => 'Longsword',
                'summary' => '1d8 Slashing · Versatile (1d10) · Sap',
                'description' => 'The quintessential blade of knights and warriors. The Sap property imposes Disadvantage on the target’s next attack.',
                'data' => [
                    'category' => 'Martial Melee',
                    'damage' => '1d8',
                    'damage_type' => 'slashing',
                    'ability' => 'str',
                    'mastery' => 'Sap',
                    'properties' => ['Versatile (1d10)'],
                    'range' => 'Melee',
                    'weight' => '3 lb',
                ],
            ],
            [
                'name' => 'Battleaxe',
                'summary' => '1d8 Slashing · Versatile (1d10) · Topple',
                'description' => 'A broad-headed axe capable of sweeping foes off their feet.',
                'data' => [
                    'category' => 'Martial Melee',
                    'damage' => '1d8',
                    'damage_type' => 'slashing',
                    'ability' => 'str',
                    'mastery' => 'Topple',
                    'properties' => ['Versatile (1d10)'],
                    'range' => 'Melee',
                    'weight' => '4 lb',
                ],
            ],
            [
                'name' => 'Warhammer',
                'summary' => '1d8 Bludgeoning · Versatile (1d10) · Push',
                'description' => 'A hefty hammer. The Push property knocks a target up to 10 ft straight back on a hit (no save).',
                'data' => [
                    'category' => 'Martial Melee',
                    'damage' => '1d8',
                    'damage_type' => 'bludgeoning',
                    'ability' => 'str',
                    'mastery' => 'Push',
                    'properties' => ['Versatile (1d10)'],
                    'range' => 'Melee',
                    'weight' => '2 lb',
                ],
            ],
            [
                'name' => 'Greatsword',
                'summary' => '2d6 Slashing · Heavy, Two-Handed · Graze',
                'description' => 'A massive two-handed sword. The Graze property deals your Ability modifier in damage even when you miss an attack roll!',
                'data' => [
                    'category' => 'Martial Melee',
                    'damage' => '2d6',
                    'damage_type' => 'slashing',
                    'ability' => 'str',
                    'mastery' => 'Graze',
                    'properties' => ['Heavy', 'Two-Handed'],
                    'range' => 'Melee',
                    'weight' => '6 lb',
                ],
            ],
            [
                'name' => 'Greataxe',
                'summary' => '1d12 Slashing · Heavy, Two-Handed · Cleave',
                'description' => 'A barbarian favorite. The Cleave property lets you make a second attack roll against an adjacent creature if you hit the first.',
                'data' => [
                    'category' => 'Martial Melee',
                    'damage' => '1d12',
                    'damage_type' => 'slashing',
                    'ability' => 'str',
                    'mastery' => 'Cleave',
                    'properties' => ['Heavy', 'Two-Handed'],
                    'range' => 'Melee',
                    'weight' => '7 lb',
                ],
            ],
            [
                'name' => 'Halberd',
                'summary' => '1d10 Slashing · Heavy, Reach, Two-Handed · Cleave',
                'description' => 'An axe blade mounted on a long pole with 10 ft reach.',
                'data' => [
                    'category' => 'Martial Melee',
                    'damage' => '1d10',
                    'damage_type' => 'slashing',
                    'ability' => 'str',
                    'mastery' => 'Cleave',
                    'properties' => ['Heavy', 'Reach', 'Two-Handed'],
                    'range' => '10 ft Reach',
                    'weight' => '6 lb',
                ],
            ],
            [
                'name' => 'Longbow',
                'summary' => '1d8 Piercing · Ammunition (150/600), Heavy, Two-Handed · Slow',
                'description' => 'A tall recurve bow with massive range.',
                'data' => [
                    'category' => 'Martial Ranged',
                    'damage' => '1d8',
                    'damage_type' => 'piercing',
                    'ability' => 'dex',
                    'mastery' => 'Slow',
                    'properties' => ['Ammunition (range 150/600)', 'Heavy', 'Two-Handed'],
                    'range' => '150/600 ft',
                    'weight' => '2 lb',
                ],
            ],
            [
                'name' => 'Heavy Crossbow',
                'summary' => '1d10 Piercing · Ammunition (100/400), Heavy, Loading, Two-Handed · Push',
                'description' => 'A high-tension crossbow capable of blasting opponents backward.',
                'data' => [
                    'category' => 'Martial Ranged',
                    'damage' => '1d10',
                    'damage_type' => 'piercing',
                    'ability' => 'dex',
                    'mastery' => 'Push',
                    'properties' => ['Ammunition (range 100/400)', 'Heavy', 'Loading', 'Two-Handed'],
                    'range' => '100/400 ft',
                    'weight' => '18 lb',
                ],
            ],
        ];

        foreach ($weapons as $item) {
            CompendiumEntry::updateOrCreate(
                ['system_id' => $system->id, 'type' => 'weapon', 'name' => $item['name']],
                [
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'data' => $item['data'],
                    'is_srd' => true,
                ],
            );
        }

        // 2. Armor & Shields (2024 SRD)
        $armors = [
            [
                'name' => 'Padded Armor',
                'summary' => 'Light · AC 11 + Dex · Disadvantage on Stealth',
                'description' => 'Quilted layers of cloth and batting.',
                'data' => ['category' => 'Light', 'ac_base' => 11, 'dex_bonus' => 'full', 'stealth_disadv' => true, 'str_req' => null, 'weight' => '8 lb'],
            ],
            [
                'name' => 'Leather Armor',
                'summary' => 'Light · AC 11 + Dex',
                'description' => 'Cured, stiffened breastpiece with supple shoulder and leg guards.',
                'data' => ['category' => 'Light', 'ac_base' => 11, 'dex_bonus' => 'full', 'stealth_disadv' => false, 'str_req' => null, 'weight' => '10 lb'],
            ],
            [
                'name' => 'Studded Leather',
                'summary' => 'Light · AC 12 + Dex',
                'description' => 'Tough but flexible leather reinforced with close-set rivets.',
                'data' => ['category' => 'Light', 'ac_base' => 12, 'dex_bonus' => 'full', 'stealth_disadv' => false, 'str_req' => null, 'weight' => '13 lb'],
            ],
            [
                'name' => 'Hide Armor',
                'summary' => 'Medium · AC 12 + Dex (max 2)',
                'description' => 'Crude armor made from thick furs and pelts.',
                'data' => ['category' => 'Medium', 'ac_base' => 12, 'dex_bonus' => 'max_2', 'stealth_disadv' => false, 'str_req' => null, 'weight' => '12 lb'],
            ],
            [
                'name' => 'Chain Shirt',
                'summary' => 'Medium · AC 13 + Dex (max 2)',
                'description' => 'Made of interlocking metal rings worn between layers of cloth or leather.',
                'data' => ['category' => 'Medium', 'ac_base' => 13, 'dex_bonus' => 'max_2', 'stealth_disadv' => false, 'str_req' => null, 'weight' => '20 lb'],
            ],
            [
                'name' => 'Scale Mail',
                'summary' => 'Medium · AC 14 + Dex (max 2) · Disadvantage on Stealth',
                'description' => 'Overlapping pieces of scale metal affixed to a backing of leather.',
                'data' => ['category' => 'Medium', 'ac_base' => 14, 'dex_bonus' => 'max_2', 'stealth_disadv' => true, 'str_req' => null, 'weight' => '45 lb'],
            ],
            [
                'name' => 'Breastplate',
                'summary' => 'Medium · AC 14 + Dex (max 2)',
                'description' => 'Fitted metal chest piece with leather fittings, offering protection without stealth penalties.',
                'data' => ['category' => 'Medium', 'ac_base' => 14, 'dex_bonus' => 'max_2', 'stealth_disadv' => false, 'str_req' => null, 'weight' => '20 lb'],
            ],
            [
                'name' => 'Half Plate',
                'summary' => 'Medium · AC 15 + Dex (max 2) · Disadvantage on Stealth',
                'description' => 'Shaped metal plates covering most of the wearer’s body.',
                'data' => ['category' => 'Medium', 'ac_base' => 15, 'dex_bonus' => 'max_2', 'stealth_disadv' => true, 'str_req' => null, 'weight' => '40 lb'],
            ],
            [
                'name' => 'Ring Mail',
                'summary' => 'Heavy · AC 14 · Disadvantage on Stealth',
                'description' => 'Leather armor with heavy rings sewn into it.',
                'data' => ['category' => 'Heavy', 'ac_base' => 14, 'dex_bonus' => 'none', 'stealth_disadv' => true, 'str_req' => null, 'weight' => '40 lb'],
            ],
            [
                'name' => 'Chain Mail',
                'summary' => 'Heavy · AC 16 · Str 13 req · Disadvantage on Stealth',
                'description' => 'Interlocking metal rings spanning the entire body.',
                'data' => ['category' => 'Heavy', 'ac_base' => 16, 'dex_bonus' => 'none', 'stealth_disadv' => true, 'str_req' => 13, 'weight' => '55 lb'],
            ],
            [
                'name' => 'Splint Armor',
                'summary' => 'Heavy · AC 17 · Str 15 req · Disadvantage on Stealth',
                'description' => 'Vertical strips of metal riveted to a backing of leather.',
                'data' => ['category' => 'Heavy', 'ac_base' => 17, 'dex_bonus' => 'none', 'stealth_disadv' => true, 'str_req' => 15, 'weight' => '60 lb'],
            ],
            [
                'name' => 'Plate Armor',
                'summary' => 'Heavy · AC 18 · Str 15 req · Disadvantage on Stealth',
                'description' => 'Interlocking shaped plates of steel covering the entire body.',
                'data' => ['category' => 'Heavy', 'ac_base' => 18, 'dex_bonus' => 'none', 'stealth_disadv' => true, 'str_req' => 15, 'weight' => '65 lb'],
            ],
            [
                'name' => 'Shield',
                'summary' => 'Shield · +2 AC',
                'description' => 'Wielded in one hand, grants a +2 bonus to Armor Class.',
                'data' => ['category' => 'Shield', 'ac_bonus' => 2, 'stealth_disadv' => false, 'str_req' => null, 'weight' => '6 lb'],
            ],
        ];

        foreach ($armors as $item) {
            CompendiumEntry::updateOrCreate(
                ['system_id' => $system->id, 'type' => 'armor', 'name' => $item['name']],
                [
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'data' => $item['data'],
                    'is_srd' => true,
                ],
            );
        }

        // 3. Spells (2024 SRD Cantrips & 1st Level)
        $spells = [
            [
                'name' => 'Fire Bolt',
                'summary' => 'Cantrip · Evocation · 1 Action · 120 ft · 1d10 Fire',
                'description' => 'You hurl a mote of fire at a creature or object within range. Make a ranged spell attack. On a hit, the target takes 1d10 Fire damage. A flammable object hit by this spell ignites if it isn’t being worn or carried.',
                'data' => [
                    'level' => 0,
                    'school' => 'Evocation',
                    'casting_time' => '1 Action',
                    'range' => '120 ft',
                    'components' => 'V, S',
                    'duration' => 'Instantaneous',
                    'attack_type' => 'ranged_spell',
                    'damage' => '1d10',
                    'damage_type' => 'fire',
                ],
            ],
            [
                'name' => 'Eldritch Blast',
                'summary' => 'Cantrip · Evocation · 1 Action · 120 ft · 1d10 Force',
                'description' => 'A beam of crackling energy streaks toward a creature within range. Make a ranged spell attack. On a hit, the target takes 1d10 Force damage.',
                'data' => [
                    'level' => 0,
                    'school' => 'Evocation',
                    'casting_time' => '1 Action',
                    'range' => '120 ft',
                    'components' => 'V, S',
                    'duration' => 'Instantaneous',
                    'attack_type' => 'ranged_spell',
                    'damage' => '1d10',
                    'damage_type' => 'force',
                ],
            ],
            [
                'name' => 'Ray of Frost',
                'summary' => 'Cantrip · Evocation · 1 Action · 60 ft · 1d8 Cold · -10 ft Speed',
                'description' => 'A frigid beam of blue-white light streaks toward a creature. On a hit, it takes 1d8 Cold damage, and its speed is reduced by 10 ft until the start of your next turn.',
                'data' => [
                    'level' => 0,
                    'school' => 'Evocation',
                    'casting_time' => '1 Action',
                    'range' => '60 ft',
                    'components' => 'V, S',
                    'duration' => 'Instantaneous',
                    'attack_type' => 'ranged_spell',
                    'damage' => '1d8',
                    'damage_type' => 'cold',
                ],
            ],
            [
                'name' => 'Sacred Flame',
                'summary' => 'Cantrip · Evocation · 1 Action · 60 ft · Dex Save · 1d8 Radiant',
                'description' => 'Flame-like radiance descends on a creature you can see. The target must succeed on a Dexterity saving throw or take 1d8 Radiant damage. The target gains no benefit from cover for this save.',
                'data' => [
                    'level' => 0,
                    'school' => 'Evocation',
                    'casting_time' => '1 Action',
                    'range' => '60 ft',
                    'components' => 'V, S',
                    'duration' => 'Instantaneous',
                    'save_ability' => 'dex',
                    'damage' => '1d8',
                    'damage_type' => 'radiant',
                ],
            ],
            [
                'name' => 'Toll the Dead',
                'summary' => 'Cantrip · Necromancy · 1 Action · 60 ft · Wis Save · 1d8 / 1d12 Necrotic',
                'description' => 'You point at one creature within range. The target must succeed on a Wisdom saving throw or take 1d8 Necrotic damage. If the target is missing any hit points, it takes 1d12 Necrotic damage instead.',
                'data' => [
                    'level' => 0,
                    'school' => 'Necromancy',
                    'casting_time' => '1 Action',
                    'range' => '60 ft',
                    'components' => 'V, S',
                    'duration' => 'Instantaneous',
                    'save_ability' => 'wis',
                    'damage' => '1d8 or 1d12',
                    'damage_type' => 'necrotic',
                ],
            ],
            [
                'name' => 'Mage Hand',
                'summary' => 'Cantrip · Conjuration · 1 Action · 30 ft · 1 min',
                'description' => 'A spectral, floating hand appears at a point you choose. You can use the hand to manipulate an object, open an unlocked door or container, stow or retrieve an item from an open container, or pour the contents out of a vial.',
                'data' => [
                    'level' => 0,
                    'school' => 'Conjuration',
                    'casting_time' => '1 Action',
                    'range' => '30 ft',
                    'components' => 'V, S',
                    'duration' => '1 minute',
                ],
            ],
            [
                'name' => 'Guidance',
                'summary' => 'Cantrip · Divination · 1 Action · Touch · Concentration 1 min',
                'description' => 'You touch one willing creature. Once before the spell ends, the target can roll a d4 and add the number rolled to one ability check of its choice.',
                'data' => [
                    'level' => 0,
                    'school' => 'Divination',
                    'casting_time' => '1 Action',
                    'range' => 'Touch',
                    'components' => 'V, S',
                    'duration' => 'Concentration, up to 1 minute',
                ],
            ],
            [
                'name' => 'Vicious Mockery',
                'summary' => 'Cantrip · Enchantment · 1 Action · 60 ft · Wis Save · 1d4 Psychic · Disadvantage',
                'description' => 'You unleash a string of insults. If the target fails a Wisdom saving throw, it takes 1d4 Psychic damage and has Disadvantage on the next attack roll it makes before the end of its next turn.',
                'data' => [
                    'level' => 0,
                    'school' => 'Enchantment',
                    'casting_time' => '1 Action',
                    'range' => '60 ft',
                    'components' => 'V',
                    'duration' => 'Instantaneous',
                    'save_ability' => 'wis',
                    'damage' => '1d4',
                    'damage_type' => 'psychic',
                ],
            ],
            [
                'name' => 'Magic Missile',
                'summary' => '1st Level · Evocation · 1 Action · 120 ft · 3x (1d4+1) Force',
                'description' => 'You create three glowing darts of magical force. Each dart hits a creature of your choice that you can see within range. A dart deals 1d4 + 1 Force damage to its target. The darts all strike simultaneously.',
                'data' => [
                    'level' => 1,
                    'school' => 'Evocation',
                    'casting_time' => '1 Action',
                    'range' => '120 ft',
                    'components' => 'V, S',
                    'duration' => 'Instantaneous',
                    'damage' => '3x (1d4+1)',
                    'damage_type' => 'force',
                ],
            ],
            [
                'name' => 'Shield',
                'summary' => '1st Level · Abjuration · 1 Reaction · Self · +5 AC',
                'description' => 'An invisible barrier of magical force appears and protects you. Until the start of your next turn, you have a +5 bonus to AC, including against the triggering attack, and you take no damage from Magic Missile.',
                'data' => [
                    'level' => 1,
                    'school' => 'Abjuration',
                    'casting_time' => '1 Reaction',
                    'range' => 'Self',
                    'components' => 'V, S',
                    'duration' => '1 round',
                ],
            ],
            [
                'name' => 'Healing Word',
                'summary' => '1st Level · Abjuration · 1 Bonus Action · 60 ft · 1d4 + Mod Heal',
                'description' => 'A creature of your choice that you can see within range regains hit points equal to 1d4 + your spellcasting ability modifier.',
                'data' => [
                    'level' => 1,
                    'school' => 'Abjuration',
                    'casting_time' => '1 Bonus Action',
                    'range' => '60 ft',
                    'components' => 'V',
                    'duration' => 'Instantaneous',
                    'heal' => '1d4 + mod',
                ],
            ],
            [
                'name' => 'Cure Wounds',
                'summary' => '1st Level · Abjuration · 1 Action · Touch · 2d8 + Mod Heal',
                'description' => 'A creature you touch regains hit points equal to 2d8 + your spellcasting ability modifier. (Enhanced in 2024 rules from 1d8 to 2d8!)',
                'data' => [
                    'level' => 1,
                    'school' => 'Abjuration',
                    'casting_time' => '1 Action',
                    'range' => 'Touch',
                    'components' => 'V, S',
                    'duration' => 'Instantaneous',
                    'heal' => '2d8 + mod',
                ],
            ],
            [
                'name' => 'Guiding Bolt',
                'summary' => '1st Level · Evocation · 1 Action · 120 ft · 4d6 Radiant · Advantage',
                'description' => 'A flash of light streaks toward a creature. On a hit, it takes 4d6 Radiant damage, and the next attack roll made against this target before the end of your next turn has Advantage.',
                'data' => [
                    'level' => 1,
                    'school' => 'Evocation',
                    'casting_time' => '1 Action',
                    'range' => '120 ft',
                    'components' => 'V, S',
                    'duration' => '1 round',
                    'attack_type' => 'ranged_spell',
                    'damage' => '4d6',
                    'damage_type' => 'radiant',
                ],
            ],
            [
                'name' => 'Bless',
                'summary' => '1st Level · Enchantment · 1 Action · 30 ft · Concentration 1 min · +1d4 Attacks & Saves',
                'description' => 'You bless up to three creatures of your choice within range. Whenever a target makes an attack roll or a saving throw before the spell ends, the target can roll a d4 and add the number rolled to the attack roll or save.',
                'data' => [
                    'level' => 1,
                    'school' => 'Enchantment',
                    'casting_time' => '1 Action',
                    'range' => '30 ft',
                    'components' => 'V, S, M',
                    'duration' => 'Concentration, up to 1 minute',
                ],
            ],
            [
                'name' => 'Thunderwave',
                'summary' => '1st Level · Evocation · 1 Action · Self (15-ft cube) · Con Save · 2d8 Thunder · Push 10 ft',
                'description' => 'A wave of thunderous force sweeps out from you. Each creature in a 15-foot cube originating from you must make a Constitution saving throw. On a failed save, a creature takes 2d8 Thunder damage and is pushed 10 feet away.',
                'data' => [
                    'level' => 1,
                    'school' => 'Evocation',
                    'casting_time' => '1 Action',
                    'range' => 'Self (15-ft cube)',
                    'components' => 'V, S',
                    'duration' => 'Instantaneous',
                    'save_ability' => 'con',
                    'damage' => '2d8',
                    'damage_type' => 'thunder',
                ],
            ],
            [
                'name' => 'Detect Magic',
                'summary' => '1st Level · Divination (Ritual) · 1 Action · Self · Concentration 10 min',
                'description' => 'For the duration, you sense the presence of magic within 30 feet of you. If you sense magic, you can use your action to see a faint aura around any visible creature or object bearing magic, and you learn its school of magic.',
                'data' => [
                    'level' => 1,
                    'school' => 'Divination',
                    'casting_time' => '1 Action or Ritual',
                    'range' => 'Self (30-ft sphere)',
                    'components' => 'V, S',
                    'duration' => 'Concentration, up to 10 minutes',
                ],
            ],
        ];

        foreach ($spells as $item) {
            CompendiumEntry::updateOrCreate(
                ['system_id' => $system->id, 'type' => 'spell', 'name' => $item['name']],
                [
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'data' => $item['data'],
                    'is_srd' => true,
                ],
            );
        }

        // 4. Adventuring Gear & Items
        $gear = [
            [
                'name' => 'Potion of Healing',
                'summary' => 'Magic Item (Potion) · Regains 2d4 + 2 HP (Bonus Action in 2024)',
                'description' => 'You regain 2d4 + 2 Hit Points when you drink this potion. In the 2024 rules, drinking a potion yourself is a Bonus Action (feeding it to another creature is an Action).',
                'data' => [
                    'category' => 'Potion',
                    'heal' => '2d4 + 2',
                    'weight' => '0.5 lb',
                ],
            ],
            [
                'name' => "Explorer's Pack",
                'summary' => 'Gear Bundle · Backpack, bedroll, mess kit, tinderbox, 10 torches, 10 rations, waterskin, 50 ft rope',
                'description' => 'Standard adventuring gear for wilderness expeditions.',
                'data' => [
                    'category' => 'Equipment Pack',
                    'weight' => '59 lb',
                ],
            ],
            [
                'name' => "Dungeoneer's Pack",
                'summary' => 'Gear Bundle · Backpack, crowbar, hammer, 10 pitons, 10 torches, tinderbox, 10 rations, waterskin, 50 ft rope',
                'description' => 'Standard adventuring gear for subterranean exploration.',
                'data' => [
                    'category' => 'Equipment Pack',
                    'weight' => '61.5 lb',
                ],
            ],
            [
                'name' => 'Bag of Holding',
                'summary' => 'Uncommon Wondrous Item · Holds up to 500 lb, 64 cu ft',
                'description' => 'This bag has an interior space considerably larger than its outside dimensions, roughly 2 feet in diameter at the mouth and 4 feet deep. It can hold up to 500 pounds, not exceeding a volume of 64 cubic feet.',
                'data' => [
                    'category' => 'Wondrous Item',
                    'weight' => '15 lb',
                ],
            ],
        ];

        foreach ($gear as $item) {
            CompendiumEntry::updateOrCreate(
                ['system_id' => $system->id, 'type' => 'gear', 'name' => $item['name']],
                [
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'data' => $item['data'],
                    'is_srd' => true,
                ],
            );
        }
    }
}
