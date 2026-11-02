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

        // 5. Classes (2024 SRD)
        $classes = [
            [
                'name' => 'Barbarian',
                'summary' => 'd12 Hit Die · STR, CON Saves · Primal warrior of relentless rage & raw resilience',
                'description' => 'Barbarians are mighty warriors powered by primal forces of the multiverse that manifest as a Rage. They thrive in close-quarters melee, shrugging off devastating blows and crushing foes with two-handed weapons.',
                'data' => [
                    'hit_die' => 12,
                    'primary_ability' => 'Strength',
                    'saving_throws' => ['str', 'con'],
                    'armor_proficiencies' => 'Light Armor, Medium Armor, Shields',
                    'weapon_proficiencies' => 'Simple Weapons, Martial Weapons',
                    'weapon_masteries' => 2,
                    'subclasses' => ['Path of the Berserker', 'Path of the Wild Heart', 'Path of the World Tree', 'Path of the Zealot'],
                ],
            ],
            [
                'name' => 'Bard',
                'summary' => 'd8 Hit Die · DEX, CHA Saves · Full Spellcaster · Bardic Inspiration (d6) & Expertise',
                'description' => 'Invoking magic through music, dance, and verse, Bards are expert inspirers of allies and masters of varied skills, wielding enchantment, illusion, and healing songs.',
                'data' => [
                    'hit_die' => 8,
                    'primary_ability' => 'Charisma',
                    'saving_throws' => ['dex', 'cha'],
                    'armor_proficiencies' => 'Light Armor',
                    'weapon_proficiencies' => 'Simple Weapons',
                    'spellcasting_ability' => 'cha',
                    'subclasses' => ['College of Dance', 'College of Glamour', 'College of Lore', 'College of Valor'],
                ],
            ],
            [
                'name' => 'Cleric',
                'summary' => 'd8 Hit Die · WIS, CHA Saves · Full Spellcaster · Divine Order & Channel Divinity',
                'description' => 'Clerics draw power from the realms of the gods and harness it to work miracles, heal wounds, smite undead, and protect the faithful with heavy armor and divine radiant wrath.',
                'data' => [
                    'hit_die' => 8,
                    'primary_ability' => 'Wisdom',
                    'saving_throws' => ['wis', 'cha'],
                    'armor_proficiencies' => 'Light Armor, Medium Armor, Shields',
                    'weapon_proficiencies' => 'Simple Weapons',
                    'spellcasting_ability' => 'wis',
                    'subclasses' => ['Life Domain', 'Light Domain', 'Trickery Domain', 'War Domain'],
                ],
            ],
            [
                'name' => 'Druid',
                'summary' => 'd8 Hit Die · INT, WIS Saves · Full Spellcaster · Wild Shape & Primal Order',
                'description' => 'Druids belong to ancient orders that call on the forces of nature, harnessing primal spells, elemental fury, and animal forms to protect the natural balance.',
                'data' => [
                    'hit_die' => 8,
                    'primary_ability' => 'Wisdom',
                    'saving_throws' => ['int', 'wis'],
                    'armor_proficiencies' => 'Light Armor, Shields',
                    'weapon_proficiencies' => 'Simple Weapons',
                    'spellcasting_ability' => 'wis',
                    'subclasses' => ['Circle of the Land', 'Circle of the Moon', 'Circle of the Sea', 'Circle of the Stars'],
                ],
            ],
            [
                'name' => 'Fighter',
                'summary' => 'd10 Hit Die · STR, CON Saves · Fighting Style, Second Wind & 3 Weapon Masteries',
                'description' => 'Fighters rule many battlefields. Questing knights, royal champions, elite soldiers, and hardened mercenaries—all share an unparalleled mastery with weapons and armor.',
                'data' => [
                    'hit_die' => 10,
                    'primary_ability' => 'Strength or Dexterity',
                    'saving_throws' => ['str', 'con'],
                    'armor_proficiencies' => 'Light Armor, Medium Armor, Heavy Armor, Shields',
                    'weapon_proficiencies' => 'Simple Weapons, Martial Weapons',
                    'weapon_masteries' => 3,
                    'subclasses' => ['Battle Master', 'Champion', 'Eldritch Knight', 'Psi Warrior'],
                ],
            ],
            [
                'name' => 'Monk',
                'summary' => 'd8 Hit Die · STR, DEX Saves · Martial Arts (1d6), Unarmored Defense & Focus',
                'description' => 'Monks use rigorous combat training and mental discipline to align themselves with the multiverse and channel internal reservoirs of power, striking with lightning speed.',
                'data' => [
                    'hit_die' => 8,
                    'primary_ability' => 'Dexterity & Wisdom',
                    'saving_throws' => ['str', 'dex'],
                    'armor_proficiencies' => 'None',
                    'weapon_proficiencies' => 'Simple Weapons, Martial Weapons with Light property',
                    'subclasses' => ['Warrior of Mercy', 'Warrior of Shadow', 'Warrior of the Elements', 'Warrior of the Open Hand'],
                ],
            ],
            [
                'name' => 'Paladin',
                'summary' => 'd10 Hit Die · WIS, CHA Saves · Half Spellcaster · Lay on Hands, Smite & Auras',
                'description' => 'Paladins are united by their oaths to stand against the forces of annihilation and corruption, smiting foes with radiant energy and shielding allies with protective auras.',
                'data' => [
                    'hit_die' => 10,
                    'primary_ability' => 'Strength & Charisma',
                    'saving_throws' => ['wis', 'cha'],
                    'armor_proficiencies' => 'Light Armor, Medium Armor, Heavy Armor, Shields',
                    'weapon_proficiencies' => 'Simple Weapons, Martial Weapons',
                    'weapon_masteries' => 2,
                    'spellcasting_ability' => 'cha',
                    'subclasses' => ['Oath of Devotion', 'Oath of Glory', 'Oath of the Ancients', 'Oath of Vengeance'],
                ],
            ],
            [
                'name' => 'Ranger',
                'summary' => 'd10 Hit Die · STR, DEX Saves · Half Spellcaster · Favored Enemy & Weapon Mastery',
                'description' => 'Far from bustling cities, Rangers keep their unending watch in the wilderness, hunting monsters with lethal quarry marks, twin blades, or longbows.',
                'data' => [
                    'hit_die' => 10,
                    'primary_ability' => 'Dexterity & Wisdom',
                    'saving_throws' => ['str', 'dex'],
                    'armor_proficiencies' => 'Light Armor, Medium Armor, Shields',
                    'weapon_proficiencies' => 'Simple Weapons, Martial Weapons',
                    'weapon_masteries' => 2,
                    'spellcasting_ability' => 'wis',
                    'subclasses' => ['Beast Master', 'Fey Wanderer', 'Gloom Stalker', 'Hunter'],
                ],
            ],
            [
                'name' => 'Rogue',
                'summary' => 'd8 Hit Die · DEX, INT Saves · Sneak Attack (1d6), Thieves’ Cant & 2 Expertises',
                'description' => 'Rogues rely on skill, stealth, and their foes’ vulnerabilities to get the upper hand in any situation, landing devastating sneak attacks and disarming traps.',
                'data' => [
                    'hit_die' => 8,
                    'primary_ability' => 'Dexterity',
                    'saving_throws' => ['dex', 'int'],
                    'armor_proficiencies' => 'Light Armor',
                    'weapon_proficiencies' => 'Simple Weapons, Martial Weapons with Finesse or Light',
                    'weapon_masteries' => 2,
                    'subclasses' => ['Arcane Trickster', 'Assassin', 'Soulknife', 'Thief'],
                ],
            ],
            [
                'name' => 'Sorcerer',
                'summary' => 'd6 Hit Die · CON, CHA Saves · Full Spellcaster · Innate Sorcery & Metamagic',
                'description' => 'Sorcerers wield magic that is stamped into their very being, unleashing Innate Sorcery and twisting magical formulas with Metamagic.',
                'data' => [
                    'hit_die' => 6,
                    'primary_ability' => 'Charisma',
                    'saving_throws' => ['con', 'cha'],
                    'armor_proficiencies' => 'None',
                    'weapon_proficiencies' => 'Simple Weapons',
                    'spellcasting_ability' => 'cha',
                    'subclasses' => ['Aberrant Sorcery', 'Clockwork Sorcery', 'Draconic Sorcery', 'Wild Magic Sorcery'],
                ],
            ],
            [
                'name' => 'Warlock',
                'summary' => 'd8 Hit Die · WIS, CHA Saves · Pact Magic (Short Rest Recharge) & Eldritch Invocations',
                'description' => 'Warlocks quest for knowledge that lies hidden in the fabric of the multiverse, unlocking Eldritch Invocations and otherworldly patron pacts.',
                'data' => [
                    'hit_die' => 8,
                    'primary_ability' => 'Charisma',
                    'saving_throws' => ['wis', 'cha'],
                    'armor_proficiencies' => 'Light Armor',
                    'weapon_proficiencies' => 'Simple Weapons',
                    'spellcasting_ability' => 'cha',
                    'subclasses' => ['Archfey Patron', 'Celestial Patron', 'Fiend Patron', 'Great Old One Patron'],
                ],
            ],
            [
                'name' => 'Wizard',
                'summary' => 'd6 Hit Die · INT, WIS Saves · Full Spellcaster · Spellbook, Ritual Adept & Arcane Recovery',
                'description' => 'Wizards are defined by their exhaustive study of magic’s inner workings, casting spells of explosive fire, arcing lightning, and subtle deception from their spellbooks.',
                'data' => [
                    'hit_die' => 6,
                    'primary_ability' => 'Intelligence',
                    'saving_throws' => ['int', 'wis'],
                    'armor_proficiencies' => 'None',
                    'weapon_proficiencies' => 'Simple Weapons',
                    'spellcasting_ability' => 'int',
                    'subclasses' => ['Abjurer', 'Diviner', 'Evoker', 'Illusionist'],
                ],
            ],
        ];

        foreach ($classes as $item) {
            CompendiumEntry::updateOrCreate(
                ['system_id' => $system->id, 'type' => 'class', 'name' => $item['name']],
                [
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'data' => $item['data'],
                    'is_srd' => true,
                ],
            );
        }

        // 6. Class & Background Features
        $features = [
            [
                'name' => 'Rage',
                'summary' => 'Barbarian Level 1 · Bonus Action · Resistance to B/P/S, +2 Melee Damage & Adv on STR',
                'description' => 'In battle, you fight with primal ferocity. On your turn, you can enter a Rage as a Bonus Action. While raging, you gain Resistance to Bludgeoning, Piercing, and Slashing damage, add +2 Rage Damage to Strength-based weapon attacks, and have Advantage on Strength checks and saving throws. Lasts for 10 minutes.',
                'data' => ['source' => 'class', 'class' => 'Barbarian', 'level' => 1],
            ],
            [
                'name' => 'Unarmored Defense (Barbarian)',
                'summary' => 'Barbarian Level 1 · AC = 10 + DEX Mod + CON Mod (Shields allowed)',
                'description' => 'While you are not wearing any armor, your base Armor Class equals 10 + your Dexterity modifier + your Constitution modifier. You can use a Shield and still gain this benefit.',
                'data' => ['source' => 'class', 'class' => 'Barbarian', 'level' => 1],
            ],
            [
                'name' => 'Bardic Inspiration (d6)',
                'summary' => 'Bard Level 1 · Bonus Action · Give d6 die to ally within 60 ft (last 1 hour)',
                'description' => 'As a Bonus Action, inspire another creature within 60 ft. Within the next hour, when that creature makes a d20 Test, it can roll your Bardic Inspiration die (d6) and add the number rolled to the total. Uses equal to Charisma modifier per Long Rest (recharges on Short Rest at Level 5).',
                'data' => ['source' => 'class', 'class' => 'Bard', 'level' => 1],
            ],
            [
                'name' => 'Divine Order: Protector',
                'summary' => 'Cleric Level 1 Specialization · Gain Martial Weapons & Heavy Armor Training',
                'description' => 'You have dedicated yourself to martial defense in service to your deity. You gain proficiency with Martial weapons and training with Heavy armor.',
                'data' => ['source' => 'class', 'class' => 'Cleric', 'level' => 1],
            ],
            [
                'name' => 'Divine Order: Thaumaturge',
                'summary' => 'Cleric Level 1 Specialization · Extra Cantrip + Add WIS modifier to Arcana & Religion',
                'description' => 'You delve into sacred mysteries and theology. You know one extra cantrip from the Cleric spell list, and you add your Wisdom modifier to Intelligence (Arcana or Religion) checks.',
                'data' => ['source' => 'class', 'class' => 'Cleric', 'level' => 1],
            ],
            [
                'name' => 'Primal Order: Warden',
                'summary' => 'Druid Level 1 Specialization · Gain Martial Weapons & Medium Armor Training',
                'description' => 'Trained for battle against unnatural monstrosities, you gain proficiency with Martial weapons and training with Medium armor.',
                'data' => ['source' => 'class', 'class' => 'Druid', 'level' => 1],
            ],
            [
                'name' => 'Primal Order: Magician',
                'summary' => 'Druid Level 1 Specialization · Extra Cantrip + Add WIS modifier to Arcana & Nature',
                'description' => 'You focus deeply on the magical currents of the natural world. You know one extra cantrip from the Druid spell list, and you add your Wisdom modifier to Intelligence (Arcana or Nature) checks.',
                'data' => ['source' => 'class', 'class' => 'Druid', 'level' => 1],
            ],
            [
                'name' => 'Fighting Style: Defense',
                'summary' => 'Fighting Style Feat · +1 Bonus to Armor Class while wearing Armor',
                'description' => 'While you are wearing Light, Medium, or Heavy armor, you gain a +1 bonus to Armor Class.',
                'data' => ['source' => 'class', 'category' => 'Fighting Style'],
            ],
            [
                'name' => 'Fighting Style: Dueling',
                'summary' => 'Fighting Style Feat · +2 Damage when wielding a one-handed melee weapon',
                'description' => 'When you are wielding a melee weapon in one hand and no other weapons, you gain a +2 bonus to damage rolls with that weapon.',
                'data' => ['source' => 'class', 'category' => 'Fighting Style'],
            ],
            [
                'name' => 'Fighting Style: Great Weapon Fighting',
                'summary' => 'Fighting Style Feat · Treat any 1 or 2 on damage dice as a 3 for two-handed weapons',
                'description' => 'When you roll damage for an attack you make with a melee weapon that you are holding with two hands, you can treat any 1 or 2 on a damage die as a 3.',
                'data' => ['source' => 'class', 'category' => 'Fighting Style'],
            ],
            [
                'name' => 'Fighting Style: Archery',
                'summary' => 'Fighting Style Feat · +2 Bonus to attack rolls with Ranged Weapons',
                'description' => 'You gain a +2 bonus to attack rolls you make with ranged weapons.',
                'data' => ['source' => 'class', 'category' => 'Fighting Style'],
            ],
            [
                'name' => 'Fighting Style: Two-Weapon Fighting',
                'summary' => 'Fighting Style Feat · Add ability modifier to the extra light weapon attack damage',
                'description' => 'When you make an extra attack as a result of the Light property, you can add your ability modifier to the damage of that attack.',
                'data' => ['source' => 'class', 'category' => 'Fighting Style'],
            ],
            [
                'name' => 'Second Wind',
                'summary' => 'Fighter Level 1 · Bonus Action · Regain 1d10 + Fighter Level HP (2 uses/Short Rest)',
                'description' => 'You have a limited well of stamina that you can draw on to protect yourself from harm. On your turn, you can use a Bonus Action to regain Hit Points equal to 1d10 + your Fighter level. You have 2 uses; you regain one expended use on a Short Rest and all uses on a Long Rest.',
                'data' => ['source' => 'class', 'class' => 'Fighter', 'level' => 1],
            ],
            [
                'name' => 'Action Surge',
                'summary' => 'Fighter Level 2 · Take one additional Action on your turn (1/Short Rest)',
                'description' => 'You can push yourself beyond your normal limits for a moment. On your turn, you can take one additional Action. Once you use this feature, you must finish a Short or Long Rest before you can use it again.',
                'data' => ['source' => 'class', 'class' => 'Fighter', 'level' => 2],
            ],
            [
                'name' => 'Weapon Mastery',
                'summary' => 'Martial Feature · Unlock tactical mastery properties (Sap, Vex, Cleave, Push, Topple, etc.)',
                'description' => 'Your training with weapons allows you to use the mastery properties of weapons you are proficient with, imposing conditions like Knockdown, Push, Slow, or gaining Advantage on consecutive attacks.',
                'data' => ['source' => 'class'],
            ],
            [
                'name' => 'Martial Arts',
                'summary' => 'Monk Level 1 · 1d6 Unarmed Strike · Dex for Attacks & Damage · Free Bonus Attack',
                'description' => 'Your practice of martial arts gives you mastery of combat styles that use Unarmed Strikes and Monk weapons. You can use Dexterity instead of Strength for attack and damage rolls, roll a d6 Martial Arts die for damage, and make one Unarmed Strike as a Bonus Action after taking the Attack action.',
                'data' => ['source' => 'class', 'class' => 'Monk', 'level' => 1],
            ],
            [
                'name' => 'Unarmored Defense (Monk)',
                'summary' => 'Monk Level 1 · AC = 10 + DEX Mod + WIS Mod (no armor or shield)',
                'description' => 'While not wearing armor and not wielding a Shield, your base Armor Class equals 10 + your Dexterity modifier + your Wisdom modifier.',
                'data' => ['source' => 'class', 'class' => 'Monk', 'level' => 1],
            ],
            [
                'name' => 'Lay on Hands',
                'summary' => 'Paladin Level 1 · Pool of 5 × Level HP · Bonus Action heal or cure Poisoned',
                'description' => 'Your blessed touch can heal wounds. You have a pool of healing power that replenishes when you finish a Long Rest. With that pool, you can restore a total number of Hit Points equal to 5 × your Paladin level. As a Bonus Action, touch a creature and draw power from the pool to restore HP or expend 5 HP to remove the Poisoned condition.',
                'data' => ['source' => 'class', 'class' => 'Paladin', 'level' => 1],
            ],
            [
                'name' => 'Favored Enemy',
                'summary' => 'Ranger Level 1 · Always have Hunter’s Mark prepared · Cast 2/Long Rest without spell slot',
                'description' => 'You are an adept hunter of prey. You always have the Hunter’s Mark spell prepared, and it does not count against your prepared spells. You can cast it twice without expending a spell slot per Long Rest.',
                'data' => ['source' => 'class', 'class' => 'Ranger', 'level' => 1],
            ],
            [
                'name' => 'Sneak Attack',
                'summary' => 'Rogue Level 1 · +1d6 Damage on Finesse/Ranged weapon hit with Advantage or nearby ally',
                'description' => 'Beginning at level 1, you know how to strike subtly and exploit a foe’s distraction. Once per turn, you can deal an extra 1d6 damage to one creature you hit with an attack roll if you have Advantage on the roll or an ally is within 5 ft of the target. Must use a Finesse or Ranged weapon.',
                'data' => ['source' => 'class', 'class' => 'Rogue', 'level' => 1],
            ],
            [
                'name' => 'Innate Sorcery',
                'summary' => 'Sorcerer Level 1 · Bonus Action · +1 Spell DC & Advantage on Spell Attacks (1 min, 2/Long Rest)',
                'description' => 'An event in your past or an inherited magical heritage unleashed an inner well of magic. As a Bonus Action, you can unleash that power for 1 minute: the spell save DC of your Sorcerer spells increases by 1, and you have Advantage on the attack rolls of Sorcerer spells you cast. (2 uses per Long Rest).',
                'data' => ['source' => 'class', 'class' => 'Sorcerer', 'level' => 1],
            ],
            [
                'name' => 'Eldritch Invocations: Pact of the Blade',
                'summary' => 'Warlock Level 1 Invocation · Conjure pact melee weapon & use Charisma for attacks',
                'description' => 'As a Bonus Action, you can conjure a pact weapon or bond with a magic weapon. When you attack with it, you can use your Charisma modifier instead of Strength or Dexterity for attack and damage rolls.',
                'data' => ['source' => 'class', 'class' => 'Warlock', 'level' => 1],
            ],
            [
                'name' => 'Eldritch Invocations: Eldritch Mind',
                'summary' => 'Warlock Level 1 Invocation · Advantage on CON saves to maintain Spell Concentration',
                'description' => 'You have Advantage on Constitution saving throws that you make to maintain Concentration on a spell.',
                'data' => ['source' => 'class', 'class' => 'Warlock', 'level' => 1],
            ],
            [
                'name' => 'Arcane Recovery',
                'summary' => 'Wizard Level 1 · Short Rest · Recover spell slots equal to half Wizard Level (1/Long Rest)',
                'description' => 'You have learned to regain some of your magical energy by studying your spellbook. Once per day when you finish a Short Rest, you can choose expended spell slots to recover with a combined level equal to or less than half your Wizard level (rounded up).',
                'data' => ['source' => 'class', 'class' => 'Wizard', 'level' => 1],
            ],
            [
                'name' => 'Ritual Adept',
                'summary' => 'Wizard Level 1 · Cast any Ritual spell in your spellbook without preparing it',
                'description' => 'You can cast any spell as a Ritual if that spell has the Ritual tag and the spell is in your spellbook. You need not have the spell prepared.',
                'data' => ['source' => 'class', 'class' => 'Wizard', 'level' => 1],
            ],
            [
                'name' => 'Shelter of the Faithful',
                'summary' => 'Acolyte Background Feature · Free religious healing and lodging at temples of your faith',
                'description' => 'You and your adventuring companions can expect to receive free healing and care at a temple, shrine, or other established presence of your faith. Priests will support you at a modest lifestyle.',
                'data' => ['source' => 'background', 'background' => 'Acolyte'],
            ],
            [
                'name' => 'Criminal Contact',
                'summary' => 'Criminal Background Feature · Reliable liaison to the local underworld & fences',
                'description' => 'You have a reliable and trustworthy contact who acts as your liaison to a network of other criminals. You know how to get messages to and from your contact and can access local black markets.',
                'data' => ['source' => 'background', 'background' => 'Criminal'],
            ],
            [
                'name' => 'Military Rank',
                'summary' => 'Soldier Background Feature · Recognition, military authority & access to friendly outposts',
                'description' => 'You have a military rank from your career as a soldier. Soldiers loyal to your former military organization still recognize your authority and influence, granting you entry to friendly encampments and outposts.',
                'data' => ['source' => 'background', 'background' => 'Soldier'],
            ],
            [
                'name' => 'City Secrets',
                'summary' => 'Wayfarer Background Feature · Travel twice as fast between urban points through alleys',
                'description' => 'You know the secret patterns and flow to cities and can find passages through the urban sprawl that others would miss. When you are not in combat, you (and companions you lead) can travel between two locations in the city twice as fast.',
                'data' => ['source' => 'background', 'background' => 'Wayfarer'],
            ],
        ];

        foreach ($features as $item) {
            CompendiumEntry::updateOrCreate(
                ['system_id' => $system->id, 'type' => 'feature', 'name' => $item['name']],
                [
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'data' => $item['data'],
                    'is_srd' => true,
                ],
            );
        }

        // 7. Feats (Origin & General 2024)
        $feats = [
            [
                'name' => 'Alert',
                'summary' => 'Origin Feat · Add Proficiency Bonus to Initiative · Swap Initiative with willing ally',
                'description' => 'Always on the lookout for danger, you gain proficiency bonus to initiative and can swap your initiative count with a willing ally in the same combat.',
                'data' => ['category' => 'Origin', 'benefits' => ['Initiative Proficiency: Add PB to Initiative.', 'Initiative Swap: Swap initiative roll with a willing ally.']],
            ],
            [
                'name' => 'Crafter',
                'summary' => 'Origin Feat · 3 Tool Proficiencies · 20% discount on gear · Fast Crafting during rests',
                'description' => 'You are adept at crafting everyday gear and improvising tools during rests, obtaining trade discounts in markets.',
                'data' => ['category' => 'Origin', 'benefits' => ['3 Artisan Tools proficiencies.', '20% discount on nonmagical items.', 'Fast Crafting after Long Rest.']],
            ],
            [
                'name' => 'Healer',
                'summary' => 'Origin Feat · Battle Medic (Healer’s Kit heals Hit Die + PB) · Reroll 1s on healing dice',
                'description' => 'You are trained in battlefield triage and restorative efficiency. A creature you tend to can spend a Hit Die to heal, and you can reroll 1s on healing spells.',
                'data' => ['category' => 'Origin', 'benefits' => ['Battle Medic: Expend Healer Kit to restore Hit Die + PB.', 'Healing Rerolls: Reroll 1s on healing dice.']],
            ],
            [
                'name' => 'Lucky',
                'summary' => 'Origin Feat · Luck Points = PB · Spend for Advantage on d20 Test or impose Disadvantage',
                'description' => 'Inexplicable fortune favors you in moments of crisis. You have Luck Points equal to your Proficiency Bonus to seize Advantage or force attackers to roll with Disadvantage.',
                'data' => ['category' => 'Origin', 'benefits' => ['Luck Points equal to Proficiency Bonus.', 'Spend 1 Luck Point for Advantage on d20 Test.', 'Spend 1 Luck Point to impose Disadvantage on attacker.']],
            ],
            [
                'name' => 'Magic Initiate (Cleric)',
                'summary' => 'Origin Feat · 2 Cleric Cantrips + 1 Level 1 Cleric Spell (cast 1/day free + with spell slots)',
                'description' => 'You have learned foundational divine magic from the Cleric spell list, preparing two cantrips and one 1st-level spell.',
                'data' => ['category' => 'Origin', 'benefits' => ['Two Cleric cantrips of choice.', 'One Level 1 Cleric spell (cast 1/day free and using spell slots).']],
            ],
            [
                'name' => 'Magic Initiate (Druid)',
                'summary' => 'Origin Feat · 2 Druid Cantrips + 1 Level 1 Druid Spell (cast 1/day free + with spell slots)',
                'description' => 'You have attuned to primal magic from the Druid spell list, preparing two cantrips and one 1st-level spell.',
                'data' => ['category' => 'Origin', 'benefits' => ['Two Druid cantrips of choice.', 'One Level 1 Druid spell (cast 1/day free and using spell slots).']],
            ],
            [
                'name' => 'Magic Initiate (Wizard)',
                'summary' => 'Origin Feat · 2 Wizard Cantrips + 1 Level 1 Wizard Spell (cast 1/day free + with spell slots)',
                'description' => 'You have studied fundamental arcane formulas from the Wizard spell list, preparing two cantrips and one 1st-level spell.',
                'data' => ['category' => 'Origin', 'benefits' => ['Two Wizard cantrips of choice.', 'One Level 1 Wizard spell (cast 1/day free and using spell slots).']],
            ],
            [
                'name' => 'Musician',
                'summary' => 'Origin Feat · 3 Instrument proficiencies · Encouraging Song gives Heroic Inspiration',
                'description' => 'Your music bolsters your companions. When you finish a Short or Long Rest, you grant Heroic Inspiration to allies who hear your performance.',
                'data' => ['category' => 'Origin', 'benefits' => ['3 Musical Instrument proficiencies.', 'Encouraging Song: Give Heroic Inspiration to PB allies on rest.']],
            ],
            [
                'name' => 'Savage Attacker',
                'summary' => 'Origin Feat · Roll weapon damage dice twice and use highest result once per turn',
                'description' => 'You have trained to deal particularly devastating blows. Once per turn when you hit with a weapon, you can roll the weapon damage dice twice and choose either result.',
                'data' => ['category' => 'Origin', 'benefits' => ['Roll weapon damage dice twice and use highest once per turn.']],
            ],
            [
                'name' => 'Skilled',
                'summary' => 'Origin Feat · Gain proficiency in any combination of 3 skills or tools of your choice',
                'description' => 'You have an exceptionally broad range of training, gaining three skill or tool proficiencies.',
                'data' => ['category' => 'Origin', 'benefits' => ['Gain 3 skill or tool proficiencies of choice.']],
            ],
            [
                'name' => 'Tavern Brawler',
                'summary' => 'Origin Feat · 1d4 Unarmed Strike · Damage Rerolls · Push 5 ft on Unarmed Strike hit',
                'description' => 'Accustomed to rough-and-tumble brawling, your Unarmed Strikes deal 1d4 damage, can reroll 1s on damage, and push creatures 5 feet.',
                'data' => ['category' => 'Origin', 'benefits' => ['1d4 + STR Unarmed Strike.', 'Reroll 1s on Unarmed Strike damage.', 'Push 5 ft on hit once per turn.']],
            ],
            [
                'name' => 'Tough',
                'summary' => 'Origin Feat · Hit Point maximum increases by 2 for each character level (+2 HP/lvl)',
                'description' => 'Your body is unusually resilient. Your Hit Point maximum increases by twice your character level when you take this feat, and by +2 HP every time you gain a level.',
                'data' => ['category' => 'Origin', 'benefits' => ['+2 Hit Points per character level.']],
            ],
            [
                'name' => 'War Caster',
                'summary' => 'General Feat · +1 INT/WIS/CHA · Advantage on Concentration saves · Cast spell as OA',
                'description' => 'You have mastered the art of casting spells in the thick of battle. You have Advantage on Constitution saving throws to maintain concentration, can perform somatic components while holding weapons and shield, and can cast a spell as an Opportunity Attack.',
                'data' => ['category' => 'General', 'prerequisite' => 'Level 4+, Spellcasting feature'],
            ],
            [
                'name' => 'Sentinel',
                'summary' => 'General Feat · +1 STR/DEX · Opportunity attack drops speed to 0 · Ignore Disengage',
                'description' => 'You have mastered techniques to take advantage of every drop in any enemy’s guard. When you hit with an Opportunity Attack, the target’s speed becomes 0 for the turn. Creatures provoke OA even if they Disengage.',
                'data' => ['category' => 'General', 'prerequisite' => 'Level 4+'],
            ],
            [
                'name' => 'Great Weapon Master',
                'summary' => 'General Feat · +1 STR · Heavy weapon mastery (+PB Damage) · Bonus attack on crit or kill',
                'description' => 'You learn to put the weight of a heavy weapon to your advantage. On your turn when you hit with a Heavy weapon, you deal extra damage equal to your Proficiency Bonus. When you score a critical hit or reduce a creature to 0 HP, make one bonus melee attack.',
                'data' => ['category' => 'General', 'prerequisite' => 'Level 4+, STR 13+'],
            ],
            [
                'name' => 'Sharpshooter',
                'summary' => 'General Feat · +1 DEX · Ignore half & three-quarters cover · Fire in melee · Max range',
                'description' => 'You are a master of ranged weaponry. Attacking at long range doesn’t impose Disadvantage, your ranged weapon attacks ignore half and three-quarters cover, and being within 5 ft of an enemy does not impose Disadvantage on your ranged attack rolls.',
                'data' => ['category' => 'General', 'prerequisite' => 'Level 4+, DEX 13+'],
            ],
        ];

        foreach ($feats as $item) {
            CompendiumEntry::updateOrCreate(
                ['system_id' => $system->id, 'type' => 'feat', 'name' => $item['name']],
                [
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'data' => $item['data'],
                    'is_srd' => true,
                ],
            );
        }

        // 8. Species & Lineages (2024 SRD)
        $species = [
            [
                'name' => 'Human',
                'summary' => 'Medium or Small · 30 ft · Resourceful (Heroic Inspiration), Skillful (+1 Skill), Versatile (+1 Origin Feat)',
                'description' => 'Found throughout the multiverse, Humans are celebrated for their adaptability, ambition, and boundless curiosity.',
                'data' => ['speed' => 30, 'size' => ['Medium', 'Small'], 'traits' => ['Resourceful', 'Skillful', 'Versatile']],
            ],
            [
                'name' => 'Elf',
                'summary' => 'Medium · 30–35 ft · Darkvision (60/120 ft), Fey Ancestry, Keen Senses, Trance & Elven Lineage',
                'description' => 'Created by the god Corellon, Elves possess keen senses, grace, and innate magic from the Feywild.',
                'data' => ['speed' => 30, 'size' => ['Medium'], 'lineages' => ['High Elf', 'Wood Elf', 'Drow']],
            ],
            [
                'name' => 'Dwarf',
                'summary' => 'Medium · 30 ft · Darkvision (120 ft), Dwarven Resilience, Dwarven Toughness (+1 HP/lvl), Stonecunning',
                'description' => 'Resilient as the mountains they call home, Dwarves boast extraordinary endurance against poison and subterranean tremorsense.',
                'data' => ['speed' => 30, 'size' => ['Medium'], 'hp_bonus_per_level' => 1],
            ],
            [
                'name' => 'Halfling',
                'summary' => 'Small · 30 ft · Brave, Halfling Nimbleness, Luck (reroll 1s on d20), Naturally Stealthy',
                'description' => 'Small in stature but immense in spirit, Halflings survive perils with luck, cheerful camaraderie, and nimble stealth.',
                'data' => ['speed' => 30, 'size' => ['Small']],
            ],
            [
                'name' => 'Dragonborn',
                'summary' => 'Medium · 30 ft · Draconic Ancestry, Breath Weapon, Damage Resistance, Darkvision',
                'description' => 'Dragonborn carry the blood of dragons, breathing elemental fire, cold, acid, lightning, or poison against their foes.',
                'data' => ['speed' => 30, 'size' => ['Medium']],
            ],
            [
                'name' => 'Gnome',
                'summary' => 'Small · 30 ft · Darkvision (60 ft), Gnomish Cunning (Adv on INT/WIS/CHA saves), Lineages',
                'description' => 'Clever, humorous, and endlessly curious, Gnomes shrug off magical manipulation with formidable mental cunning.',
                'data' => ['speed' => 30, 'size' => ['Small'], 'lineages' => ['Forest Gnome', 'Rock Gnome']],
            ],
            [
                'name' => 'Goliath',
                'summary' => 'Medium · 35 ft · Giant Ancestry (Cloud/Fire/Frost/Hill/Stone/Storm), Large Form (Lvl 5), Powerful Build',
                'description' => 'Towering mortals descending from giants, Goliaths possess immense physical strength and supernatural elemental boons.',
                'data' => ['speed' => 35, 'size' => ['Medium']],
            ],
            [
                'name' => 'Orc',
                'summary' => 'Medium · 30 ft · Adrenaline Rush (Bonus Dash + Temp HP), Darkvision (120 ft), Relentless Endurance',
                'description' => 'Tempered by rugged wilderness, Orcs charge into combat with adrenaline surges and defy death itself.',
                'data' => ['speed' => 30, 'size' => ['Medium']],
            ],
            [
                'name' => 'Tiefling',
                'summary' => 'Medium or Small · 30 ft · Darkvision, Fiendish Legacy (Abyssal/Chthonic/Infernal), Otherworldly Presence',
                'description' => 'Touched by the Lower Planes, Tieflings command planar resistances and innate sorcerous legacy spells.',
                'data' => ['speed' => 30, 'size' => ['Medium', 'Small'], 'lineages' => ['Infernal Legacy', 'Abyssal Legacy', 'Chthonic Legacy']],
            ],
            [
                'name' => 'Aasimar',
                'summary' => 'Medium or Small · 30 ft · Celestial Resistance, Darkvision, Healing Hands, Celestial Revelation',
                'description' => 'Blessed by the Upper Planes, Aasimar manifest celestial light, restorative hands, and radiant wings.',
                'data' => ['speed' => 30, 'size' => ['Medium', 'Small']],
            ],
        ];

        foreach ($species as $item) {
            CompendiumEntry::updateOrCreate(
                ['system_id' => $system->id, 'type' => 'species', 'name' => $item['name']],
                [
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'data' => $item['data'],
                    'is_srd' => true,
                ],
            );
        }

        // 9. Species Traits
        $traits = [
            [
                'name' => 'Resourceful',
                'summary' => 'Human Trait · Gain Heroic Inspiration whenever you finish a Long Rest',
                'description' => 'You gain Heroic Inspiration whenever you finish a Long Rest, allowing you to reroll any d20 Test.',
                'data' => ['species' => 'Human'],
            ],
            [
                'name' => 'Skillful',
                'summary' => 'Human Trait · Gain proficiency in one skill of your choice',
                'description' => 'You gain proficiency in one skill of your choice from the skill list.',
                'data' => ['species' => 'Human'],
            ],
            [
                'name' => 'Versatile',
                'summary' => 'Human Trait · Gain one extra Origin Feat of your choice',
                'description' => 'You gain one Origin Feat of your choice (Skilled, Alert, or Tough recommended).',
                'data' => ['species' => 'Human'],
            ],
            [
                'name' => 'Darkvision (60 ft)',
                'summary' => 'Species Trait · See in Dim Light as Bright Light and Darkness as Dim Light up to 60 ft',
                'description' => 'You can see in Dim Light within 60 feet of you as if it were Bright Light, and in Darkness as if it were Dim Light. You discern colors in that darkness only as shades of gray.',
                'data' => ['range' => '60 ft'],
            ],
            [
                'name' => 'Darkvision (120 ft)',
                'summary' => 'Superior Darkvision · See in Dim Light and Darkness up to 120 ft',
                'description' => 'You can see in Dim Light within 120 feet of you as if it were Bright Light, and in Darkness as if it were Dim Light.',
                'data' => ['range' => '120 ft'],
            ],
            [
                'name' => 'Dwarven Resilience',
                'summary' => 'Dwarf Trait · Resistance to Poison damage & Adv on saves to avoid or end Poisoned',
                'description' => 'You have Resistance to Poison damage, and you have Advantage on saving throws you make to avoid or end the Poisoned condition.',
                'data' => ['species' => 'Dwarf'],
            ],
            [
                'name' => 'Dwarven Toughness',
                'summary' => 'Dwarf Trait · Hit Point maximum increases by +1 per character level',
                'description' => 'Your Hit Point maximum increases by 1, and it increases by 1 again whenever you gain a level.',
                'data' => ['species' => 'Dwarf', 'hp_bonus_per_level' => 1],
            ],
            [
                'name' => 'Fey Ancestry',
                'summary' => 'Elf Trait · Advantage on saves to avoid or end the Charmed condition',
                'description' => 'You have Advantage on saving throws you make to avoid or end the Charmed condition on yourself.',
                'data' => ['species' => 'Elf'],
            ],
            [
                'name' => 'Trance',
                'summary' => 'Elf Trait · Magic cannot put you to sleep · Long Rest takes 4 hours of meditation',
                'description' => 'You don’t need to sleep, and magic can’t put you to sleep. You can finish a Long Rest in 4 hours if you spend those hours in a trancelike meditation.',
                'data' => ['species' => 'Elf'],
            ],
            [
                'name' => 'Gnomish Cunning',
                'summary' => 'Gnome Trait · Advantage on Intelligence, Wisdom, and Charisma saving throws',
                'description' => 'You have Advantage on all Intelligence, Wisdom, and Charisma saving throws against magic and other mental effects.',
                'data' => ['species' => 'Gnome'],
            ],
            [
                'name' => 'Breath Weapon (Dragonborn)',
                'summary' => 'Dragonborn Trait · Exhale elemental cone (15 ft) or line (30 ft) for 1d10 damage',
                'description' => 'When you take the Attack action, you can replace one attack with an exhalation of elemental energy in a 15-ft cone or 30-ft line. Targets make a Dex saving throw or take 1d10 damage (scales at levels 5, 11, and 17). Uses = PB per Long Rest.',
                'data' => ['species' => 'Dragonborn'],
            ],
            [
                'name' => 'Luck (Halfling)',
                'summary' => 'Halfling Trait · Reroll any 1 rolled on a d20 Test',
                'description' => 'When you roll a 1 on the d20 of a d20 Test, you can reroll the die, and you must use the new roll.',
                'data' => ['species' => 'Halfling'],
            ],
            [
                'name' => 'Relentless Endurance',
                'summary' => 'Orc Trait · When reduced to 0 HP, drop to 1 HP instead once per Long Rest',
                'description' => 'When you are reduced to 0 Hit Points but not killed outright, you can drop to 1 Hit Point instead. Once you use this trait, you can’t do so again until you finish a Long Rest.',
                'data' => ['species' => 'Orc'],
            ],
            [
                'name' => 'Adrenaline Rush',
                'summary' => 'Orc Trait · Bonus Action Dash & gain Temporary HP equal to Proficiency Bonus',
                'description' => 'You can take the Dash action as a Bonus Action. When you do so, you gain Temporary Hit Points equal to your Proficiency Bonus. Uses = PB per Short or Long Rest.',
                'data' => ['species' => 'Orc'],
            ],
        ];

        foreach ($traits as $item) {
            CompendiumEntry::updateOrCreate(
                ['system_id' => $system->id, 'type' => 'trait', 'name' => $item['name']],
                [
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'data' => $item['data'],
                    'is_srd' => true,
                ],
            );
        }

        // 10. Backgrounds (2024 SRD)
        $backgrounds = [
            ['name' => 'Acolyte', 'summary' => 'INT/WIS/CHA · Magic Initiate (Cleric) · Insight & Religion · Holy Symbol', 'description' => 'Devoted service in a temple performing rites and rituals.'],
            ['name' => 'Artisan', 'summary' => 'STR/DEX/INT · Crafter Feat · Investigation & Persuasion · Artisan Tools', 'description' => 'Apprenticed to master guild artisans, skilled in crafting and trade.'],
            ['name' => 'Charlatan', 'summary' => 'DEX/CON/CHA · Skilled Feat · Deception & Sleight of Hand · Forgery Kit', 'description' => 'A grifter, card sharp, and master of false personas.'],
            ['name' => 'Criminal', 'summary' => 'DEX/CON/INT · Alert Feat · Sleight of Hand & Stealth · Thieves’ Tools', 'description' => 'Operating on the wrong side of the law, cutting purses or running heists.'],
            ['name' => 'Entertainer', 'summary' => 'STR/DEX/CHA · Musician Feat · Acrobatics & Performance · Musical Instrument', 'description' => 'Roving fairs, carnivals, and high-society theatrical stages.'],
            ['name' => 'Farmer', 'summary' => 'STR/CON/WIS · Tough Feat · Animal Handling & Nature · Carpenter’s Tools', 'description' => 'Hardened by seasons of tilling soil and rearing livestock.'],
            ['name' => 'Guard', 'summary' => 'STR/INT/WIS · Alert Feat · Athletics & Perception · Gaming Set', 'description' => 'Keeping vigil at city gates, barracks posts, and patrol towers.'],
            ['name' => 'Guide', 'summary' => 'DEX/CON/WIS · Magic Initiate (Druid) · Stealth & Survival · Cartographer’s Tools', 'description' => 'Leading expeditions through uncharted wilderness and hostile biomes.'],
            ['name' => 'Hermit', 'summary' => 'CON/WIS/CHA · Healer Feat · Medicine & Religion · Herbalism Kit', 'description' => 'Quiet contemplation in a remote shrine, discovering cosmic truths.'],
            ['name' => 'Merchant', 'summary' => 'CON/INT/CHA · Lucky Feat · Animal Handling & Persuasion · Navigator’s Tools', 'description' => 'Caravans, trade tariffs, and maritime commerce.'],
            ['name' => 'Noble', 'summary' => 'STR/INT/CHA · Skilled Feat · History & Persuasion · Gaming Set', 'description' => 'High society, court diplomacy, and aristocratic privilege.'],
            ['name' => 'Sage', 'summary' => 'CON/INT/WIS · Magic Initiate (Wizard) · Arcana & History · Calligrapher’s Supplies', 'description' => 'Scholarly researcher scouring ancient libraries and arcane codices.'],
            ['name' => 'Sailor', 'summary' => 'STR/DEX/WIS · Tavern Brawler Feat · Acrobatics & Perception · Navigator’s Tools', 'description' => 'Hardened by ocean gales, shipboard brawls, and navigating coastlines.'],
            ['name' => 'Scribe', 'summary' => 'DEX/INT/WIS · Skilled Feat · Investigation & Perception · Calligrapher’s Supplies', 'description' => 'Precision recordkeeping in legal scriptoriums and royal chanceries.'],
            ['name' => 'Soldier', 'summary' => 'STR/DEX/CON · Savage Attacker Feat · Athletics & Intimidation · Gaming Set', 'description' => 'Trained in army ranks, marching under banners and holding the shield wall.'],
            ['name' => 'Wayfarer', 'summary' => 'DEX/WIS/CHA · Lucky Feat · Insight & Stealth · Thieves’ Tools', 'description' => 'Raised on street alleys and city rooftops, surviving by wit and agility.'],
        ];

        foreach ($backgrounds as $item) {
            CompendiumEntry::updateOrCreate(
                ['system_id' => $system->id, 'type' => 'background', 'name' => $item['name']],
                [
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'data' => [],
                    'is_srd' => true,
                ],
            );
        }

        // 11. Languages (2024 SRD)
        $languages = [
            ['name' => 'Common', 'summary' => 'Standard Language · Common Script · Spoken across all civilized realms', 'description' => 'The standard trading language spoken by almost all mortals.'],
            ['name' => 'Dwarvish', 'summary' => 'Standard Language · Runic Script · Spoken by Dwarves and smiths', 'description' => 'Full of hard consonants and guttural sounds.'],
            ['name' => 'Elvish', 'summary' => 'Standard Language · Elvish Script · Spoken by Elves, Bards, and Fey', 'description' => 'Fluid and subtle language of poetry and ancient magical songs.'],
            ['name' => 'Giant', 'summary' => 'Standard Language · Runic Script · Spoken by Goliaths, Ogres, and Giants', 'description' => 'Deep booming cadence derived from ancient giant runes.'],
            ['name' => 'Gnomish', 'summary' => 'Standard Language · Dwarvish Script · Spoken by Gnomes', 'description' => 'Rich in technical terminology and lively verbal humor.'],
            ['name' => 'Goblin', 'summary' => 'Standard Language · Dwarvish Script · Spoken by Goblins and Bugbears', 'description' => 'Short, clipped syllables favored in raiding camps.'],
            ['name' => 'Halfling', 'summary' => 'Standard Language · Common Script · Spoken by Halflings', 'description' => 'Filled with rustic idioms, oral history, and domestic comfort.'],
            ['name' => 'Orc', 'summary' => 'Standard Language · Dwarvish Script · Spoken by Orcs', 'description' => 'Harsh, guttural language suited for martial commands.'],
            ['name' => 'Abyssal', 'summary' => 'Exotic Language · Infernal Script · Spoken by Demons and chaotic fiends', 'description' => 'Barking, grating tongue of the infinite layers of the Abyss.'],
            ['name' => 'Celestial', 'summary' => 'Exotic Language · Celestial Script · Spoken by Angels and Devas', 'description' => 'Luminous, resonant language of the Upper Planes.'],
            ['name' => 'Draconic', 'summary' => 'Exotic Language · Draconic Script · Spoken by Dragons and Dragonborn', 'description' => 'One of the oldest tongues, used in arcane incantations.'],
            ['name' => 'Deep Speech', 'summary' => 'Exotic Language · No Alphabet · Spoken by Aberrations and Mind Flayers', 'description' => 'Alien telepathic tongue of the Far Realm.'],
            ['name' => 'Infernal', 'summary' => 'Exotic Language · Infernal Script · Spoken by Devils of the Nine Hells', 'description' => 'Rigid, contractual language of legalistic planar pacts.'],
            ['name' => 'Primordial', 'summary' => 'Exotic Language · Dwarvish Script · Spoken by Elementals, Genies', 'description' => 'Includes dialects of Aquan, Auran, Ignan, and Terran.'],
            ['name' => 'Sylvan', 'summary' => 'Exotic Language · Elvish Script · Spoken by Pixies, Dryads, and Fey creatures', 'description' => 'Enchanting language of nature spirits and the Feywild.'],
            ['name' => 'Undercommon', 'summary' => 'Exotic Language · Elvish Script · Spoken by Drow and Underdark denizens', 'description' => 'Trade language of the vast subterranean subterranean realms.'],
            ['name' => 'Druidic', 'summary' => 'Secret Language · Spoken exclusively by Druids', 'description' => 'A secret language of signs, glyphs, and natural metaphors known only to initiated druids.'],
            ['name' => "Thieves' Cant", 'summary' => "Secret Language · Spoken by Rogues and criminal syndicates", 'description' => 'A secret mix of dialect, argot, and code that allows you to hide messages in seemingly normal conversation.'],
        ];

        foreach ($languages as $item) {
            CompendiumEntry::updateOrCreate(
                ['system_id' => $system->id, 'type' => 'language', 'name' => $item['name']],
                [
                    'summary' => $item['summary'],
                    'description' => $item['description'],
                    'data' => [],
                    'is_srd' => true,
                ],
            );
        }
    }
}
